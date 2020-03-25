<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Problem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProblemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function problems(){
        $list=DB::table('problems')
            ->select('problems.id','title','source','hidden',
                DB::raw("(select count(id) from solutions where problem_id=problems.id) as submit"),
                DB::raw("(select count(id) from solutions where problem_id=problems.id and result=4) as solved"))
            ->when(!Auth::check()||!Auth::user()->privilege('problem'),function ($q){return $q->where('hidden',0);})
            ->when(isset($_GET['pid'])&&$_GET['pid']!='',function ($q){return $q->where('id',$_GET['pid']);})
            ->when(isset($_GET['title'])&&$_GET['title']!='',function ($q){return $q->where('title','like','%'.$_GET['title'].'%');})
            ->when(isset($_GET['source'])&&$_GET['source']!='',function ($q){return $q->where('source','like','%'.$_GET['source'].'%');})
            ->orderBy('id')
            ->paginate(isset($_GET['perPage'])?$_GET['perPage']:100);
        return view('client.problems',['problems' => $list,]);
    }

    public function problem($id)
    {
        //在网页展示一个问题
        $problem=DB::table('problems')->select('*',
            DB::raw("(select count(id) from solutions where problem_id=problems.id) as submit"),
            DB::raw("(select count(id) from solutions where problem_id=problems.id and result=4) as solved")
            )->find($id);
        if($problem==null)
            abort(404);
        if(!Auth::check() && !config('oj.main.guest_see_problem')) //未登录&&不允许访客看题 => 请先登录
            return view('client.fail',['msg'=>trans('sentence.Please login first')]);

        //查询引入这道题的竞赛
        $contests=DB::table('contest_problems')
            ->join('contests','contests.id','=','contest_id')
            ->select('contest_id as id','title')
            ->distinct()
            ->where('problem_id',$id)
            ->get();
        if (Auth::check() && !Auth::user()->privilege('problem') && $problem->hidden==1) //已登录&&不是管理员&&问题隐藏 => 不允许查看
        {
            $msg=trans('main.Problem').$problem->id.'：'.trans('main.Hidden').'<br>';
            if($contests){
                $msg.=trans('main.Contests involved').":<br>";
                foreach ($contests as $item)
                    $msg.=sprintf('<a href="%s">%s. %s</a><br>',route('contest.home',$item->id),$item->id,$item->title);
            }
            return view('client.fail',compact('msg'));
        }

        //读取样例文件
        $samples=read_problem_samples($id);

        //读取历史提交
        $solutions=DB::table('solutions')
            ->select('id','result','time','memory','language')
            ->where('user_id','=',Auth::id())
            ->where('problem_id','=',$problem->id)
            ->orderByDesc('id')
            ->limit(8)->get();
        $has_more=DB::table('solutions')
            ->where('user_id','=',Auth::id())
            ->where('problem_id','=',$problem->id)
            ->count('id')>8;

        $hasSpj=Storage::exists('data/'.$problem->id.'/spj/spj.cpp');
        return view('client.problem',compact('problem','contests','samples','solutions','has_more','hasSpj'));
    }

}
