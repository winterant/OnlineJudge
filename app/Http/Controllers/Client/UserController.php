<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
            return view('message', ['msg' => trans('sentence.User not found', ['un' => $username])]);

        $problems_solved = Cache::remember(
            sprintf("user:%d:soved_problems:ids", $user->id),
            30, // 缓存
            function () use ($user) {
                return DB::table('solutions')
                    ->where('user_id', $user->id)
                    ->where('result', 4)
                    ->distinct()
                    ->pluck('problem_id');
            }
        );

        //对访客隐藏部分信息
        if (!Auth::user() && !get_setting('display_complete_userinfo')) {
            $user->email = null;
            $user->school = '****';
            $user->class = '****';
            $user->nick = '***';
        }
        return view('auth.user', compact('user', 'problems_solved'));
    }

    public function user_edit(Request $request, $username)
    {
        $user = DB::table('users')->where('username', $username)->first();
        // 提供修改界面
        if ($request->isMethod('get')) {
            return view('auth.user_edit', compact('user'));
        }

        // 提交修改资料
        if ($request->isMethod('post')) {
            if (Auth::user()->id == $user->id && $user->revise <= 0)     // 是本人&&没有修改次数
                return view('message', ['msg' => trans('sentence.forbid_edit')]); // 不允许本人修改

            $user = $request->input('user');
            if (!isset($user['school']))
                $user['school'] = '';
            if (!isset($user['class']))
                $user['class'] = '';
            if (!isset($user['nick']))
                $user['nick'] = '';
            $user['updated_at'] = date('Y-m-d H:i:s');
            $ret = DB::table('users')->where('username', $username)->update($user);
            if ($ret != 1) //失败
                return view('message', ['msg' => trans('sentence.Operation failed')]);

            // if (Auth::user()->username == $username) //是本人则次数减一
            //     DB::table('users')->where('username', $username)->decrement('revise');
            return redirect(route('user', $username));
        }
    }

    public function password_reset(Request $request, $username)
    {
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
                return view('message', ['msg' => trans('sentence.Operation failed')]);
            Auth::logoutOtherDevices($user['new_password']); //其他设备全部失效
            return view('message', ['success' => true, 'msg' => trans('passwords.reset')]);
        }
    }

    public function standings()
    {
        // todo
        $timediff = isset($_GET['range']) && $_GET['range'] != '0'
            ? sprintf(' and TIMESTAMPDIFF(%s,submit_time,now())=0', $_GET['range']) : '';

        $users = DB::table('users')->select([
            'username', 'nick', 'solved', 'accepted', 'submitted'
        ])
            ->when($_GET['username'] ?? false, function ($q) {
                return $q->where('username', 'like', $_GET['username'] . '%');
            })
            ->orderByDesc('solved')
            ->orderBy('submitted')
            ->paginate($_GET['perPage'] ?? 50);

        // 对访客隐藏用户信息
        if (!Auth::check() && !get_setting('display_complete_standings')) {
            foreach ($users as &$user) {
                for ($i = 3; $i < strlen($user->username) - 3 || $i < 6; $i++)
                    $user->username[$i] = '*';
            }
        }
        return view('user.standings', compact('users'));
    }

    public function change_language(Request $request, $user_lang)
    {
        return back()->withCookie(cookie('unencrypted_client_language', $user_lang, 5256000)); // ten years
    }
}
