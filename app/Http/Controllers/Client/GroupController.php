<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GroupController extends Controller
{
    // 我的群组/当前用户的所有的群组
    public function mygroups()
    {
        $groups = DB::table('groups as g')
            ->leftJoin('users as u', 'u.id', '=', 'g.creator')
            ->join('group_users as gu', 'gu.group_id', '=', 'g.id')
            ->select('g.*', 'u.username as creator')
            ->where('gu.user_id', Auth::id())
            ->where('gu.identity', '>', 1)
            ->orderByDesc('id')
            ->paginate(isset($_GET['perPage']) ? $_GET['perPage'] : 12);
        return view('group.groups', compact('groups'));
    }

    // 所有群组
    public function allgroups()
    {
        $groups = DB::table('groups as g')
            ->leftJoin('users as u', 'u.id', '=', 'g.creator')
            ->select('g.*', 'u.username as creator')
            ->when(!privilege('admin.group'), function ($q) {
                return $q->where('hidden', 0);
            })
            ->orderByDesc('id')
            ->paginate(isset($_GET['perPage']) ? $_GET['perPage'] : 12);
        foreach ($groups as &$g) {
            $g->user_in_group = DB::table('group_users')
                ->where('user_id', Auth::id())
                ->where('group_id', $g->id)
                ->value('identity'); // 获取当前用户在该group中的身份。未加入为null
            if ($g->user_in_group == null)
                $g->user_in_group = -1;
        }
        return view('group.groups', compact('groups'));
    }

    // 具体的某一个群组（课程）的首页
    public function home(Request $request, $group_id)
    {
        $group = DB::table('groups as g')
            ->leftJoin('users as u', 'u.id', '=', 'g.creator')
            ->select('g.*', 'u.username as creator_username')
            ->where('g.id', $group_id)
            ->first();
        if (!$group)
            return abort(404);
        $contests = DB::table('group_contests as gc')
            ->join('contests as c', 'c.id', '=', 'gc.contest_id')
            ->where('gc.group_id', $group->id)
            ->orderBy('gc.id')
            ->get('c.*');
        return view('group.home', compact('group', 'contests'));
    }

    // 具体的某一群组的成员列表
    public function members(Request $request, $group_id)
    {
        $group = DB::table('groups as g')
            ->leftJoin('users as u', 'u.id', '=', 'g.creator')
            ->select('g.*', 'u.username as creator_username')
            ->where('g.id', $group_id)
            ->first();

        // $identities = [0 => '已退出', 1 => '申请加入', 2 => '学生', 3 => '班长', 4 => '管理员'];
        if (!isset($_GET['identity']))
            $_GET['identity'] = '2,3,4'; // 以逗号分隔的数字,身份代号

        $members = DB::table('group_users as gu')
            ->join('users as u', 'u.id', '=', 'gu.user_id')
            ->select(['u.username', 'u.nick', 'u.school', 'u.class', 'u.id as user_id', 'gu.id', 'gu.identity', 'gu.created_at'])
            ->where('gu.group_id', $group_id)
            ->when(isset($_GET['username']) && $_GET['username'] != '', function ($q) {
                return $q->where('u.username', 'like', $_GET['username'] . '%');
            })
            ->whereIn('identity', explode(',', $_GET['identity']))
            ->orderByDesc('gu.identity')
            // ->orderBy('u.username')
            ->paginate();
        $member_count = [];
        for ($i = 0; $i <= 4; $i++) $member_count[$i] = 0;
        foreach ($members as $m) $member_count[$m->identity]++;
        return view('group.members', compact('group', 'members', 'member_count'));
    }

    // 学习报告
    public function member(Request $request, $group_id, $user_id)
    {
        $group = DB::table('groups as g')
            ->leftJoin('users as u', 'u.id', '=', 'g.creator')
            ->select('g.*', 'u.username as creator_username')
            ->where('g.id', $group_id)
            ->first();
        $user = DB::table('users')->find($user_id);
        return view('group.member', compact('group', 'user'));
    }
}
