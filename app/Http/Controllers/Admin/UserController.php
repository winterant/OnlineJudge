<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function list(Request $request)
    {
        $users = DB::table('users')->select([
            'id', 'username', 'email', 'nick', 'school', 'class',
            'solved', 'accepted', 'submitted',
            'revise', 'locked', 'created_at'
        ])
            ->when(isset($_GET['kw']) && $_GET['kw'], function ($q) {
                return $q->where('username', 'like', '%' . $_GET['kw'] . '%')
                    ->orWhere('email', 'like', '%' . $_GET['kw'] . '%')
                    ->orWhere('nick', 'like', '%' . $_GET['kw'] . '%')
                    ->orWhere('school', 'like', '%' . $_GET['kw'] . '%')
                    ->orWhere('class', 'like', '%' . $_GET['kw'] . '%');
            })
            ->orderBy('id')->paginate($_GET['perPage'] ?? 10);

        return view('admin.user.list', compact('users'));
    }

    // 已废弃；权限管理
    public function privileges()
    {
        $privileges = DB::table('privileges')
            ->leftJoin('users as u1', 'u1.id', '=', 'user_id')
            ->leftJoin('users as u2', 'u2.id', '=', 'creator')
            ->select(['privileges.id', 'u1.username', 'u1.nick', 'authority', 'u2.username as creator', 'privileges.created_at'])
            ->orderBy('u1.username')->get();
        return view('admin.user.privilege', compact('privileges'));
    }

    // public function privilege_create(Request $request)
    // {
    //     if ($request->isMethod('post')) {
    //         $privilege = $request->input('privilege');
    //         $privilege['user_id'] = DB::table('users')->where('username', $request->input('username'))->value('id');
    //         if ($privilege['user_id'] == null)
    //             $msg = '该用户不存在！请先至用户列表确认用户的登录名！';
    //         else {
    //             $privilege['creator'] = Auth::id();
    //             $msg = '成功添加' . DB::table('privileges')->insert($privilege) . '个权限用户';
    //         }
    //         return back()->with('msg', $msg);
    //     }
    //     return view('message', ['msg' => '请求有误！', 'success' => false, 'is_admin' => true]);
    // }

    // public function privilege_delete(Request $request)
    // {
    //     $pids = $request->input('pids') ?: [];
    //     return DB::table('privileges')->whereIn('id', $pids)->where('user_id', '!=', 1000)->delete();
    // }

    /**
     * 显示账号生成页面，含历史数据
     */
    public function create(Request $request)
    {
        $files = Storage::allFiles('temp/created_users');
        $files = array_reverse($files);
        $created_csv = [];
        foreach ($files as $path) {
            if (time() - Storage::lastModified($path) > 3600 * 24 * 365) // 超过365天的数据删除掉
                Storage::delete($path);
            else {
                $info = pathinfo($path);
                preg_match('/\[(\S+?)\]/', $info['filename'], $matches);
                $created_csv[] = [
                    'name' => $info['basename'],
                    'creator' => $matches[1] ?? '',
                    'created_at' => date('Y-m-d H:i:s', Storage::lastModified($path))
                ];
            }
        }
        return view('admin.user.create', compact('created_csv'));
    }

    public function update_revise(Request $request)
    {
        if ($request->isMethod('post')) {
            $uids = $request->input('uids') ?: [];
            $revise = $request->input('revise');
            return DB::table('users')->whereIn('id', $uids)->update(['revise' => $revise]);
        }
        return 0;
    }

    public function update_locked(Request $request)
    {
        if ($request->isMethod('post')) {
            $uids = $request->input('uids') ?: [];
            $locked = $request->input('locked');
            return DB::table('users')->whereIn('id', $uids)->update(['locked' => $locked]);
        }
        return 0;
    }

    //重置密码
    public function reset_password(Request $request)
    {
        return view('admin.user.reset_password');
    }

    public function roles()
    {
        if (isset($_GET['kw']) && $_GET['kw'] != '')
            $roles = Role::where('name', 'like', '%' . $_GET['kw'] . '%')->get();
        else
            $roles = Role::all();
        $role_users = [];
        foreach ($roles as $role) {
            $users = User::role($role)->get();
            $role_users[$role->id] = $users;
        }
        return view('admin.user.roles', compact('roles', 'role_users'));
    }
}
