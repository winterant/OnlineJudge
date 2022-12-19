<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\CorrectSolutionsStatistics;
use App\Jobs\GenerateRejudgedCode;


class SolutionController extends Controller
{
    //重判题目|竞赛|提交记录
    public function rejudge(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('admin.solution.rejudge');
        }
        if ($request->isMethod('post')) {
            $pid = $request->input('pid');
            $cid = $request->input('cid');
            $sid = $request->input('sid');
            $date = $request->input('date');

            if ($pid || $cid || $sid || ($date[0] && $date[1])) {
                $num_updated = DB::table('solutions')
                    ->when($pid ?? false, function ($q) use ($pid) {
                        $q->where('problem_id', $pid);
                    })
                    ->when($cid ?? false, function ($q) use ($cid) {
                        $q->where('contest_id', $cid);
                    })
                    ->when($sid ?? false, function ($q) use ($sid) {
                        $q->where('id', $sid);
                    })
                    ->when($date[1] ?? false, function ($q) use ($date) {
                        $q->where('submit_time', '>', str_replace('T', ' ', $date[0]))
                            ->where('submit_time', '<', str_replace('T', ' ', $date[1]));
                    })
                    ->update(['result' => 0]);
                // 发起判题任务
                // foreach ($solution_ids as $id)
                //     dispatch(new Judger($id));
            }

            // 发生重判后必须重新统计数据，以及更新重判唯一标识符
            // 任务投入队列，预估等待到判题结束时执行
            if ($num_updated ?? 0) {
                dispatch(new GenerateRejudgedCode())->delay($num_updated * 5); // 预估平均每条solution重判需要5秒
                dispatch(new CorrectSolutionsStatistics())->delay($num_updated * 5);
            }

            // 返回提交记录页面
            $query = ['inc_contest' => 'on'];
            if ($pid) $query['pid'] = $pid;
            if ($cid) $query['cid'] = $cid;
            if ($sid) $query['sid'] = $sid;
            return redirect(route("solutions", $query));
        }
    }
}
