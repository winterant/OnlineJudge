<?php

namespace App\Http\Controllers\Admin;

use DOMDocument;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UploadController;
use App\Http\Helpers\ProblemHelper;
use App\Jobs\Judger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class ProblemController extends Controller
{
    // 管理员显示题目列表
    public function list()
    {
        $problems = DB::table('problems as p')
            ->leftJoin('users', 'p.user_id', '=', 'users.id')
            ->select([
                'p.id', 'title', 'type', 'source', 'spj', 'p.created_at', 'hidden',
                'username as creator', 'p.solved', 'p.accepted', 'p.submitted'
            ])
            ->when(request()->has('kw') && request('kw'), function ($q) {
                return $q->where('p.id', request('kw'))
                    ->orWhere('title', 'like', '%' . request('kw') . '%')
                    ->orWhere('source', 'like', '%' . request('kw') . '%')
                    ->orWhere('username', 'like', '%' . request('kw') . '%');
            })
            ->orderByDesc('p.id')
            ->paginate(request('perPage') ?? 100);
        return view('admin.problem.list', compact('problems'));
    }

    // 管理员添加题目
    public function create(Request $request)
    {
        $pageTitle = '添加题目';
        return view('admin.problem.edit', compact('pageTitle'));
    }

    // 管理员修改题目
    public function update(Request $request, $id)
    {
        $pageTitle = '修改题目';
        $problem = DB::table('problems')->find($id);  // 提取出要修改的题目
        if ($problem == null)
            return view('message', ['msg' => '该题目不存在或操作有误!', 'success' => false, 'is_admin' => true]);

        $problem->tags = implode(',', json_decode($problem->tags ?? '[]', true)); // json => string
        $samples = ProblemHelper::readSamples($problem->id);
        $spj_code = ProblemHelper::readSpj($problem->id);
        return view('admin.problem.edit', compact('pageTitle', 'problem', 'samples', 'spj_code'));
    }


    // ======================== 管理题目标签 ===========================
    public function tags()
    {
        $tags = DB::table('tag_marks')
            ->join('users', 'user_id', '=', 'users.id')
            ->join('tag_pool', 'tag_id', '=', 'tag_pool.id')
            ->join('problems', 'problem_id', '=', 'problems.id')
            ->select('tag_marks.id', 'problem_id', 'title', 'username', 'nick', 'name', 'tag_marks.created_at')
            ->when(request()->has('pid') && request('pid') != '', function ($q) {
                return $q->where('problem_id', request('pid'));
            })
            ->when(request()->has('username') && request('username') != '', function ($q) {
                return $q->where('username', request('username'));
            })
            ->when(request()->has('tag_name') && request('tag_name') != '', function ($q) {
                return $q->where('name', 'like', '%' . request('tag_name') . '%');
            })
            ->orderByDesc('id')
            ->paginate(request()->has('perPage') ? request('perPage') : 20);
        return view('admin.problem.tags', compact('tags'));
    }

    public function tag_pool()
    {
        $tag_pool = DB::table('tag_pool as tp')
            ->leftJoin('users as u', 'u.id', '=', 'user_id')
            ->select('tp.id', 'tp.name', 'tp.hidden', 'u.username as creator', 'tp.created_at')
            ->when(request()->has('tag_name') && request('tag_name') != '', function ($q) {
                return $q->where('tp.name', 'like', '%' . request('tag_name') . '%');
            })
            ->orderByDesc('tp.id')
            ->paginate(request()->has('perPage') ? request('perPage') : 20);
        return view('admin.problem.tag_pool', compact('tag_pool'));
    }


    // ============================== 测试数据管理 ==============================
    // 测试数据管理页面 get
    public function test_data()
    {
        // 读取数据文件
        $tests = [];
        if (request()->has('pid')) {
            if (!DB::table('problems')->where('id', request('pid'))->exists())
                return view('message', ['msg' => '题目' . request('pid') . '不存在', 'success' => false, 'is_admin' => true]);
            foreach (get_all_files_path(testdata_path(request('pid') . '/test')) as $filepath) {
                $name = pathinfo($filepath, PATHINFO_FILENAME);  // 文件名
                $ext = pathinfo($filepath, PATHINFO_EXTENSION);    // 拓展名
                $tests[] = ['index' => $name, 'filename' => $name . '.' . $ext, 'size' => filesize($filepath)];
            }
        }
        uasort($tests, function ($x, $y) {
            return $x['index'] > $y['index'];
        });
        return view('admin.problem.test_data', compact('tests'));
    }

    // ajax post
    public function upload_data(Request $request)
    {
        $pid = $request->input('pid');
        $filename = $request->input('filename');
        $filename = str_replace('../', '', $filename);
        $filename = str_replace('/', '', $filename);
        $allowed_ext = ["in", "out", "ans", "txt"];
        if (!in_array(pathinfo($filename, PATHINFO_EXTENSION), $allowed_ext)) {
            // 文件后缀名不在允许的范围内，不执行上传逻辑。
            return 1;
        }

        $uc = new UploadController;
        $isUploaded = $uc->upload($request, testdata_path($pid . '/test'), $filename);
        if (!$isUploaded)
            return 0;

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


    // =================== 题目的导入与导出 ========================
    // get
    public function import_export()
    {
        $files = Storage::allFiles('temp/exported_problems');
        $files = array_reverse($files);
        $history_xml = [];
        foreach ($files as $path) {
            if (time() - Storage::lastModified($path) > 3600 * 24 * 365) // 超过365天的数据删除掉
                Storage::delete($path);
            else {
                $info = pathinfo($path);
                $status = [
                    'pending' => '等待中',
                    'running' => '运行中',
                    'saving' => '保存中',
                    'failed' => '失败',
                    'xml' => '成功'
                ][$info['extension']];
                preg_match('/\[(\S+?)\]/', $info['filename'], $matches); // 匹配出创建者用户名
                $history_xml[] = [
                    'name' => $info['basename'],
                    'status' => $status,
                    'creator' => $matches[1] ?? '',
                    'created_at' => date('Y-m-d H:i:s', Storage::lastModified($path))
                ];
            }
        }
        return view('admin.problem.import_export', compact('history_xml'));
    }

    // post
    public function import(Request $request)
    {
        if (!$request->isMethod('post')) {
            return redirect(route('admin.problem.import_export'));
        }

        $folder = uniqid("temp/import_problems/" . (Auth::user()->username ?? 'unknown') . "-"); // 末尾长度13的随机串

        $uc = new UploadController;
        $isUploaded = $uc->upload($request, storage_path('app/' . $folder), 'import_problems.xml');
        if (!$isUploaded)
            return 0;

        // 读取xml->导入题库
        ini_set('memory_limit', '4096M'); // php单线程最大内存占用，默认128M不够用
        $xmlDoc = simplexml_load_file(storage_path('app/' . $folder . '/import_problems.xml'), null, LIBXML_NOCDATA | LIBXML_PARSEHUGE);
        $searchNodes = $xmlDoc->xpath("/*/item");
        $first_pid = null;
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
            $pid = DB::table('problems')->insertGetId($problem);
            if (!$first_pid)
                $first_pid = $pid;
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
        Storage::deleteDirectory($folder); // 删除已经没用的xml文件
        return $first_pid . ($first_pid < $pid ? '-' . $pid : '');
    }

}
