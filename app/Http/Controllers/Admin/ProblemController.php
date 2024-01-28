<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UploadController;
use App\Http\Helpers\ProblemHelper;
use App\Jobs\Judge\Judger;
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


    // =================== 题目的导入与导出页面 ========================
    // get
    public function import_export()
    {
        return view('admin.problem.import_export');
    }
}
