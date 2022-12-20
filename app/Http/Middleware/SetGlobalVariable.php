<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\App;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class SetGlobalVariable
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
        // ========================== 获取APItoken ===================
        // 对于已登陆用户的api请求，自动从redis中获取api_token
        if (Auth::check() && request()->is('api/*')) {
            /** @var \App\Models\User */
            $user = Auth::user();
            $request['api_token'] = $user->get_api_token();
        }

        // ========================== 设置时区 ========================
        //date()函数的时区默认UTC，用这个全局中间件来改为上海时间
        date_default_timezone_set(config('app.timezone'));
        App::setLocale(request()->cookie('unencrypted_client_language') ?? get_setting('APP_LOCALE', 'en')); //设置语言
        return $next($request);
    }
}
