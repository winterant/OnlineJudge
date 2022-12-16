<?php

namespace App\Http\Controllers\Api\Admin;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class UserController extends Controller
{
    public function create_role(Request $request)
    {
        $role_name = $request->input('role_name');
        $role_guard_name = $request->input('role_guard_name');

        try {
            // 角色不存在，创建
            $role = Role::create(['name' => $role_name, 'guard_name' => $role_guard_name]);
            // 初始化权限
            $permissions = $request->input('permissions') ?? [];
            $role->syncPermissions(array_keys($permissions));
            return [
                'ok' => 1,
                'msg' => sprintf('成功创建角色: %s (%s)', $role_name, $role_guard_name),
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return [
                'ok' => 1,
                'msg' => sprintf('角色创建失败: %s (%s); 可能角色已存在', $role_name, $role_guard_name),
            ];
        }
    }

    public function update_role(Request $request, $role_id)
    {
        $role = Role::findById($role_id, 'web');
        $updated_permissions = $request->input('permissions');
        // 修改权限
        $role->syncPermissions(array_keys($updated_permissions));
        return [
            'ok' => 1,
            'msg' => sprintf('已修改角色[%s]的信息', $role->name),
        ];
    }

    public function delete_role(Request $request, $role_id)
    {
        try {
            Role::findById($role_id, 'web')->delete();
            return [
                'ok' => 1,
                'msg' => '已删除'
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return [
                'ok' => 1,
                'msg' => '删除失败，请确认数据存在！'
            ];
        }
    }

    /**
     * 获取某角色的所有权限
     * 若get参数bool==1，则返回{permission:bool, }
     */
    public function get_role_permissions($role_id)
    {
        $role = Role::findById($role_id, 'web');
        $ret = [];
        if ($_GET['bool'] ?? false) {
            foreach (Permission::all() as $p)
                $ret[$p->name] = ($role->hasPermissionTo($p));
        } else {
            $ret = $role->permissions();
        }
        return [
            'ok' => 1,
            'data' => $ret,
        ];
    }

    // 向某角色中添加批量用户
    public function role_add_users(Request $request, $role_id)
    {
        $role = Role::findById($role_id, 'web');
        $usernames = explode(PHP_EOL, $request->input('usernames'));
        foreach ($usernames as &$name) {
            $name = trim($name);
            $user = User::where('username', $name)->first();
            if ($user)
                $user->assignRole($role);
        }
        return [
            'ok' => 1,
            'msg' => '成功在角色' . $role->name . '中添加用户:' . implode(',', $usernames)
        ];
    }

    public function role_delete_user($role_id, $user_id)
    {
        $role = Role::findById($role_id, 'web');
        $user = User::find($user_id);
        $user->removeRole($role);
        return [
            'ok' => 1,
            'msg' => '成功将用户' . $user->username . '从角色' . $role->name . '中移除'
        ];
    }
}
