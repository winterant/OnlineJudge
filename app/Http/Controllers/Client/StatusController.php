<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Api\SolutionController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!isset($_GET['sim_rate']))
            $_GET['inc_contest'] = 'on';
        //用户名模糊查询: 找出符合条件的用户
        $users = null;
        if (isset($_GET['username']) && $_GET['username'] != '') {
            $query = DB::table('users')
                ->select('id', 'username', 'nick')
                ->where('username', 'like', '%' . $_GET['username'] . '%')
                ->get();
            foreach ($query as $u)
                $users[$u->id] = $u;
        }
        //读取提交记录
        $solutions = DB::table('solutions')
            ->select(
                'id',
                'contest_id',
                'problem_id',
                'user_id',
                'result',
                'time',
                'memory',
                'language',
                'submit_time',
                'judge_type',
                'pass_rate',
                'judger',
                'sim_rate',
                'sim_sid',
                'ip',
                'ip_loc',
                'judge0result'
            )
            //普通用户只能查看非竞赛提交
            //关闭“包含竞赛”按钮时只能查看非竞赛提交
            ->when(!privilege('admin.problem.solution') || !isset($_GET['inc_contest']), function ($q) {
                return $q->where('solutions.contest_id', -1);
            })

            ->when(isset($_GET['sim_rate']) && $_GET['sim_rate'] != 0, function ($q) {
                return $q->where('sim_rate', '>=', $_GET['sim_rate']);
            })
            ->when(isset($_GET['sid']) && $_GET['sid'] != '', function ($q) {
                return $q->where('solutions.id', $_GET['sid']);
            })
            ->when(isset($_GET['pid']) && $_GET['pid'] != '', function ($q) {
                return $q->where('solutions.problem_id', $_GET['pid']);
            })
            ->when($users != null, function ($q) use ($users) {
                return $q->whereIn('user_id', array_keys($users));
            })
            ->when(isset($_GET['result']) && $_GET['result'] != '-1', function ($q) {
                return $q->where('result', $_GET['result']);
            })
            ->when(isset($_GET['language']) && $_GET['language'] != '-1', function ($q) {
                return $q->where('language', $_GET['language']);
            })
            ->when(isset($_GET['ip']) && $_GET['ip'] != '', function ($q) {
                return $q->where('ip', $_GET['ip']);
            })
            ->orderByDesc('solutions.id')
            ->paginate(10);

        foreach ($solutions as $s) {
            // ======== 处理显示信息 ==========
            $u = DB::table('users')->find($s->user_id);
            //所有人都能看到用户名
            $s->username = $u->username ?? null;
            //管理员能看到昵称、ip及其属地
            if (privilege('admin.problem.solution')) {
                $s->nick = $u->nick ?? null;
            } else {
                // 非管理员，抹掉ip信息
                $s->ip = '-';
                $s->ip_loc = '';
            }
        }
        return view('client.status', compact('solutions'));
    }

    // 状态页面使用ajax实时更新题目的判题结果 TODO Delete this function
    // public function ajax_get_status(Request $request)
    // {
    //     if ($request->isMethod('post')) {
    //         $sids = $request->input('sids');
    //         $solutions = DB::table('solutions')
    //             ->select(['id', 'judge_type', 'result', 'time', 'memory', 'pass_rate'])
    //             ->whereIn('id', $sids)->get();
    //         $ret = [];
    //         foreach ($solutions as $item) {
    //             $ret[] = [
    //                 'id' => $item->id,
    //                 'result' => $item->result,
    //                 'color' => null,
    //                 'text' => trans('result.' . config('oj.result.' . $item->result))
    //                     . ($item->judge_type == 'oi' && $item->result >= 5 && $item->result <= 10 ? sprintf(' (%s%%)', round($item->pass_rate * 100)) : null),
    //                 'time' => $item->time . 'MS',
    //                 'memory' => round($item->memory, 2) . 'MB'
    //             ];
    //         }
    //         return json_encode($ret);
    //     }
    //     return json_encode([]);
    // }

    // web 查看一条提交记录
    public function solution($id)
    {
        $solution = DB::table('solutions')
            ->join('users', 'solutions.user_id', '=', 'users.id')
            ->leftJoin('contest_problems', function ($q) {
                $q->on('solutions.contest_id', '=', 'contest_problems.contest_id')->on('solutions.problem_id', '=', 'contest_problems.problem_id');
            })
            ->select([
                'solutions.id', 'solutions.problem_id', 'index', 'solutions.contest_id', 'user_id', 'username',
                'result', 'pass_rate', 'time', 'memory', 'judge_type', 'submit_time', 'judge_time',
                'code', 'code_length', 'language', 'error_info', 'wrong_data'
            ])
            ->where('solutions.id', $id)->first();
        if (
            privilege('admin.problem.solution') ||
            (Auth::id() == $solution->user_id && $solution->submit_time > Auth::user()->created_at)
        )
            return view('client.solution', compact('solution'));
        return view('client.fail', ['msg' => trans('sentence.Permission denied')]);
    }

    // web 读取出错数据
    public function solution_wrong_data($id, $type)
    {
        $solution = DB::table('solutions')
            ->leftJoin('contests', 'solutions.contest_id', '=', 'contests.id')  //非必须，left
            ->select('solutions.problem_id', 'solutions.user_id', 'contests.end_time', 'solutions.wrong_data')
            ->where('solutions.id', $id)
            ->first();
        if (!$solution || !$solution->wrong_data)
            return view('client.fail', ['msg' => '没有记录出错数据']);
        $allow_get = false;
        if (privilege('admin.problem.solution')) // 管理员可以直接看
            $allow_get = true;
        else if (Auth::id() == $solution->user_id) // 普通用户
        {
            if ($solution->end_time && date('Y-m-d H:i:s') < $solution->end_time) // 比赛未结束
                return view('client.fail', ['msg' => trans('sentence.not_end')]);
            $allow_get = true;
        }
        if ($allow_get) {
            if ($type == 'in')
                $text = file_get_contents(testdata_path($solution->problem_id . '/test/' . $solution->wrong_data . '.in'));
            else if (file_exists(testdata_path($solution->problem_id . '/test/' . $solution->wrong_data . '.out')))
                $text = file_get_contents(testdata_path($solution->problem_id . '/test/' . $solution->wrong_data . '.out'));
            else
                $text = file_get_contents(testdata_path($solution->problem_id . '/test/' . $solution->wrong_data . '.ans'));
            return view('client.solution_wrong_data', compact('text'));
        }
        return view('client.fail', ['msg' => trans('sentence.Permission denied')]);
    }

    //将用户解决方案提交到数据库
    // public function submit_solution(Request $request)
    // {
    //     //============================= 拦截非管理员的频繁提交 =================================
    //     if (!privilege('admin.problem.list') || !privilege('admin.problem.solution')) {
    //         $last_submit_time = DB::table('solutions')
    //             ->where('user_id', Auth::id())
    //             ->orderByDesc('submit_time')
    //             ->value('submit_time');
    //         if (time() - strtotime($last_submit_time) < intval(get_setting('submit_interval')))
    //             return view('client.fail', ['msg' => trans('sentence.submit_frequently', ['sec' => get_setting('submit_interval')])]);
    //     }

    //     //============================= 预处理提交记录的字段 =================================
    //     //获取前台提交的solution信息
    //     $data = $request->input('solution');
    //     if(isset($data['code']))
    //         $data['code'] = base64_decode($data['code']);
    //     $problem = DB::table('problems')->find($data['pid']); //找到题目
    //     $submitted_result = 0;
        
    //     //判断提交的来源
    //     //如果有cid，说明实在竞赛中进行提交
    //     if (isset($data['cid'])) {
    //         $contest = DB::table("contests")->select('judge_type', 'allow_lang', 'end_time')->find($data['cid']);
    //         if (!((1 << $data['language']) & $contest->allow_lang)) //使用了不允许的代码语言
    //             return view('client.fail', ['msg' => 'Using a programming language that is not allowed!']);
    //     } else { //else 从题库中进行提交，需要判断一下用户权限
    //         $hidden = $problem->hidden;
    //         if (
    //             !privilege('admin.problem.solution') &&
    //             !privilege('admin.problem.list') &&
    //             $hidden == 1
    //         ) //不是管理员&&问题隐藏 => 不允许提交
    //             return view('client.fail', ['msg' => trans('main.Problem') . $data['pid'] . '：' . trans('main.Hidden')]);
    //     }

    //     //如果是填空题，填充用户的答案
    //     if ($problem->type == 1)
    //     {
    //         $data['code'] = $problem->fill_in_blank;
    //         foreach ($request->input('filled') as $ans) {
    //             $data['code'] = preg_replace("/\?\?/", base64_decode($ans), $data['code'], 1);
    //         }
    //     }

    //     //检测过短的代码
    //     if (strlen($data['code']) < 3)
    //         return view('client.fail', ['msg' => '代码长度过短！']);

    //     $solution = [
    //         'problem_id'    => $data['pid'],
    //         'contest_id'    => isset($data['cid']) ? $data['cid'] : -1,
    //         'user_id'       => Auth::id(),
    //         'result'        => $submitted_result,
    //         'language'      => ($data['language'] != null) ? $data['language'] : 0,
    //         'submit_time'   => date('Y-m-d H:i:s'),

    //         'judge_type'    => isset($contest->judge_type) ? $contest->judge_type : 'oi', //acm,oi

    //         'ip'            => get_client_real_ip(),
    //         'ip_loc'        => getIpAddress(get_client_real_ip()),
    //         'code_length'   => strlen($data['code']),
    //         'code'          => $data['code']
    //     ];

    //     //=============================== 将提交记录写入数据库 ======================================
    //     $solution['id'] = DB::table('solutions')->insertGetId($solution);

    //     //=============================== 展示网页 ===============================
    //     if (isset($data['cid'])) //竞赛提交
    //         return redirect(route('contest.status', [$data['cid'], 'index' => $data['index'], 'username' => Auth::user()->username, 'group' => $request->input('group') ?? null]));
    //     return redirect(route('status', ['pid' => $data['pid'], 'username' => Auth::user()->username]));
    // }
}
