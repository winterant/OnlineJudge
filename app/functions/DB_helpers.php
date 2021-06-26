<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/************************ 前台 ***********************************/
//查询一条最置顶的系统公告的id和title
function get_top_notice(){
    return DB::table('notices')
        ->select('id','title')
        ->where('state','!=',0)
        ->orderByDesc('state')
        ->orderByDesc('id')
        ->first();
}

/************************* 后台管理 *****************************/
//获取配置值
function get_setting($key,$default=null){
    $val=DB::table('settings')->where('key',$key)->value('value');
    if($val==null){
        $sys_conf = config('oj.main.'.$key, $default);
        DB::table('settings')->updateOrInsert(['key'=>$key,'value'=>$sys_conf]);
        return $sys_conf;
    }
    return $val;
}
