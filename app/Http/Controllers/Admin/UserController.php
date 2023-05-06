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
            ->when(request()->has('kw') && request('kw'), function ($q) {
                return $q->where('username', 'like', '%' . request('kw') . '%')
                    ->orWhere('email', 'like', '%' . request('kw') . '%')
                    ->orWhere('nick', 'like', '%' . request('kw') . '%')
                    ->orWhere('school', 'like', '%' . request('kw') . '%')
                    ->orWhere('class', 'like', '%' . request('kw') . '%');
            })
            ->orderBy('id')->paginate(request('perPage') ?? 10);

        return view('admin.user.list', compact('users'));
    }

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

    // 用户角色管理页面
    public function roles()
    {
        if (request()->has('kw') && request('kw') != '')
            $roles = Role::where('name', 'like', '%' . request('kw') . '%')->get();
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
