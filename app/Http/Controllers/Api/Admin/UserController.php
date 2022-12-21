<?php

namespace App\Http\Controllers\Api\Admin;

use Exception;
use App\Http\Controllers\Controller;
use App\Jobs\User\CreateUsers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class UserController extends Controller
{
    /**
     * 批量生成账号
     *
     */
    public function create_batch(Request $request)
    {
        $data = $request->input('data');

        // 解析用户名
        $usernames = [];
        if (isset($data['stu_id'])) {
            $usernames = decode_str_to_array($data['stu_id']);
        } else {
            $data['prefix'] = trim($data['prefix']);
            if (intval($data['begin']) == intval($data['end'])) // 单个用户，则直接生成名字
                $usernames[] = $data['prefix'];
            else
                for ($i = intval($data['begin']); $i <= intval($data['end']); $i++) // 多个用户，按顺序生成
                    $usernames[] = sprintf("%s%03d", $data['prefix'], $i);
        }

        //设置了安全检查，发现已存在用户时，告诉管理员，而不是直接删除
        if (isset($data['check_exist'])) {
            $existed_users = DB::table('users')->whereIn('username', $usernames)->pluck('username');
            if (count($existed_users) > 0)
                return [
                    'ok' => 0,
                    'msg' => '部分用户已存在',
                    'data' => $existed_users
                ];
        }

        $nick = decode_str_to_array($data['nick'] ?? null);
        $email = decode_str_to_array($data['email'] ?? null);
        $school = decode_str_to_array($data['school'] ?? null);
        $class = decode_str_to_array($data['class'] ?? null);

        // 处理用户信息
        foreach ($usernames as $i => $username) {
            $password = substr(str_shuffle("ABCDMNXYZ"), 0, 4) . substr(str_shuffle("0123456789ABCDEF"), 0, 4);
            $users[] = [
                'index' => $i + 1,
                'username' => trim($username),
                'password' => $password,
                'nick' => $nick[$i] ?? '',
                'email' => $email[$i] ?? '',
                'school' => $school[$i] ?? '',
                'class' => $class[$i] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'revise' => isset($data['revise']) ? 1 : 0,
            ];
        }

        // 写入文件
        Storage::makeDirectory('temp/created_users');
        $file = fopen(Storage::path(sprintf('temp/created_users/%s[%s]%s.csv', date('Ymd_His'), Auth::user()->username, $users[0]['username'])), 'a');
        fputcsv($file, array_keys($users[0]));
        foreach ($users as &$user) {
            fputcsv($file, array_values($user));
            unset($user['index']);
        }
        fclose($file);

        // 加入到任务队列 去插入到数据库
        dispatch(new CreateUsers($users));

        return [
            'ok' => 1,
            'msg' => '已生成账号'
        ];
    }

    // 下载csv文件
    public function download_created_users_csv(Request $request)
    {
        return Storage::download('temp/created_users/' . $_GET['filename']);
    }

    // 批量删除用户
    public function delete_batch(Request $request)
    {
        $uids = $request->input('uids') ?: [];
        $deleted = DB::table('users')->whereIn('id', $uids)->whereNot('username', 'admin')->delete();
        return [
            'ok' => 1,
            'msg' => '已删除' . $deleted . '个用户',
        ];
    }

    //重置密码
    public function reset_password(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $user = User::where('username', $username)->first();
        if ($user) {
            if ($user->can('admin')) {
                $msg = "该用户拥有超级管理员权限(admin)，不能被重置密码。请先取消该账号的权限再尝试！";
            } else {
                DB::table('users')->where('id', $user->id)->update(['password' => Hash::make($password)]);
                $msg = '重置成功！';
            }
            return ['ok' => 1, 'msg' => $msg];
        }
        return ['ok' => 0, 'msg' => '账号不存在'];
    }


    // ================================ 角色管理 ========================================
    /**
     * 创建一个角色
     */
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
        $role = Role::find($role_id);
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
            Role::find($role_id)->delete();
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
        $role = Role::find($role_id);
        $ret = [];
        if ($_GET['bool'] ?? false) {
            foreach (config('init.permissions') as $p => $desc)
                $ret[$p] = ($role->hasPermissionTo($p));
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
        $role = Role::find($role_id);
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
        $role = Role::find($role_id);
        $user = User::find($user_id);
        $user->removeRole($role);
        return [
            'ok' => 1,
            'msg' => '成功将用户' . $user->username . '从角色' . $role->name . '中移除'
        ];
    }
}
