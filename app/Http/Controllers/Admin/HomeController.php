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
            exec('sudo bash '.base_path('judge/startup.sh').' 2>&1',$out,$status);
        else if($oper==='stop')
            exec('sudo bash '.base_path('judge/stop.sh').' 2>&1',$out,$status);
        return back()->with('ret',implode('<br>',$out));
    }
}
