<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Privilege
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$role,$page=null)
    {
        if($page==null)$page='admin'; //默认是后台页面的fail
        if(!Auth::user()->privilege($role))
            return response()->view($page.'.fail',['msg'=>'权限不足！']);
        return $next($request);
    }
}
