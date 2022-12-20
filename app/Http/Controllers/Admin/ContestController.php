<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\Admin\ContestController as ApiAdminContestController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContestController extends Controller
{
    private function get_categories()
    {
        $categories = DB::table('contest_cate as cc')
            ->leftJoin('contest_cate as father', 'father.id', 'cc.parent_id')
            ->select([
                'cc.id', 'cc.title', 'cc.description', 'cc.hidden',
                'cc.order', 'cc.parent_id',
                'cc.updated_at', 'cc.created_at',
                'father.title as parent_title',
                DB::raw('(case cc.parent_id when 0 then 1 else 0 end) as is_parent'),
                DB::raw('(case cc.parent_id when 0 then cc.id else cc.parent_id end) as l1_cate')
            ])
            ->orderBy('l1_cate') // 1 全局，统一按一级类别的order，同一大类挨在一起
            ->orderByDesc('is_parent') // 2 同一父类下，父类排在首位
            ->orderBy('cc.order') // 3 同一父类下的二级类别，按自身order排序
            ->get();
        // dd($categories);
        return $categories;
    }

    public function list()
    {
        $contests = DB::table('contests as c')
            ->leftJoin('users', 'users.id', '=', 'user_id')
            ->select(['c.*', 'username'])
            ->when(isset($_GET['state']) && $_GET['state'] != 'all', function ($q) {
                if ($_GET['state'] == 'ended') return $q->where('end_time', '<', date('Y-m-d H:i:s'));
                else if ($_GET['state'] == 'waiting') return $q->where('start_time', '>', date('Y-m-d H:i:s'));
                else return $q->where('start_time', '<', date('Y-m-d H:i:s'))->where('end_time', '>', date('Y-m-d H:i:s'));
            })
            ->when(isset($_GET['cate_id']) && $_GET['cate_id'] != null, function ($q) {
                return $q->where('c.cate_id', $_GET['cate_id']);
            })
            ->when(isset($_GET['judge_type']) && $_GET['judge_type'] != null, function ($q) {
                return $q->where('judge_type', $_GET['judge_type']);
            })
            ->when(isset($_GET['title']), function ($q) {
                return $q->where('c.title', 'like', '%' . $_GET['title'] . '%');
            })
            ->orderByDesc(isset($_GET['cate_id']) && $_GET['cate_id'] !== '' ? 'c.order' : 'c.id')
            ->paginate($_GET['perPage'] ?? 10);

        $categories = $this->get_categories();
        return view('admin.contest.list', compact('contests', 'categories'));
    }

    public function add(Request $request)
    {
        if ($request->isMethod('get')) {
            $pageTitle = '创建竞赛';
            $categories = $this->get_categories();
            return view('admin.contest.edit', compact('pageTitle', 'categories'));
        }
        if ($request->isMethod('post')) {
            $cid = DB::table('contests')->insertGetId([
                'user_id' => Auth::id(),
                'order' => 0 // 先默认不分类，修改时再调整
            ]);
            $this->update($request, $cid);
            $msg = sprintf('成功创建竞赛：<a href="%s" target="_blank">%d</a>', route('contest.home', $cid), $cid);
            return view('message', ['msg' => $msg, 'success' => true, 'is_admin' => true]);
        }
        return abort(404);
    }

    public function update(Request $request, $id)
    {
        if ($request->isMethod('get')) {
            $contest = DB::table('contests')->find($id);

            /** @var \App\Models\User */
            $user = Auth::user(); // 不是管理员，也不是创建者
            if (!$user->can('admin.contest.update') && $user->id != $contest->user_id)
                return view('message', ['msg' => trans('sentence.Permission denied'), 'success' => false, 'is_admin' => true]);


            $unames = DB::table('contest_users')
                ->leftJoin('users', 'users.id', '=', 'user_id')
                ->where('contest_id', $id)
                ->orderBy('contest_users.id')
                ->pluck('username');
            $pids = DB::table('contest_problems')->where('contest_id', $id)
                ->orderBy('index')->pluck('problem_id');
            $files = [];
            foreach (Storage::allFiles('public/contest/files/' . $id) as &$item) {
                $files[] = array_slice(explode('/', $item), -1, 1)[0];
            }
            $pageTitle = '修改竞赛';
            $categories = $this->get_categories();
            return view('admin.contest.edit', compact('pageTitle', 'contest', 'unames', 'pids', 'files', 'categories'));
        }
        if ($request->isMethod('post')) {
            $old_contest = DB::table('contests')->find($id);


            /** @var \App\Models\User */
            $user = Auth::user(); // 不是管理员，也不是创建者
            if (!$user->can('admin.contest.update') && $user->id != $old_contest->user_id)
                return view('message', ['msg' => trans('sentence.Permission denied'), 'success' => false, 'is_admin' => true]);


            $contest = $request->input('contest');
            $problem_ids = $request->input('problems');
            $c_users = $request->input('contest_users'); //指定用户

            // ======================= 类别 特别注意 =============================
            // 竞赛类别单独处理。竞赛类别改动时，涉及order的变动
            (new ApiAdminContestController())->update_cate_id($id, $contest['cate_id']);
            unset($contest['cate_id']);

            // ======================= 题号列表 注意格式处理 =======================
            $pids = [];
            foreach (explode(PHP_EOL, $problem_ids) as &$item) {
                $item = trim($item);
                if (strlen($item) == 0)
                    continue;
                $line = explode('-', $item);

                if (count($line) == 1) $pids[] = intval($line[0]);
                else if (count($line) == 2)
                    foreach (range(intval($line[0]), intval(($line[1]))) as $i) $pids[] = $i;
            }

            // ======================= 必要的字段处理 =======================
            $contest['start_time'] = str_replace('T', ' ', $contest['start_time']);
            $contest['end_time'] = str_replace('T', ' ', $contest['end_time']);
            if ($contest['access'] != 'password')
                unset($contest['password']);
            $contest['public_rank'] = isset($contest['public_rank']) ? 1 : 0; // 公开榜单

            // ======================= 更新contests =======================
            DB::table('contests')->where('id', $id)->update($contest);

            // ======================= 更新题号列表 =======================
            DB::table('contest_problems')->where('contest_id', $id)->update(['index' => -1]); //标记原来的题目为无效
            foreach ($pids as $i => $pid) {
                if (DB::table('problems')->find($pid)) // 更新index或插入新纪录
                    DB::table('contest_problems')->updateOrInsert(['contest_id' => $id, 'problem_id' => $pid], ['index' => $i]);
            }
            DB::table('contest_problems')->where('contest_id', $id)->where('index', -1)->delete(); // 删除无效纪录

            // ======================= 更新可参与用户 =======================
            if ($contest['access'] == 'private') {
                $unames = explode(PHP_EOL, $c_users);
                foreach ($unames as &$item)
                    $item = trim($item); //去除多余空白符号\r
                $new_uids = DB::table('users')->whereIn('username', $unames)->pluck('id')->toArray();
                $old_uids = DB::table('contest_users')->where('contest_id', $id)->pluck('id')->toArray();
                // 删除无效选手
                DB::table('contest_users')->where('contest_id', $id)->whereIn('id', array_diff($old_uids, $new_uids))->delete();
                // 添加新增选手
                $new_uids = array_diff($new_uids, $old_uids);
                foreach ($new_uids as &$u)
                    $u = ['contest_id' => $id, 'user_id' => $u];
                DB::table('contest_users')->insert($new_uids);
            }

            // 修改了密码 或者 从其它方式变为密码验证方式，则清空参赛选手
            if ($contest['access'] == 'password') {
                if ($contest['password'] != $old_contest->password || $old_contest->access != 'password')
                    DB::table('contest_users')->where('contest_id', $id)->delete();
            }

            // =========================== 附件 =============================
            $files = $request->file('files') ?: [];
            $allowed_ext = ["txt", "pdf", "doc", "docx", "xls", "xlsx", "csv", "ppt", "pptx"];
            foreach ($files as $file) {     //保存附件
                if (in_array($file->getClientOriginalExtension(), $allowed_ext)) {
                    $file->move(storage_path('app/public/contest/files/' . $id), $file->getClientOriginalName()); //保存附件
                }
            }
            $msg = sprintf('成功更新竞赛：<a href="%s">%d</a>', route('contest.home', $id), $id);
            return view('message', ['msg' => $msg, 'success' => true, 'is_admin' => true]);
        }
    }


    public function clone(Request $request)
    {
        $cid = $request->input('cid');
        $contest = DB::table('contests')->find($cid);
        if (isset($contest->id)) {
            unset($contest->id);
            $contest->title .= "[cloned " . $cid . "]";
            $contest->num_members = 0; // 参与人数归零
            $contest->user_id = Auth::id(); // 创建人
            //复制竞赛主体
            $cloned_cid = DB::table('contests')->insertGetId((array)$contest);
            //复制题号
            $con_problems = DB::table('contest_problems')
                ->distinct()->select('problem_id', 'index')
                ->where('contest_id', $cid)
                ->orderBy('index')->get();
            $cps = [];
            foreach ($con_problems as $i => $item)
                $cps[] = ['contest_id' => $cloned_cid, 'problem_id' => $item->problem_id, 'index' => intval($i) + 1];
            DB::table('contest_problems')->insert($cps);
            //            复制附件
            foreach (Storage::allFiles('public/contest/files/' . $cid) as $fp) {
                $name = pathinfo($fp, PATHINFO_FILENAME);  //文件名
                $ext = pathinfo($fp, PATHINFO_EXTENSION);    //拓展名
                Storage::copy($fp, 'public/contest/files/' . $cloned_cid . '/' . $name . $ext);
            }

            return json_encode(['cloned' => true, 'cloned_cid' => $cloned_cid, 'url' => route('admin.contest.update', $cloned_cid)]);
        }
        return json_encode(['cloned' => false]);
    }

    public function delete_file(Request $request, $id)
    {  //$id:竞赛id
        $filename = $request->input('filename');
        if (Storage::exists('public/contest/files/' . $id . '/' . $filename))
            return Storage::delete('public/contest/files/' . $id . '/' . $filename) ? 1 : 0;
        return 0;
    }

    public function update_hidden(Request $request)
    {
        $cids = $request->input('cids') ?: [];
        $hidden = $request->input('hidden');
        return DB::table('contests')->whereIn('id', $cids)->update(['hidden' => $hidden]);
    }

    // 修改榜单的可见性
    public function update_public_rank(Request $request)
    {
        $cids = $request->input('cids') ?: [];
        $public_rank = $request->input('public_rank');
        return DB::table('contests')->whereIn('id', $cids)->update(['public_rank' => $public_rank]);
    }

    /*****************************  类别   ***********************************/
    public function categories(Request $request)
    {
        $categories = $this->get_categories();
        return view('admin.contest.categories', compact('categories'));
    }
}
