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
        if ($request->isMethod('get')) {
            if (isset($_GET['id'])) {
                // 当前是修改群组
                $group = DB::table('groups')->find($_GET['id']);
                if (!$group)
                    return abort(404); // 群组不存在
                if(!privilege(Auth::user(), 'admin.group') && Auth::id()!=$group->creator)
                    return view('client.fail',['msg'=>'您既不是该群组的创建者，也没有群组管理权限!']);
                $contest_ids = DB::table('group_contests as gc')
                    ->join('contests as c', 'c.id', '=', 'gc.contest_id')
                    ->where('gc.group_id', $_GET['id'])
                    ->pluck('c.id');
                // dd($contest_ids);
                return view('group.edit', compact('group', 'contest_ids'));
            }
            return view('group.edit');
        } else if ($request->isMethod('post')) {
            // 修改群组基本信息
            $group = $request->input('group');
            if (!DB::table('groups')->find($group['id'])) { // 新建群组
                $group['id'] = DB::table('groups')->insertGetId([
                    'creator' => Auth::id()
                ]);
            } else { // 修改群组
                $group['updated_at'] = date('Y-m-d H:i:s');
            }
            if(!privilege(Auth::user(), 'admin.group') && Auth::id()!=$group['id'])
                return view('client.fail',['msg'=>'您既不是该群组的创建者，也没有群组管理权限!']);
            $group_id = $group['id'];
            unset($group['id']);
            DB::table('groups')->where('id', $group_id)->update($group);

            // 添加竞赛
            $contest_ids = $request->input('contest_ids');
            DB::table('group_contests')->where('group_id', $group_id)->delete();
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
                            'group_id' => $group_id,
                            'contest_id' => $c,
                        ]);
            }
            return redirect(route('group.home', $group_id));
        }
    }
}
