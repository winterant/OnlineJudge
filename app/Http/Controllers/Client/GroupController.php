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
    public function groups()
    {
        return redirect(route('groups.my'));
    }
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
        foreach($groups as &$g){
            $g->user_in_group = DB::table('group_users')
                ->where('user_id', Auth::id())
                ->where('group_id', $g->id)
                ->value('identity');// 获取当前用户在该group中的身份。未加入为null
            if($g->user_in_group==null)
                $g->user_in_group=-1;
        }
        return view('group.groups', compact('groups'));
    }

    public function home(Request $request, $id)
    {
        $group = DB::table('groups as g')
            ->leftJoin('users as u', 'u.id', '=', 'g.creator')
            ->select('g.*', 'u.username as creator_username')
            ->where('g.id', $id)
            ->first();
        if (!$group)
            return abort(404);
        $contests = DB::table('group_contests as gc')
            ->join('contests as c', 'c.id', '=', 'gc.contest_id')
            ->where('gc.group_id', $group->id)
            ->orderBy('gc.id')
            ->get('c.*');
        // dd($group_contests);
        return view('group.home', compact('group', 'contests'));
    }

    public function members(Request $request, $id)
    {
        $group = DB::table('groups as g')
            ->leftJoin('users as u', 'u.id', '=', 'g.creator')
            ->select('g.*', 'u.username as creator_username')
            ->where('g.id', $id)
            ->first();
        $members = DB::table('group_users as gu')
            ->join('users as u', 'u.id', '=', 'gu.user_id')
            ->where('gu.group_id', $id)
            ->orderByDesc('gu.identity')
            ->orderBy('u.username')
            ->get(['u.*', 'gu.identity', 'gu.created_at']);
        $member_count = [];
        for($i=0;$i<=4;$i++)$member_count[$i]=0;
        foreach($members as $m)$member_count[$m->identity]++;
        return view('group.members', compact('group', 'members', 'member_count'));
    }


    public function joinin($id)
    {
        DB::table('group_users')
            ->updateOrInsert(['group_id'=>$id, 'user_id'=>Auth::id()], ['identity'=>1]);
        return back();
    }
}
