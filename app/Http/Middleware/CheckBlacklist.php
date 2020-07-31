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
            $black_user = DB::table('blacklist')->where('user_id',Auth::id())->first();
            if($black_user)
            {
                $msg = trans('sentence.in_blacklist');
                if($black_user->reason)
                    $msg .= "<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".trans('main.Reason').': '.$black_user->reason;
                return response()->view('client.fail', ['msg'=>$msg]);
            }
        }
        return $next($request); //通过验证
    }
}
