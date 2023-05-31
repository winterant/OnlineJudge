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
    public function solutions()
    {
        return view('solution.solutions');
    }

    // web 读取出错数据
    public function solution_wrong_data(Request $request, $id, $type)
    {
        $solution = DB::table('solutions')->select(['problem_id', 'contest_id', 'user_id', 'submit_time', 'wrong_data'])->find($id);
        if (!$solution || $solution->wrong_data === null)
            return view('message', ['msg' => '没有记录出错数据']);
        /** @var App/Model/User */
        $user = Auth::user();
        if ($user->can_view_solution($id)) {

            // ========================= 无权限时，要检查竞赛，未结束竞赛不允许查看数据 =========================
            if (!$user->can('admin.solution.view') && ($solution->contest_id ?? 0) > 0) {
                $contest = DB::table('contests as c')
                    ->join('contest_problems as cp', 'c.id', 'cp.contest_id')
                    ->select(['c.end_time', 'cp.index'])
                    ->where('c.id', $solution->contest_id)
                    ->where('cp.problem_id', $solution->problem_id)
                    ->first();
                if (date('Y-m-d H:i:s') < ($contest->end_time ?? 0))
                    return view('message', ['msg' => trans('sentence.not_end')]); // 竞赛未结束不允许查看
            }

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
        return view('message', ['msg' => __('sentence.Permission denied')]); // 失败
    }
}
