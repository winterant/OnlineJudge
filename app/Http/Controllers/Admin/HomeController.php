<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    private function get_code_version($current){
        exec('cd '.base_path(),$_,$status);
        if($current){
            exec('git log | head -5 2>&1',$version,$status);
        }else{
            exec('git fetch 2>&1',$_,$status);
            exec('git log remotes/origin/master | head -5 2>&1',$version,$status);
        }
        if(count($version)>=5){
            $date = strtotime(substr($version[2], 6));
            $date = date('Y-m-d H:i:s', $date);
            $version[2] = 'Date: '.$date;
            unset($version[3]);
        }
        return implode('<br>', $version);
    }

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

        $old_version = $this->get_code_version(true);
        $new_version = $this->get_code_version(false);
        return view('admin.home',compact('run','info', 'old_version', 'new_version'));
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
        return 1;
//        return '<h1>升级成功！</h1><br>'.implode('<br>',$out);
//        return view('client.success',['msg'=>implode('<br>',$out)]);
    }
}
