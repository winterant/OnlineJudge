<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Permission
{
    /**
     * 检查当前用户必须具备的权限，否则拒之门外。
     * 对于复杂的权限判断，请放弃使用该中间件，而是在控制器中判断权限。
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param $permission
     * @return mixed
     */
    public function handle($request, $next, string $permission)
    {
        // ================================== 权限检查 ==================================
        /** @var \App\Models\User */
        $user = Auth::user();
        if (Auth::check() && $user->can($permission))
            return $next($request);

        // ================================== 权限不足，返回提示信息 ==================================
        if (request()->is('api/*'))
            return response()->json(['ok' => 0, 'msg' => '权限不足! 您没有该权限:' . $permission]);
        else if (request()->is('admin/*') && $user != null && $user->can('admin.view')) // 管理员在后台页面访问时，权限不足
            return response()->view('message', ['msg' => '权限不足！如果您需要访问该页面，请联系管理员索要权限：' . $permission, 'success' => false, 'is_admin' => true]);
        else
            return response()->view('message', ['msg' => '权限不足！']);
    }
}
