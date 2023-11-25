<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function list(Request $request)
    {
        $notices = DB::table('notices')
            ->leftJoin('users', 'users.id', '=', 'user_id')
            ->select(['notices.*', 'username'])
            ->when(request()->has('kw') && request('kw'), function ($q) {
                return $q->where('notices.id', request('kw'))
                    ->orWhere('notices.title', 'like', '%' . request('kw') . '%')
                    ->orWhere('notices.content', 'like', '%' . request('kw') . '%');
            })
            ->orderByDesc('id')
            ->paginate($request->input('perPage') ?? 15);
        return view('admin.notice.list', compact('notices'));
    }

    public function create(Request $request)
    {
        $pageTitle = '发布公告';
        return view('admin.notice.edit', compact('pageTitle'));
    }

    public function update(Request $request, $id)
    {
        $notice = DB::table('notices')->find($id);
        $pageTitle = '修改公告';
        return view('admin.notice.edit', compact('pageTitle', 'notice'));
    }
}
