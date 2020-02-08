<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //user information page
    public function user($username){
        $user=DB::table('users')->where('username',$username)->first();
        if($user==null)
            return view('client.fail',['msg'=>trans('sentence.User not found',['un'=>$username])]);
        return view('client.user',compact('user'));
    }

    public function user_edit(Request $request,$username){

        if(!Auth::user()->privilege('admin') && Auth::user()->username!=$username) //不是管理员&&不是本人
            return view('client.fail',['msg'=>trans('sentence.Permission denied')]);
        if(DB::table('users')->where('username',$username)->value('revise')<=0
            && Auth::user()->username==$username) //是本人&没有修改次数
            return view('client.fail',['msg'=>trans('sentence.user_edit_chances',['i'=>Auth::user()->revise])]);

        // 提供修改界面
        if ($request->isMethod('get')){
            $user=DB::table('users')->where('username',$username)->first();
            if($user->revise <= 0)
                return view('client.fail',['msg'=>trans('sentence.user_edit_chances',['i'=>$user->revise])]);
            return view('client.user_edit',compact('user'));
        }

        // 提交修改资料
        if ($request->isMethod('post')){
            $user=$request->input('user');
            $user['updated_at']=date('Y-m-d H:i:s');
            $ret=DB::table('users')->where('username',$username)->update($user);
            if($ret!=1) //失败
                return view('client.fail',['msg'=>trans('sentence.Operation failed')]);

            if(Auth::user()->username==$username) //是本人则次数减一
                DB::table('users')->where('username',$username)->decrement('revise');
            return redirect(route('user',$username));
        }
    }

    public function password_reset(Request $request,$username){
        if(!Auth::user()->privilege('admin') && Auth::user()->username!=$username) //不是管理员&&不是本人
            return view('client.fail',['msg'=>trans('sentence.Permission denied')]);

        // 提供界面
        if ($request->isMethod('get')){
            return view('client.password_reset',compact('username'));
        }

        // 提交修改
        if ($request->isMethod('post')){

            $user=$request->input('user');

            if(strlen($user['new_password'])<8) //密码太短
                return back()->with('message','密码太短');

            if($user['new_password']!=$user['password_confirmation']) //密码不一致
                return back()->with('message','密码不一致');

            $old=DB::table('users')->where('username',$username)->value('password');
            if(!Hash::check($user['old_password'],$old))  //原密码错误
                return back()->with('message','原密码错误');

            $ret=DB::table('users')->where('username',$username)
                ->update(['password'=>Hash::make($user['new_password']),'updated_at'=>date('Y-m-d H:i:s')]);
            if($ret!=1) //失败
                return view('client.fail',['msg'=>trans('sentence.Operation failed')]);
            return view('client.success',['msg'=>'Password modified successfully']);
        }
    }
}
