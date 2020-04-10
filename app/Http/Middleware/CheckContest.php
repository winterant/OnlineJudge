<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class CheckContest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $contest=DB::table('contests')->find($request->route()->parameter('id'));
        if(Auth::user()->privilege('contest')) //contest管理员直接进入
            return $next($request);
        if(time()<strtotime($contest->start_time)&& \Request::route()->getName()!='contest.home') //比赛尚未开始,必须重定向到home
            return redirect(route('contest.home',$contest->id));

        if($contest->access=='private'
                && DB::table('contest_users')->where('contest_id',$contest->id)
                ->where('user_id',Auth::id())->doesntExist()) //私有竞赛，没有邀请该用户
            return response()->view('client.fail',['msg'=>trans('sentence.not_invited')]);

        if($contest->access=='password') { //需要密码
            if(DB::table('contest_users')->where('contest_id',$contest->id)
                    ->where('user_id',Auth::id())->exists()) //已通过验证
                return $next($request);
            return redirect(route('contest.password',$contest->id)); //去验证
        }

        return $next($request); //public和private通过验证的
    }
}
