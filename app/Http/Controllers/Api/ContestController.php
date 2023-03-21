<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContestController extends Controller
{
    // 读取竞赛公告内容
    public function get_notice(Request $request, $id, $nid)
    {
        $notice = DB::table('contest_notices')->select(['title', 'content', 'created_at'])->find($nid);
        return $notice;
    }

    // 创建公告  //post
    public function create_notice(Request $request, $cid)
    {
        $notice = $request->input('notice');
        if ($notice['id'] == null) {
            //new
            unset($notice['id']);
            $notice['contest_id'] = $cid;
            DB::table('contest_notices')->insert($notice);
        }
        return ['ok' => 1, 'msg' => '已添加公告', 'data' => $notice];
    }

    // 编辑公告 // patch
    public function update_notice(Request $request, $cid, $nid)
    {
        $notice = $request->input('notice');
        DB::table('contest_notices')->where('id', $nid)->update($notice);
        return ['ok' => 1, 'msg' => '已修改公告', 'data' => $notice];
    }

    // 删除竞赛公告
    public function delete_notice($id, $nid)
    {
        DB::table('contest_notices')->where('id', $nid)->delete();
        return ['ok' => 1, 'msg' => '已删除公告'];
    }
}
