<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UploadController;
use App\Http\Helpers\DBHelper;
use App\Http\Helpers\ProblemHelper;
use App\Jobs\Judge\Judger;
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
        ProblemHelper::saveSamples($id, $samp_ins, $samp_outs); // 保存样例

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

    /**
     * 分块上传所有文件
     * @param Request $request
     * @return int
     */
    public function upload_data(Request $request)
    {
        $pid = $request->input('pid');
        $filename = $request->input('filename');
        $allowed_ext = ["in", "out", "ans", "txt"];
        if (!in_array(pathinfo($filename, PATHINFO_EXTENSION), $allowed_ext)) {
            // 文件后缀名不在允许的范围内，不执行上传逻辑。
            abort(400, "不支持的文件类型！请上传以下文件类型：" . implode(',', $allowed_ext));
        }

        // 分块上传文件
        $uc = new UploadController;
        $isUploaded = $uc->upload($request, testdata_path($pid . '/test'));
        if (!$isUploaded)
            return 0; // 返回啥都无所谓，只要成功返回(状态码200)就可以触发前端续传下一片

        return 1;
    }

    // form post
    public function update_data(Request $request)
    {
        $pid = $request->input('pid');
        $filename = $request->input('filename');
        $filename = str_replace('../', '', $filename);
        $filename = str_replace('/', '', $filename);
        $content = $request->input('testdata_content');
        file_put_contents(testdata_path($pid . '/test/' . $filename), str_replace(["\r\n", "\r", "\n"], PHP_EOL, $content));
        return back();
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
        DB::table('tag_marks')->whereIn('tag_id', $tids)->delete(); // 先删除用户提交的标记
        return DB::table('tag_pool')->whereIn('id', $tids)->delete();
    }


    // ============================= 题目导入与导出 ==============================

    /**
     * 上传题目文件，并导入到题库
     */
    public function import(Request $request): ?array
    {
        // 题目文件临时存放目录和文件名
        $folder = "temp/import_problems/" . (Auth::user()->username ?? 'unknown') . "-" . date('YmdHis') . "-" . uniqid();
        $folder = storage_path('app/' . $folder); // 绝对路径
        $filename = 'import_problems.tmp';

        // 文件分片上传
        $uc = new UploadController;
        $isUploaded = $uc->upload($request, $folder, $filename);
        if (!$isUploaded)
            return null;

        // 根据题目来源不同的oj，进行不同的处理
        $imported_pids = [];
        switch (request("source")) {
            case "lduoj":
                $imported_pids = $this->importFromXml($folder . '/' . $filename);
                break;
            case "hoj":
                $imported_pids = $this->importFromHoj($folder . '/' . $filename);
                break;
            default:
                abort(400, "只支持导入以下oj的题目：lduoj、hustoj、hoj");
        }

        if (empty($imported_pids))
            return ['ok' => 0, 'msg' => 'No problems have been imported.', 'numProblems' => 0, 'problemIds' => []];
        else
            return ['ok' => 1, 'msg' => 'problems have been imported',
                'numProblems' => count($imported_pids),
                'problemIds' => $imported_pids
            ];
    }

    /**
     * 给定xml文件路径，将其中的题目信息和数据导入题库。支持lduoj、hoj
     */
    private function importFromXml($xmlPath): array
    {
        // 读取xml->导入题库
        ini_set('memory_limit', '4096M'); // php单线程最大内存占用，默认128M不够用
        $xmlDoc = simplexml_load_file($xmlPath, null, LIBXML_NOCDATA | LIBXML_PARSEHUGE);
        $searchNodes = $xmlDoc->xpath("/*/item");
        $pids = []; //  收集导入后的题号
        foreach ($searchNodes as $node) {
            $problem = [
                'title' => $node->title,
                'description' => $node->description,
                'input' => $node->input,
                'output' => $node->output,
                'hint' => $node->hint,
                'source' => $node->source,
                'spj' => $node->spj ? 1 : 0,
                'tags' => isset($node->tags) && $node->tags != null ? json_encode(
                    array_map(
                        function ($v) {
                            return trim($v);
                        },
                        explode(',', $node->tags)
                    )
                ) : null,
                'time_limit' => $node->time_limit * (strtolower($node->time_limit->attributes()->unit) == 's' ? 1000 : 1), // 本oj用ms
                'memory_limit' => $node->memory_limit / (strtolower($node->memory_limit->attributes()->unit) == 'kb' ? 1024 : 1),
                'user_id' => Auth::id()
            ];

            // 如有特判，提取编程语言代号
            if ($node->spj ?? false) {
                $language = $node->spj->attributes()->language;
                if ($language == 'Python')
                    $language .= '3';  // 本oj只支持python3
                if ($language == 'C++')
                    $language .= '14 -O2';    // 默认C++14 -O2
                if ($language == 'C')
                    $language .= '17';      // 默认C17
                $lang = array_search($language, config('judge.lang')); // 查出编程语言的代号
                // 保存提交记录
                if ($lang !== false) {
                    $problem['spj_language'] = $lang;
                }
            }

            // 保存图片
            foreach ($node->img as $img) {
                $ext = pathinfo($img->src, PATHINFO_EXTENSION); // 后缀
                $save_path = 'public/ckeditor/images/' . uniqid(date('Ymd/His_')) . '.' . $ext; // 路径
                Storage::put($save_path, base64_decode($img->base64)); // 保存
                $problem['description'] = str_replace($img->src, Storage::url($save_path), $problem['description']);
                $problem['input'] = str_replace($img->src, Storage::url($save_path), $problem['input']);
                $problem['output'] = str_replace($img->src, Storage::url($save_path), $problem['output']);
                $problem['hint'] = str_replace($img->src, Storage::url($save_path), $problem['hint']);
            }

            // ==== 写入数据库 =====
            $pid = DB::table('problems')->insertGetId($problem);
            $pids[] = $pid;

            // 保存sample
            $samp_inputs = (array)($node->children()->sample_input);
            $samp_outputs = (array)($node->children()->sample_output);
            ProblemHelper::saveSamples($pid, $samp_inputs, $samp_outputs); // 保存样例
            // 保存test
            $test_inputs = [];
            foreach ($node->children()->test_input as $t) {
                if ($fname = ((string)($t->attributes()->filename) ?? false))
                    $test_inputs[$fname] = (string)$t;
                else $test_inputs[] = (string)$t;
            }
            ProblemHelper::saveTestDatas($pid, $test_inputs, true);
            $test_outputs = [];
            foreach ($node->children()->test_output as $t) {
                if ($fname = ((string)($t->attributes()->filename) ?? false))
                    $test_outputs[$fname] = (string)$t;
                else $test_outputs[] = (string)$t;
            }
            ProblemHelper::saveTestDatas($pid, $test_outputs); // 保存测试数据

            // 保存spj
            if ($node->spj ?? false)
                ProblemHelper::saveSpj($pid, $node->spj);

            // 不存在的标签插入到标签库
            if (isset($node->tags) && $node->tags != null)
                foreach (explode(',', $node->tags) as $tag_name) {
                    DB::table('tag_pool')->updateOrInsert(['name' => trim($tag_name)], ['name' => trim($tag_name), 'user_id' => Auth::id()]);
                }
            // ================================================================

            foreach ($node->solution as $solu) {
                $language = $solu->attributes()->language;
                if ($language == 'Python')
                    $language .= '3';  // 本oj只支持python3
                if ($language == 'C++')
                    $language .= '14 -O2';    // 默认C++14 -O2
                if ($language == 'C')
                    $language .= '17';      // 默认C17
                $lang = array_search($language, config('judge.lang')); // 查出编程语言的代号
                // 保存提交记录
                if ($lang !== false) {
                    $solution = [
                        'problem_id' => $pid,
                        'contest_id' => -1,
                        'user_id' => Auth::id(),
                        'result' => 0,
                        'language' => $lang,
                        'submit_time' => date('Y-m-d H:i:s'),
                        'judge_type' => 'oi', // acm,oi
                        'ip' => ($guest_ip = get_client_real_ip()),
                        'ip_loc' => get_ip_address($guest_ip),
                        'code_length' => strlen($solu),
                        'code' => (string)$solu,
                    ];
                    $solution['id'] = DB::table('solutions')->insertGetId($solution);
                    // 发送到判题队列
                    dispatch(new Judger($solution));
                }
            }
        }

        File::deleteDirectory(dirname($xmlPath)); // 删除已经没用的xml文件
        return $pids;
    }


    /**
     * 给定xml文件路径，将其中的题目信息和数据导入题库。支持lduoj、hoj
     */
    private function importFromHoj($zipPath): array
    {
        Log::info("Start to import problems from hoj zip. user:" . (Auth::user()->username ?? 'unknown'));
        ini_set('memory_limit', '4096M'); // php单线程最大内存占用，默认128M不够用

        $folder = dirname($zipPath);
        $output = $folder . '/output';

        // 使用 ZipArchive 类进行解压缩
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($output);
            $zip->close();
        } else {
            abort(400, "The zip file is damaged and cannot be decompressed.");
        }

        /* 解压后目录结构
         * problem_1001.json
         * problem_1002.json
         * problem_1001/
         *     1.in  1.out  2.in  2.out
         * problem_1002
         *     1.in  1.out  2.in  2.out
         */

        $pids = [];
        // 遍历题目json文件
        foreach (File::allFiles($output) as $file) {
            if (!preg_match('/^problem_\d+\.json$/', $file->getFilename())) {
                continue;
            }
            // 遇到一个题目
            $problemJson = json_decode($file->getContents(), true);  // 题目基本信息
            $problemInfo = $problemJson['problem'];
            $dataDir = $file->getPath() . '/' . $file->getFilenameWithoutExtension(); // 测试数据所在目录

            $problem = [
                'title' => $problemInfo['title'],
                'description' => nl2br(trim($problemInfo['description'])),
                'input' => nl2br(trim($problemInfo['input'])),
                'output' => nl2br(trim($problemInfo['output'])),
                'hint' => nl2br(trim($problemInfo['hint'])),
                'source' => trim($problemInfo['source']),
                'spj' => $problemInfo['judgeMode'] == 'spj' ? 1 : 0,
                'tags' => json_encode($problemJson['tags'] ?? []),
                'time_limit' => $problemInfo['timeLimit'], // 本oj用ms
                'memory_limit' => $problemInfo['memoryLimit'], // 本oj用MB
                'user_id' => Auth::id()
            ];

            // 如有特判，保存 语言、spj代码
            if ($problem['spj']) {
                $language = $problemInfo['spjLanguage'];
                if ($language == 'Python')
                    $language .= '3';  // 本oj只支持python3
                if ($language == 'C++')
                    $language .= '17 -O2';    // 默认C++17 -O2
                if ($language == 'C')
                    $language .= '17';      // 默认C17
                $lang = array_search($language, config('judge.lang')); // 查出编程语言的代号
                // 保存提交记录
                if ($lang !== false) {
                    $problem['spj_language'] = $lang;
                }
            }

            // ==== 写入数据库 =====
            $pid = DB::table('problems')->insertGetId($problem);
            $pids[] = $pid;

            // todo 保存图片

            // 保存样例, 格式为<input>a</input><output>12345</output>
            $inputMatches = [];
            $outputMatches = [];
            // 使用正则表达式匹配 <input> 标签中的内容
            preg_match_all('/<input>([\s\S]*?)<\/input>/', $problemInfo['examples'], $inputMatches);
            // 使用正则表达式匹配 <output> 标签中的内容
            preg_match_all('/<output>([\s\S]*?)<\/output>/', $problemInfo['examples'], $outputMatches);
            // 得到结果
            $samp_inputs = $inputMatches[1];
            $samp_outputs = $outputMatches[1];
            ProblemHelper::saveSamples($pid, $samp_inputs, $samp_outputs); // 保存样例

            // 保存测试数据
            $test_files = [];
            foreach (File::files($dataDir) as $file) {
                if (preg_match('/^.*?\.in$/', $file->getFilename()) || preg_match('/^.*?\.(out|ans)$/', $file->getFilename())) {
                    $test_files[] = $file->getPathname();
                }
            }
            ProblemHelper::saveTestdataFromFile($pid, $test_files);

            // 保存spj
            if ($problem['spj']) {
                ProblemHelper::saveSpj($pid, $problemInfo['spjCode']);
            }

            // 不存在的标签插入到标签库
            foreach ($problemInfo['tags'] ?? [] as $tag_name) {
                DB::table('tag_pool')->updateOrInsert(['name' => trim($tag_name)], ['name' => trim($tag_name), 'user_id' => Auth::id()]);
            }
        } // end for

        File::deleteDirectory(dirname($zipPath)); // 删除已经没用的文件

        return $pids;
    }


    /**
     * 管理员下载xml文件
     */
    public function download_exported_xml(Request $request)
    {
        return Storage::download('temp/exported_problems/' . request('filename'));
    }
}
