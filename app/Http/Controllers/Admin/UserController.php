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

        $oper_checked=[
            sprintf('<a href="javascript:alert(\'暂未实现删除用户!\');"
                    title="选中的将被删除!"
                    data-toggle="tooltip" data-placement="bottom">批量删除</a>'),
        ];

        $list=DB::table('users')->select(array_keys($thead))->orderBy('id')->paginate(20);
        $operation=[];//操作
        foreach ($list as $item){
//            $item->username=sprintf('<a href="%s" target="_blank">%s</a>',route('user',$item->id),$item->username);

            $operation[$item->id]=sprintf('<a href="%s" class="mr-2">
                                                      <i class="fa fa-trash" aria-hidden="true"></i> 删除
                                                  </a>','javascript:alert(\'暂未实现删除用户!\')');
        }
        return view('admin.list',compact('list','secTitle','thead','oper_checked','operation'));
    }
}
