<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    /**
     * 批量更新groups记录
     *
     * request:{
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
