<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //user information page
    public function user($username)
    {
        $user = DB::table('users')->where('username', $username)->first();
        if ($user == null)
            return view('client.fail', ['msg' => trans('sentence.User not found', ['un' => $username])]);

        $submissions = DB::table('solutions')
            ->where('user_id', $user->id)
            ->get();
        $results = []; // 按提交结果统计
        $problem_submitted = []; // 每个题目的提交次数
        $problem_ac = []; // 每个题目的ac次数
        foreach ($submissions as $item) {
            $results[$item->result] = (isset($results[$item->result]) ? $results[$item->result] : 0) + 1;
            $problem_submitted[$item->problem_id] = (isset($problem_submitted[$item->problem_id]) ? $problem_submitted[$item->problem_id] : 0) + 1;
            if ($item->result == 4)
                $problem_ac[$item->problem_id] = (isset($problem_ac[$item->problem_id]) ? $problem_ac[$item->problem_id] : 0) + 1;
        }

        if (!Auth::user() && !get_setting('display_complete_userinfo')) {
            //对访客隐藏部分信息
            $user->email = null;
            $user->school = '****';
            $user->class = '****';
            $user->nick = '***';
        }
        return view('auth.user', compact('user', 'result', 'problem_submitted', 'problem_ac'));
    }

    public function user_edit(Request $request, $username)
    {
        if (!privilege('admin.user.edit') && Auth::user()->username != $username) //不是管理员&&不是本人
            return view('client.fail', ['msg' => trans('sentence.Permission denied')]);

        $user = DB::table('users')->where('username', $username)->first();
        // 提供修改界面
        if ($request->isMethod('get')) {
            return view('auth.user_edit', compact('user'));
        }

        // 提交修改资料
        if ($request->isMethod('post')) {
            if (Auth::user()->id == $user->id && $user->revise <= 0)     // 是本人&&没有修改次数
                return view('client.fail', ['msg' => trans('sentence.forbid_edit')]); // 不允许本人修改

            $user = $request->input('user');
            $user['updated_at'] = date('Y-m-d H:i:s');
            $ret = DB::table('users')->where('username', $username)->update($user);
            if ($ret != 1) //失败
                return view('client.fail', ['msg' => trans('sentence.Operation failed')]);

            if (Auth::user()->username == $username) //是本人则次数减一
                DB::table('users')->where('username', $username)->decrement('revise');
            return redirect(route('user', $username));
        }
    }

    public function password_reset(Request $request, $username)
    {
        if (!privilege('admin.user.edit') && Auth::user()->username != $username) //不是管理员&&不是本人
            return view('client.fail', ['msg' => trans('sentence.Permission denied')]);

        // 提供界面
        if ($request->isMethod('get')) {
            return view('auth.password_reset', compact('username'));
        }

        // 提交修改
        if ($request->isMethod('post')) {

            $user = $request->input('user');

            if (strlen($user['new_password']) < 8) //密码太短
                return back()->with('message', '新密码太短');

            if ($user['new_password'] != $user['password_confirmation']) //密码不一致
                return back()->with('message', '确认密码不一致');

            $old = DB::table('users')->where('username', $username)->value('password');
            if (!Hash::check($user['old_password'], $old))  //原密码错误
                return back()->with('message', '原密码错误');

            $ret = DB::table('users')->where('username', $username)
                ->update(['password' => Hash::make($user['new_password']), 'updated_at' => date('Y-m-d H:i:s')]);
            if ($ret != 1) //失败
                return view('client.fail', ['msg' => trans('sentence.Operation failed')]);
            Auth::logoutOtherDevices($user['new_password']); //其他设备全部失效
            return view('client.success', ['msg' => 'Password modified successfully']);
        }
    }

    public function standings()
    {
        // 已解决数量
        $query_solved = DB::table('solutions')
            ->select('user_id', DB::raw('count(distinct problem_id) as solved'))
            ->where('result', 4)
            ->groupBy('user_id');

        // 提交总次数
        $query_submitted = DB::table('solutions')
            ->select('user_id', DB::raw('count(*) as submitted'))
            ->groupBy('user_id');

        $users = DB::table('users as u')
            ->leftJoinSub($query_solved, 'q1', 'q1.user_id', '=', 'u.id')
            ->leftJoinSub($query_submitted, 'q2', 'q2.user_id', '=', 'u.id')
            ->when(isset($_GET['username']) && $_GET['username'] != '', function ($q) {
                return $q->where('username', 'like', '%' . $_GET['username'] . '%');
            })
            ->orderByDesc('q1.solved')
            ->orderBy('q2.submitted')
            ->paginate($_GET['perPage'] ?? 50);

        // 遮掩真实用户名等敏感信息
        if (!Auth::user() && !get_setting('display_complete_standings')) {
            foreach ($users as &$user) {
                for ($i = 3; $i < strlen($user->username) - 3 || $i < 6; $i++)
                    $user->username[$i] = '*';
            }
        }
        return view('client.standings', compact('users'));
    }

    public function standings_back()
    {
        $timediff = isset($_GET['range']) && $_GET['range'] != '0'
            ? sprintf(' and TIMESTAMPDIFF(%s,submit_time,now())=0', $_GET['range']) : '';
        $users = DB::table('users')->select(
            'username',
            'nick',
            DB::raw("(select count(id) from solutions where user_id=users.id and result=4" . $timediff . ") as accepted"),
            DB::raw("(select count(distinct problem_id) from solutions where user_id=users.id and result=4" . $timediff . ") as solved"),
            DB::raw("(select count(id) from solutions where user_id=users.id" . $timediff . ") as submit")
        )
            ->when(isset($_GET['username']), function ($q) {
                return $q->where('username', 'like', '%' . $_GET['username'] . '%');
            })
            ->orderByDesc('solved')
            ->orderBy('submit')
            ->paginate($_GET['perPage'] ?? 50);
        if (!Auth::user() && !get_setting('display_complete_standings')) {
            foreach ($users as &$user) {
                for ($i = 3; $i < strlen($user->username) - 3 || $i < 6; $i++)
                    $user->username[$i] = '*';
            }
        }
        return view('client.standings', compact('users'));
    }

    public function change_language(Request $request, $user_lang)
    {
        Cookie::queue('client_language', $user_lang);
        return back();
    }
}
