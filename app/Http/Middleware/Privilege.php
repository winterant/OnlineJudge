<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Privilege
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (!Auth::user()->privilege($role)) {
            if (DB::table('privileges')->where('user_id', Auth::id())->limit(1)->exists())//是个特权用户
                $page = 'admin'; //特权用户，提供后台的fail页面
            else
                $page = 'client';
            return response()->view($page . '.fail', ['msg' => '权限不足！您没有该权限：' . $role]);
        }
        return $next($request);
    }
}
