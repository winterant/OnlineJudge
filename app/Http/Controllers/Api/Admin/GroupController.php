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
     * 修改group信息
     * put request:{
     *   group:{...}
     * }
     */
    public function update(Request $request, $group_id)
    {
        if (!($group = DB::table('groups')->find($group_id)))
            return view('layouts.failure', ['msg' => '群组不存在!']);
        if (!privilege('admin.group') && Auth::id() != $group->creator)
            return view('layouts.failure', ['msg' => '您没有管理权限，也不是该群组的创建者!']);

        $request_group = $request->input('group');
        $request_group['updated_at'] = date('Y-m-d H:i:s');
        DB::table('groups')->where('id', $group->id)->update($request_group);

        // 添加竞赛
        $contest_ids = $request->input('contest_ids');
        DB::table('group_contests')->where('group_id', $group->id)->delete();
        foreach (explode(PHP_EOL, $contest_ids) as &$contest_id) {
            $line = explode('-', trim($contest_id));
            $group_contests = [];
            if (count($line) == 1) {
                $cid = intval(trim($line[0]));
                if (DB::table('contests')->find($cid))
                    $group_contests[] = [
                        'group_id' => $group->id,
                        'contest_id' => $cid,
                    ];
            } else {
                foreach (range(intval(trim($line[0])), intval((trim($line[1])))) as $i) {
                    if (DB::table('contests')->find($i))
                        $group_contests[] = [
                            'group_id' => $group->id,
                            'contest_id' => $i,
                        ];
                }
            }
            DB::table('group_contests')->insert($group_contests);
        }
        return [
            'ok' => 1,
            'msg' => '修改成功',
            'redirect' => route('group.home', $group->id)
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
            return TemplateController::update_batch('groups', $ids, $request->input('values'));
        else
            return TemplateController::update_batch('groups', $ids, $request->input('value'), true);
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
            return TemplateController::update_batch('group_users', $ids, $request->input('values'));
        else
            return TemplateController::update_batch('group_users', $ids, $request->input('value'), true);
    }
}
