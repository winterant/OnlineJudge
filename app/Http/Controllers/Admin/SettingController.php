<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    private function get_code_version($local){
        exec('cd '.base_path(),$_,$status);
        if($local){
            exec('git log | head -5 2>&1',$version,$status);
        }else{
            if(isset($_GET['check_update']))
                exec('sudo git fetch 2>&1',$_,$status);
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

    //升级系统
    public function upgrade_oj(Request $request){
        $source = $request->input('upgrade_source');

        $new_project = base_path('storage/oj_upgrade');
        exec('rm -rf '.$new_project,$out,$status); //删除旧文件夹

        $cmd_git = 'git clone https://'.$source.'/iamwinter/LDUOnlineJudge.git '.$new_project.' 2>&1';
        $cmd_bash = 'bash '.$new_project.'/install/ubuntu/update.sh '.base_path().' 2>&1';
        exec($cmd_git,$out,$status);
        exec($cmd_bash,$out,$status);
        return 1;
//        return '<h1>升级成功！</h1><br>'.implode('<br>',$out);
//        return view('client.success',['msg'=>implode('<br>',$out)]);
    }

    public function settings(Request $request){
        if ($request->isMethod('get')){
            $old_version = $this->get_code_version(true);
            $new_version = $this->get_code_version(false);
            exec('git remote -v |head -1|cut -d / -f 3', $remote_domain, $status);
            $remote_domain = $remote_domain[0];
            return view('admin.settings', compact('old_version', 'new_version', 'remote_domain'));
        }


        if($request->isMethod('post')){
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
}
