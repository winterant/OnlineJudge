<?php

namespace App\Jobs;

use App\Http\Helpers\ProblemHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class Judger implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 最长执行时间 秒
    public $tries = 3;     // 最多尝试次数
    public $backoff = 5;   // 重试任务前等待的秒数

    private array $solution;
    private array $cachedIds;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $solution)
    {
        $this->onQueue('solutions');
        $this->solution = $solution;
        $this->cachedIds = [];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->judge(); // 若有异常会自动执行failed函数
        // 清除缓存
        $this->deleteCachedFiles();
    }

    private function judge()
    {
        // 更新solution结果为Queueing
        $this->update_db_solution(['result' => 1, 'judge_time' => date('Y-m-d H:i:s')]); // 队列中

        // 获取题目的相关属性
        $problem = (array)(DB::table('problems')
            ->select(['id', 'time_limit', 'memory_limit', 'spj', 'spj_language']) // MS,MB,int
            ->find($this->solution['problem_id']));

        // 获取编译运行指令
        $config = config('judge.language.' . $this->solution['language']);

        // 时间、空间的缩放
        $problem['time_limit'] = intval($problem['time_limit']) * $config['run']['limit_amplify']; // MS
        $problem['memory_limit'] = intval($problem['memory_limit']) * $config['run']['limit_amplify']; // MB

        // 编译
        if (!empty($config['compile'])) { // 需编译
            // 向JudgeServer发送请求 编译用户代码
            $res_compile = $this->compile($this->solution['code'], $config);
            if ($res_compile != null && $res_compile['status'] != 'Accepted') { // 编译失败
                $this->update_db_solution([
                    'result' => 11, // 编译错误 11
                    'error_info' => sprintf(
                        "[Compilation error] %s\n%s\n%s\n%s",
                        $res_compile['status'],
                        $res_compile['error'] ?? '',
                        $res_compile['files']['stdout'] ?? '',
                        $res_compile['files']['stderr'] ?? ''
                    ),
                    'pass_rate' => 0
                ]);
                return;
            }
        }

        // 向JudgeServer发送请求 编译spj（若有）
        if ($problem['spj']) {
            // 获取spj代码
            $spj_code = ProblemHelper::readSpj($problem['id']);
            if ($spj_code == null) {
                $this->update_db_solution(['result' => 14, 'error_info' => "[Special Judge Error] Special judge is enabled but the code is not provided.\n"]); // 系统错误 14
                return;
            }
            // 获取spj编译运行指令
            $spj_config = config("judge.language.{$problem['spj_language']}");
            if ($spj_config['compile'] ?? false) { // todo 优化：缓存编译好的特判，省去这步编译。spj.cpp修改时要重新编译。
                $res_compile_spj = $this->compile($spj_code, $spj_config);
                if ($res_compile_spj['status'] != 'Accepted') {
                    $this->update_db_solution(['result' => 14, 'error_info' => "[Special Judge Compile Error]\n" . $res_compile_spj['files']['stderr']]); // 系统错误 14
                    return;
                }
                $spj = [
                    $spj_config['compile']['compiled_filename'] =>
                    ['fileId' => $res_compile_spj['fileIds']['Main'] ?? null]
                ]; // go-judge文件格式
            } else { // spj无需编译
                $spj = [$spj_config['filename'] => ['content' => $spj_code]]; // go-judge文件格式
            }
        }

        // 向JudgeServer发送请求 运行代码（每组测试数据运行一次）
        $this->run(
            $problem,
            $config,
            empty($config['compile']) ? // 是否已编译
                [$config['filename'] => ['content' => $this->solution['code']]] :
                [$config['compile']['compiled_filename'] => ['fileId' => $res_compile['fileIds'][$config['compile']['compiled_filename']]]],
            $spj ?? null,
            $spj_config ?? null
        );
        // 结束
    }

    // 编译
    private function compile(string $code, array $config)
    {
        $this->update_db_solution(['result' => 2]); // 编译中
        // 要发送的数据
        $data = [
            'cmd' => [
                [
                    'args' => ['/bin/bash', '-c', $config['compile']['command']],
                    'env' => $config['env'],
                    'files' => [   // 指定 标准输入、标准输出和标准错误的文件
                        ['content' => ''],
                        ['name' => 'stdout', 'max' => 10240],
                        ['name' => 'stderr', 'max' => 10240],
                    ],
                    'cpuLimit' => $config['compile']['cpuLimit'],
                    'clockLimit' =>  $config['compile']['cpuLimit'] * 2,
                    'memoryLimit' => $config['compile']['memoryLimit'],
                    'procLimit' => $config['compile']['procLimit'],
                    'copyIn' => [
                        $config['filename'] => ['content' => $code],
                    ],
                    'copyOut' => ['stdout', 'stderr'],
                    'copyOutCached' => [$config['compile']['compiled_filename']],
                    // 'copyOutDir' => '1'
                ]
            ]
        ];

        $res = Http::timeout($this->timeout)->post(config('app.JUDGE_SERVER') . '/run', $data);
        if ($fid = ($res[0]['fileIds'][$config['compile']['compiled_filename']] ?? false))
            $this->cachedIds[] = $fid; // 记录缓存的文件id，最后清除
        return $res->json()[0];
    }

    // 运行评测
    private function run($problem, $config, $copyIn, $spj = null, $spj_config = null)
    {
        $solution = ['result' => 3, 'pass_rate' => 0, 'error_info' => '', 'time' => 0, 'memory' => 0];
        $this->update_db_solution($solution); // 运行中

        // 初始化所有测试点
        $tests = ProblemHelper::getTestDataFilenames($problem['id']);
        $judge_result = [];
        foreach ($tests as $k => $test)
            $judge_result[$k] = [
                'result' => 1, // 队列中
                'time' => 0,
                'memory' => 0,
            ];
        $this->update_db_solution(['judge_result' => $judge_result]); // 初始化测试点信息写入数据库，以供前台显示

        // 遍历测试点，运行用户程序，得到输出
        $ac = 0;
        $not_ac = 0;
        $max_time = 0;
        $max_memory = 0;
        $test_index = 0;
        foreach ($tests as $k => $test) {
            $test_index++;
            // 构造请求
            $data = ['cmd' => [
                [
                    'args' => ["/bin/bash", "-c", $config['run']['command']],
                    'env' => $config['env'],
                    'files' => [
                        ['src' => sprintf("/testdata/%d/test/%s", $problem['id'], $test['in'])],
                        ['name' => 'stdout', 'max' => $config['run']['stdoutMax']],
                        ['name' => 'stderr', 'max' => $config['run']['stderrMax']],
                    ],
                    'cpuLimit' => $problem['time_limit'] * 1000000, // ms ==> ns
                    'clockLimit' => $problem['time_limit'] * 1000000 * 4 + 1000000000, // *4+1s
                    'memoryLimit' => ($problem['memory_limit'] << 20), // MB ==> B
                    'stackLimit' => $config['run']['stackLimit'],
                    // 'strictMemoryLimit' => true,
                    'procLimit' => $config['run']['procLimit'],
                    'copyIn' =>  $copyIn,
                    'copyOut' => ['stdout', 'stderr'],
                    'copyOutCached' => ($problem['spj'] ? ['stdout'] : []), //copyOutCached中stdout会覆盖copyOut中stdout，故非特判时别缓存导致拿不到stdout原文
                ]
            ]];

            // 向判题服务发起请求
            $res = Http::timeout($this->timeout)->post(config('app.JUDGE_SERVER') . '/run', $data);
            $res = $res->json();
            if ($res[0]['fileIds']['stdout'] ?? false)
                $this->cachedIds[] = $res[0]['fileIds']['stdout']; // 记录缓存的文件id，最后清除

            // 接收到运行结果，分析运行结果，即答案评判
            if ($res[0]['status'] == 'Accepted') { // 运行成功，对比文件
                if ($problem['spj']) {
                    $std_in_path = sprintf("/testdata/%d/test/%s", $problem['id'], $test['in']);
                    $std_out_path = sprintf("/testdata/%d/test/%s", $problem['id'], $test['out']);
                    $ret = $this->special_judge($spj, $spj_config, $std_in_path, $std_out_path, $res[0]['fileIds']['stdout']);
                    $result = $ret['result'] ?? 14;
                    $error_info = $ret['error_info'] ?? '[Filed to run spj]';
                } else {
                    $std_out_path = testdata_path(sprintf('%d/test/%s', $problem['id'], $test['out']));
                    $ret = $this->diff_judge($std_out_path, $res[0]['files']['stdout']);
                    $result = $ret['result'];
                    $error_info = $ret['error_info'];
                }
            } else { // 运行出错
                $result = array_search($res[0]['status'], config('judge.result'));
                if ($result === false)
                    $result = 10; // RE
                $error_info = sprintf("[%s]\n%s\n%s\n", $res[0]['status'], $res[0]['files']['stderr'] ?? '', $res['error'] ?? '');
            }

            // 实时更新运行结果
            $judge_result[$k] = [
                'result' => $result,
                'time' => intdiv($res[0]['time'], 1000000), // ns==>ms
                'memory' => round($res[0]['memory'] / 1024 / 1024, 2), // B==>MB
                'error_info' => $error_info
            ];
            $this->update_db_solution(['judge_result' => $judge_result]); // 向数据库刷新测试点状态
            // 记录时间、内存
            $max_time = max($max_time, $judge_result[$k]['time']);
            $max_memory = max($max_memory, $judge_result[$k]['memory']);

            // 统计测试点对错情况
            if ($result == 4) {
                $ac++;
            } else {
                $not_ac++;
                if ($not_ac == 1) { // 首次遇到的错误作为本solution的错误
                    if (!empty($error_info))
                        $error_info = "[Test #{$test_index} {$k}.in]\n" . $error_info;
                    $solution['result'] = $result;
                    $solution['error_info'] = $error_info;
                    $solution['wrong_data'] = $k;
                }
                // 如果是acm模式，遇到错误，直接终止
                if ($this->solution['judge_type'] == 'acm') {
                    // 剩余评测点标记为放弃评测
                    foreach ($tests as $kk => $test2)
                        if ($judge_result[$kk]['result'] < 4)
                            $judge_result[$kk]['result'] = 13; // 跳过
                    $solution['judge_result'] = $judge_result;
                    break;
                }
            }
        }
        if ($ac == 0 && $not_ac == 0) { // 没有测试数据
            $solution['result'] = 14;   // 系统错误
            $solution['error_info'] = 'There is no test data, please contact the administrator to add test data.';
        } else { // 记录下通过率和结果
            $solution['pass_rate'] = $ac / count($tests);
            $solution['time'] = $max_time;
            $solution['memory'] = $max_memory;
            if ($not_ac == 0) // 该solution完全正确
            {
                $solution['result'] = 4;
                dispatch(new CodeReviewer($this->solution['id']));
            }
        }
        $this->update_db_solution($solution); // 更新判题结果
    }

    // 特判
    private function special_judge($spj, $spj_config, $std_in_path, $std_out_path, $user_out_file_id)
    {
        $data = ['cmd' => [[
            'args' => ["/bin/bash", "-c", $spj_config['run']['command'] . " std.in std.out user.out"],
            'env' => $spj_config['env'],
            'files' => [
                ['content' => ''],
                ['name' => 'stdout', 'max' => 10240],
                ['name' => 'stderr', 'max' => 10240],
            ],
            'cpuLimit' => 60000000000, // 60s ==> ns
            'clockLimit' => 300000000000, // 300s
            'memoryLimit' => 2048 << 20, // 2048MB ==> B
            'stackLimit' => $spj_config['run']['stackLimit'],
            'procLimit' => $spj_config['run']['procLimit'],
            'copyIn' => array_merge($spj, [
                'std.in' => ['src' => $std_in_path],
                'std.out' => ['src' => $std_out_path],
                'user.out' => ['fileId' => $user_out_file_id]
            ]),
            'copyOut' => ['stdout', 'stderr'],
        ]]];

        // 向判题服务发起请求
        $res = Http::timeout($this->timeout)->post(config('app.JUDGE_SERVER') . '/run', $data);
        return [
            'result' => $res[0]['exitStatus'] == 0 ? 4 : 6,
            'error_info' => "[Special Judge Runtime Error]\n" . implode("\n", array_values($res[0]['files']))
        ];
    }

    // 文本对比
    private function diff_judge($std_out_path, $user_out)
    {
        $result = 4; // 默认AC，下面检查是否出错
        $msg = '';

        // 按行分割
        $std_out = file_get_contents($std_out_path);
        $std_out = explode("\n", trim($std_out));
        $user_out = explode("\n", trim($user_out));
        $count_lines = count($std_out);

        // 检查行数
        if ($count_lines != count($user_out)) {
            $result = 6; // WA 行数不一致，必错
            $msg = "Inconsistent number of rows";
        } else {
            // 逐行检查
            for ($i = 0; $i < $count_lines; $i++) {
                $user_line = trim($user_out[$i], "\r\n");
                $answer_line = trim($std_out[$i], "\r\n");
                if (strcmp($user_line, $answer_line) != 0) { // 内容不一致
                    if (strcmp(trim($user_line), trim($answer_line)) != 0) {
                        $result = 6; // WA 可见字符不一致
                    } else {
                        $result = 5; // PE 空白符不一致
                    }
                    $user_line = strlen($user_line) <= 60 ? $user_line : substr($user_line, 0, 60) . '...(Too long to display)';
                    $user_line=preg_replace('/[^\x{0000}-\x{007F}\x{0080}-\x{07FF}\x{0800}-\x{FFFF}]/u', '', $user_line);
                    $answer_line = strlen($answer_line) <= 60 ? $answer_line : substr($answer_line, 0, 60) . '...(Too long to display)';
                    $msg = sprintf("[Test %s] Wrong answer on line %d\n", pathinfo($std_out_path, PATHINFO_BASENAME), $i + 1);
                    $msg .= sprintf("Yours:\n%s\n", ($user_line));
                    $msg .= sprintf("Correct:\n%s\n", $answer_line);
                }
            }
        }
        return ['result' => $result, 'error_info' => $msg];
    }

    // 将判题结果写入数据库
    private function update_db_solution($values)
    {
        DB::table('solutions')->where('id', $this->solution['id'])->update($values);
    }

    // 清除所有judge server中的缓存文件
    private function deleteCachedFiles(): void
    {
        foreach ($this->cachedIds as $id) {
            Http::delete(config('app.JUDGE_SERVER') . '/file/' . $id);
        }
    }

    /**
     * 处理失败作业
     */
    public function failed(Throwable $exception): void
    {
        Log::error($exception);
        Log::error(json_encode($this->solution));
        Log::error("Judge Failed. Updating DB and deleting cached files...");
        $this->update_db_solution(['result' => 14, 'error_info' => '[Judger Error] Something went wrong when executing the job.']);
        $this->deleteCachedFiles();
    }
}
