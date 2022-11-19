<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Permission
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param $permission
     * @return mixed
     */
    public function handle($request, $next, $permission)
    {
        if (privilege($permission))
            return $next($request);
        if (request()->is('api/*'))
            return response()->json(['ok' => 0, 'msg' => 'api 权限不足']);
        else if (request()->is('admin/*') && DB::table('privileges')->where('user_id', Auth::id())->exists()) // 管理员在后台页面访问时，权限不足
            return response()->view('layouts.message', ['msg' => '权限不足！如果您需要访问该页面，请联系管理员索要权限：' . $permission, 'success' => false, 'is_admin' => true]);
        else
            return response()->view('layouts.message', ['msg' => '权限不足！']);
    }
}
