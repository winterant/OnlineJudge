<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function list()
    {
        $notices = DB::table('notices')
            ->leftJoin('users', 'users.id', '=', 'user_id')
            ->select(['notices.*', 'username'])
            ->orderByDesc('id')->paginate();
        return view('admin.notice.list', compact('notices'));
    }

    public function add(Request $request)
    {
        if ($request->isMethod('get')) {
            $pageTitle = '发布公告';
            return view('admin.notice.edit', compact('pageTitle'));
        }
        if ($request->isMethod('post')) {
            $notice = $request->input('notice');
            $notice['user_id'] = Auth::id();
            $nid = DB::table('notices')->insertGetId($notice);
            $msg = '成功发布公告（id=' . $nid . '），你可以在首页查看';
            return view('message', ['msg' => $msg, 'success' => true, 'is_admin' => true]);
        }
    }

    public function update(Request $request, $id)
    {
        $notice = DB::table('notices')->find($id);

        if ($request->isMethod('get')) {
            $pageTitle = '修改公告';
            return view('admin.notice.edit', compact('pageTitle', 'notice'));
        }
        if ($request->isMethod('post')) {
            $notice = $request->input('notice');
            $notice['updated_at'] = date('Y-m-d H:i:s');
            // $notice['user_id'] = Auth::id();
            DB::table('notices')->where('id', $id)->update($notice);
            $msg = '已更新公告（id=' . $id . '），你可以在首页查看';
            return view('message', ['msg' => $msg, 'success' => true, 'is_admin' => true]);
        }
    }

    public function delete(Request $request)
    {
        $nids = $request->input('nids') ?: [];
        DB::table('notices')->whereIn('id', $nids)->delete();
    }

    public function update_state(Request $request)
    {
        $nids = $request->input('nids') ?: [];
        $state = $request->input('state');
        return DB::table('notices')->whereIn('id', $nids)->update(['state' => $state]);
    }
}
