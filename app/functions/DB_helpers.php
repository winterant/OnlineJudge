<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


//查询一条最置顶的系统公告的id和title
function get_top_notice(){
    return DB::table('notices')
        ->select('id','title')
        ->where('state','!=',0)
        ->orderByDesc('state')
        ->orderByDesc('id')
        ->first();
}
