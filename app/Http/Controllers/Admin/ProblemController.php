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
    //管理员显示题目列表
    public function list()
    {
        $problems = DB::table('problems as p')
            ->leftJoin('users', 'p.user_id', '=', 'users.id')
            ->select(
                'p.id',
                'title',
                'type',
                'source',
                'spj',
                'p.created_at',
                'hidden',
                'username as creator',
                'p.solved',
                'p.accepted',
                'p.submitted'
            )
            ->when(request()->has('kw') && request('kw'), function ($q) {
                return $q->where('p.id', request('kw'))
                    ->orWhere('title', 'like', '%' . request('kw') . '%')
                    ->orWhere('source', 'like', '%' . request('kw') . '%');
            })
            ->orderByDesc('p.id')
            ->paginate(request('perPage') ?? 100);
        return view('admin.problem.list', compact('problems'));
    }

    //管理员添加题目
    public function add(Request $request)
    {
        //提供加题界面
        if ($request->isMethod('get')) {
            $pageTitle = '添加题目';
            return view('admin.problem.edit', compact('pageTitle'));
        }
        //提交一条新题目
        if ($request->isMethod('post')) {
            $pid = DB::table('problems')->insertGetId(['user_id' => Auth::id()]);
            return $this->update($request, $pid, true);
        }
    }

    //管理员修改题目
    public function update(Request $request, $id)
    {
        //get提供修改界面
        if ($request->isMethod('get')) {
            $pageTitle = '修改题目';
            $problem = DB::table('problems')->find($id);  // 提取出要修改的题目
            if ($problem == null)
                return view('message', ['msg' => '该题目不存在或操作有误!', 'success' => false, 'is_admin' => true]);

            $problem->tags = implode(',', json_decode($problem->tags ?? '[]', true)); // json => string
            $samples = ProblemHelper::readSamples($problem->id);
            return view('admin.problem.edit', compact('pageTitle', 'problem', 'samples'));
        }

        // 提交修改好的题目
        if ($request->isMethod('post')) {
            $problem = DB::table('problems')->find($id);  // 提取出要修改的题目
            // 读取表单
            $problem = $request->input('problem');
            if (!isset($problem['spj'])) // 默认不特判
                $problem['spj'] = 0;

            // 标签使用json保存。同时，不存在的标签插入到标签库
            if (!empty($problem['tags'])) {
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
                    // if (!DB::table('tag_pool')->where('name', $tag_name)->exists())
                    //     $tid = DB::table('tag_pool')->insertGetId(['name' => $tag_name]);
                    // else
                    //     $tid = DB::table('tag_pool')->where('name', $tag_name)->first()->id;
                    // $tag_marks[] = ['problem_id' => $id, 'user_id' => Auth::id(), 'tag_id' => $tid];
                }
                // foreach ($tag_marks ?? [] as $mark) {
                //     DB::table('tag_marks')->updateOrInsert($mark, $mark);
                // }
            }
            // ================================================================

            $problem['updated_at'] = date('Y-m-d H:i:s');
            $update_ret = DB::table('problems')
                ->where('id', $id)
                ->update($problem);
            if (!$update_ret)
                return view('message', ['msg' => '没有任何数据被修改，请检查操作是否合理!', 'success' => false, 'is_admin' => true]);

            ///保存样例
            $samp_ins = (array)$request->input('sample_ins');
            $samp_outs = (array)$request->input('sample_outs');
            ProblemHelper::saveSamples($id, $samp_ins, $samp_outs); //保存样例

            // 保存spj
            ProblemHelper::saveSpj($id, $request->input('spj_code')??'111');

            $msg = sprintf(
                '题目<a href="%s" target="_blank">%d</a>修改成功！ <a href="%s">上传测试数据</a>',
                route('problem', $id),
                $id,
                route('admin.problem.test_data', 'pid=' . $id)
            );
            return view('message', ['msg' => $msg, 'success' => true, 'is_admin' => true]);
        }
    }

    //管理员修改题目状态  0密封 or 1公开
    public function update_hidden(Request $request)
    {
        if ($request->isMethod('post')) {
            $pids = $request->input('pids') ?: [];
            $hidden = $request->input('hidden');
            return DB::table('problems')
                ->whereIn('id', $pids)
                ->update(['hidden' => $hidden]);
        }
        return 0;
    }


    //管理标签
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


    // ============================== 测试数据管理（2.1未来版本将遗弃） ==============================
    // 测试数据管理页面 get
    public function test_data()
    {
        //读取数据文件
        $tests = [];
        if (request()->has('pid')) {
            if (!DB::table('problems')->where('id', request('pid'))->exists())
                return view('message', ['msg' => '题目' . request('pid') . '不存在', 'success' => false, 'is_admin' => true]);
            foreach (getAllFilesPath(testdata_path(request('pid') . '/test')) as $filepath) {
                $name = pathinfo($filepath, PATHINFO_FILENAME);  //文件名
                $ext = pathinfo($filepath, PATHINFO_EXTENSION);    //拓展名
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
            //文件后缀名不在允许的范围内，不执行上传逻辑。
            return 1;
        }

        $uc = new UploadController;
        $isUploaded = $uc->upload($request, testdata_path($pid . '/test'), $filename);
        if (!$isUploaded) return 0;

        return 1;
    }

    // ajax post
    public function get_data(Request $request)
    {
        $pid = $request->input('pid');
        $filename = $request->input('filename');
        $filename = str_replace('../', '', $filename);
        $filename = str_replace('/', '', $filename);
        $data = file_get_contents(testdata_path($pid . '/test/' . $filename));
        return json_encode($data);
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

    // ajax post
    public function delete_data(Request $request)
    {
        $pid = $request->input('pid');
        $fnames = $request->input('fnames');
        foreach ($fnames as $filename)
            if (file_exists(testdata_path($pid . '/test/' . $filename)))
                unlink(testdata_path($pid . '/test/' . $filename));
        return 1;
    }

    // get
    public function import_export()
    {
        $files = Storage::allFiles('temp/exported');
        $files = array_reverse($files);
        $history_xml = [];
        foreach ($files as $path) {
            if (time() - Storage::lastModified($path) > 3600 * 24 * 365) // 超过365天的数据删除掉
                Storage::delete($path);
            else {
                $info = pathinfo($path);
                preg_match('/\[(\S+?)\]/', $info['filename'], $matches);
                $history_xml[] = [
                    'name' => $info['basename'],
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

        $uc = new UploadController;
        $isUploaded = $uc->upload($request, storage_path('app/temp/import'), 'import_problems.xml');
        if (!$isUploaded) return 0;

        //读取xml->导入题库
        ini_set('memory_limit', '4096M'); //php单线程最大内存占用，默认128M不够用
        $xmlDoc = simplexml_load_file(storage_path('app/temp/import/import_problems.xml'), null, LIBXML_NOCDATA | LIBXML_PARSEHUGE);
        $searchNodes = $xmlDoc->xpath("/*/item");
        $first_pid = null;
        foreach ($searchNodes as $node) {
            $problem = [
                'title'       => $node->title,
                'description' => $node->description,
                'input'       => $node->input,
                'output'      => $node->output,
                'hint'        => $node->hint,
                'source'      => $node->source,
                'spj'         => $node->spj ? 1 : 0,
                'tags'        => isset($node->tags) && $node->tags != null ? json_encode(
                    array_map(
                        function ($v) {
                            return trim($v);
                        },
                        explode(',', $node->tags)
                    )
                ) : null,
                'time_limit'  => $node->time_limit * (strtolower($node->time_limit->attributes()->unit) == 's' ? 1000 : 1), //本oj用ms
                'memory_limit' => $node->memory_limit / (strtolower($node->memory_limit->attributes()->unit) == 'kb' ? 1024 : 1),
                'user_id'     => Auth::id()
            ];
            //保存图片
            foreach ($node->img as $img) {
                $ext = pathinfo($img->src, PATHINFO_EXTENSION); //后缀
                $save_path = 'public/ckeditor/images/' . uniqid(date('Ymd/His_')) . '.' . $ext; //路径
                Storage::put($save_path, base64_decode($img->base64)); //保存
                $problem['description'] = str_replace($img->src, Storage::url($save_path), $problem['description']);
                $problem['input']      = str_replace($img->src, Storage::url($save_path), $problem['input']);
                $problem['output']     = str_replace($img->src, Storage::url($save_path), $problem['output']);
                $problem['hint']       = str_replace($img->src, Storage::url($save_path), $problem['hint']);
            }
            $pid = DB::table('problems')->insertGetId($problem);
            if (!$first_pid) $first_pid = $pid;
            //下面保存sample，test
            $samp_inputs = (array)($node->children()->sample_input);
            $samp_outputs = (array)($node->children()->sample_output);
            $test_inputs = (array)($node->children()->test_input);
            $test_outputs = (array)($node->children()->test_output);
            ProblemHelper::saveSamples($pid, $samp_inputs, $samp_outputs); //保存样例
            ProblemHelper::saveTestData($pid, $test_inputs, $test_outputs); //保存测试数据

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
                if ($language == 'Python') $language .= '3';  // 本oj只支持python3
                if ($language == 'C++') $language .= '14 -O2';    // 默认C++14 -O2
                if ($language == 'C') $language .= '17';      // 默认C17
                $lang = array_search($language, config('judge.lang')); // 查出编程语言的代号
                //保存提交记录
                if ($lang !== false) {
                    $solution = [
                        'problem_id'    => $pid,
                        'contest_id'    => -1,
                        'user_id'       => Auth::id(),
                        'result'        => 0,
                        'language'      => $lang,
                        'submit_time'   => date('Y-m-d H:i:s'),
                        'judge_type'    => 'oi', //acm,oi
                        'ip'            => ($guest_ip = get_client_real_ip()),
                        'ip_loc'        => getIpAddress($guest_ip),
                        'code_length'   => strlen($solu),
                        'code'          => (string)$solu,
                    ];
                    $solution['id'] = DB::table('solutions')->insertGetId($solution);
                    // 发送到判题队列
                    dispatch(new Judger($solution));
                }
            }
        }
        Storage::deleteDirectory("temp/import"); //删除已经没用的xml文件
        return $first_pid . ($first_pid < $pid ? '-' . $pid : '');
    }

    // post
    public function export(Request $request)
    {
        // 辅助函数：导出题目时，描述、题目数据等可能含有xml不支持的特殊字符，过滤掉
        $filter_special_characters = function ($str) {
            return preg_replace('/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/', '', $str);
        };

        // 只接受post请求
        if (!$request->isMethod('post')) {
            return redirect(route('admin.problem.import_export'));
        }
        ini_set('memory_limit', '2G'); //php单线程最大内存占用，默认128M不够用
        //处理题号,获取题目
        $problem_ids = decode_str_to_array($request->input('pids'));
        $problems = DB::table("problems")->whereIn('id', $problem_ids)->orderBy('id')->get();

        // 生成xml
        $dom = new DOMDocument("1.0", "UTF-8");
        $root = $dom->createElement('fps'); //为了兼容hustoj的fps标签
        // 作者信息 generator标签
        $generator = $dom->createElement('generator');
        $attr = $dom->createAttribute('name');
        $attr->appendChild($dom->createTextNode('LDUOJ'));
        $generator->appendChild($attr);
        $attr = $dom->createAttribute('url');
        $attr->appendChild($dom->createTextNode('https://github.com/winterant/OnlineJudge'));
        $generator->appendChild($attr);
        $root->appendChild($generator);
        //遍历题目，生成xml字符串
        foreach ($problems as $problem) {
            $item = $dom->createElement('item');
            //title
            $title = $dom->createElement('title');
            $title->appendChild($dom->createCDATASection($filter_special_characters($problem->title)));
            $item->appendChild($title);
            //time_limit
            $unit = $dom->createAttribute('unit');
            $unit->appendChild($dom->createTextNode('ms'));
            $time_limit = $dom->createElement('time_limit');
            $time_limit->appendChild($unit);
            $time_limit->appendChild($dom->createCDATASection($problem->time_limit));
            $item->appendChild($time_limit);
            //memory_limit
            $unit = $dom->createAttribute('unit');
            $unit->appendChild($dom->createTextNode('mb'));
            $memory_limit = $dom->createElement('memory_limit');
            $memory_limit->appendChild($unit);
            $memory_limit->appendChild($dom->createCDATASection($problem->memory_limit));
            $item->appendChild($memory_limit);
            //description
            $description = $dom->createElement('description');
            $description->appendChild($dom->createCDATASection($filter_special_characters($problem->description)));
            $item->appendChild($description);
            //input
            $input = $dom->createElement('input');
            $input->appendChild($dom->createCDATASection($filter_special_characters($problem->input)));
            $item->appendChild($input);
            //output
            $output = $dom->createElement('output');
            $output->appendChild($dom->createCDATASection($filter_special_characters($problem->output)));
            $item->appendChild($output);
            //hint
            $hint = $dom->createElement('hint');
            $hint->appendChild($dom->createCDATASection($filter_special_characters($problem->hint)));
            $item->appendChild($hint);
            //source
            $source = $dom->createElement('source');
            $source->appendChild($dom->createCDATASection($filter_special_characters($problem->source)));
            $item->appendChild($source);

            //sample_input & sample_output
            foreach (ProblemHelper::readSamples($problem->id) as $sample) {
                $sample_input = $dom->createElement('sample_input');
                $sample_input->appendChild($dom->createCDATASection($filter_special_characters($sample['in'])));
                $item->appendChild($sample_input);
                $sample_output = $dom->createElement('sample_output');
                $sample_output->appendChild($dom->createCDATASection($filter_special_characters($sample['out'])));
                $item->appendChild($sample_output);
            }
            //test_input & test_output
            foreach (ProblemHelper::readTestData($problem->id) as $test) {
                $test_input = $dom->createElement('test_input');
                $test_input->appendChild($dom->createCDATASection($filter_special_characters($test['in'])));
                $item->appendChild($test_input);
                $test_output = $dom->createElement('test_output');
                $test_output->appendChild($dom->createCDATASection($filter_special_characters($test['out'])));
                $item->appendChild($test_output);
            }
            //spj language
            if ($problem->spj) {
                $spj_code = ProblemHelper::readSpj($problem->id);

                $cpp = $dom->createElement('spj');
                $attr = $dom->createAttribute('language');
                $attr->appendChild($dom->createTextNode(config("judge.lang.{$problem->spj_language}"))); // spj 语言
                $cpp->appendChild($attr);
                $cpp->appendChild($dom->createCDATASection($spj_code));
                $item->appendChild($cpp);
            }
            // tags
            $tags = $dom->createElement('tags');
            $tags->appendChild($dom->createCDATASection(implode(',', json_decode($problem->tags ?? '[]', true))));
            $item->appendChild($tags);

            //solution language
            $solutions = DB::table('solutions')
                ->select('language', 'code')
                ->whereRaw("id in(select min(id) from solutions where problem_id=? and result=4 group by language)", [$problem->id])
                ->get();
            foreach ($solutions as $sol) {
                $solution = $dom->createElement('solution');
                $attr = $dom->createAttribute('language');
                $attr->appendChild($dom->createTextNode(config('judge.lang.' . $sol->language)));
                $solution->appendChild($attr);
                $solution->appendChild($dom->createCDATASection($sol->code));
                $item->appendChild($solution);
            }

            //img of description,input,output,hint
            preg_match_all('/<img.*?src=\"(.*?)\".*?>/i', $problem->description . $problem->input . $problem->output . $problem->hint, $matches);
            foreach ($matches[1] as $url) {
                $stor_path = str_replace("storage", "public", $url);
                if (Storage::exists($stor_path)) {
                    $img = $dom->createElement('img');
                    $src = $dom->createElement('src');
                    $src->appendChild($dom->createCDATASection($url));
                    $img->appendChild($src);
                    $base64 = $dom->createElement('base64');
                    $base64->appendChild($dom->createCDATASection(base64_encode(Storage::get($stor_path))));
                    $img->appendChild($base64);
                    $item->appendChild($img);
                }
            }
            //将该题插入root
            $root->appendChild($item);
        }
        $dom->appendChild($root);

        // 创建文件夹，顺便删除过期文件
        $dir = "temp/exported";
        if (!Storage::exists($dir))
            Storage::makeDirectory($dir);
        foreach (Storage::allFiles($dir) as $fpath) {  //删除365天以上的文件
            if (time() - Storage::lastModified($fpath) > 3600 * 24 * 365)
                Storage::delete($fpath);
        }
        // 根据传入题号命名文件名
        $filename = str_replace(["\r\n", "\r", "\n"], ',', trim($request->input('pids')));
        $filename = sprintf('%s[%s]%s', date('YmdHis'), Auth::user()->username, $filename);
        if (strlen($filename) > 36) // 文件名过长用省略号代替
            $filename = substr($filename, 0, 36) . '...';
        $filepath = sprintf('%s/%s.xml', $dir, $filename);

        //  保存文件，并提供下载链接
        $dom->save(Storage::path($filepath));
        return Storage::download($filepath);
    }
}
