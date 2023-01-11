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
        return view('solution.solutions');
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
