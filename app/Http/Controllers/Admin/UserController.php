<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function users(){
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

        //可无。为td添加链接 $key=>[是否使用带一个参数的route，地址 ，a标签target]
        $links=[
//            'username'=>[true,'user','_blank'],
//            'update'=>[true,'update_user','_blank'],
            'delete'=>[false,'javascript:alert("暂不提供删除用户。");','_self'],
        ];

        $list=DB::table('users')->select(array_keys($thead))->orderBy('id')->paginate(20);
        return view('admin.list',compact('list','secTitle','thead','links'));
    }
}
