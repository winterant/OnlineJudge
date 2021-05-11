<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request){
        if (!DB::table('privileges')->where('user_id',Auth::id())->exists())
            abort(404);
        exec('sudo ps -eo pid,user,comm,vsz|grep polling 2>&1',$out,$status);
        if(count($out)>0){
            $run=true;
            $info='[ 正在运行 ] pid='.$out[0].'KB';
        }else{
            $run=false;
            $info='[ 停止运行 ]';
        }
        return view('admin.home',compact('run','info'));
    }

    public function cmd_polling(Request $request){
        $oper=$request->input('oper');
        if($oper==='start'||$oper==='restart')
            exec('sudo bash '.base_path('judge/startup.sh'),$out,$status);
        else if($oper==='stop')
            exec('sudo bash '.base_path('judge/stop.sh'),$out,$status);
        return back()->with('ret',implode('<br>',$out));
    }

    //升级系统
    public function upgrade_oj(Request $request){
        $source = $request->input('upgrade_source');
        $cmd_git = 'sudo git clone https://'.$source.'.com/iamwinter/LDUOnlineJudge.git /home/lduoj_upgrade 2>&1';
        $cmd_bash = 'sudo bash /home/lduoj_upgrade/install/ubuntu16.04/update.sh 2>&1';
        exec('sudo rm -rf /home/lduoj_upgrade 2>&1',$out,$status);
        exec($cmd_git,$out,$status);
        exec($cmd_bash,$out,$status);
        return view('client.success',['msg'=>implode('<br>',$out)]);
    }
}
