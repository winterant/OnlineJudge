<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list=DB::table('solutions')
            ->join('users','solutions.user_id','=','users.id')
            ->select('solutions.id','problem_id','username','result','time','memory','language','submit_time')
            ->where('contest_id','<',1);
        if(isset($_GET['pid'])&&$_GET['pid']!='')
            $list=$list->where('problem_id','=',$_GET['pid']);
        if(isset($_GET['username'])&&$_GET['username']!='')
            $list=$list->where('username','=',$_GET['username']);
        if(isset($_GET['result'])&&$_GET['result']!=-1)
            $list=$list->where('result','=',$_GET['result']);
        $list=$list->orderByDesc('solutions.id')
            ->paginate(10);
        return view('client.status',['solutions' => $list,]);
    }

    public function solution($id){
        $solution=DB::table('solutions')->find($id);
        return view('client.solution',compact('solution'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //提交一条solution
        date_default_timezone_set('PRC'); //设置时区
        $data = $request->input('solution');


        if(!Auth::check() && !config('oj.main.guest_see_problem')) //未登录&&不允许访客看题 => 请先登录
            return view('client.fail',['msg'=>trans('sentence.Please login first')]);
        $problem=DB::table('problems')->select('state')->find($data['pid']);
        if (Auth::check() && !Auth::user()->is_admin() && $problem->state==0) //已登录&&不是管理员&&问题隐藏 => 不允许查看
            return view('client.fail',['msg'=>trans('main.Problem').$problem->id.'：'.trans('main.Hidden')]);


        if($request->input('submit_way')=='#tag_file'){
            $file=$request->file('code_file');//用户提交了文件,从临时文件中直接提取文本
            $data['code']=file_get_contents($file->getRealPath());
        }
        DB::table('solutions')->insert([
            'problem_id'    => $data['pid'],
            'contest_id'    => isset($data['cid'])?$data['cid']:-1,
            'user_id'       => Auth::id(),
            'result'        => 0,
            'language'      => ($data['language']!=null)?$data['language']:0,
            'submit_time'   => date('Y-m-d H:i:s'),

            'judge_type'    => isset($data['judge_type'])?$data['judge_type']:'acm', //acm,oi,exam

            'ip'            => $request->getClientIp(),
            'code_length'   => strlen($data['code']),
            'code'          => $data['code'],
            ]);
        Cookie::queue('submit_language',$data['language']);
        if(isset($data['cid'])) //竞赛提交
            return redirect(route('contest.status',[$data['cid'],'index'=>$data['index'],'username'=>Auth::user()->username]));

        return redirect(route('status',['pid'=>$data['pid'],'username'=>Auth::user()->username]));
    }
}
