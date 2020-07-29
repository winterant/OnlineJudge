<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function settings(Request $request){
        if ($request->isMethod('get')){
            return view('admin.settings');
        }


        if($request->isMethod('post')){
            $modified=$request->all();
            foreach($modified as $key=>$val) {
                if($val==='true')$val=true;
                if($val==='false')$val=false;
                if(is_numeric($val))$val=intval($val);

                DB::table('settings')->updateOrInsert(['key'=>$key],['value'=>$val]);
            }
            system('sudo php '.base_path('artisan').' optimize',$out);
            return "OK";
        }
    }
}
