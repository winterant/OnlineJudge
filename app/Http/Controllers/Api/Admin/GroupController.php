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
            return view('client.fail', ['msg' => '群组不存在!']);
        if (!privilege('admin.group') && Auth::id() != $group->creator)
            return view('client.fail', ['msg' => '您没有管理权限，也不是该群组的创建者!']);

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
        $updated = 0;
        $ids = $request->input('ids');
        Log::info($request->all());

        // 多条数据各自更新，将执行多条sql
        if ($request->has('values')) {
            $values = $request->input('values');
            if (count($ids) != count($values)) {
                return [
                    'ok' => 0,
                    'msg' => '请求提供的id数量与值的数量不相等，无法正常执行更新操作！',
                ];
            }
            foreach ($ids as $i => $id)
                $updated += DB::table('groups')->where('id', $id)->update($values[$i]);
        }

        // 多条记录一起更新为同一个值，将执行一条sql
        if ($request->has('value')) {
            $updated += DB::table('groups')->whereIn('id', $ids)->update($request->input('value'));
        }

        return [
            'ok' => 1,
            'msg' => sprintf("成功更新%d条数据", $updated)
        ];
    }
}
