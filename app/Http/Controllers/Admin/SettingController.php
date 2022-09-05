<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function settings(Request $request){
        if ($request->isMethod('get')){
            return view('admin.settings');
        }else{
            $modified=$request->all();
            foreach($modified as $key=>$val) {
                if($val==='true')$val=true;
                if($val==='false')$val=false;
                if(is_numeric($val))$val=intval($val);

                DB::table('settings')->updateOrInsert(['key'=>$key],['value'=>$val]);
            }
            system('php '.base_path('artisan').' optimize',$out);
            return "OK";
        }
    }

    // 根据git获取代码版本信息
    // private function get_code_version($local){
    //     exec('cd '.base_path(),$_,$status);
    //     if($local){
    //         exec('git log | head -5 2>&1',$version,$status);
    //     }else{
    //         exec('sudo git fetch 2>&1',$_,$status);
    //         exec('git log remotes/origin/master | head -5 2>&1',$version,$status);
    //     }
    //     if(count($version)>=5){
    //         $date = strtotime(substr($version[2], 6));
    //         $date = date('Y-m-d H:i:s', $date);
    //         $version[2] = 'Date: '.$date;
    //         unset($version[3]);
    //     }
    //     return implode('<br>', $version);
    // }

    // 执行：升级系统
    // public function upgrade_oj(Request $request){  
    //           Log::info('----------------------------------------------------------------');
    //     Log::info('Start to upgrade LDUOnlineJudge!');
    //     $source = $request->input('upgrade_source');
    //     $update_sh = storage_path('install/updata.sh');
    //     exec('sudo bash '.$update_sh.' '.$source, $out, $status); //删除旧文件夹
    //     foreach ($out as $line)
    //         Log::info($line);
    //     Log::info('----------------------------------------------------------------');
    //     return 1;
    // }

    // web页面：升级系统
    // public function upgrade(Request $request){
    //     $old_version = $this->get_code_version(true);
    //     $new_version = $this->get_code_version(false);
    //     exec('git remote -v |head -1|cut -d / -f 3', $remote_domain, $status);
    //     $remote_domain = $remote_domain[0]??"github";
    //     return view('admin.upgrade', compact('old_version', 'new_version', 'remote_domain'));
    // }

}
