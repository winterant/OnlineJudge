<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    public function list(Request $request){
        $users=DB::table('users')->select(['id','username','email','nick','school','class','revise','created_at'])
            ->when(isset($_GET['username']),function ($q){return $q->where('username','like',$_GET['username'].'%');})
            ->when(isset($_GET['email']),function ($q){return $q->where('email','like',$_GET['email'].'%');})
            ->when(isset($_GET['nick']),function ($q){return $q->where('nick','like',$_GET['nick'].'%');})
            ->when(isset($_GET['school']),function ($q){return $q->where('school','like',$_GET['school'].'%');})
            ->when(isset($_GET['class']),function ($q){return $q->where('class','like',$_GET['class'].'%');})
            ->orderBy('id')->paginate(isset($_GET['perPage'])?$_GET['perPage']:10);
        return view('admin.user.list',compact('users'));
    }

    public function privileges(){
        $privileges=DB::table('privileges')
            ->leftJoin('users','users.id','=','user_id')
            ->select(['privileges.id','username','nick','authority','privileges.created_at'])
            ->orderBy('authority')->get();
        return view('admin.user.privilege',compact('privileges'));
    }




    private function trans_data($list_str,$use_end_num=false){
        $list=explode(PHP_EOL,$list_str); //按行分割
        foreach ($list as &$item) {
            if($use_end_num && preg_match('/\d+$/',$item,$arr)) {
                $c=intval($arr[0]);
                $item=trim(substr($item,0,-strlen($arr[0])));
            }
            else $c=1;
            while($c--)$ret[]=$item;
        }
        return $ret;
    }
    private function make_passwd($len){
        return substr(str_shuffle("0123456789ABCDEF"),0,8);
    }
    public function create(Request $request){
        if ($request->isMethod('get')){
            return view('admin.user.create');
        }
        if($request->isMethod('post')){
            $data=$request->input('data');
            if($data['stu_id']!=null){
                $usernames=explode(PHP_EOL,$data['stu_id']); //将要注册的账号名收集到$usernames中
            }else{
                $format="%s%0".strlen($data['end'])."d";
                for ($i=intval($data['begin']);$i<=intval($data['end']);$i++)
                    $usernames[]=sprintf($format,$data['prefix'],$i);
            }
            $number=count($usernames);
            $nick=$this->trans_data($data['nick']);
            $email=$this->trans_data($data['email']);
            $school=$this->trans_data($data['school'],true);
            $class=$this->trans_data($data['class'],true);
            foreach($usernames as $i=>$username){
                $password[$username]=$this->make_passwd(8);
                $users[]=[
                    'username'=>$username,
                    'password'=>Hash::make($password[$username]),
                    'revise'=>$data['revise'],
                    'nick'=>isset($nick[$i])?$nick[$i]:'',
                    'email'=>isset($email[$i])?$email[$i]:'',
                    'school'=>isset($school[$i])?$school[$i]:'',
                    'class'=>isset($class[$i])?$class[$i]:'',
                    'created_at'=>date('Y-m-d H:i:s')
                ];
            }
            if (isset($data['check_exist'])){
                //设置了安全检查，发现已存在用户时，告诉管理员，而不是直接删除
                $exist_users=DB::table('users')->whereIn('username',$usernames)->pluck('username');
                if(count($exist_users)>0)
                    return back()->withInput()->with(['exist_users'=>$exist_users]);
            }
            DB::table('users')->whereIn('username',$usernames)->delete();
            DB::table('users')->insert($users);
            foreach($users as &$user)$user['password']=$password[$user['username']];
            return view('admin.user.create',compact('users'));
        }
    }

    public function delete(Request $request){
        $uids=$request->input('uids')?:[];
        DB::table('users')->whereIn('id',$uids)->delete();
    }

    public function update_revise(Request $request){
        if($request->ajax()){
            $uids=$request->input('uids')?:[];
            $revise=$request->input('revise');
            return DB::table('users')->whereIn('id',$uids)->update(['revise'=>$revise]);
        }
        return 0;
    }

    public function privilege_create(Request $request){
        if(!$request->isMethod('post'))return view('admin.fail',['msg'=>'请求有误！']);
        $privilege=$request->input('privilege');
        $privilege['user_id']=DB::table('users')->where('username',$request->input('username'))->value('id');
        if($privilege['user_id']==null)
            $msg='该用户不存在！请先至用户列表确认用户的登录名！';
        else{
            $msg='成功添加'.DB::table('privileges')->insert($privilege).'个权限用户';
        }
        return back()->with('msg',$msg);
    }

    public function privilege_delete(Request $request){
        return DB::table('privileges')->delete($request->input('id'));
    }

    public function reset_pwd(Request $request){
        if($request->isMethod('get')){
            return view('admin.user.reset_pwd');
        }
        if($request->isMethod('post')){
            $username=$request->input('username');
            $password=$request->input('password');
            $user_id=DB::table('users')->where('username',$username)->value('id');
            if($user_id){
                if(DB::table('privileges')->where('user_id',$user_id)->where('authority','admin')->exists()){
                    $msg="该用户拥有管理员权限(admin)，不能被重置密码。请先取消该账号的权限再尝试！";
                }else{
                    DB::table('users')->where('id',$user_id)->update(['password'=>Hash::make($password)]);
                    $user=Auth::user();
                    Auth::onceUsingId($user_id);
                    Auth::logoutOtherDevices($password);
                    Auth::login($user);
                    $msg='重置成功！';
                }
            }else{
                $msg='该账号不存在！';
            }
            return view('admin.user.reset_pwd',compact('msg'));
        }
    }

}
