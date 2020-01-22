<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function users(){
        /**
         * 使用模板list.blade时的变量含义
         * $secTitle  必须，表格标题
         * $thead     必须，表头
         * $intTrans  可选，个别列的数字含义转化
         */
        $secTitle='用户列表';
        $thead=['id'=>'#',
            'username'=>'登录名',
            'email'=>'邮箱',
            'nick'=>'姓名',
            'school'=>'学校',
            'class'=>'班级',
            'submit'=>'提交',
            'solved'=>'解决',
            'created_at'=>'注册于',
        ];
        $list=DB::table('users')->select(array_keys($thead))->orderBy('id')->paginate(20);
        return view('admin.list',compact('list','secTitle','thead'));
    }
}
