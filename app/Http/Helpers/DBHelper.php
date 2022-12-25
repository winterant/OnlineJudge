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
}
