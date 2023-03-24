<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\DBHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{

    /**
     * 创建群组
     * post request:{
     *   group:{...}
     * }
     */
    public function create(Request $request)
    {
        // 处理请求; 新建一条数据，跳转到修改
        $group_id = DB::table('groups')->insertGetId([
            'creator' => Auth::id()
        ]);
        // 把创建者自己加入到群组成员
        DB::table('group_users')->insert([
            'group_id' => $group_id,
            'user_id' => Auth::id(),
            'identity' => 4, // 老师/管理员
        ]);
        return $this->update($request, $group_id);
    }

    /**
     * 删除group
     * delete request:{
     *   group:{...}
     * }
     */
    public function delete($id)
    {
        $group = DB::table('groups')->find($id);
        if ($group) {
            DB::table('groups')->delete($id);
            return [
                'ok' => 1,
                'msg' => '已成功删除 ' . $group->name
            ];
        } else {
            return [
                'ok' => 0,
                'msg' => '要删除的项目不存在！请刷新页面后重试'
            ];
        }
    }

    /**
     * 修改group信息
     * put request:{
     *   group:{...}
     * }
     */
    public function update(Request $request, $group_id)
    {
        $request_group = $request->input('group');
        $request_group['updated_at'] = date('Y-m-d H:i:s');
        $updated = DB::table('groups')->where('id', $group_id)->update($request_group);
        if ($updated)
            return [
                'ok' => 1,
                'msg' => '修改成功',
                'redirect' => route('group', $group_id)
            ];
        return ['ok' => 0, 'msg' => '没有数据被修改'];
    }

    /**
     * 批量更新groups记录
     *
     * patch request:{
     *   ids:[1,2,...],
     *   value:{},
     * }
     */
    public function update_batch_to_one(Request $request)
    {
        $ids = $request->input('ids') ?? [];
        $value = $request->input('value');
        $updated = DBHelper::update_batch_to_one('groups', ['id' => $ids], $value);
        if ($updated > 0)
            return ['ok' => 1, 'msg' => '成功修改' . $updated . '条数据'];
        return ['ok' => 0, 'msg' => '没有任何数据被修改'];
    }

    /**
     * 批量添加group_contests
     *
     * post request:{
     *   contests_id:[id1,id2,...]
     * }
     *
     * response:{
     *   ok:(0|1),
     *   msg:string
     * }
     */
    public function create_contests(Request $request, $group_id)
    {
        // 开始处理
        $contests_id = decode_str_to_array($request->input('contests_id'));
        $max_order = DB::table('group_contests')->where('group_id', $group_id)->max('order'); // 已存在的最大顺序序号
        foreach ($contests_id as $cid) {
            if (
                DB::table('contests')->find($cid)
                && DB::table('group_contests')->where([
                    'group_id' => $group_id,
                    'contest_id' => $cid
                ])->doesntExist()
            ) {
                DB::table('group_contests')->insert([
                    'group_id' => $group_id,
                    'contest_id' => $cid,
                    'order' => ++$max_order
                ]);
            }
        }
        return [
            'ok' => 1,
            'msg' => sprintf("已成功新增%d个竞赛: %s", count($contests_id), $request->input('contests_id'))
        ];
    }


    /**
     * 批量删除group_contests
     *
     * delete request:{
     *   ids:[1,2,...],   // 注意这是group_contests表的主键id
     * }
     *
     * response:{
     *   ok:(0|1),
     *   msg:string,
     * }
     */
    public function delete_contests_batch(Request $request, $group_id)
    {
        $contest_ids = $request->input('contest_ids');
        // 开始处理
        $deleted = DB::table('group_contests')
            ->where('group_id', $group_id)
            ->whereIn('contest_id', $contest_ids)
            ->delete();
        // 调整受影响的顺序，使其连续
        DBHelper::continue_order('group_contests', ['group_id' => $group_id]);

        return [
            'ok' => 1,
            'msg' => sprintf("已删除%d个竞赛", $deleted)
        ];
    }

    /**
     * 批量更新group_members
     *
     * patch request:{
     *   user_ids:[1,2,...],
     *   value:{},
     * }
     * response:{
     *   ok:(0|1),
     *   msg:string,
     * }
     */
    public function update_members_batch_to_one(Request $request, $group_id)
    {
        $user_ids = $request->input('user_ids') ?? [];
        $value = $request->input('value');
        $updated = DBHelper::update_batch_to_one('group_users', ['user_id' => $user_ids], $value, ['group_id' => $group_id]);
        if ($updated > 0)
            return ['ok' => 1, 'msg' => '成功修改' . $updated . '条数据'];
        return ['ok' => 0, 'msg' => '没有任何数据被修改'];
    }


    /**
     * 批量添加group_members
     *
     * post request:{
     *   usernames:[user1,user2,...],
     *   identity: int(^[0-4].$)
     * }
     *
     * response:{
     *   ok:(0|1),
     *   msg:string,
     * }
     */
    public function create_members(Request $request, $group_id)
    {
        // 开始处理
        $unames = explode(PHP_EOL, $request->input('usernames'));
        $iden = $request->input('identity');
        foreach ($unames as &$item)
            $item = trim($item);
        $uids = DB::table('users')->whereIn('username', $unames)->pluck('id')->toArray(); // 欲添加用户id
        foreach ($uids as $uid) {
            DB::table('group_users')->updateOrInsert(
                ['group_id' => $group_id, 'user_id' => $uid],
                ['identity' => $iden ?: 2]
            );
        }
        return [
            'ok' => 1,
            'msg' => sprintf("已成功新增%d个成员: %s", count($uids), $request->input('usernames'))
        ];
    }

    /**
     * 批量删除group_members
     *
     * delete request:{
     *   ids:[1,2,...],
     * }
     *
     * response:{
     *   ok:(0|1),
     *   msg:string,
     * }
     */
    public function delete_members_batch(Request $request, $group_id)
    {
        // 开始处理
        $deleted = DB::table('group_users')
            ->where('group_id', $group_id)
            ->whereIn('user_id', $request->input('user_ids'))
            ->delete();
        return [
            'ok' => 1,
            'msg' => sprintf("已删除%d个成员", $deleted)
        ];
    }

    /**
     * 修改某一群组内的竞赛的顺序，即order字段
     * 注意！竞赛order是顺序还是倒序展示的，取决于群组类型是课程还是班级，此处无需关心
     * 输入：
     *      gc_id: group_contests表的主键id
     *      shift: 对order字段的偏移量，整数范围
     */
    public function update_contest_order($group_id, $gc_id, $shift)
    {
        // 获取当前竞赛
        $gc = DB::table('group_contests')->find($gc_id);
        $updated = DBHelper::shift_order('group_contests', ['group_id' => $group_id], $gc->order, $shift);
        if ($updated > 0)
            return [
                'ok' => 1,
                'msg' => sprintf('%d items have been affected.', $updated)
            ];
        return [
            'ok' => 0,
            'msg' => 'Nothing has been affected.'
        ];
    }

    // ================================= 成员个人档案 =====================================

    /**
     * 查询档案记录
     *
     * get request:{
     *   group_id:int,
     *   username:string
     * }
     *
     * 返回档案最新版本
     * response: {'content':string, 'creator': string, 'created_at': Datetime}
     */
    public function get_archive(int $group_id, string $username)
    {
        $hist = $this->get_archive_history($group_id, $username); // 只返回最新版本，不存在则返回false
        return empty($hist) ? [] : end($hist);
    }

    /**
     * 查询档案历史记录
     *
     * get request:{
     *   group_id:int,
     *   username:string
     * }
     *
     * 返回所有历史版本
     * response:[
     *   {'content':string, 'creator': string, 'created_at': Datetime}, ...
     * ]
     */
    public function get_archive_history(int $group_id, string $username)
    {
        $user_id = DB::table('users')->where('username', $username)->value('id');
        $archive = DB::table('group_users')
            ->where('group_id', $group_id)
            ->where('user_id', $user_id)
            ->first()->archive; // 获取已有字段内容
        $archive = json_decode($archive, true) ?? []; // 解码为php数组
        return $archive;
    }

    /**
     * 查询档案记录
     *
     * get request:{
     *   group_id:int,
     *   username:string
     * }
     *
     * response:[
     *   {'content':string, 'creator': string, 'created_at': Datetime}, ...
     * ]
     */
    public function update_archive(Request $request, $group_id, $username)
    {
        $user_id = DB::table('users')->where('username', $username)->value('id');
        $archive = $this->get_archive_history($group_id, $username); // 获取所有历史
        $archive[] = [
            'content' => $request->input('content'), // 更新内容
            'creator' => Auth::user()->username,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // 确保最多只保留100个历史记录,防止爆库
        if (count($archive) > 100) {
            $archive = array_slice($archive, -100);
        }

        // 写回数据库
        DB::table('group_users')
            ->where('group_id', $group_id)
            ->where('user_id', $user_id)
            ->update(['archive' => json_encode($archive)]);
        return ['ok' => 1, 'msg' => '已更新'];
    }
}
