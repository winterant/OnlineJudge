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

        $opened=DB::table('solutions')
            ->leftJoin('users','user_id','=','users.id')
            ->where('username',$username)
            ->distinct()
            ->count('problem_id');

        $submissions=DB::table('solutions')
            ->leftJoin('users','user_id','=','users.id')
            ->where('username',$username)
            ->count();

        $group_results=DB::table('solutions')
            ->leftJoin('users','user_id','=','users.id')
            ->where('username',$username)
            ->select('result',DB::raw('COUNT(*) as num'))
            ->groupBy('result')
            ->get();
        $results=[4=>0];
        foreach($group_results as $item)
            $results[$item->result]=$item->num;

        $solved=DB::table('solutions')
            ->leftJoin('users','user_id','=','users.id')
            ->where('username',$username)
            ->where('result',4)
            ->distinct()
            ->count('problem_id');

        $submit=DB::table('solutions as A')
            ->leftJoin('users','user_id','=','users.id')
            ->select('problem_id',
                //此处查询AC数目，效率较低
                DB::raw('(select count(*) from solutions join users on user_id=users.id
                    where username=\''.$username.'\' and problem_id=A.problem_id and result=4) as ac'),
                DB::raw('COUNT(*) as sum'))
            ->where('username',$username)
            ->groupBy('problem_id')
            ->get();

        return view('client.user',compact('user','opened','submissions','results','solved','submit'));
    }

    public function user_edit(Request $request,$username){
        $online=Auth::user();
        $user=DB::table('users')->where('username',$username)->first();
        if(!$online->privilege('admin') && $online->username!=$username) //不是管理员&&不是本人
            return view('client.fail',['msg'=>trans('sentence.Permission denied')]);
        if($online->id==$user->id && $user->revise<=0) //是本人&&没有修改次数
            return view('client.fail',['msg'=>trans('sentence.user_edit_chances',['i'=>Auth::user()->revise])]);

        // 提供修改界面
        if ($request->isMethod('get')){
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
        $online=Auth::user();
        if(!$online->privilege('admin') && $online->username!=$username) //不是管理员&&不是本人
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
            Auth::logoutOtherDevices($user['new_password']); //其他设备全部失效
            return view('client.success',['msg'=>'Password modified successfully']);
        }
    }


    public function standings(){
        $timediff=isset($_GET['range'])&&$_GET['range']!='0'
            ?sprintf(' and TIMESTAMPDIFF(%s,submit_time,now())=0',$_GET['range']):'';
        $users=DB::table('users')->select('username','nick',
                DB::raw("(select count(id) from solutions where user_id=users.id".$timediff.") as submit"),
                DB::raw("(select count(distinct problem_id) from solutions where user_id=users.id and result=4".$timediff.") as solved")
            )
            ->when(isset($_GET['username']),function ($q){return $q->where('username','like',$_GET['username'].'%');})
            ->orderByDesc('solved')
            ->orderBy('submit')
            ->paginate(isset($_GET['perPage'])?$_GET['perPage']:30);
        return view('client.standings',compact('users'));
    }
}
