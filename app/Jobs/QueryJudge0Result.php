<?php

namespace App\Jobs;

use App\Http\Controllers\Api\SolutionController;
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
            if ($problem->spj && $one['status_id'] == 3) // 用户程序已经运行完成，考虑特判
            {
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
                            'fields' => 'token,status_id,exit_code,compile_output,stderr,message,stdout'
                        ]
                    );
                    $one['spj'] = json_decode($res_spj[1], true);
                    $one['spj'] = $this->decode_base64_judge0_submission($one['spj']);
                }
            }
            if ($problem->spj) {
                $status_id = $one['status_id'];
                if ($status_id == 3) {
                    if (($one['spj']['exit_code'] ?? 0) > 0)
                        $status_id = 4; // Wrong answer
                    else
                        $status_id = $one['spj']['status_id'];
                }
                $one['result_id'] = config('oj.judge02result.' . $status_id); // web端判题结果代号
                $one['result_desc'] = config("oj.result." . $one["result_id"]); // 判题结果文字说明   
            } else {
                $one['result_id'] = config('oj.judge02result.' . $one['status_id']); // web端判题结果代号
                $one['result_desc'] = config("oj.result." . $one["result_id"]); // 判题结果文字说明   
            }

            unset($one['stdout']);
            unset($one['token']);
        }
        // 更新数据库
        $solution_result = $this->calculate_solution($judge0result, $problem->spj);
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

    private function calculate_solution($judge0result, $spj = false)
    {
        $judge0_status_id = 1000; // 无状态
        $num_tests = 0;    // 测试组数
        $num_ac_tests = 0; // 正确组数
        $used_time = 0;    // MS
        $used_memory = 0;  // MB
        $finished_time = null;
        $error_info = null;
        $wrong_data = null;
        foreach ($judge0result as $s) {
            if ($spj) {
                $spj_status_id = $s['spj']['status_id'] ?? 0; // 如有特判，则以特判结果为准
                if ($s['status_id'] < 3 || $s['status_id'] == 3 && $spj_status_id < 3) {
                    // running
                } else if ($s['status_id'] == 3 && ($spj_status_id == 3 || $s['spj']['exit_code'] > 0)) {
                    // Both user and spj run completed.
                    if ($s['spj']['exit_code'] == 0) // AC only if exit code of spj is 0.
                        $num_ac_tests++;
                    else {
                        $spj_status_id = 4; // Wrong Answer
                        if (!$error_info) // 记录错误
                            $error_info = implode(PHP_EOL, array_filter([
                                '[Special judge description]',
                                ($s['spj']['stdout'] ?? null),
                                ($s['spj']['error_info'] ?? null)
                            ]));
                    }
                } else if ($s['status_id'] == 3 && $spj_status_id > 3) {
                    // spj runtime error
                    if (!$error_info) // 记录错误
                        $error_info = implode(PHP_EOL, array_filter([
                            '[Special judge runtime error]',
                            'This is because the spj program crashed abnormally during running.',
                            ($s['spj']['error_info'] ?? null)
                        ]));
                } else {
                    // user runtime error
                    if (!$error_info) // 记录错误
                        $error_info = $s['error_info'];
                    if (!$wrong_data) // 记录出错数据文件名
                        $wrong_data = $s['testname'] ?? null;
                }

                // 讨论整条solution所处的状态
                $temp_status_id = $s['status_id'] == 3 ? $spj_status_id : $s['status_id'];
                if ($temp_status_id != 3)
                    $judge0_status_id = min($judge0_status_id, $temp_status_id);
            } else {
                if ($s['status_id'] < 3) {
                    // running
                } else if ($s['status_id'] == 3) {
                    // AC
                    $num_ac_tests++;
                } else {
                    // runtime error
                    if (!$error_info) // 记录错误
                        $error_info = $s['error_info'];
                    if (!$wrong_data) // 记录出错数据文件名
                        $wrong_data = $s['testname'] ?? null;
                }

                // 讨论整条solution所处的状态
                if ($s['status_id'] != 3)
                    $judge0_status_id = min($judge0_status_id, $s['status_id']);
            } // end of non-spj.

            $num_tests++;
            $used_time = max($used_time, $s['time']);
            $used_memory = max($used_memory, $s['memory']);
            $finished_time = max($finished_time, date('Y-m-d H:i:s', strtotime($s['finished_at'])));
        }
        if ($num_ac_tests == $num_tests) // All Accepted
            $judge0_status_id = 3;
        return [
            'result' => config('oj.judge02result.' . $judge0_status_id), // judge0 status_id => web result id
            'time' => $used_time,
            'memory' => $used_memory,
            'pass_rate' => $num_ac_tests * 1.0 / $num_tests,
            'error_info' => $error_info,
            'wrong_data' => $wrong_data,
            'judge0result' => $judge0result,
            'judge_time' => $finished_time
        ];
    }

    // 发起特判，return {token:*}
    private function send_spj()
    {
        // todo spj
        $lang_id = 1;
        $data = [
            'language_id'     => config('oj.langJudge0Id.' . $lang_id),
            'source_code'     => base64_encode('#include<bits/stdc++.h>' . PHP_EOL . 'int main(){std::cout<<"Yes";return 0;}'),
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
