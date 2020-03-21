<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function settings(Request $request){
        if ($request->isMethod('get')){
            return view('admin.settings');
        }


        if($request->isMethod('post')){
            $settings=include config_path('oj/main.php');
            $modified=$request->all();
            foreach($modified as $key=>$val) {
                if($val==='true')$val=true;
                if($val==='false')$val=false;
                if(is_numeric($val))$val=intval($val);

                if(isset($settings[$key])){
                    $settings[$key]=$val;
                }
            }

            $text="<?php\n\n"."return"." [\n";
            foreach ($settings as $key=>$value){
                if(is_numeric($key))
                    $text.="\t".$key."\t=> ";
                else //is string
                    $text.="\t\"".$key."\"\t=> ";

                if (is_string($value))
                    $text.="\"".$value."\",\n";
                else if(is_numeric($value))
                    $text.=$value.",\n";
                else if(is_bool($value))
                    $text.=($value?"true":"false").",\n";
            }
            $text.="];";
            file_put_contents(config_path('oj/main.php'),$text);
            system('php '.base_path('artisan').' config:cache');
            return back();
        }
    }
}
