<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Judger implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $solution_id;
    private $judge0result;
    private $tokens_tobe_query;
    public function __construct($solution_id)
    {
        $this->solution_id = $solution_id;
        $this->judge0result = [];
        $this->tokens_tobe_query = [];
    }

    /**
     * Execute the job.
     * 根据solution id，发起判题请求
     * 随后轮询judge0，更新判题结果
     *
     * @return void
     */
    public function handle()
    {
        $this->update_db_solution(['result' => 1]); // Queueing

        // 1. 获取提交记录的属性
        $solution = DB::table('solutions')
            ->select(['contest_id', 'problem_id', 'user_id', 'language', 'code'])
            ->find($this->solution_id);
        $problem = DB::table('problems')
            ->select(['time_limit', 'memory_limit', 'spj'])
            ->find($solution->problem_id);

        // 2. 向judge0发送判题请求
        $judge0response = $this->send_solution_to_judge0(
            $solution->problem_id,
            $solution->language,
            $solution->code,
            $problem->time_limit,
            $problem->memory_limit,
            $problem->spj   // if spj is true, 则不对比标准答案
        );
        if (intdiv($judge0response[0], 10) == 20) {
            foreach ($judge0response[1] as $res) {
                $token = $res['token'];
                unset($res['token']);
                $this->judge0result[$token] = $res;
                $this->tokens_tobe_query[] = $token;
            }
            $this->update_db_solution([
                'result' => 1, // Queueing
                'judge0result' => $this->judge0result
            ]);
        } else {
            $error = '[Judger] ' . $judge0response[0] . ' | ' . $judge0response[1]['error'];
            if($judge0response[0] == 0){
                $error.='\njudge0服务器因压力过大崩溃';
            }
            $this->update_db_solution([
                'result' => 14, // System Error
                'error_info' => $error
            ]);
            return; // Over
        }

        // 3. 轮训查询判题结果，如需特判将会发起特判。全部运行完成后，退出轮训
        while (count($this->tokens_tobe_query) > 0) {
            $solution_result = $this->query_judge0_result($solution, $problem->spj);
            $this->update_db_solution($solution_result);
            foreach ($this->judge0result as $token => $one) // 从待查列表中清除已经运行完成的token
                if ($one['result_id'] >= 4 && ($k = array_search($token, $this->tokens_tobe_query)) !== false)
                    unset($this->tokens_tobe_query[$k]);
            usleep(400000); // us
        }

        // 4. 根据结果刷新过题数信息
        $this->update_submitted_count($solution->user_id, $solution->problem_id, $solution->contest_id);
    }

    // 向jduge0发送判题请求
    private function send_solution_to_judge0($problem_id, $lang_id, $code, $time_limit_ms, $memory_limit_mb, $is_spj)
    {
        set_time_limit(600); // 秒
        ini_set('memory_limit', '2G');
        $prev_time = time(); // 用于计时，每秒刷新数据库
        $judge0result = [];  // 收集jduge0结果

        // 1. 扫描测试数据，保存于samples_path
        $samples_path = [];
        $temp_map = [];
        $dir = testdata_path($problem_id . '/test');
        foreach (readAllFilesPath($dir) as $item) {
            $name = pathinfo($item, PATHINFO_FILENAME);  //文件名
            $ext = pathinfo($item, PATHINFO_EXTENSION);  //拓展名
            if ($ext === 'in')
                $temp_map[$name]['in'] = $item;
            if ($ext === 'out' || $ext === 'ans')
                $temp_map[$name]['out'] = $item;
            if (count($temp_map[$name]) == 2)
                $samples_path[$name] = $temp_map[$name];
        }

        // 2. 遍历测试数据，发送判题请求
        foreach ($samples_path as $sample) {
            $data = [
                'language_id'     => config('oj.langJudge0Id.' . $lang_id),
                'source_code'     => base64_encode($code),
                'stdin'           => base64_encode(file_get_contents($sample['in'])),
                'cpu_time_limit'  => $time_limit_ms / 1000.0, //convert to S
                'memory_limit'    => $memory_limit_mb * 1024, //convert to KB
                'max_file_size'   => max(
                    intval(filesize($sample['out']) / 1024) * 2 + 64, //convert B to KB and double add 64KB
                    64 // at least 64KB
                ),
                'enable_network'  => false
            ];
            if ($lang_id == 1) //C++
                $data['compiler_options'] = "-O2 -std=c++17";
            if (!$is_spj) // 非特判，则严格对比答案
                $data['expected_output'] = base64_encode(file_get_contents($sample['out']));

            // 向judge0发送判题请求
            $res = send_post(config('app.JUDGE0_SERVER') . '/submissions?base64_encoded=true', $data);

            // 解析judge0返回token
            if (intdiv($res[0], 10) == 20) {
                $res[1] = json_decode($res[1], true);
                $res[1] = $this->decode_base64_judge0_submission($res[1]);
                // 收集判题token
                $judge0result[] = [
                    'token' => $res[1]['token'],
                    'testname' => basename($sample['in'], '.in')
                ];
                // 如果超过1s，则及时更新一下数据库solutions表
                if (time() - $prev_time >= 1) {
                    $prev_time = time();
                    $this->update_db_solution(['judge0result' => $judge0result]);
                }
            } else {
                return [$res[0], ['error' => 'Failed to send data and code to judge0. ' . $res[1]]];
            }
        }
        if (count($judge0result) == 0)
            return [502, ['error' => 'The problem has no testdata!']];
        return [201, $judge0result];
    }

    public function query_judge0_result($solution, $is_spj)
    {
        // 1. 向judge0查询运行结果
        $fields_str = 'token,status_id,time,memory,finished_at,compile_output,stderr,message';
        $res = send_get(
            config('app.JUDGE0_SERVER') . '/submissions/batch',
            [
                'tokens' => implode(',', $this->tokens_tobe_query),
                'base64_encoded' => 'true',
                'fields' => $fields_str . ($is_spj ? ',stdout' : null) // spj需要拿到stdout，再去特判
            ]
        );

        // 2. 解析jduge0运行结果，分析结果
        $submissions = json_decode($res[1], true)['submissions'];
        foreach ($submissions as $fields) {
            $one = &$this->judge0result[$fields['token']]; // 取到历史查询结果
            $current = $this->decode_base64_judge0_submission($fields); // 解析本次查询到的结果
            $one = array_merge($one ?? [], $current);  // 把新结果$current合并到历史结果$one
            // $one == array (
            //     'spj' => []    // 如果第一次运行到这，是没有的
            //     'time' => 4.0,
            //     'token' => '20297a7a-eecc-4495-9a90-967a882ba14c',
            //     'memory' => 1.0,
            //     'stdout' => '',  // if spj 则有此项
            //     'testname' => 'a',
            //     'status_id' => 3,
            //     'error_info' => '',
            //     'finished_at' => '2022-09-08T10:10:51.185Z',
            // );

            // ================== 若用户程序运行成功，则考虑特判其stdout ===============
            $special_result_id = -1;
            if ($is_spj && $one['status_id'] == 3) {
                // 1. 请求judge0，获取spj运行结果
                if (!isset($one['spj']['token'])) {
                    // 发送特判请求
                    $one['spj'] = $this->send_spj_to_judge0(
                        $this->solution_id,
                        $solution->problem_id,
                        $one['testname'],
                        $one['stdout']
                    )[1]; // Send spj request and get submission token
                    $one['spj']['status_id'] = 2; // Set initial status_id to Running
                } else if ($one['spj']['status_id'] < 3) {
                    // 查询特判结果
                    $res_spj = send_get(
                        config('app.JUDGE0_SERVER') . '/submissions/' . $one['spj']['token'],
                        [
                            'base64_encoded' => 'true',
                            'fields' => 'token,status_id,status,exit_code,compile_output,stderr,message,stdout'
                        ]
                    );
                    $one['spj'] = json_decode($res_spj[1], true);
                    $one['spj'] = $this->decode_base64_judge0_submission($one['spj']);
                }
                // 2. 根据spj结果，标记本组测试两种特殊情况(web result_id)：3:judging, 5:user wrong answer
                if ($one['spj']['status_id'] < 3) // spj judging
                    $special_result_id = 3; // web shows judging
                else if ($one['spj']['exit_code'] == 1) // spj completed but wrong answer.
                {
                    $special_result_id = 6; // web shows wrong answer
                    $one['error_info'] .= $one['spj']['stdout'] ?? null;
                } else if ($one['spj']['status_id'] > 3) // spj runtime error
                {
                    $special_result_id = 14; // web shows system error
                    $one['error_info'] .= '[Special Judge Error]' . PHP_EOL;
                    $one['error_info'] .= $one['spj']['status']['description'] . PHP_EOL;
                    $one['error_info'] .= $one['spj']['error_info'] ?? null;
                }
            }
            if ($special_result_id != -1)  // spj 特殊结果
                $one['result_id'] = $special_result_id;
            else
                $one['result_id'] = config('oj.judge02result.' . $one['status_id']);
            // user stdout, user judge0 token 没必要保存到database
            unset($one['stdout']);
            unset($one['token']);
        }

        // 3. 根据所有测试组运行结果，汇总出solution结果
        $solution = [
            'result' => 100,  // web端判题结果代号，初始无状态
            'time' => 0,
            'memory' => 0,
            'pass_rate' => 0,
            'error_info' => null,
            'wrong_data' => null,
            'judge0result' => $this->judge0result,
            'judge_time' => null,
        ];
        $num_tests = 0;    // 测试组数
        $num_ac_tests = 0; // 正确组数
        foreach ($this->judge0result as $s) {
            if ($s['result_id'] > 4) // 答案错误(或spj运行崩溃)，记录错误信息
            {
                if (!$solution['error_info']) // 记录错误信息
                    $solution['error_info'] = $s['error_info'];
                if (!$solution['wrong_data']) // 记录出错数据文件名
                    $solution['wrong_data'] = $s['testname'] ?? null;
            }

            if ($s['result_id'] == 4) // AC
                $num_ac_tests++;
            else // 尚未AC，以最小结果代号为准
                $solution['result'] = min($solution['result'], $s['result_id']);
            $num_tests++;
            $solution['time'] = max($solution['time'], $s['time']);
            $solution['memory'] = max($solution['memory'], $s['memory']);
            $solution['judge_time'] = max($solution['judge_time'], date('Y-m-d H:i:s', strtotime($s['finished_at'])));
        }
        if ($num_ac_tests == $num_tests) // All Accepted
            $solution['result'] = 4;
        $solution['pass_rate'] = $num_ac_tests * 1.0 / $num_tests;
        return $solution;
    }


    // 发起特判，return {token:*};
    private function send_spj_to_judge0($solution_id, $problem_id, $testname, $user_stdout)
    {
        $testfilename = Storage::path(sprintf('data/%s/test/%s', $problem_id, $testname));
        $testfilein = $testfilename . '.in';
        $testfileout = $testfilename . (file_exists($testfilename . '.out') ?  '.out' : '.ans');
        $spj_file = Storage::path(sprintf('data/%s/spj/spj.cpp', $problem_id));

        $zip_dirname = sprintf('solution_spj/%s_%s', $solution_id, $testname);
        Storage::makeDirectory($zip_dirname);
        $zip_file = Storage::path($zip_dirname . '/spj.zip');

        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFile($testfilein, 'std.in');
        $zip->addFile($testfileout, 'std.out');
        $zip->addFromString('user.out', $user_stdout);
        $zip->addFile($spj_file, 'spj.cpp');
        $zip->addFromString('compile', 'g++ spj.cpp -O2 -std=c++17 -o spj');
        $zip->addFromString('run', './spj std.in std.out user.out');
        $zip->close();

        // 向判题端发送数据
        $post_data = [
            'language_id'      => 89,
            'additional_files' => base64_encode(file_get_contents($zip_file)),
            'cpu_time_limit'  => 300,    // S
            'memory_limit'    => 512000, // KB
            'max_file_size'   => 1024,   // KB
            'enable_network'  => false,
        ];
        $res = send_post(config('app.JUDGE0_SERVER') . '/submissions?base64_encoded=true', $post_data);
        $res[1] = json_decode($res[1], true);

        Storage::deleteDirectory($zip_dirname); // 删除临时文件夹
        return $res;
    }

    // 将judge0查询结果(base64)解码， 并汇总报错信息为error_info字段
    private function decode_base64_judge0_submission($s)
    {
        if (isset($s['message'])) $s['message'] = base64_decode($s['message']);
        if (isset($s['compile_output'])) $s['compile_output'] = base64_decode($s['compile_output']);
        if (isset($s['stderr'])) $s['stderr'] = base64_decode($s['stderr']);
        if (isset($s['stdout'])) $s['stdout'] = base64_decode($s['stdout']);
        if (isset($s['time'])) $s['time'] *= 1000;  // convert to MS for lduoj_web
        if (isset($s['memory'])) $s['memory'] = round($s['memory'] / 1024.0, 2); // convert to MB for lduoj_web
        $s['error_info'] = implode(PHP_EOL, array_filter([
            $s['message'] ?? null,
            $s['compile_output'] ?? null,
            $s['stderr'] ?? null,
        ]));
        unset($s['message']);
        unset($s['compile_output']);
        unset($s['stderr']);
        return $s;
    }

    // 更新过题数
    private function update_submitted_count($user_id, $problem_id, $contest_id)
    {
        // todo 改为自增，而不是查表
        // users
        $accepted = DB::table('solutions')
            ->where('result', 4)
            ->where('user_id', $user_id)
            ->select([
                DB::raw('count(distinct problem_id) as solved'),
                DB::raw('count(*) as accepted')
            ])
            ->first();
        $total = DB::table('solutions')->where('user_id', $user_id)->first(DB::raw('count(*) as submitted'));
        DB::table('users')->where('id', $user_id)
            ->update([
                'solved' => $accepted->solved,
                'accepted' => $accepted->accepted,
                'submitted' => $total->submitted
            ]);
        // problems
        $accepted = DB::table('solutions')
            ->where('result', 4)
            ->where('problem_id', $problem_id)
            ->select([
                DB::raw('count(distinct user_id) as solved'),
                DB::raw('count(*) as accepted')
            ])
            ->first();
        $total = DB::table('solutions')->where('problem_id', $problem_id)->first(DB::raw('count(*) as submitted'));
        DB::table('problems')->where('id', $problem_id)
            ->update([
                'solved' => $accepted->solved,
                'accepted' => $accepted->accepted,
                'submitted' => $total->submitted
            ]);
        // contest_problem
        if ($contest_id > 0) {
            $accepted = DB::table('solutions')
                ->where('result', 4)
                ->where('problem_id', $problem_id)
                ->where('contest_id', $contest_id)
                ->select([
                    DB::raw('count(distinct user_id) as solved'),
                    DB::raw('count(*) as accepted')
                ])
                ->first();
            $total = DB::table('solutions')
                ->where('contest_id', $contest_id)
                ->where('problem_id', $problem_id)
                ->first(DB::raw('count(*) as submitted'));
            DB::table('contest_problems')
                ->where('contest_id',  $contest_id)
                ->where('problem_id', $problem_id)
                ->update([
                    'solved' => $accepted->solved,
                    'accepted' => $accepted->accepted,
                    'submitted' => $total->submitted
                ]);
        }
    }

    // 更新solution
    private function update_db_solution($data)
    {
        DB::table('solutions')->where('id', $this->solution_id)->update($data);
    }
}
