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

        $problems=DB::table('problems');
        if(isset($_GET['tag_id'])&&$_GET['tag_id']!='')
            $problems=$problems->join('tag_marks','problem_id','=','problems.id')
                ->where('tag_id',$_GET['tag_id']);
        $problems=$problems->select('problems.id','title','source','hidden',
                DB::raw("(select count(id) from solutions where problem_id=problems.id) as submit"),
                DB::raw("(select count(id) from solutions where problem_id=problems.id and result=4) as solved"))
            ->when(!Auth::check()||!Auth::user()->privilege('problem'),function ($q){return $q->where('hidden',0);})
            ->when(isset($_GET['pid'])&&$_GET['pid']!='',function ($q){return $q->where('problems.id',$_GET['pid']);})
            ->when(isset($_GET['title'])&&$_GET['title']!='',function ($q){return $q->where('title','like','%'.$_GET['title'].'%');})
            ->when(isset($_GET['source'])&&$_GET['source']!='',function ($q){return $q->where('source','like','%'.$_GET['source'].'%');})
            ->orderBy('problems.id')
            ->distinct()
            ->paginate(isset($_GET['perPage'])?$_GET['perPage']:100);
        foreach ($problems as &$problem) {
            $tag = DB::table('tag_marks')
                ->join('tag_pool','tag_pool.id','=','tag_id')
                ->groupBy('tag_pool.id','name')
                ->where('problem_id',$problem->id)
                ->where('hidden',0)
                ->select('tag_pool.id','name',DB::raw('count(name) as count'))
                ->orderByDesc('count')
                ->limit(2)
                ->get();
            $problem->tags=$tag;
        }
        $tag_pool=DB::table('tag_pool')
            ->select('id','name')
            ->where('hidden',0)
            ->orderBy('id')
            ->get();
        return view('client.problems',compact('problems','tag_pool'));
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
        if(!Auth::check() && !get_setting('guest_see_problem')) //未登录&&不允许访客看题 => 请先登录
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
        $samples=read_problem_data($id);

        //读取历史提交
        $solutions=DB::table('solutions')
            ->select('id','result','time','memory','language')
            ->where('user_id','=',Auth::id())
            ->where('problem_id','=',$problem->id)
            ->orderByDesc('id')
            ->limit(8)->get();

        $hasSpj=(get_spj_code($problem->id)!=null);

        $tags = DB::table('tag_marks')
            ->join('tag_pool','tag_pool.id','=','tag_id')
            ->groupBy('name')
            ->where('problem_id',$problem->id)
            ->where('hidden',0)
            ->select('name',DB::raw('count(name) as count'))
            ->orderByDesc('count')
            ->limit(3)
            ->get();

        //是否显示窗口：对题目进行打标签
//        $tag_mark_enable = (!isset($contest)||time()>strtotime($contest->end_time))
        $tag_mark_enable = Auth::check()
            && !DB::table('tag_marks')
                ->where('user_id','=',Auth::id())
                ->where('problem_id','=',$problem->id)
                ->exists()
            && DB::table('solutions')
            ->where('user_id','=',Auth::id())
            ->where('problem_id','=',$problem->id)
            ->where('result',4)
            ->exists();
        if($tag_mark_enable)
            $tag_pool=DB::table('tag_pool')
                ->select('id','name')
                ->where('hidden',0)
                ->orderBy('id')
                ->get();
        else
            $tag_pool=[];
        return view('client.problem',compact('problem','contests','samples','solutions','hasSpj','tags','tag_mark_enable','tag_pool'));
    }

    function tag_mark(Request $request){
        $problem_id = $request->input('problem_id');
        $tag_names = $request->input('tag_names');
        $tag_names=array_unique($tag_names);
        $tag_marks = [];
        foreach ($tag_names as $tag_name) {
            if(!DB::table('tag_pool')->where('name',$tag_name)->exists())
                $tid = DB::table('tag_pool')->insertGetId(['name'=>$tag_name]);
            else
                $tid = DB::table('tag_pool')->where('name',$tag_name)->first()->id;
            $tag_marks[]=['problem_id'=>$problem_id,'user_id'=>Auth::id(),'tag_id'=>$tid];
        }
        DB::table('tag_marks')->insert($tag_marks);
        return back()->with('tag_marked',true);
    }



    public function load_discussion(Request $request){
        $problem_id = $request->input('problem_id');
        $page = $request->input('page');
        $discussions = DB::table('discussions')
            ->select('id','username','content','top','hidden','created_at')
            ->where('problem_id',$problem_id)
            ->where('discussion_id',-1)
            ->when(!Auth::check()||!Auth::user()->privilege('problem_tag'),function ($q){return $q->where('hidden',0);})
            ->orderByDesc('top')
            ->orderByDesc('created_at')
            ->forPage($page,3)
            ->get();

        $ids=[];
        foreach ($discussions as &$item) {
            if($item->username)
            $item->username=sprintf("<a href='%s'>%s</a>",route('user',$item->username),$item->username);
            $ids[]=$item->id;
        }

        $son_disc = DB::table('discussions')
            ->select('id','discussion_id','username','reply_username','content','top','hidden','created_at')
            ->whereIn('discussion_id',$ids)
            ->when(!Auth::check()||!Auth::user()->privilege('problem_tag'),function ($q){return $q->where('hidden',0);})
            ->orderBy('created_at')
            ->get();
        $replies = [];
        foreach ($son_disc as &$item){
            if($item->username)
                $item->username=sprintf("<a href='%s'>%s</a>",route('user',$item->username),$item->username);
            if($item->reply_username)
                $item->reply_username=sprintf("<a href='%s'>%s</a>",route('user',$item->reply_username),$item->reply_username);
            $replies[$item->discussion_id][] = $item;
        }

        return json_encode([$discussions,$replies]);
    }

    public function edit_discussion(Request $request,$pid){
        $disc = [];
        if($request->input('discussion_id'))
            $disc['discussion_id'] = $request->input('discussion_id');
        if($request->input('reply_username'))
            $disc['reply_username'] = $request->input('reply_username');
        $disc['problem_id'] = $pid;
        $disc['username'] = Auth::user()->username;
        $disc['content'] = $request->input('content');
        DB::table('discussions')->insert($disc);
        return back()->with("discussion_added",true);
    }

    public function delete_discussion(Request $request){
        return DB::table('discussions')->delete($request->input('id'));
    }

    public function top_discussion(Request $request){
        if($request->input('way')==0)
            $new_top=0;
        else
            $new_top=DB::table('discussions')->max('top')+1;
        DB::table('discussions')->where('id',$request->input('id'))->update(['top'=>$new_top]);
        return 1;
    }

    public function hidden_discussion(Request $request){
        return DB::table('discussions')
            ->where('id',$request->input('id'))
            ->update(['hidden'=>$request->input('value')]);
    }
}
