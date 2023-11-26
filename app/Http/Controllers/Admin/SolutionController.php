<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\CorrectSolutionsStatistics;
use App\Jobs\Judger;
use App\Jobs\ResetSolutionStamp;


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
                $solutions = DB::table('solutions')
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
                    })->get()->toArray();

                // 更新solution结果为rejudging
                $num_updated = DB::table('solutions')->whereIn('id', array_map(function ($v) {
                    return $v->id;
                }, $solutions))->update(['result' => 12]); // rejudging

                // 发起判题任务
                foreach ($solutions as $s)
                    dispatch(new Judger((array)$s));

                // 发生重判后必须重新统计数据，以及更新重判唯一标识符
                // 任务投入队列，预估等待到判题结束时执行
                if ($num_updated ?? 0) {
                    // 有很多页面的提交记录数据统计依赖缓存，发生重判后，为了使旧缓存失效，依据solution stamp是否变化来判断
                    dispatch(new ResetSolutionStamp())->delay(count($solutions) * 10); // 预估平均每条solution重判需要10秒
                    // 重新统计提交记录
                    dispatch(new CorrectSolutionsStatistics())->delay(count($solutions) * 10 + 5);
                }
            }


            // 返回提交记录页面
            $query = ['inc_contest' => 'on'];
            if ($pid)
                $query['pid'] = $pid;
            if ($cid)
                $query['cid'] = $cid;
            if ($sid)
                $query['sid'] = $sid;
            return redirect(route("solutions", $query));
        }
    }
}
