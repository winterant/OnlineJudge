<?php

namespace App\Http\Middleware;

use Closure;

// use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route;
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
        //contest管理员直接进入
        if(privilege(Auth::user(), 'admin.contest')) 
            return $next($request);

        //============== 剩余情况均为普通用户 ==============
        // 比赛尚未开始，只允许查看概览
        if(time()<strtotime($contest->start_time) && Route::currentRouteName()!='contest.home') //比赛尚未开始,必须重定向到home
            return redirect(route('contest.home',$contest->id));

        // 群组成员有权访问群组内的竞赛，直接进入竞赛
        if(DB::table('group_contests as gc')
            ->join('group_users as gu', 'gu.group_id', '=', 'gc.group_id')
            ->where('gc.contest_id', $contest->id)
            ->where('gu.user_id', Auth::id())
            ->exists())
            return $next($request);

        // 隐藏的竞赛不允许访问
        if($contest->hidden)
            return response()->view('client.fail',['msg'=>trans('sentence.hidden')]);
        
        // 私有的竞赛检查用户是否被邀请为参赛成员
        if($contest->access=='private'
                && DB::table('contest_users')->where('contest_id',$contest->id)
                ->where('user_id',Auth::id())->doesntExist()) //私有竞赛，没有邀请该用户
            return response()->view('client.fail',['msg'=>trans('sentence.not_invited')]);

        // 加密的竞赛需要验证参赛密码
        if($contest->access=='password') {
            if(!DB::table('contest_users')->where('contest_id',$contest->id)
                    ->where('user_id',Auth::id())->exists()) // 没有验证过密码
                return redirect(route('contest.password',$contest->id)); //去验证
        }

        return $next($request); // 通过验证
    }
}
