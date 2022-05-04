<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class CheckGroup
{
    public function handle($request, Closure $next)
    {
        $group=DB::table('groups')->find($request->route()->parameter('id'));
        //管理员直接进入
        if(privilege(Auth::user(), 'admin.group')) 
            return $next($request);

        //============== 剩余情况均为普通用户 ==============
        // 群组成员直接进入
        if(DB::table('group_users as gu')
            ->where('gu.group_id', $group->id)
            ->where('gu.user_id', Auth::id())
            ->exists())
            return $next($request);

        // 隐藏的群组不允许访问
        if($group->hidden)
            return response()->view('client.fail',['msg'=>'你不是该群组成员或当前群组已隐藏']);

        return $next($request); // 通过验证
    }
}
