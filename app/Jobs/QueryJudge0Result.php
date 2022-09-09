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
        $results = json_decode($res[1], true)['submissions']; // 读取提交记录的判题结果
        foreach ($results as $fields) {
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
                if (!isset($one['spj']['status_id'])) {
                    // $one['status_id'] = 2;  // 特判中
                    $one['status_id'] = 14;  // 特判中
                    $one['spj'] = ['status_id' => 14];
                    // todo 发起特判
                    // 只有
                } else if ($one['spj']['status_id'] < 3) {
                    // todo 查询特判结果，并更新父token的status_id,error_info(拼接)
                }
            }
            unset($one['stdout']);
            $one['result_id'] = config('oj.judge02result.' . $one['status_id']); // web端判题结果代号
            $one['result_desc'] = trans('result.' . config("oj.result." . $one["result_id"])); // 判题结果文字说明
        }
        // 更新数据库
        $solution_result = $this->calculate_solution($judge0result, $problem->spj);
        DB::table('solutions')->where('id', $this->solution_id)->update($solution_result);
        // 若判题还未结束，则持续更新
        if ($solution_result['result'] < 4) {
            usleep(500000); // sleeping for 500ms (500000us)
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
        $judge_result_id = 0; // Waiting
        $num_tests = 0;    // 测试组数
        $num_ac_tests = 0; // 正确组数
        $used_time = 0;    // MS
        $used_memory = 0;  // MB
        $finished_time = null;
        $error_info = null;
        $wrong_data = null;
        foreach ($judge0result as $s) {
            if ($spj) {
                $s['status_id'] = $s['spj']['status_id'] ?? 2; // 如有特判，则已特判结果为准,todo:能否标记出spj的错误呢？
            }

            if ($s['status_id'] == 3) {
                $num_ac_tests++;
            } else {
                $judge_result_id = max($judge_result_id, $s['status_id']);
                if ($s['status_id'] > 3) {
                    if (!$error_info) // 记录错误
                        $error_info = $s['error_info'];
                    if (!$wrong_data)
                        $wrong_data = $s['testname'] ?? null;
                }
            }
            $num_tests++;
            $used_time = max($used_time, $s['time']);
            $used_memory = max($used_memory, $s['memory']);
            if (!$finished_time)
                $finished_time = $s['finished_at'];
            else
                $finished_time = max($finished_time, $s['finished_at']);
        }
        if ($num_ac_tests == $num_tests) //All Accepted
            $judge_result_id = 3;
        $web_result = config('oj.judge02result.' . $judge_result_id); // judge0 status_id ==> web result id
        return [
            'result' => $web_result,
            'time' => $used_time,
            'memory' => $used_memory,
            'pass_rate' => $num_ac_tests * 1.0 / $num_tests,
            'error_info' => $error_info,
            'wrong_data' => $wrong_data,
            'judge0result' => $judge0result,
            'judge_time' => date('Y-m-d H:i:s', strtotime($finished_time))
        ];
    }
}
