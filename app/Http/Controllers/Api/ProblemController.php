<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\DBHelper;
use App\Http\Helpers\ProblemHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProblemController extends Controller
{
    public function create(Request $request)
    {
        $pid = DB::table('problems')->insertGetId(['user_id' => Auth::id()]);
        $ret = $this->update($request, $pid);
        $ret['msg'] = "已创建题目 {$pid} ";
        return $ret;
    }

    public function update(Request $request, $id)
    {
        $problem = $request->input('problem');
        $problem['spj'] = (isset($problem['spj']) ? 1 : 0); // 默认不特判

        // 标签使用json保存。同时，不存在的标签插入到标签库
        if (empty($problem['tags'])) {
            $problem['tags'] = null; // json字段只能为null，不能为空串
        } else {
            $problem['tags'] = json_encode(
                array_map(
                    function ($v) {
                        return trim($v);
                    },
                    explode(',', $problem['tags'])
                )
            );
            foreach (json_decode($problem['tags'], true) as $tag_name) {
                DB::table('tag_pool')->updateOrInsert(['name' => $tag_name], ['name' => $tag_name, 'user_id' => Auth::id()]);
            }
        }
        // ================================================================

        $problem['updated_at'] = date('Y-m-d H:i:s');
        $update_ret = DB::table('problems')
            ->where('id', $id)
            ->update($problem);
        if (!$update_ret)
            return ['ok' => 0, 'msg' => '没有任何数据被修改，请检查操作是否合理!'];

        ///保存样例
        $samp_ins = (array)$request->input('sample_ins');
        $samp_outs = (array)$request->input('sample_outs');
        ProblemHelper::saveSamples($id, $samp_ins, $samp_outs); //保存样例

        // 保存spj
        if ($problem['spj'])
            ProblemHelper::saveSpj($id, $request->input('spj_code') ?? null);

        return ['ok' => 1, 'msg' => "已成功修改题目 {$id} ", 'data' => [
            'problem_url' => route('problem', $id),
            'testdata_url' => route('admin.problem.test_data', ['pid' => $id])
        ]];
    }

    public function update_batch_to_one(Request $request)
    {
        $ids = $request->input('ids') ?? [];
        $value = $request->input('value');
        $updated = DBHelper::update_batch_to_one('problems', ['id' => $ids], $value);
        if ($updated > 0)
            return ['ok' => 1, 'msg' => '成功修改' . $updated . '条数据'];
        return ['ok' => 0, 'msg' => '没有任何数据被修改'];
    }

    // 删除1个题目
    public function delete($problem_id)
    {
        // 查一下该竞赛涉及哪些群组，被引用到群组时，禁止删除
        $involved_contests = DB::table('contest_problems as cp')
            ->join('contests as c', 'c.id', 'cp.contest_id')
            ->where('problem_id', $problem_id)
            ->get(['c.id', 'c.title']);
        if (count($involved_contests)) {
            $msg = '当前题目已在以下竞赛中被使用，无法删除。<br>如需删除，请先在相应的竞赛中取消该题目。<br>';
            $count_contests = count($involved_contests);
            for ($i = 0; $i < 2 && $i < $count_contests; $i++) {
                $item = $involved_contests[$i];
                $msg .= sprintf("%d.%s<br>", $item->id, $item->title);
            }
            if ($count_contests > 2) {
                $msg .= '...<br>更多涉及竞赛请前往题目页面查看。';
            }
            return ['ok' => 0, 'msg' => $msg];
        }
        $deleted = DB::table('problems')->delete($problem_id);
        // 删除文件夹，删除测试数据文件夹
        if (File::isDirectory(testdata_path($problem_id)))
            File::deleteDirectories(testdata_path($problem_id));

        return ['ok' => 1, 'msg' => sprintf("已成功删除%d条竞赛", $deleted)];
    }


    // ================ 测试数据管理 =============================
    public function get_data($pid, $filename)
    {
        $filename = str_replace('../', '', $filename);
        $filename = str_replace('/', '', $filename);
        $data = file_get_contents(testdata_path($pid . '/test/' . $filename));
        return ['ok' => 1, 'data' => $data];
    }

    public function delete_data(Request $request, $pid)
    {
        $fnames = $request->input('fnames');
        foreach ($fnames as $filename)
            if (file_exists(testdata_path($pid . '/test/' . $filename)))
                unlink(testdata_path($pid . '/test/' . $filename));
        return ['ok' => 1, 'msg' => 'Deleted.'];
    }


    // ===================== 题目标签 ========================≠≠
    // 普通用户为已通过的题目贡献标签（题目涉及知识点）
    function submit_problem_tag(Request $request)
    {
        $problem_id = $request->input('problem_id');
        $tag_names = $request->input('tag_names') ?? [];
        $tag_names = array_unique($tag_names);
        $tag_marks = [];
        foreach ($tag_names as $tag_name) {
            if (!DB::table('tag_pool')->where('name', $tag_name)->exists())
                $tid = DB::table('tag_pool')->insertGetId(['name' => $tag_name, 'user_id' => Auth::id()]);
            else
                $tid = DB::table('tag_pool')->where('name', $tag_name)->first()->id;
            $tag_marks[] = ['problem_id' => $problem_id, 'user_id' => Auth::id(), 'tag_id' => $tid];
        }
        $inserted = DB::table('tag_marks')->insert($tag_marks);
        if ($inserted == 0)
            return [
                'ok' => 0,
                'msg' => '提交失败，请检查数据合法性'
            ];
        return [
            'ok' => 1,
            'msg' => sprintf("成功为题目%d添加%d个标签", $problem_id, $inserted)
        ];
    }

    // ========================= 收集标签管理 ===========================
    // 批量删除用户提交的标签（但不删除标签库）
    public function tag_delete_batch(Request $request)
    {
        $tids = $request->input('ids');
        $updated = DB::table('tag_marks')->whereIn('id', $tids)->delete();
        return ['ok' => $updated ? 1 : 0, 'msg' => $updated ? 'Deleted' : 'Failed to delete'];
    }

    // 更新单个tag_pool
    public function tag_pool_update(Request $request, $id)
    {
        $updated = DB::table('tag_pool')->where('id', $id)->update($request->input('value'));
        if ($updated) {
            return [
                'ok' => 1,
                'msg' => 'Updated successfully!'
            ];
        }
        return [
            'ok' => 0,
            'msg' => 'Failed to update!'
        ];
    }
    /**
     * 批量更新groups记录
     *
     * patch request:{
     *   ids:[1,2,...],
     *   value:{},
     * }
     */
    public function tag_pool_update_batch(Request $request)
    {
        $ids = $request->input('ids') ?? [];
        $value = $request->input('value');
        Log::info($ids);
        Log::info($value);
        $updated = DBHelper::update_batch_to_one('tag_pool', ['id' => $ids], $value);
        if ($updated > 0)
            return ['ok' => 1, 'msg' => '成功修改' . $updated . '条数据'];
        return ['ok' => 0, 'msg' => '没有任何数据被修改'];
    }

    // 批量删除标签库中的标签
    public function tag_pool_delete_batch(Request $request)
    {
        $tids = $request->input('ids') ?: [];
        DB::table('tag_marks')->whereIn('tag_id', $tids)->delete(); //先删除用户提交的标记
        return DB::table('tag_pool')->whereIn('id', $tids)->delete();
    }


    // 管理员下载xml文件
    public function download_exported_xml(Request $request)
    {
        return Storage::download('temp/exported/' . request('filename'));
    }

    // 管理员清空历史xml
    public function clear_exported_xml(Request $request)
    {
        Storage::delete(Storage::allFiles('temp/exported'));
        return ['ok' => 1, 'msg' => '已清空'];
    }
}
