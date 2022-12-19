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
     * 若提供了以下参数，则查询该值是否等于当前用户id，是则获得特权；
     * 也就是说，自己创建的条目自己拥有特权；
     *  $table  表名
     *  $column 用户id列名，默认'user_id'，用于与当前用户id比较
     *  $db_key 查询该资源时使用的列名，默认'id'，用于从数据库中查找该资源
     *  $route_key 路由参数中提供的键名，默认'id'，用于获取路由参数值
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param $permission
     * @return mixed
     */
    public function handle($request, $next, string $permission, $table = null, $column = 'user_id', $db_key = 'id', $route_key = 'id')
    {
        // ================================== 权限名称预处理 ==================================
        // 匹配出所有占位符，例如{id}替换为路由中携带的id值
        // preg_match_all('/{.*?}/', $permission, $matches);
        // $values = [];
        // foreach ($matches[0] as $p) {
        //     $key = str_replace(['{', '}'], '', $p); // '{id}' returns 'id'
        //     $values[$p] = request()->route($key); // '{id}' => 13
        // }
        // $permission = strtr($permission, $values); // '{id}' to 13

        // ================================== 创建者特权检查 ==================================
        /** @var \App\Models\User */
        $user = Auth::user() ?? auth('api')->user();

        // 检查是否是创建者特权
        if ($table !== null && $user) {
            $existed = DB::table($table)
                ->where($db_key, $request->route()->parameter($route_key))
                ->where($column, $user->id)
                ->exists();
            if ($existed)
                return $next($request);
        }

        // ================================== 权限检查 ==================================
        if ($permission != null && $permission != 'null' && $user && $user->can($permission))
            return $next($request);

        // ================================== 权限不足，返回提示信息 ==================================
        if (request()->is('api/*'))
            return response()->json(['ok' => 0, 'msg' => '权限不足! 您没有该权限:'.$permission]);
        else if (request()->is('admin/*') && DB::table('privileges')->where('user_id', Auth::id())->exists()) // 管理员在后台页面访问时，权限不足
            return response()->view('message', ['msg' => '权限不足！如果您需要访问该页面，请联系管理员索要权限：' . $permission, 'success' => false, 'is_admin' => true]);
        else
            return response()->view('message', ['msg' => '权限不足！']);
    }
}
