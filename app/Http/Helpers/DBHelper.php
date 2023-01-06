<?php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\DB;

class DBHelper
{
    /**
     * 调整某一类记录的顺序。
     * 目标表的order字段用于前端展示顺序（1～n），该函数根据提供的$shift值调整当前记录的顺序
     * @param $table 要操作的数据库表名，如group_contests
     * @param $where 要调整顺序的条目的筛选条件，如['group_id'=>1]
     * @param $current_order 要调整顺序的那个条目的当前order值
     * @param $shift 要调整的幅度，如-2表示当前条目顺序减少2
     * @return int 受影响的记录条数
     */
    public static function shift_order(string $table, array $where, int $current_order, int $shift)
    {
        if ($shift > 0) { // order增加
            $shift = min($shift, DB::table($table)->where($where)->max('order') - $current_order); // 不要超出范围
            $updated = DB::table($table)->where($where)->whereBetween('order', [$current_order, $current_order + $shift])
                ->update(['order' => DB::raw(sprintf(
                    "case when `order`=%d then `order`+%d else `order`-1 end",
                    $current_order,
                    $shift
                ))]);
        } else { // order减小
            $shift = -min(abs($shift), $current_order - 1); // 不要超出范围
            $updated = DB::table($table)->where($where)->whereBetween('order', [$current_order + $shift, $current_order])
                ->update(['order' => DB::raw(sprintf(
                    "case when `order`=%d then `order`-%d else `order`+1 end",
                    $current_order,
                    abs($shift)
                ))]);
        }
        return $updated;
    }


    /**
     * 调整某一类记录的顺序，使其连续。
     * 目标表的order字段用于前端展示顺序（1～n）
     * 例如 1,3,4,4,6 调整后变为1,2,3,4,5
     * @param $table 要操作的数据库表名，如group_contests
     * @param $where 要调整顺序的条目的筛选条件，如['group_id'=>1]
     * @return int 受影响的记录条数
     */
    public static function continue_order(string $table, array $where)
    {
        $updated = DB::table($table . ' as T')
            ->joinSub(
                DB::table($table)->where($where)
                    ->select([
                        'id',
                        DB::raw('row_number() over(order by `order`,`id`) as row_id')
                    ]),
                'B',
                'B.id',
                'T.id'
            )
            ->update(['order' => DB::raw('`B`.`row_id`')]);
        return $updated;
    }


    // /**
    //  * 对于某张表，批量更新
    //  * @param attributes [{},{},...] 筛选字段
    //  * @param values [{},{},...] 要更新的字段
    //  * @return int 修改的记录条数
    //  * 使用方法：
    //  *   update_batch($table_name, $attributes, $values) // 多条数据分别更新
    //  */
    // public static function update_batch(string $table, array $attributes, array $values)
    // {
    //     $updated = 0;
    //     // 多条数据各自更新，将执行多条sql
    //     if (count($attributes) == count($values))
    //         foreach ($attributes as $i => $attr)
    //             $updated += DB::table($table)->where($attr)->update($values[$i]);
    //     return $updated;
    // }

    /**
     * 对于某张表，批量更新，多个记录更新为同一个值
     * @param table  表名，例如'groups'
     * @param in  whereIn的键值对，例如['id'=>[1,2,...]]
     * @param value  {} 要更新的字段值，例如['identity'=>0]
     * @param extra_where  额外的筛选条件，例如['group_id'=>4]
     * @return int 受影响的记录条数
     */
    public static function update_batch_to_one(string $table, array $in, array $value, array $extra_where = null)
    {
        $q = DB::table($table);
        if ($extra_where)
            $q = $q->where($extra_where);
        foreach ($in as $k => $vs)
            $q = $q->whereIn($k, $vs);
        return $q->update($value);
    }
}
