<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GroupController extends Controller
{
    // get: 后台管理 group列表
    public function list()
    {
        $groups = DB::table('groups as c')
            ->leftJoin('users', 'users.id', '=', 'creator')
            ->select(['c.*', 'username'])
            ->when(isset($_GET['name']), function ($q) {
                return $q->where('c.name', 'like', '%' . $_GET['name'] . '%');
            })
            ->orderByDesc('id')
            ->paginate($_GET['perPage'] ?? 10);

        return view('admin.group.list', compact('groups'));
    }

    // get: 新建 group
    public function create(Request $request)
    {
        return view('admin.group.edit'); //提供界面
    }

    // get: 编辑已存在的 group
    public function edit(Request $request, $group_id)
    {
        if (!($group = DB::table('groups')->find($group_id)))
            return view('layouts.failure', ['msg' => '群组不存在!']);
        if (!privilege('admin.group') && Auth::id() != $group->creator)
            return view('layouts.failure', ['msg' => '您既不是该群组的创建者，也不具备管理权限[admin.group]!']);
        // 提供界面
        $contest_ids = DB::table('group_contests as gc')
            ->join('contests as c', 'c.id', '=', 'gc.contest_id')
            ->where('gc.group_id', $group_id)
            ->orderBy('gc.id')
            ->pluck('c.id');
        return view('admin.group.edit', compact('group', 'contest_ids'));
    }

    // get 删除group
    public function delete($id)
    {
        DB::table('groups')->delete($id);
        return back();
    }

    // post
    public function add_member(Request $request, $id)
    {
        if (!($group = DB::table('groups')->find($id)))
            return view('layouts.failure', ['msg' => '群组不存在!']);
        if (!privilege('admin.group') && Auth::id() != $group->creator)
            return view('layouts.failure', ['msg' => '您既不是该群组的创建者，也不具备管理权限[admin.group]!']);
        // 开始处理
        $unames = explode(PHP_EOL, $request->input('usernames'));
        $iden = $request->input('identity');
        foreach ($unames as &$item)
            $item = trim($item);
        $uids = DB::table('users')->whereIn('username', $unames)->pluck('id');
        foreach ($uids as &$uid) {
            DB::table('group_users')->updateOrInsert(
                ['group_id' => $id, 'user_id' => $uid],
                [
                    'identity' => $iden ?: 2, // 默认2为普通成员
                ]
            );
        }
        return back();
    }

    // get
    public function del_member(Request $request, $id, $uid)
    {
        if (!($group = DB::table('groups')->find($id)))
            return view('layouts.failure', ['msg' => '群组不存在!']);
        if (!privilege('admin.group') && Auth::id() != $group->creator)
            return view('layouts.failure', ['msg' => '您既不是该群组的创建者，也不具备管理权限[admin.group]!']);
        // 开始处理
        DB::table('group_users')
            ->where('group_id', $id)
            ->where('user_id', $uid)
            ->delete();
        return back();
    }

}
