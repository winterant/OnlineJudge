<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProblemController extends Controller
{
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
        File::deleteDirectories(testdata_path($problem_id));

        return ['ok' => 1, 'msg' => sprintf("已成功删除%d条竞赛", $deleted)];
    }

    // 普通用户为已通过的题目贡献标签（题目涉及知识点）
    function submit_problem_tag(Request $request)
    {
        $problem_id = $request->input('problem_id');
        $tag_names = $request->input('tag_names');
        $tag_names = array_unique($tag_names);
        $tag_marks = [];
        foreach ($tag_names as $tag_name) {
            if (!DB::table('tag_pool')->where('name', $tag_name)->exists())
                $tid = DB::table('tag_pool')->insertGetId(['name' => $tag_name]);
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
