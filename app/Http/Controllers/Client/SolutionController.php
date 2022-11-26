<?php

namespace App\Http\Controllers\Client;

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
        if (privilege('admin.problem.solution') && !isset($_GET['sim_rate']))
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
            ->when(!privilege('admin.problem.solution') || !isset($_GET['inc_contest']), function ($q) {
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
            if (!privilege('admin.problem.solution')) {
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
        if ($solution->contest_id > 0) {
            $cp = DB::table('contest_problems')
                ->where('contest_id', $solution->contest_id)
                ->where('problem_id', $solution->problem_id)
                ->first();
            if ($cp)
                $solution->index = $cp->index;
            else
                $solution->contest_id = -1; // 这条solution以前是竞赛中的，但题目现在被从竞赛中删除了
        }

        if (
            privilege('admin.problem.solution') ||
            (Auth::id() == $solution->user_id && $solution->submit_time > Auth::user()->created_at)
        )
            return view('solution.solution', compact('solution'));
        return view('layouts.message', ['msg' => trans('sentence.Permission denied')]);
    }

    // web 读取出错数据
    public function solution_wrong_data($id, $type)
    {
        $solution = DB::table('solutions')
            ->leftJoin('contests', 'solutions.contest_id', '=', 'contests.id')  //非必须，left
            ->select('solutions.problem_id', 'solutions.user_id', 'contests.end_time', 'solutions.wrong_data')
            ->where('solutions.id', $id)
            ->first();
        if (!$solution || $solution->wrong_data === null)
            return view('layouts.message', ['msg' => '没有记录出错数据']);
        $allow_get = false;
        if (privilege('admin.problem.solution')) // 管理员可以直接看
            $allow_get = true;
        else if (Auth::id() == $solution->user_id) // 普通用户
        {
            if ($solution->end_time && date('Y-m-d H:i:s') < $solution->end_time) // 比赛未结束
                return view('layouts.message', ['msg' => trans('sentence.not_end')]);
            $allow_get = true;
        }
        if ($allow_get) {
            if ($type == 'in')
                $text = file_get_contents(testdata_path($solution->problem_id . '/test/' . $solution->wrong_data . '.in'));
            else if (file_exists(testdata_path($solution->problem_id . '/test/' . $solution->wrong_data . '.out')))
                $text = file_get_contents(testdata_path($solution->problem_id . '/test/' . $solution->wrong_data . '.out'));
            else
                $text = file_get_contents(testdata_path($solution->problem_id . '/test/' . $solution->wrong_data . '.ans'));
            return view('solution.solution_wrong_data', compact('text'));
        }
        return view('layouts.message', ['msg' => trans('sentence.Permission denied')]);
    }
}
