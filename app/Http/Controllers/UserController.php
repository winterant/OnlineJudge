<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
            30, // 缓存一会儿，可以防止爬虫耗尽资源
            function () use ($user) {
                return DB::table('solutions')
                    ->where('user_id', $user->id)
                    ->where('result', 4)
                    ->distinct()
                    ->orderBy('problem_id')
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

        // 当前用户已加入的群组：
        $groups = DB::table('groups as g')
            ->join('group_users as gu', 'gu.group_id', 'g.id')
            ->join('users as u', 'u.id', 'g.user_id')
            ->select(['g.id', 'g.name', 'g.teacher', 'g.class', 'g.type', 'u.username as creator', 'g.hidden'])
            ->where('gu.user_id', $user->id)
            ->where('gu.identity', '>=', 2)
            ->paginate(4);
        return view('user.user', compact('user', 'groups', 'problems_solved'));
    }

    public function edit(Request $request, $username)
    {
        $user = User::where('username', $username)->first(); // 要修改的user
        if ($user === null) {
            return view('message', ['msg' => 'User does not exist']);
        }

        if (Auth::id() == $user->id && $user->revise <= 0)     // 是本人&&没有修改次数
        {
            /** @var \App\Models\User */
            $online_user = Auth::user();
            if (!$online_user->can('admin.user.update')) // 不是管理员
                return view('message', ['msg' => trans('sentence.forbid_edit')]); // 不允许本人修改
        }

        // GET 提供修改界面
        if ($request->isMethod('get')) {
            return view('user.edit', compact('user'));
        }

        // POST 提交修改资料
        if ($request->isMethod('post')) {
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

            return redirect(route('user', $username));
        }
    }

    public function password_reset(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

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

            try {
                Auth::logoutOtherDevices($user['new_password']); //其他设备全部失效
            } catch (Exception $e) {
                Log::error('Failed to logout other devices when modify password');
                Log::error($e->getMessage());
            }
            return view('message', ['success' => true, 'msg' => trans('passwords.reset')]);
        }
    }

    public function standings()
    {
        // todo
        $timediff = request()->has('range') && request('range') != '0'
            ? sprintf(' and TIMESTAMPDIFF(%s,submit_time,now())=0', request('range')) : '';

        $users = DB::table('users')->select([
            'username', 'nick', 'solved', 'accepted', 'submitted'
        ])
            ->when(request('kw') ?? false, function ($q) {
                $q->where(function ($q) {
                    $q->where('username', 'like', '%' . request('kw') . '%')
                        ->orWhere('nick', 'like', '%' . request('kw') . '%')
                        ->orWhere('school', 'like', '%' . request('kw') . '%')
                        ->orWhere('class', 'like', '%' . request('kw') . '%');
                });
            })
            ->orderByDesc('solved')
            ->orderBy('submitted')
            ->paginate(request('perPage') ?? 50);

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
