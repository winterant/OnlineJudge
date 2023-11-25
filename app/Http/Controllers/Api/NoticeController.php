<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NoticeController extends Controller
{
    // GET 获取一条公告
    public function get_notice($id)
    {
        $user = Auth::user();
        $notice = DB::table('notices')->select(['title', 'content', 'created_at', 'hidden'])->find($id);
        if (!$notice || ($notice->hidden && !$user->can('admin.notice.view')))
            return ['ok' => 0, 'msg' => '不存在的公告'];
        return [
            'ok' => 1,
            'msg' => 'success',
            'data' => $notice
        ];
    }


    // POST 创建一条公告
    public function create(Request $request)
    {
        $notice = $request->input('notice');
        $notice['user_id'] = Auth::id();
        $notice['id'] = DB::table('notices')->insertGetId($notice);
        return ['ok' => 1, 'msg' => 'Created a new notice.', 'data' => $notice, 'redirect' => route('admin.notice.list', ['kw' => $notice['id']])];
    }

    // PUT 修改公告
    public function update(Request $request, $noticeId)
    {
        $notice = $request->input('notice');
        $notice['updated_at'] = date('Y-m-d H:i:s');
        $ret = DB::table('notices')->where('id', $noticeId)->update($notice);
        if ($ret == 0) {
            return ['ok' => 0, 'msg' => 'Failed to update the notice.', 'data' => $notice, 'redirect' => route('admin.notice.list')];
        }
        return ['ok' => 1, 'msg' => 'Updated the notice.', 'data' => $notice, 'redirect' => route('admin.notice.list', ['kw' => $noticeId])];
    }

    // DELETE 删除公告
    public function delete(Request $request, $noticeId)
    {
        $ret = DB::table('notices')->where('id', $noticeId)->delete();
        return ['ok' => $ret, 'msg' => ($ret ? 'Deleted the notice' : 'Failed to delete notice')];
    }

    // DELETE 批量删除公告
    public function delete_batch(Request $request)
    {
        $nids = $request->input('nids') ?: [];
        $ret = DB::table('notices')->whereIn('id', $nids)->delete();
        if ($ret > 0)
            return ['ok' => 1, 'msg' => "$ret notices has been deleted."];
        else
            return ['ok' => 0, 'msg' => "No notice has deleted!"];
    }

    // PATCH 修改公告状态
    public function update_state_batch(Request $request)
    {
        $nids = $request->input('nids') ?: [];
        $state = $request->input('state');
        $ret = DB::table('notices')->whereIn('id', $nids)->update(['state' => $state]);
        if ($ret > 0)
            return ['ok' => 1, 'msg' => "$ret notices has updated state."];
        else
            return ['ok' => 0, 'msg' => "No notice has updated!"];
    }
}
