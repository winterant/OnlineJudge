<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SolutionController extends Controller
{
    public function solutions(Request $request)
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user != null && $user->can('admin.solution.view') && !isset($_GET['sim_rate']))
            $_GET['inc_contest'] = 'on';

        //读取提交记录
        $solutions = DB::table('solutions as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->select([
                'user_id', 'username', 'nick', // 用户信息
                's.id', 'contest_id', 'problem_id', 'ip', 'ip_loc',
                'judge_type', 'language', 'submit_time',
                'result', 'time', 'memory', 'pass_rate', 'judger', 'sim_rate', 'sim_sid',
            ])
            //普通用户只能查看非竞赛提交
            //关闭“包含竞赛”按钮时只能查看非竞赛提交
            ->when($user == null || !$user->can('admin.solution.view') || !isset($_GET['inc_contest']), function ($q) {
                $q->whereIn('s.contest_id', [-1, null]);
            })

            ->when(isset($_GET['sid']) && $_GET['sid'] != null, function ($q) {
                return $q->where('s.id', $_GET['sid']);
            })
            ->when(isset($_GET['pid']) && $_GET['pid'] != null, function ($q) {
                return $q->where('s.problem_id', $_GET['pid']);
            })

            ->when(intval($_GET['sim_rate'] ?? 0) > 0, function ($q) {
                return $q->where('sim_rate', '>=', $_GET['sim_rate']); // 0~100
            })
            ->when(isset($_GET['username']) && $_GET['username'] != null, function ($q) {
                return $q->where('username', 'like', $_GET['username'] . '%');
            })
            ->when(isset($_GET['result']) && $_GET['result'] >= 0, function ($q) {
                return $q->where('result', $_GET['result']);
            })
            ->when(isset($_GET['language']) && $_GET['language'] >= 0, function ($q) {
                return $q->where('language', $_GET['language']);
            })
            ->when(isset($_GET['ip']) && $_GET['ip'] != null, function ($q) {
                return $q->where('ip', $_GET['ip']);
            })
            ->when(isset($_GET['top_id']) && $_GET['top_id'] != null, function ($q) {
                if (isset($_GET['reverse']) && $_GET['reverse'] == 1)
                    return $q->where('s.id', '>=', $_GET['top_id']);
                return $q->where('s.id', '<=', $_GET['top_id']);
            })
            ->orderBy('s.id', (isset($_GET['reverse']) && $_GET['reverse'] == 1) ? 'asc' : 'desc')
            ->limit(10)
            ->get();

        if (isset($_GET['reverse']) && $_GET['reverse'] == 1)
            $solutions = $solutions->reverse();

        // ======== 处理显示信息 ==========
        foreach ($solutions as $s) {
            // 非管理员，抹掉重要信息
            if ($user == null || !$user->can('admin.solution.view')) {
                $s->nick = null;
                $s->ip = '-';
                $s->ip_loc = '';
            }
        }
        return view('solution.solutions', compact('solutions'));
    }

    // web 查看一条提交记录
    public function solution($id)
    {
        $solution = DB::table('solutions')->find($id);
        $solution->username = DB::table('users')->find($solution->user_id)->username ?? null;
        $can = $this->can_view_solution($solution, true);
        if ($can['ok'])
            return view('solution.solution', compact('solution'));
        return view('message', ['msg' => $can['msg']]); // 失败
    }

    // web 读取出错数据
    public function solution_wrong_data($id, $type)
    {
        $solution = DB::table('solutions')->select(['problem_id', 'user_id', 'submit_time', 'wrong_data'])->find($id);
        if (!$solution || $solution->wrong_data === null)
            return view('message', ['msg' => '没有记录出错数据']);

        $can = $this->can_view_solution($solution, false);
        if ($can['ok']) {
            $path_without_ext = testdata_path($solution->problem_id . '/test/' . $solution->wrong_data);
            if ($type == 'in' && file_exists($path_without_ext . '.in'))
                $text = file_get_contents($path_without_ext . '.in');
            if ($type == 'out' && file_exists($path_without_ext . '.out'))
                $text = file_get_contents($path_without_ext . '.out');
            if ($type == 'out' && file_exists($path_without_ext . '.ans'))
                $text = file_get_contents($path_without_ext . '.ans');
            if (!isset($text))
                return view('message', ['msg' => '测试文件不存在或已被移走']); // 失败
            return view('solution.solution_wrong_data', compact('text'));
        }
        return view('message', ['msg' => $can['msg']]); // 失败
    }


    private function can_view_solution(&$solution, $can_in_contest = False)
    {
        // ========================= 先查询所在竞赛的必要信息 ==========================
        if (($solution->contest_id ?? -1) > 0) {
            $contest = DB::table('contests as c')
                ->join('contest_problems as cp', 'c.id', 'cp.contest_id')
                ->select(['c.end_time', 'cp.index'])
                ->where('c.id', $solution->contest_id)
                ->where('cp.problem_id', $solution->problem_id)
                ->first();
            if ($contest) {
                $solution->index = $contest->index; // 记下该代码在竞赛中的题号
                $solution->end_time = $contest->end_time; // 记下所在竞赛的结束时间
            } else
                $solution->contest_id = -1; // 这条solution以前是竞赛中的，但题目现在被从竞赛中删除了
        }
        // =================== 管理员特权 =====================
        /** @var \App\Models\User */
        $user = Auth::user();
        if ($user->can('admin.solution.view'))
            return ['ok' => 1];
        // ================ 下面检查普通用户 ===================
        if (isset($solution->contest_id) && !$can_in_contest && date('Y-m-d H:i:s') < $solution->end_time)
            return ['ok' => 0, 'msg' => trans('sentence.not_end')]; // 竞赛未结束不允许查看
        if ($solution->user_id != Auth::id())
            return ['ok' => 0, 'msg' => trans('sentence.Permission denied')]; // 不能查看他人代码
        else if ($solution->submit_time < Auth::user()->created_at)
            return ['ok' => 0, 'msg' => trans('sentence.Permission denied')]; // 不能查看早于账号注册前的代码
        return ['ok' => 1]; // 通过检查
    }
}
