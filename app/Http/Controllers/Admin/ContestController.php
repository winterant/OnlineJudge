<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsArray\consume;

class ContestController extends Controller
{
    public function list(){
        $contests=DB::table('contests')
            ->leftJoin('users','users.id','=','user_id')
            ->select('contests.*','username')
            ->orderBy('id')->paginate();
        return view('admin.contest.list',compact('contests'));
    }

    public function add(Request $request){
        if($request->isMethod('get')){
            $pageTitle='创建竞赛';
            return view('admin.contest.edit',compact('pageTitle'));
        }
        if($request->isMethod('post')){
            $contest=$request->input('contest');
            $c_users=$request->input('contest_users'); //指定用户
            $files=$request->file('files')?:[];

            //数据格式处理
            $contest['start_time']=str_replace('T',' ',$contest['start_time']);
            $contest['end_time']  =str_replace('T',' ',$contest['end_time']);
            $contest['lock_rate']=is_numeric($contest['lock_rate'])?min(1,max(0,intval($contest['lock_rate']))):0;
            if($contest['access']!='password')unset($contest['password']);
            $contest['user_id']=Auth::id(); //创建者

            //数据库插入
            $cid=DB::table('contests')->insertGetId($contest);
            if($contest['access']=='private'){
                $uids=DB::table('users')->whereIn('username',explode(PHP_EOL,$c_users))->pluck('id');
                $u_c=[];
                foreach ($uids as &$u)
                    array_push($u_c,['user_id'=>$u,'contest_id'=>$cid]);
                DB::table('contest_users')->insert($u_c);
            }

            foreach ($files as $file) {     //保存附件
                $file->move(storage_path('app/public/contest/files/'.$cid),$file->getClientOriginalName());//保存附件
            }
            $msg=sprintf('成功创建竞赛：<a href="%s" target="_blank">%d</a>',route('contest.home',$cid),$cid);
            return view('admin.success',compact('msg'));
        }
    }

    public function update(Request $request,$id){
        if (!Auth::user()->privilege('admin')&&Auth::id()!=DB::table('contests')->where('id',$id)->value('id'))
            return view('admin.fail',['msg'=>'权限不足!您不是这场比赛的创建者']);

        if($request->isMethod('get')){
            $contest=DB::table('contests')->find($id);
            $files=Storage::allFiles('public/contest/files/'.$id);
            $pageTitle='修改竞赛';
            return view('admin.contest.edit',compact('pageTitle','contest','files'));
        }
        if($request->isMethod('post')){
            $contest=$request->input('contest');
            $c_users=$request->input('contest_users'); //指定用户
            $files=$request->file('files')?:[];

            //数据格式处理
            $contest['start_time']=str_replace('T',' ',$contest['start_time']);
            $contest['end_time']  =str_replace('T',' ',$contest['end_time']);
            $contest['lock_rate']=is_numeric($contest['lock_rate'])?intval(min(1,max(0,intval($contest['lock_rate'])))):0;
            if($contest['access']!='password')unset($contest['password']);

            //数据库插入
            if($contest['access']=='private'||$contest['password']!=DB::table('contests')->where('id',$id)->value('password'))
                DB::table('contest_users')->where('contest_id',$id)->delete(); //删掉已通行的用户，重新加入
            $ret=DB::table('contests')->where('id',$id)->update($contest);
            if($contest['access']=='private'){
                $uids=DB::table('users')->whereIn('username',explode(PHP_EOL,$c_users))->pluck('id');
                $u_c=[];
                foreach ($uids as &$u)
                    array_push($u_c,['user_id'=>$u,'contest_id'=>$id]);
                DB::table('contest_users')->insert($u_c);
            }

            foreach ($files as $file) {     //保存附件
                $file->move(storage_path('app/public/contest/files/'.$id),$file->getClientOriginalName());//保存附件
            }
            $msg=sprintf('成功更新竞赛：<a href="%s" target="_blank">%d</a>',route('contest.home',$id),$id);
            return view('admin.success',compact('msg'));
        }
    }

    public function upload_image(Request $request){
        $image=$request->file('upload');
        $fname=uniqid(date('Ymd_His_')).'.'.$image->getClientOriginalExtension();
        $image->move(storage_path('app/public/contest/images'),$fname);
        return json_encode(['uploaded'=>true,'url'=> Storage::url('public/contest/images/'.$fname)]);
    }

    public function delete(Request $request){
        $cids=$request->input('cids')?:[];
        return DB::table('contests')->whereIn('id',$cids)->delete();
    }

    public function update_hidden(Request $request){
        $cids=$request->input('cids')?:[];
        $hidden=$request->input('hidden');
        return DB::table('contests')->whereIn('id',$cids)->update(['hidden'=>$hidden]);
    }
}
