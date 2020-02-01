<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    //user information page
    public function user($username){
        $user=DB::table('users')->where('username',$username)->first();
        return view('client.user',compact('user'));
    }

    public function user_edit(Request $request,$username){

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
            if(DB::table('users')->where('id',$user['id'])->value('revise') <= 0)
                return redirect(url()->previous());
            foreach ($user as $item) if($item==null)$item='';  //DB update null 会报错
            $ret=DB::table('users')->where('id',$user['id'])->update($user);
            if($ret!=1) //失败
                return view('client.fail',['msg'=>trans('sentence.Operation failed')]);
            DB::table('users')->where('id',$user['id'])->decrement('revise');
            return redirect(route('user',$user['username']));
        }
    }
}
