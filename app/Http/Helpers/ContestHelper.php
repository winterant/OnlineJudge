<?php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ContestHelper
{

    /**
     * 返回所有竞赛类别
     * 当tree==true时，把类别转化为树结构，二级类别作为主类别的子数组 sons
     * 当tree==false时，所有类别都放在同一个数组中，但按照order字段排序
     * @param bool $tree
     * @return array
     */
    public static function get_categories(bool $tree = false): array
    {
        $categories = DB::table('contest_cate as cc')
            ->select(['cc.id', 'cc.title', 'cc.description', 'cc.hidden', 'cc.order', 'cc.parent_id', 'cc.updated_at', 'cc.created_at'])
            ->get()->toArray();

        if ($tree) {
            // todo
            return $categories;
        } else {
            // 以列表的形式返回所有类别，排序依据：主类别order、二级类别order
            $sorted_categories = [];
            // 求出以id为键的数组
            foreach ($categories as $category) {
                $category->num_sons = 0; // 子类别数量
                $category->is_parent = !($category->parent_id > 0);
                $sorted_categories[$category->id] = $category;
            }
            // 计算每个类别的子类别数量
            foreach ($sorted_categories as $category) {
                if (!$category->is_parent) { // 是个子类别
                    $sorted_categories[$category->parent_id]->num_sons++;
                }
                $category->parent_title = $sorted_categories[$category->parent_id]->title ?? '';
                $category->parent_order = $sorted_categories[$category->parent_id]->order ?? $category->order; // 注意父类别parent_order==自身order
            }
            // 排序，例如[主类别1、子类别1、子类别2、主类别2、子类别1、子类别2、...]
            uasort($sorted_categories, function ($x, $y) {
                // 1. 先按所在主类别
                if ($x->parent_order != $y->parent_order) {
                    return $x->parent_order - $y->parent_order;
                }
                // 2. 同一主类别下，主类别在最前面
                if ($x->is_parent != $y->is_parent) {
                    return $y->is_parent - $x->is_parent;
                }
                // 3. 子类别按自身order
                return $x->order - $y->order;
            });
            return $sorted_categories;
        }
    }
}
