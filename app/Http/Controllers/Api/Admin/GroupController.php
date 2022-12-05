<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    /**
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
        if (!($group = DB::table('groups')->find($group_id)))
            return view('message', ['msg' => '群组不存在!']);
        if (!privilege('admin.group') && Auth::id() != $group->creator)
            return view('message', ['msg' => '您没有管理权限，也不是该群组的创建者!']);

        $request_group = $request->input('group');
        if ($request_group['class'] == '') // class是integer，无法赋值空串
            $request_group['class'] = null;
        $request_group['updated_at'] = date('Y-m-d H:i:s');
        DB::table('groups')->where('id', $group->id)->update($request_group);

        // 添加竞赛
        // $contest_ids = $request->input('contest_ids');
        // DB::table('group_contests')->where('group_id', $group->id)->delete();
        // foreach (explode(PHP_EOL, $contest_ids) as &$contest_id) {
        //     $line = explode('-', trim($contest_id));
        //     $group_contests = [];
        //     if (count($line) == 1) {
        //         $cid = intval(trim($line[0]));
        //         if (DB::table('contests')->find($cid))
        //             $group_contests[] = [
        //                 'group_id' => $group->id,
        //                 'contest_id' => $cid,
        //             ];
        //     } else {
        //         foreach (range(intval(trim($line[0])), intval((trim($line[1])))) as $i) {
        //             if (DB::table('contests')->find($i))
        //                 $group_contests[] = [
        //                     'group_id' => $group->id,
        //                     'contest_id' => $i,
        //                 ];
        //         }
        //     }
        //     DB::table('group_contests')->insert($group_contests);
        // }
        return [
            'ok' => 1,
            'msg' => '修改成功',
            'redirect' => route('group', $group->id)
        ];
    }

    /**
     * 批量更新groups记录
     *
     * patch request:{
     *   ids:[1,2,...],
     *   values:[{},{},...]
     *   value:{},
     * }
     * 注释：values/二选一，有values则每条记录单独更新，否则一条sql将记录批量更新为value。
     *
     * response:{
     *   ok:(0|1),
     *   msg:string,
     *   data:{
     *     updated:int
     *   }
     * }
     */
    public function update_batch(Request $request)
    {
        $ids = $request->input('ids') ?? [];
        if ($request->has('values'))
            return HomeController::update_batch('groups', $ids, $request->input('values'));
        else
            return HomeController::update_batch('groups', $ids, $request->input('value'), true);
    }


    /**
     * 批量添加group_contests
     *
     * post request:{
     *   contests_id:[id1,id2,...],
     *   identity: int(^[0-4].$)
     * }
     *
     * response:{
     *   ok:(0|1),
     *   msg:string,
     *   data:{
     *     updated:int
     *   }
     * }
     */
    public function create_contests(Request $request, $group_id)
    {
        if (!($group = DB::table('groups')->find($group_id)))
            return [
                'ok' => 0,
                'msg' => '群组不存在！'
            ];
        if (!privilege('admin.group') && Auth::id() != $group->creator)
            return [
                'ok' => 0,
                'msg' => '您既不是该群组的创建者，也不具备管理权限[admin.group]!'
            ];

        // 开始处理
        $contests_id = explode(PHP_EOL, $request->input('contests_id'));
        foreach ($contests_id as &$item)
            $item = trim($item);
        $cids = DB::table('contests')->whereIn('id', $contests_id)->pluck('id')->toArray(); // 欲添加竞赛id
        $max_order = DB::table('group_contests')->where('group_id', $group->id)->max('order'); // 已存在的最大顺序序号
        foreach ($cids as $cid) {
            DB::table('group_contests')->insert([
                'group_id' => $group->id,
                'contest_id' => $cid,
                'order' => ++$max_order
            ]);
        }
        return [
            'ok' => 1,
            'msg' => sprintf("已成功新增%d个竞赛: %s", count($cids), $request->input('contests_id'))
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
        if (!($group = DB::table('groups')->find($group_id)))
            return ['ok' => 0, 'msg' => '群组不存在!'];
        if (!privilege('admin.group') && Auth::id() != $group->creator)
            return ['ok' => 0, 'msg' => '您既不是该群组的创建者，也不具备管理权限[admin.group]!'];
        // 开始处理
        $deleted = DB::table('group_contests')
            ->where('group_id', $group_id)
            ->whereIn('id', $request->input('ids'))
            ->delete();
        return [
            'ok' => 1,
            'msg' => sprintf("已删除%d个竞赛", $deleted)
        ];
    }

    /**
     * 批量更新group_members
     *
     * patch request:{
     *   ids:[1,2,...],
     *   values:[{},{},...]
     *   value:{},
     * }
     * 注释：values/二选一，有values则每条记录单独更新，否则一条sql将记录批量更新为value。
     *
     * response:{
     *   ok:(0|1),
     *   msg:string,
     *   data:{
     *     updated:int
     *   }
     * }
     */
    public function update_members_batch(Request $request)
    {
        $ids = $request->input('ids') ?? [];
        if ($request->has('values'))
            return HomeController::update_batch('group_users', $ids, $request->input('values'));
        else
            return HomeController::update_batch('group_users', $ids, $request->input('value'), true);
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
     *   data:{
     *     updated:int
     *   }
     * }
     */
    public function create_members(Request $request, $group_id)
    {
        if (!($group = DB::table('groups')->find($group_id)))
            return [
                'ok' => 0,
                'msg' => '群组不存在！'
            ];
        if (!privilege('admin.group') && Auth::id() != $group->creator)
            return [
                'ok' => 0,
                'msg' => '您既不是该群组的创建者，也不具备管理权限[admin.group]!'
            ];

        // 开始处理
        $unames = explode(PHP_EOL, $request->input('usernames'));
        $iden = $request->input('identity');
        foreach ($unames as &$item)
            $item = trim($item);
        $uids = DB::table('users')->whereIn('username', $unames)->pluck('id')->toArray(); // 欲添加用户id
        foreach ($uids as $uid) {
            DB::table('group_users')->updateOrInsert(
                ['group_id' => $group->id, 'user_id' => $uid],
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
        if (!($group = DB::table('groups')->find($group_id)))
            return ['ok' => 0, 'msg' => '群组不存在!'];
        if (!privilege('admin.group') && Auth::id() != $group->creator)
            return ['ok' => 0, 'msg' => '您既不是该群组的创建者，也不具备管理权限[admin.group]!'];
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
    public function update_contest_order($gc_id, $shift)
    {
        // 获取当前竞赛
        $gc = DB::table('group_contests')->find($gc_id);
        if ($shift > 0) {
            // order增加，上移
            $count_updated = 0;
            DB::transaction(function () use ($gc, $shift) {
                // 当前竞赛后面受影响的竞赛，前移1
                $count_updated = DB::table('group_contests')
                    ->where('group_id', $gc->group_id)
                    ->whereBetween('order', [$gc->order + 1, $gc->order + $shift])
                    ->decrement('order');
                // 当前竞赛移动到指定位置
                DB::table('group_contests')
                    ->where('id', $gc->id)
                    ->increment('order', $count_updated);
            });
            return [
                'ok' => 1,
                'msg' => sprintf('竞赛[%s]已向上移动%d项', $gc->contest_id, $count_updated)
            ];
        } else {
            // order降低，下移
            $count_updated = 0;
            DB::transaction(function () use ($gc, $shift) {
                $count_updated = DB::table('group_contests')
                    ->where('group_id', $gc->group_id)
                    ->whereBetween('order', [$gc->order + $shift, $gc->order - 1])
                    ->increment('order');
                // 当前竞赛移动到指定位置
                DB::table('group_contests')
                    ->where('id', $gc->id)
                    ->decrement('order', $count_updated);
            });
            return [
                'ok' => 1,
                'msg' => sprintf('竞赛[%s]已向下移动%d项', $gc->contest_id, $count_updated)
            ];
        }
        return [
            'ok' => 0,
            'msg' => '移动失败'
        ];
    }
}
