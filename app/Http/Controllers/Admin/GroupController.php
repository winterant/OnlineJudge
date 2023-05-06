<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    // get: 后台管理 group列表
    public function list()
    {
        $groups = DB::table('groups as c')
            ->leftJoin('users', 'users.id', '=', 'creator')
            ->select(['c.*', 'username'])
            ->when(request()->has('name'), function ($q) {
                return $q->where('c.name', 'like', '%' . request('name') . '%');
            })
            ->orderByDesc('id')
            ->paginate(request('perPage') ?? 10);

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
        $group = DB::table('groups')->find($group_id);
        if (!$group)
            return view('message', ['msg' => '群组不存在!']);

        // json格式解码为逗号间隔格式
        $group->archive_cite = implode(',', json_decode($group->archive_cite ?? '[]', true));

        return view('admin.group.edit', compact('group'));
    }
}
