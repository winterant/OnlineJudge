<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    // 群组列表
    public function groups()
    {
        $groups = DB::table('groups as g')
            ->select('g.*', 'u.username as creator')
            ->leftJoin('users as u', 'u.id', '=', 'g.creator');
        if (Auth::check() && ($_GET['mygroups'] ?? false)) {
            // 仅查看我加入的群组（含隐藏的）
            $groups = $groups->join('group_users as gu', function ($q) {
                $q->on('gu.group_id', '=', 'g.id')
                    ->on('gu.identity', '>=', DB::raw(2))
                    ->on('gu.user_id', '=', DB::raw(Auth::id()));
            });
        } else {
            // 查看全部群组时，已登陆用户要查出身份
            if (Auth::check()) {
                $groups = $groups->leftJoin('group_users as gu', function ($q) {
                    $q->on('gu.group_id', '=', 'g.id')
                        ->on('gu.user_id', '=', DB::raw(Auth::id()));
                })->addSelect('gu.identity');
            }
            // 查看全部群组时，未登陆 or 无特权，则只能查看不隐藏的群组
            if (!Auth::check() || !request()->user()->can('admin.group.view')) {
                $groups = $groups->where('hidden', 0);
            }
        }
        // 其它筛选条件，以及排序、分页
        $groups = $groups->when($_GET['kw'] ?? false, function ($q) {
            $q->where(function ($q) { // sql加括号
                $q->where('g.name', 'like', '%' . $_GET['kw'] . '%')
                    ->orWhere('g.teacher', 'like', '%' . $_GET['kw'] . '%')
                    ->orWhere('g.class', 'like', '%' . $_GET['kw'] . '%');
            });
        })->orderByDesc('id')
            ->paginate($_GET['perpage'] ?? 12);

        // 查看全部群组时，要查出身份
        if (!isset($_GET['mygroups'])) {
            foreach ($groups as &$g) {
                $g->user_in_group = DB::table('group_users')
                    ->where('user_id', Auth::id())
                    ->where('group_id', $g->id)
                    ->value('identity'); // 获取当前用户在该group中的身份。未加入为null
            }
        }
        return view('group.groups', compact('groups'));
    }

    // 具体的某一个群组（课程）的首页
    public function group(Request $request, $group_id)
    {
        $group = DB::table('groups as g')->find($group_id);
        if (!$group)
            return abort(404);
        $contests = DB::table('group_contests as gc')
            ->join('contests as c', 'c.id', '=', 'gc.contest_id')
            ->select([
                'gc.id', 'gc.contest_id', 'gc.order',
                'c.title', 'c.judge_type', 'c.start_time', 'c.end_time', 'c.num_members'
            ])
            ->where('gc.group_id', $group->id)
            ->orderBy('gc.order', $group->type == 0 ? 'asc' : 'desc') // 课程正序，竞赛列表基本不变；班级逆序，竞赛持续添加
            ->paginate($group->type == 0 ? 100 : ($_GET['perPage'] ?? 20)); // 课程显示100项，班级现实20项
        return view('group.group', compact('group', 'contests'));
    }

    public function solutions(Request $request, $group_id)
    {
        $group = DB::table('groups as g')->find($group_id);
        return view('group.solutions', compact('group'));
    }

    // 具体的某一群组的成员列表
    public function members(Request $request, $group_id)
    {
        $group = DB::table('groups as g')->find($group_id);

        // $identities = [0 => '已退出', 1 => '申请加入', 2 => '学生', 3 => '班长', 4 => '管理员'];
        if (!isset($_GET['identity']))
            $_GET['identity'] = '2,3,4'; // 以逗号分隔的数字,身份代号

        $members = DB::table('group_users as gu')
            ->join('users as u', 'u.id', '=', 'gu.user_id')
            ->select(['u.username', 'u.nick', 'u.school', 'u.class', 'u.id as user_id', 'gu.id', 'gu.identity', 'gu.created_at'])
            ->where('gu.group_id', $group_id)
            ->when(isset($_GET['username']) && $_GET['username'] != '', function ($q) {
                return $q->where('u.username', 'like', '%' . $_GET['username'] . '%');
            })
            ->whereIn('identity', explode(',', $_GET['identity']))
            ->orderByDesc('gu.identity')
            ->orderBy('u.username')
            ->paginate();
        $member_count = [];
        for ($i = 0; $i <= 4; $i++) $member_count[$i] = 0;
        foreach ($members as $m) $member_count[$m->identity]++;
        return view('group.members', compact('group', 'members', 'member_count'));
    }

    // 学习报告
    public function member(Request $request, $group_id, $user_id)
    {
        $group = DB::table('groups as g')->find($group_id);
        $user = DB::table('users')->find($user_id);

        $contests = DB::table('contests as c')
            ->join('group_contests as gc', function ($q) use ($group_id) {
                $q->on('gc.contest_id', 'c.id')->on('gc.group_id', DB::raw($group_id));
            })
            ->select([
                'c.id', 'c.judge_type', 'c.title', 'c.start_time', 'c.end_time',
                'c.access', 'c.order', 'c.hidden',
                'c.password',
                'c.num_members'
            ])
            ->orderBy('gc.order', $group->type == 0 ? 'asc' : 'desc')
            ->paginate($_GET['perPage'] ?? 15);
        return view('group.member', compact('group', 'user', 'contests'));
    }
}
