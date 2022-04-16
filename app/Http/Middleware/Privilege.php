<?php

namespace App\Http\Middleware;

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
    public function handle($request, $next, $role)
    {
        if (!privilege(Auth::user(), $role))
        // if (!privilege(Auth::user(), $request->route()->getName()))  // todo
        {
            if (
                strpos($request->getRequestUri(), '/admin') == 0
                && DB::table('privileges')->where('user_id', Auth::id())->limit(1)->exists()
            ) //管理员在后台页面访问时，权限不足
                return response()->view('admin.fail', ['msg' => '权限不足！如果您需要访问该页面，请联系管理员索要权限：' . $role]);
            else
                return response()->view('client.fail', ['msg' => '权限不足！']);
        }
        return $next($request);
    }
}
