<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class CheckUserLocked
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
        // 检查已登陆用户是否已被拉黑？（不登陆用户将不会被阻止，直接跳过该中间件）
        if (request()->is('api/*') && auth('api')->check()) {
            if (auth('api')->user()->locked && auth('api')->user()->username != 'admin')
                return response()->json([
                    'ok' => 0,
                    'msg' => '您的账号已被锁定，无法继续使用此api。如需继续使用，请联系管理员解锁。'
                ]);
        } else if (Auth::check()) // web
        {
            if (Auth::user()->locked && Auth::user()->username != 'admin')
                return response()->view('message', ['msg' => '您的账号已被锁定，无法继续使用。如需继续使用，请联系管理员解锁。']);
        }
        return $next($request); //通过验证
    }
}
