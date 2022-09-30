<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContestController extends Controller
{
    /*****************************  类别   ***********************************/

    /**
     * 添加类别
     * 输入：
     *      values: 数组，类别里的各个字段
     * */
    public function add_contest_cate(Request $request)
    {
        $values = $request->input('values');
        $max_order = DB::table('contest_cate')->where('parent_id', $values['parent_id'])->max('order')+1;
        $values['order'] = $max_order;
        $id = DB::table('contest_cate')->insertGetId($values);
        return [
            'ok' => 1,
            'msg' => '已添加新类别'
        ];
    }

    /**
     * 修改类别信息
     * 输入：
     *      values: 数组，要修改的字段
     */
    public function update_contest_cate($id, Request $request)
    {
        $values = $request->input('values');
        if (isset($values['parent_id'])) //拦截非法的父级类别修改
        {
            $parent = DB::table('contest_cate')->find($values['parent_id']); // 欲指定的父类别
            if ($values['parent_id'] > 0 && !$parent) {
                return [
                    'ok' => 0,
                    'msg' => '指定的父级类别不存在！'
                ];
            }
            if ($values['parent_id'] == $id) {
                return [
                    'ok' => 0,
                    'msg' => '不能作为自身的子类别！'
                ];
            }
            if ($values['parent_id'] > 0 && $parent->parent_id > 0) {
                return [
                    'ok' => 0,
                    'msg' => '指定的父级类别必须是一级类别！请刷新页面后重试！'
                ];
            }
            if ($values['parent_id'] > 0 && DB::table('contest_cate')->where('parent_id', $id)->count() > 0) {
                return [
                    'ok' => 0,
                    'msg' => '当前类别下有二级类别，请先移走它们，再修改当前类别的父类别！'
                ];
            }
        }

        //执行修改
        DB::table('contest_cate')->where('id', $id)->update($values);
        return [
            'ok' => 1,
            'msg' => '已修改'
        ];
    }

    /**
     * 删除类别
     */
    public function delete_contest_cate($id)
    {
        if (DB::table('contest_cate')->where('parent_id', $id)->exists()) {
            return [
                'ok' => 0,
                'msg' => '一级分类下包含子类别，请先删除或移走所有子类别再删除当前类别'
            ];
        }
        $cate = DB::table('contest_cate')->find($id);
        // order字段矫正（比当前类别大的，-1）
        DB::table('contest_cate')
            ->where('parent_id', $cate->parent_id)
            ->where('order', '>', $cate->order)
            ->decrement('order');
        DB::table('contest_cate')->where('id', $id)->delete();
        return [
            'ok' => 1,
            'msg' => '已删除'
        ];
    }

    /**
     * 修改竞赛类别的顺序，即order字段
     * 输入：
     *      id: 类别编号
     *      mode: 'up' or 'down' 表示上移或下移
     */
    public function update_cate_order($id, $mode)
    {
        assert(in_array($mode, ['up', 'down']));
        $cate = DB::table('contest_cate')->find($id);
        if ($mode == 'up' && $cate->order > 1) {
            DB::table('contest_cate')
                ->where('parent_id', $cate->parent_id)
                ->whereBetween('order', [$cate->order - 1, $cate->order])
                ->update(['order' => DB::raw(sprintf("%d-`order`", $cate->order * 2 - 1))]);
            return [
                'ok' => 1,
                'msg' => sprintf('类别%s已上移', $cate->title)
            ];
        }
        if ($mode == 'down' && $cate->order < DB::table('contest_cate')->where('parent_id', $cate->parent_id)->count()) {
            DB::table('contest_cate')
                ->where('parent_id', $cate->parent_id)
                ->whereBetween('order', [$cate->order, $cate->order + 1])
                ->update(['order' => DB::raw(sprintf("%d-`order`", $cate->order * 2 + 1))]);
            return [
                'ok' => 1,
                'msg' => sprintf('类别%s已下移', $cate->title)
            ];
        }
        return [
            'ok' => 0,
            'msg' => sprintf('类别%s已处于开头或末尾，无法继续移动', $cate->title)
        ];
    }
}
