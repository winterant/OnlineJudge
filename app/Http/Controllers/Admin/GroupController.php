<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GroupController extends Controller
{
    public function list()
    {
        // todo
        return view('admin.success', ['msg' => '请在前台查看和管理群组']);
    }

    public function edit(Request $request)
    {
        // 用isset($_GET['id'])区分新建和修改
        // ============ 新建群组 ================
        if (!isset($_GET['id'])) // 新建
        {
            if ($request->isMethod('get')) {
                return view('group.edit'); //提供界面
            } else {
                // 处理请求; 新建一条数据，跳转到修改
                $_GET['id'] = DB::table('groups')->insertGetId([
                    'creator' => Auth::id()
                ]);
                // 把创建者自己加入到群组成员
                DB::table('group_users')->insert([
                    'group_id' => $_GET['id'],
                    'user_id' => Auth::id(),
                    'identity' => 4, // 老师/管理员
                ]);
            }
        }

        // ============  修改群组信息 ==============
        if (!($group = DB::table('groups')->find($_GET['id'])))
            return view('client.fail', ['msg' => '群组不存在!']);
        if (!privilege(Auth::user(), 'admin') && Auth::id() != $group->creator)
            return view('client.fail', ['msg' => '您既不是该群组的创建者，也不具备最高管理权限[admin]!']);
        // 提供界面
        if ($request->isMethod('get')) {
            $contest_ids = DB::table('group_contests as gc')
                ->join('contests as c', 'c.id', '=', 'gc.contest_id')
                ->where('gc.group_id', $_GET['id'])
                ->pluck('c.id');
            return view('group.edit', compact('group', 'contest_ids'));
        } else {
            // 接收修改请求
            $group = $request->input('group');
            $group['updated_at'] = date('Y-m-d H:i:s');
            DB::table('groups')->where('id', $_GET['id'])->update($group);

            // 添加竞赛
            $contest_ids = $request->input('contest_ids');
            DB::table('group_contests')->where('group_id', $_GET['id'])->delete();
            foreach (explode(PHP_EOL, $contest_ids) as &$cid) {
                $line = explode('-', trim($cid));
                $cids = [];
                if (count($line) == 1)
                    $cids[] = intval($line[0]);
                else
                    foreach (range(intval($line[0]), intval(($line[1]))) as $i)
                        $cids[] = $i;
                foreach ($cids as $c)
                    if (DB::table('contests')->find($c))
                        DB::table('group_contests')->insert([
                            'group_id' => $_GET['id'],
                            'contest_id' => $c,
                        ]);
            }
            return redirect(route('group.home', $_GET['id']));
        }
    }

    // post
    public function add_member(Request $request, $id)
    {
        if (!($group = DB::table('groups')->find($id)))
            return view('client.fail', ['msg' => '群组不存在!']);
        if (!privilege(Auth::user(), 'admin') && Auth::id() != $group->creator)
            return view('client.fail', ['msg' => '您既不是该群组的创建者，也不具备最高管理权限[admin]!']);
        // 开始处理
        $unames = explode(PHP_EOL, $request->input('usernames'));
        $iden = $request->input('identity');
        foreach ($unames as &$item)
            $item = trim($item);
        $uids = DB::table('users')->whereIn('username', $unames)->pluck('id');
        foreach ($uids as &$uid) {
            DB::table('group_users')->updateOrInsert(
                ['group_id' => $id, 'user_id' => $uid],
                [
                    'identity' => $iden ?: 2, // 默认2为普通成员
                ]
            );
        }
        return back();
    }

    // post
    public function del_member(Request $request, $id, $uid)
    {
        if (!($group = DB::table('groups')->find($id)))
            return view('client.fail', ['msg' => '群组不存在!']);
        if (!privilege(Auth::user(), 'admin') && Auth::id() != $group->creator)
            return view('client.fail', ['msg' => '您既不是该群组的创建者，也不具备最高管理权限[admin]!']);
        // 开始处理
        DB::table('group_users')
            ->where('group_id', $id)
            ->where('user_id', $uid)
            ->delete();
        return back();
    }

    // post 修改用户身份
    public function member_iden(Request $request, $id, $uid, $iden)
    {
        if (!($group = DB::table('groups')->find($id)))
            return view('client.fail', ['msg' => '群组不存在!']);
        if (!privilege(Auth::user(), 'admin') && Auth::id() != $group->creator)
            return view('client.fail', ['msg' => '您既不是该群组的创建者，也不具备最高管理权限[admin]!']);
        // 开始处理
        DB::table('group_users')
            ->where('group_id', $id)
            ->where('user_id', $uid)
            ->update(['identity' => $iden]);
        return back();
    }
}
