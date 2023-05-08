<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ProblemHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProblemController extends Controller
{
    public function problems()
    {
        $problems = DB::table('problems');
        if (request()->has('tag_id') && request('tag_id') != '')
            $problems = $problems->join('tag_marks', 'problem_id', '=', 'problems.id')
                ->where(function ($q) {
                    // 筛选民间标签标记的题目
                    $q->where('tag_id', request('tag_id'));
                    // 或者官方标签标记的题目
                    if (($official = DB::table('tag_pool')->find(request('tag_id'))->name ?? false))
                        $q->orWhere('tags', 'like', '%' . $official . '%');
                });
        $problems = $problems->select(
            'problems.id',
            'title',
            'source',
            'hidden',
            'solved',
            'accepted',
            'submitted'
        )
            ->when(!request()->has('show_hidden'), function ($q) {
                return $q->where('hidden', 0);
            })
            ->when(request()->has('kw') && request('kw') != '', function ($q) {
                $q->where(function ($q) {
                    $q->where('title', 'like', '%' . request('kw') . '%')
                        ->orWhere('source', 'like', '%' . request('kw') . '%')
                        ->orWhere('problems.id', request('kw'));
                });
            })
            ->orderBy('problems.id')
            ->distinct()
            ->paginate(request()->has('perPage') ? request('perPage') : 100);

        //  ================== 获取题目标签 =======================
        foreach ($problems as &$problem) {
            $problem->tags = ProblemHelper::getTags($problem->id); // 用户标记的（含出题人标记过的）
        }

        $tag_pool = DB::table('tag_pool')
            ->select('id', 'name')
            ->where('hidden', 0)
            ->orderBy('name')
            ->get();
        return view('problem.problems', compact('problems', 'tag_pool'));
    }

    public function problem($id)
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if (!Auth::check() && !get_setting('guest_see_problem')) //未登录&&不允许访客看题 => 请先登录
            return redirect(route('login'));
        // 在网页展示一个问题
        $problem = DB::table('problems')->select([
            'hidden', 'id', 'title', 'description',
            'language', // 代码填空的语言
            'input', 'output', 'hint', 'source', 'tags', 'time_limit', 'memory_limit', 'spj',
            'type', 'fill_in_blank',
            'accepted', 'solved', 'submitted'
        ])->find($id);
        if ($problem == null) //问题不存在
            return view('message', ['msg' => trans('sentence.problem_not_found')]);

        if ($problem->hidden && ($user == null || !$user->can('admin.problem.view'))) // 问题是隐藏的，那么不登录或无权限是不可以看题的
        {
            $msg = trans('main.Problem') . $id . ': ' . trans('main.Hidden') . '; ';
            return view('message', compact('msg'));
        }

        //读取样例文件
        $samples = ProblemHelper::readSamples($id);

        // 获取本题的民间收集标签（有缓存）
        $tags = ProblemHelper::getTags($problem->id, official: false, informal_limit: 5);

        // 官方tag
        $problem->tags = json_decode($problem->tags ?? '[]', true); // json => array

        // 可能指定了solution代码
        $solution = DB::table('solutions')->find(request('solution') ?? -1);
        if (Auth::check() && $solution && ($solution->user_id == Auth::id() || $user->can('admin.solution.view')))
            $solution_code = $solution->code ?? null;
        else
            $solution_code = null;
        return view('problem.problem', compact('problem', 'samples', 'tags'));
    }

    /**
     * @deprecated 未来版本中将重构讨论版功能，此方法将废弃。
     */
    public function load_discussion(Request $request)
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        $problem_id = $request->input('problem_id');
        $page = $request->input('page');
        $discussions = DB::table('discussions')
            ->select('id', 'username', 'content', 'top', 'hidden', 'created_at')
            ->where('problem_id', $problem_id)
            ->where('discussion_id', -1)
            ->when(!$user->can('admin.problem.view'), function ($q) {
                return $q->where('hidden', 0);
            })
            ->orderByDesc('top')
            ->orderByDesc('created_at')
            ->forPage($page, 3)
            ->get();

        $ids = [];
        foreach ($discussions as &$item) {
            if ($item->username)
                $item->username = sprintf("<a href='%s'>%s</a>", route('user', $item->username), $item->username);
            $ids[] = $item->id;
        }

        $son_disc = DB::table('discussions')
            ->select('id', 'discussion_id', 'username', 'reply_username', 'content', 'top', 'hidden', 'created_at')
            ->whereIn('discussion_id', $ids)
            ->when(!$user->can('admin.problem.view'), function ($q) {
                return $q->where('hidden', 0);
            })
            ->orderBy('created_at')
            ->get();
        $replies = [];
        foreach ($son_disc as &$item) {
            if ($item->username)
                $item->username = sprintf("<a href='%s'>%s</a>", route('user', $item->username), $item->username);
            if ($item->reply_username)
                $item->reply_username = sprintf("<a href='%s'>%s</a>", route('user', $item->reply_username), $item->reply_username);
            $replies[$item->discussion_id][] = $item;
        }

        return json_encode([$discussions, $replies]);
    }

    /**
     * @deprecated 未来版本中将重构讨论版功能，此方法将废弃。
     */
    public function edit_discussion(Request $request, $pid)
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if (!$user->can('admin.problem.view')) {
            $last_time = DB::table('discussions')
                ->where('username', Auth::user()->username)
                ->where('discussion_id', -1)
                ->max('created_at');
            if (time() - strtotime($last_time) < 300) //少于5分钟，不能再次提交
                return back()->with("discussion_add_failed", true);
        }
        $disc = [];
        if ($request->input('discussion_id'))
            $disc['discussion_id'] = $request->input('discussion_id');
        if ($request->input('reply_username'))
            $disc['reply_username'] = $request->input('reply_username');
        $disc['problem_id'] = $pid;
        $disc['username'] = Auth::user()->username;
        $disc['content'] = $request->input('content');
        DB::table('discussions')->insert($disc);
        return back()->with("discussion_added", true);
    }

    /**
     * @deprecated 未来版本中将重构讨论版功能，此方法将废弃。
     */
    public function delete_discussion(Request $request)
    {
        return DB::table('discussions')->delete($request->input('id'));
    }

    /**
     * @deprecated 未来版本中将重构讨论版功能，此方法将废弃。
     */
    public function top_discussion(Request $request)
    {
        if ($request->input('way') == 0)
            $new_top = 0;
        else
            $new_top = DB::table('discussions')->max('top') + 1;
        DB::table('discussions')->where('id', $request->input('id'))->update(['top' => $new_top]);
        return 1;
    }

    /**
     * @deprecated 未来版本中将重构讨论版功能，此方法将废弃。
     */
    public function hidden_discussion(Request $request)
    {
        return DB::table('discussions')
            ->where('id', $request->input('id'))
            ->update(['hidden' => $request->input('value')]);
    }
}
