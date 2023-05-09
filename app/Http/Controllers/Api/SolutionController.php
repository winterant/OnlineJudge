<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Judger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolutionController extends Controller
{
    // api：提交一份代码; Affect database
    public function submit_solution(Request $request)
    {
        //======================= 拦截非管理员的频繁提交 =================================
        /** @var \App\Models\User */
        $user = Auth::user();
        if (!$user->can('admin.solution.view')) {
            // 规定时间内，不允许多次提交
            $last_submit_time = DB::table('solutions')
                ->where('user_id', Auth::id())
                ->orderByDesc('submit_time')
                ->value('submit_time');
            if (time() - strtotime($last_submit_time) < intval(get_setting('submit_interval')))
                return [
                    'ok' => 0,
                    'msg' => trans('sentence.submit_frequently', ['sec' => get_setting('submit_interval')])
                ];

            // 编译错误，单独惩罚，规定时间内直接不允许提交！
            $last_ce_time = DB::table('solutions')
                ->where('user_id', Auth::id())
                ->where('result', 11) // 编译错误
                ->orderByDesc('submit_time')
                ->value('submit_time');
            if (time() - strtotime($last_ce_time) < intval(get_setting('compile_error_submit_interval')))
                return [
                    'ok' => 0,
                    'msg' => trans('sentence.submit_ce_frequently', [
                        'dt' => $last_ce_time,
                        'sec' => get_setting('compile_error_submit_interval')
                    ])
                ];
        }

        // ======================= 获取数据 =================================
        $data = $request->input('solution');  //获取前台提交的solution信息
        $problem = DB::table('problems')->find($data['pid']);  //找到题目

        // 判断提交的来源; 如果有cid，说明在竞赛中进行提交
        if (isset($data['cid'])) {
            $contest = DB::table("contests")->select('judge_type', 'allow_lang', 'end_time')->find($data['cid']);
            if ((($contest->allow_lang >> $data['language']) & 1) == 0) //使用了不允许的代码语言
                return [
                    'ok' => 0,
                    'msg' => 'Using a programming language that is not allowed!'
                ];
        } else { //else 从题库中进行提交，需要判断一下用户权限
            if ($problem->hidden == 1 && !$user->can('admin.solution.view'))
                return [
                    'ok' => 0,
                    'msg' => '该题目为私有题目，您没有权限提交'
                ];
        }

        // 如果是填空题，填充用户的答案
        if ($problem->type == 1) {
            $data['code'] = $problem->fill_in_blank;
            foreach ($request->input('filled') as $ans) {
                $data['code'] = preg_replace("/\?\?/", $ans, $data['code'], 1);
            }
        }

        //检测代码长度
        if (strlen($data['code']) < 3)
            return ['ok' => 0, 'msg' => '代码长度过短！'];
        if (strlen($data['code']) > 50000)
            return ['ok' => 0, 'msg' => '代码长度不能超过50000! 请适当缩减冗余代码。'];

        // 构造提交记录
        $solution = [
            'problem_id'    => $data['pid'],
            'contest_id'    => isset($data['cid']) ? $data['cid'] : -1,
            'user_id'       => Auth::id(),
            'result'        => 0,
            'language'      => ($data['language'] != null) ? $data['language'] : 0,
            'submit_time'   => date('Y-m-d H:i:s'),

            'judge_type'    => isset($contest->judge_type) ? $contest->judge_type : 'oi', //acm,oi

            'ip'            => ($guest_ip = get_client_real_ip()),
            'ip_loc'        => getIpAddress($guest_ip),
            'code_length'   => strlen($data['code']),
            'code'          => $data['code']
        ];

        //====================== 数据库初始化一条提交记录 ========================
        $solution['id'] = DB::table('solutions')->insertGetId($solution);

        // ===================== 使用任务队列 提交判题任务 =====================
        dispatch(new Judger($solution));

        // ===================== 给前台返回提交信息 ====================
        // if ($solution['contest_id'] > 0) //竞赛提交
        //     $redirect = route('contest.solutions', [$solution['contest_id'], 'user_id' => Auth::id(), 'group' => $request->input('group') ?? null]);
        // else
        //     $redirect = route('solutions', ['pid' => $solution['problem_id'], 'user_id' => Auth::id()]);

        return [
            'ok' => 1,
            'msg' => '您已提交代码，正在评测中...',
            'data' => [
                'solution_id' => $solution['id'],
                // 'redirect'  => $redirect,
                // 'judge0result' => $judge0result
            ]
        ];
    }

    // api 从数据库查询一条提交记录的判题结果; No update Database.
    public function solution_result($solution_id)
    {
        // ==================== 根据solution id，查询结果 ====================
        $solution = DB::table('solutions')->find($solution_id);
        if (!$solution)
            return ['ok' => 0, 'msg' => '提交记录不存在'];

        // 读取 所有测试数据的详细结果
        $judge_result = json_decode($solution->judge_result ?? '[]', true);
        ksort($judge_result); // 按key排序（为了解决mysql json类型乱序）
        foreach ($judge_result as $k => &$test) {
            $judge_result[$k]['result_desc'] = trans('result.' . config("judge.result." . $test['result'] ?? 0));
        }

        return [
            'ok' => 1,
            'msg' => 'OK',
            'data' => [
                'result' => $solution->result,
                'result_desc' => trans('result.' . config("judge.result." . $solution->result)),
                'time' => $solution->time,
                'memory' => $solution->memory,
                'error_info' => $solution->error_info,
                'details' => $judge_result
            ]
        ];
    }


    // ==================================== 以下废弃 ===================================

    // api 提交一条本地测试，仅运行返回结果。 No DB
    public function submit_local_test(Request $request)
    {
        //============================= 拦截非管理员的频繁提交 =================================
        /** @var \App\Models\User */
        $user = Auth::user();
        if (!$user->can('admin.solution.view')) {
            $last_submit_time = DB::table('solutions')
                ->where('user_id', Auth::id())
                ->orderByDesc('submit_time')
                ->value('submit_time');
            if (time() - strtotime($last_submit_time) < intval(get_setting('submit_interval')))
                return [
                    'ok' => 0,
                    'msg' => trans('sentence.submit_frequently', ['sec' => get_setting('submit_interval')])
                ];
        }

        //============================= 获取数据 =================================
        $data = $request->input('solution');        //获取前台提交的solution信息
        $problem = DB::table('problems')->find($data['pid']); //找到题目

        //如果是填空题，填充用户的答案
        if ($problem->type == 1) {
            $data['code'] = $problem->fill_in_blank;
            foreach ($request->input('filled') as $ans) {
                $data['code'] = preg_replace("/\?\?/", base64_decode($ans), $data['code'], 1);
            }
        }

        //============================== 使用judge0判题 ==========================
        $judge_response = $this->send_to_run_code(
            ($data['language'] != null) ? $data['language'] : 0, // 默认C
            $data['code'],
            $request->input('stdin'),
            $problem->time_limit,
            $problem->memory_limit,
            true
        );
        if ($judge_response[0] != 201)
            return ['ok' => 0, 'msg' => '[Run] Cannot connect to judge server: ' . $judge_response[0]];

        unset($judge_response[1]['token']); // 不给用户看到token
        return [
            'ok' => 1,
            'msg' => '运行完成',
            'data' => ['judge_result' => $judge_response[1]]
        ];
    }
}
