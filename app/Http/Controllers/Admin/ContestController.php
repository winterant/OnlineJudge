<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsArray\consume;

class ContestController extends Controller
{
    private function get_categories()
    {
        $categories = DB::table('contest_cate as cc')
            ->leftJoin('contest_cate as other', 'other.id', '=', 'cc.parent_id')
            ->select('cc.*')
            ->orderBy('cc.order')->get();

        //找出父分类的名字
        $cate_titles = [];
        foreach ($categories as $cate) {
            $cate_titles[$cate->id] = $cate->title;
        }
        foreach ($categories as &$cate) {
            $cate->parent_title = $cate_titles[$cate->parent_id] ?? null;
        }
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
            ->orderByDesc('c.order')
            ->orderByDesc('c.id')
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
            $cid = DB::table('contests')->insertGetId(['user_id' => Auth::id()]);
            $this->update($request, $cid);
            DB::table('contests')->update(['order' => $cid]);//设置顺序
            $msg = sprintf('成功创建竞赛：<a href="%s" target="_blank">%d</a>', route('contest.home', $cid), $cid);
            return view('admin.success', compact('msg'));
        }
        return abort(404);
    }

    public function update(Request $request, $id)
    {
        if (!privilege(Auth::user(),'admin') && Auth::id() != DB::table('contests')->where('id', $id)->value('id'))
            return view('admin.fail', ['msg' => '权限不足！您不是这场比赛的创建者']);

        if ($request->isMethod('get')) {
            $contest = DB::table('contests')->find($id);
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
            $contest = $request->input('contest');
            $problem_ids = $request->input('problems');
            $c_users = $request->input('contest_users'); //指定用户
            //数据格式处理
            foreach (explode(PHP_EOL, $problem_ids) as &$item) {
                $line = explode('-', $item);
                if (count($line) == 1) $pids[] = intval($line[0]);
                else foreach (range(intval($line[0]), intval(($line[1]))) as $i) $pids[] = $i;
            }
            $contest['start_time'] = str_replace('T', ' ', $contest['start_time']);
            $contest['end_time'] = str_replace('T', ' ', $contest['end_time']);
            if ($contest['access'] != 'password') unset($contest['password']);

            //数据库
            DB::table('contests')->where('id', $id)->update($contest);
            DB::table('contest_problems')->where('contest_id', $id)->delete();//舍弃原来的题目
            foreach ($pids as $i => $pid) {
                if (DB::table('problems')->find($pid))
                    DB::table('contest_problems')->insert(['contest_id' => $id, 'problem_id' => $pid, 'index' => $i]);
            }
            //可参与用户
            DB::table('contest_users')->where('contest_id', $id)->delete();
            if ($contest['access'] == 'private') {
                $unames = explode(PHP_EOL, $c_users);
                foreach ($unames as &$item) $item = trim($item); //去除多余空白符号\r
                $uids = DB::table('users')->whereIn('username', $unames)->pluck('id');
                foreach ($uids as &$uid) {
                    DB::table('contest_users')->insertOrIgnore(['contest_id' => $id, 'user_id' => $uid]);
                }
            }

            //附件
            $files = $request->file('files') ?: [];
            $allowed_ext = ["txt", "pdf", "doc", "docx", "xls", "xlsx", "csv", "ppt", "pptx"];
            foreach ($files as $file) {     //保存附件
                if(in_array($file->getClientOriginalExtension(), $allowed_ext)){
                    $file->move(storage_path('app/public/contest/files/' . $id), $file->getClientOriginalName());//保存附件
                }
            }
            $msg = sprintf('成功更新竞赛：<a href="%s">%d</a>', route('contest.home', $id), $id);
            return view('admin.success', compact('msg'));
        }
    }


    public function clone(Request $request)
    {
        $cid = $request->input('cid');
        $contest = DB::table('contests')->find($cid);
        if (isset($contest->id)) {
            unset($contest->id);
            $contest->title .= "[cloned " . $cid . "]";
            //复制竞赛主体
            $cloned_cid = DB::table('contests')->insertGetId((array)$contest);
            //复制题号
            $con_problems = DB::table('contest_problems')
                ->distinct()->select('problem_id', 'index')
                ->where('contest_id', $cid)
                ->orderBy('index')->get();
            $cps = [];
            foreach ($con_problems as $i => $item)
                $cps[] = ['contest_id' => $cloned_cid, 'problem_id' => $item->problem_id, 'index' => $i + 1];
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

    /*
    public function set_top(Request $request)
    {
        $cid = $request->input('cid');
        $way = $request->input('way');
        if ($way == 0)
            $new_top = 0;
        else
            $new_top = DB::table('contests')->max('top') + 1;
        DB::table('contests')->where('id', $cid)->update(['top' => $new_top]);
        return $new_top;
    }
    */

    public function delete(Request $request)
    {
        $cids = $request->input('cids') ?: [];
        if (privilege(Auth::user(), 'admin')) //超管，直接进行
            $ret = DB::table('contests')->whereIn('id', $cids)->delete();
        else
            $ret = DB::table('contests')->whereIn('id', $cids)->where('user_id', Auth::id())->delete();//创建者
        if ($ret > 0) {
            foreach ($cids as $cid) {
                Storage::deleteDirectory('public/contest/files/' . $cid); //删除附件
            }
        }
        return $ret;
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
        if (privilege(Auth::user(), 'admin')) //超管，直接进行
            return DB::table('contests')->whereIn('id', $cids)->update(['hidden' => $hidden]);
        return DB::table('contests')->whereIn('id', $cids)
            ->where('user_id', Auth::id())->update(['hidden' => $hidden]);
    }


    //修改竞赛的类别号
    public function update_contest_cate_id(Request $request)
    {
        $contest_id = $request->input('contest_id');
        $new_cate_id = $request->input('cate_id');
        DB::table('contests')->where('id', $contest_id)->update(['cate_id' => $new_cate_id]);
        $new_cate = DB::table('contest_cate')->find($new_cate_id);
        return json_encode([
            'ret' => true,
            'msg' => sprintf('竞赛%d已修改类别为：%s', $contest_id, $new_cate ? $new_cate->title : '未分类')
        ]);
    }

    //修改竞赛的顺序，即order字段
    public function update_order(Request $request)
    {
        $contest_id = (int)$request->input('contest_id');
        $mode = $request->input('mode');
        assert(in_array($mode, ['to_top', 'to_up', 'to_down']));
        $contest = DB::table('contests')->find($contest_id);
        if ($mode == 'to_top')//置顶
        {
            $contests = DB::table('contests')->select(['id', 'order'])
                ->where('order', '>=', $contest->order)
                ->orderByDesc('order')->get();
            if ($contests) {
                $top_order = $contests[0]->order;
                $len = count($contests);
                for ($i = 0; $i < $len - 1; $i++) {
                    $contests[$i]->order = $contests[$i + 1]->order;
                }
                $contests[$len - 1]->order = $top_order;
                foreach ($contests as $item)
                    DB::table('contests')->where('id', $item->id)->update(['order' => $item->order]);
            }
            return json_encode([
                'ret' => true,
                'msg' => sprintf('竞赛%d已置顶', $contest_id)
            ]);
        } else if ($mode == 'to_up') {
            $contests = DB::table('contests')->select(['id', 'order'])
                ->where('order', '>=', $contest->order)
                ->orderByDesc('order')->get();
            if (($len = count($contests)) >= 2) {
                DB::table('contests')->where('id', $contests[$len - 1]->id)->update(['order' => $contests[$len - 2]->order]);
                DB::table('contests')->where('id', $contests[$len - 2]->id)->update(['order' => $contests[$len - 1]->order]);
            }
            return json_encode([
                'ret' => true,
                'msg' => sprintf('竞赛%d已上移', $contest_id)
            ]);
        } else//下移
        {
            $contests = DB::table('contests')->select(['id', 'order'])
                ->where('order', '<=', $contest->order)
                ->orderByDesc('order')->get();
            if (($len = count($contests)) >= 2) {
                DB::table('contests')->where('id', $contests[0]->id)->update(['order' => $contests[1]->order]);
                DB::table('contests')->where('id', $contests[1]->id)->update(['order' => $contests[0]->order]);
            }
            return json_encode([
                'ret' => true,
                'msg' => sprintf('竞赛%d已下移', $contest_id)
            ]);
        }
    }


    /*****************************  类别   ***********************************/
    public function categories(Request $request)
    {
        $categories = $this->get_categories();
        return view('admin.contest.categories', compact('categories'));
    }

    //修改类别信息
    public function update_cate(Request $request)
    {
        $id = $request->input('id');
        $values = $request->input('values');
        if ($id == null || $id == -1)//视为插入新记录
        {
            $id = DB::table('contest_cate')->insertGetId($values);
            DB::table('contest_cate')->where('id', $id)->update(['order' => $id]);
            return back();
        }

        //以下处理修改记录
        if (isset($values['parent_id']))//拦截非法的父级类别修改
        {
            $parent = DB::table('contest_cate')->find($values['parent_id']);
            if ($values['parent_id'] > 0 && !$parent) {
                return json_encode([
                    'ret' => false,
                    'msg' => '指定的父级类别不存在！'
                ]);
            }
            if ($values['parent_id'] == $parent->id) {
                return json_encode([
                    'ret' => false,
                    'msg' => '不能作为自身的子类别！'
                ]);
            }
            if ($values['parent_id'] > 0 && $parent->parent_id > 0) {
                return json_encode([
                    'ret' => false,
                    'msg' => '指定的父级类别必须是一级类别！请刷新页面后重试！'
                ]);
            }
        }

        //执行修改
        DB::table('contest_cate')->where('id', $id)->update($values);
        return json_encode([
            'ret' => true,
            'msg' => '已修改'
        ]);
    }

    public function delete_cate($id)
    {
        if (DB::table('contest_cate')->where('parent_id', $id)->exists()) {
            return json_encode([
                'ret' => false,
                'msg' => '一级分类下包含子类别，请先删除或移走所有子类别再删除当前类别'
            ]);
        }
        DB::table('contest_cate')->where('id', $id)->delete();
        return json_encode([
            'ret' => true,
            'msg' => '已删除; 请刷新页面'
        ]);
    }

    //修改竞赛类别的顺序，即order字段
    public function update_cate_order(Request $request)
    {
        $id = (int)$request->input('id');
        $mode = $request->input('mode');
        assert(in_array($mode, ['to_up', 'to_down']));
        $cate = DB::table('contest_cate')->find($id);
        if ($mode == 'to_up') {
            $cates = DB::table('contest_cate')->select(['id', 'order'])
                ->where('order', '<=', $cate->order)
                ->orderBy('order')
                ->get();
            if (($len = count($cates)) >= 2) {
                DB::table('contest_cate')->where('id', $cates[$len - 1]->id)->update(['order' => $cates[$len - 2]->order]);
                DB::table('contest_cate')->where('id', $cates[$len - 2]->id)->update(['order' => $cates[$len - 1]->order]);
            }
            return json_encode([
                'ret' => true,
                'msg' => sprintf('类别%s已上移', $cate->title)
            ]);
        } else//下移
        {
            $cates = DB::table('contest_cate')->select(['id', 'order'])
                ->where('order', '>=', $cate->order)
                ->orderBy('order')
                ->get();
            if (($len = count($cates)) >= 2) {
                DB::table('contest_cate')->where('id', $cates[0]->id)->update(['order' => $cates[1]->order]);
                DB::table('contest_cate')->where('id', $cates[1]->id)->update(['order' => $cates[0]->order]);
            }
            return json_encode([
                'ret' => true,
                'msg' => sprintf('类别%s已下移', $cate->title)
            ]);
        }
    }

}
