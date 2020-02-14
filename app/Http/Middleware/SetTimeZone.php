<?php

namespace App\Http\Middleware;

use Closure;

class SetTimeZone
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
        //date()函数的时区默认UTC，用这个全局中间件来改为上海时间
        date_default_timezone_set('Asia/Shanghai');
        return $next($request);
    }
}
