<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $solution = DB::table('solutions')->find($this->solution_id);
        $problem = DB::table('problems')->find($solution->problem_id);
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
                    $one['spj'] = $this->send_spj()[1]; // Send spj request and get submission token
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

    // 根据所有测试组的结果，汇总出solution结果
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

    // 发起特判，return {token:*}
    private function send_spj()
    {
        // todo spj
        $lang_id = 1;
        $data = [
            'language_id'     => config('oj.langJudge0Id.' . $lang_id),
            'source_code'     => base64_encode('#include<bits/stdc++.h>' . PHP_EOL . 'int main(){int *p=new int[9];std::cout<<"Yes";return 1;}'),
            'stdin'           => base64_encode('stdout'),
            'cpu_time_limit'  => 300, // S
            'memory_limit'    => 512000, // KB
            'max_file_size'   => 1024, // KB
            'enable_network'  => false,
        ];
        if ($lang_id == 1) //C++
            $data['compiler_options'] = "-O2 -std=c++17";
        $url = config('app.JUDGE0_SERVER') . '/submissions/?base64_encoded=true';
        $res = send_post($url, $data);
        $res[1] = json_decode($res[1], true);
        return $res;
    }
}
