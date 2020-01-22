<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Problem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProblemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function problems(){
        $list=DB::table('problems')
            ->select('problems.id','title','source','solved','submit','state')
            ->orderBy('id')
            ->paginate(100);
        return view('client.problems',['problems' => $list,]);
    }

    public function problem($id)
    {
        //在网页展示一个问题
        $problem=DB::table('problems')->find($id);

        if(!Auth::check() && !config('oj.main.guest_see_problem')) //未登录&&不允许访客看题 => 请先登录
            return view('client.fail',['msg'=>trans('sentence.Please login first')]);

        if (Auth::check() && !Auth::user()->is_admin() && $problem->state==0) //已登录&&不是管理员&&问题隐藏 => 不允许查看
            return view('client.fail',['msg'=>trans('main.Problem').$problem->id.'：'.trans('main.Hidden')]);

        //读取样例文件
        $samples=read_problem_samples($id);

        //读取历史提交
        $solutions=DB::table('solutions')
            ->select('id','result','time','memory','language')
            ->where('user_id','=',Auth::id())
            ->where('problem_id','=',$problem->id)
            ->orderByDesc('id')
            ->limit(10)->get();
        $has_more=DB::table('solutions')
            ->where('user_id','=',Auth::id())
            ->where('problem_id','=',$problem->id)
            ->count('id')>10;

//        arrayToConfig(["siteName"=>"Winter Online Judge11",3=>true],base_path("config/oj/main.php"));
//        exec("cd ".base_path()." && php artisan config:cache",$res,$status);
//        print_r($res);
        return view('client.problem',compact('problem','samples','solutions','has_more'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Problem  $problem
     * @return \Illuminate\Http\Response
     */
    public function show(Problem $problem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Problem  $problem
     * @return \Illuminate\Http\Response
     */
    public function edit(Problem $problem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Problem  $problem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Problem $problem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Problem  $problem
     * @return \Illuminate\Http\Response
     */
    public function destroy(Problem $problem)
    {
        //
    }
}
