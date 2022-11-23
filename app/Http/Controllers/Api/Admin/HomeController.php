<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * patch request:{
     *   `key`:`value`,
     *   ...
     * }
     */
    public function settings(Request $request)
    {
        $modified = $request->all();
        foreach ($modified as $key => $val) {
            if ($val === null) $val = ''; // 前端传过来的空串会被laravel转为null，此处还原为空串
            if ($val === 'true') $val = true;
            if ($val === 'false') $val = false;
            if (is_numeric($val)) $val = intval($val);
            get_setting($key, $val, true);
        }
        return ['ok' => 1, 'msg' => 'Settings have updated.'];
    }


    // ======================== 以下方法是通用的常用静态方法，以便在其他控制器中调用 =============
    /**
     * 对于某张表，批量更新部分字段
     *
     * patch request:{
     *   ids:[1,2,...],
     *   values:[{},{},...] or {}
     * }
     * 使用方法：
     *   update_batch($table_name, $ids, $values) // 多条数据分别更新
     *   update_batch($table_name, $ids, $values, true) // 所有记录更新为同一个值
     *
     * response:{
     *   ok:(0|1),
     *   msg:string,
     *   data:{
     *     updated:int
     *   }
     * }
     */
    public static function update_batch($table, $ids, $values, $only_one_value = false)
    {
        $updated = 0;
        // 多条数据各自更新，将执行多条sql
        if ($only_one_value) {
            $updated += DB::table($table)->whereIn('id', $ids)->update($values);
        } else {
            if (count($ids) != count($values)) {
                return [
                    'ok' => 0,
                    'msg' => '请求提供的id数量与值的数量不相等，无法正常执行批量更新操作！',
                ];
            }
            foreach ($ids as $i => $id)
                $updated += DB::table($table)->where('id', $id)->update($values[$i]);
        }

        return [
            'ok' => 1,
            'msg' => sprintf("成功更新%d条数据", $updated),
            'data' => ['updated' => $updated]
        ];
    }
}
