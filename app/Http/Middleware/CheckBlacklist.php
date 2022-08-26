<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class CheckBlacklist
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
        if(Auth::check())
        {   
            if(Auth::user()->locked && Auth::user()->username!='admin')
            {
                $msg = '您的账号已被锁定，无法继续使用。如需继续使用，请联系管理员解锁。';
                return response()->view('client.fail', ['msg'=>$msg]);
            }
        }
        return $next($request); //通过验证
    }
}
