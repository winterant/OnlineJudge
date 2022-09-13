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

class QueryJudge0Result implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $solution_id;
    public function __construct($solution_id)
    {
        $this->solution_id = $solution_id;
    }

    /**
     * Execute the job.
     * 根据solution id，轮询judge0，更新判题结果
     *
     * @return void
     */
    public function handle()
    {
        $solution = DB::table('solutions')->select([
            'contest_id',
            'problem_id',
            'user_id',
            'judge0result',
        ])->find($this->solution_id);
        $problem = DB::table('problems')->select(['spj'])->find($solution->problem_id);
        $judge0result = json_decode($solution->judge0result, true);
        $tokens_str = implode(',', array_keys($judge0result));
        $fields_str = 'token,status_id,compile_output,stderr,message,time,memory,finished_at';
        if ($problem->spj)
            $fields_str .= ',stdout'; // spj需要拿到stdout，再去特判
        $res = send_get(
            config('app.JUDGE0_SERVER') . '/submissions/batch',
            ['tokens' => $tokens_str, 'base64_encoded' => 'true', 'fields' => $fields_str]
        );
        foreach (json_decode($res[1], true)['submissions'] as $fields) {
            $current = $this->decode_base64_judge0_submission($fields); // 本次查询到的结果
            $one = &$judge0result[$fields['token']];  // 数据库中保存的结果（未更新）
            $one = array_merge($one, $current);  // 把新结果$current合并到旧结果$one
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
            // ================== 若用户程序运行成功，则考虑特判其stdout
            $special_result_id = -1;
            if ($problem->spj && $one['status_id'] == 3) {
                // 1. 请求judge0，获取spj运行结果
                if (!isset($one['spj']['token'])) {
                    // 发送特判请求
                    $one['spj'] = $this->send_spj(
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
            // user stdout, judge0 token 不要保存到database
            unset($one['stdout']);
            unset($one['token']);
        }
        // 根据所有测试组，汇总出solution结果，并更新数据库
        $solution_result = $this->calculate_solution($judge0result);
        DB::table('solutions')->where('id', $this->solution_id)->update($solution_result);

        // 若判题还未结束，则持续更新
        if ($solution_result['result'] < 4) {
            usleep(800000); // sleeping for 800ms (800000us)
            dispatch(new QueryJudge0Result($this->solution_id));
        } else {
            // 判题结束，则刷新统计信息
            $this->update_submitted_count($solution->user_id, $solution->problem_id, $solution->contest_id);
        }
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

    // 根据所有测试组的结果，汇总出solution结果；No database
    private function calculate_solution($judge0result)
    {
        $solution = [
            'result' => 100,  // web端判题结果代号，初始无状态
            'time' => 0,
            'memory' => 0,
            'pass_rate' => 0,
            'error_info' => null,
            'wrong_data' => null,
            'judge0result' => $judge0result,
            'judge_time' => null,
        ];
        $num_tests = 0;    // 测试组数
        $num_ac_tests = 0; // 正确组数
        foreach ($judge0result as $s) {
            if ($s['result_id'] > 4) // 答案错误(或spj运行崩溃)
            {
                if (!$solution['error_info']) // 记录错误信息
                    $solution['error_info'] = $s['error_info'];
                if (!$solution['wrong_data']) // 记录出错数据文件名
                    $solution['wrong_data'] = $s['testname'] ?? null;
            }
            if ($s['result_id'] == 4) // AC
                $num_ac_tests++;
            else // 尚未AC，记录最小代号即可
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

    // 发起特判，return {token:*}; No database
    private function send_spj($solution_id, $problem_id, $testname, $user_stdout)
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
}
