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
     * @param $verify_privilege 验证是否为创建者/拥有者，格式 table.{route_key}.column
     *        例如 groups.{id}.user_id 表示取`groups`表`id`={id}这条记录的`user_id`字段，{id}取自路由参数
     *        例如 groups.{username}.id 表示取`groups`表`username`={username}这条记录的`id`字段，{id}取自路由参数
     * @return mixed
     */
    public function handle($request, $next, string $permission, string $verify_privilege = null)
    {
        // ======================== 创建者特权检查，验证是否是当前记录的创建者 ==================
        if ($verify_privilege && Auth::check()) {
            [$table, $key, $user_column] = explode('.', $verify_privilege); // e.g. notices.{id}.user_id where {id} must be appeared in the route.
            $key = substr($key, 1, strlen($key) - 2); // strip { and }
            $user_id = DB::table($table)->where($key, $request->route($key))->value($user_column);

            if ($user_id == Auth::id()) // 当前用户是创建者
                return $next($request);

            // ================ * 特判group成员管理员 ================
            if (
                $table == 'groups' && $key == 'id' &&
                DB::table('group_users')->where('group_id', $request->route($key))
                ->where('user_id', Auth::id())->where('identity', 4)->exists()
            ) return $next($request);
        }

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
            return response()->view('message', ['msg' => trans('sentence.Permission denied')]);
    }
}
