<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function users(Request $request){
        $users=DB::table('users')->select(['id','username','email','nick','school','class','revise','created_at'])
            ->orderBy('id')->paginate(20);
        return view('admin.user.list',compact('users'));
    }

    public function privileges(){
        $privileges=DB::table('privileges')
            ->leftJoin('users','users.id','=','user_id')
            ->select(['privileges.id','username','nick','authority','privileges.created_at'])
            ->orderBy('authority')->get();
        return view('admin.user.privilege',compact('privileges'));
    }

    public function create_users(Request $request){
        if ($request->isMethod('get')){
            return view('admin.user.create');
        }
        if($request->isMethod('post')){
            $users=[]; //生成用户
            $data=$request->input('data');
            dump($data);
            if($data['stu_id']!=null){
                $usernames=explode(PHP_EOL,$data['stu_id']); //将要注册的账号名收集到$usernames中
            }else{
                $usernames=array();
                $format="%s%0".strlen($data['end'])."d";
                for ($i=intval($data['begin']);$i<=intval($data['end']);$i++)
                    array_push($usernames,sprintf($format,$data['prefix'],$i));
            }
            dd($usernames);



            $data['nick']  =explode(PHP_EOL,$data['nick']);
            $data['school']=explode(PHP_EOL,$data['school']);
            $data['class'] =explode(PHP_EOL,$data['class']);
            $data['email'] =explode(PHP_EOL,$data['email']);
            dd($data);

            $users='{1:one,2:two}';
            return view('admin.user.create',['users'=>json_encode($users)]);
        }
    }

    public function change_privilege(Request $request){
        if(!$request->isMethod('post'))return view('admin.fail',['msg'=>'请求有误！']);
        if($request->input('type')=='add'){
            $privilege=$request->input('privilege');
            $privilege['user_id']=DB::table('users')->where('username',$request->input('username'))->value('id');
            if($privilege['user_id']==null)
                $msg='该用户不存在！请先至用户列表确认用户的登录名！';
            else{
                $msg='成功添加'.DB::table('privileges')->insert([$privilege]).'个权限用户';
            }

            return redirect(route('admin.privileges'))->with('msg',$msg);
        }else if($request->input('type')=='delete'){   //删除
            DB::table('privileges')->delete($request->input('id'));
        }
    }

    public function change_revise_to(Request $request){
        if($request->ajax()){
            $uids=$request->input('uids')?:[];
            $revise=$request->input('revise');
            return DB::table('users')->whereIn('id',$uids)->update(['revise'=>$revise]);
        }
        return 0;
    }
}
