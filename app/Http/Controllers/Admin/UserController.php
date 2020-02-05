<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function users(Request $request){
        $secTitle='用户列表';
        $thead=['id'=>'编号',
            'username'=>'登录名',
            'email'=>'邮箱',
            'nick'=>'姓名',
            'school'=>'学校',
            'class'=>'班级',
            'revise'=>'可修改资料次数',
            'created_at'=>'注册于',
        ];

        $oper_checked=[
            sprintf('<a href="javascript:change_revise_to(0);" class="px-1"
                    title="选中的用户将被禁止修改个人资料!" data-toggle="tooltip">禁止修改</a>
                    
                    <a href="javascript:change_revise_to(1);" class="px-1"
                    title="选中的用户将被设为仅有 1 次修改个人资料的机会！可用于防止用户乱改个人资料"
                    data-toggle="tooltip">允许修改1</a>
                    
                    <a href="javascript:change_revise_to(3);" class="px-1"
                    title="选中的用户将被设为有 3 次修改个人资料的机会!" data-toggle="tooltip">允许修改3</a>
                    
                    <a href="javascript:alert(\'暂未实现删除用户!\');" class="px-1"
                    title="选中的用户将被删除!" data-toggle="tooltip">批量删除</a>'),
        ];

        $list=DB::table('users')->select(array_keys($thead))->orderBy('id')->paginate(20);
        $operation=[];//操作
        foreach ($list as $item){
            $username=''.$item->username;
            $item->username=sprintf('<a href="%s" target="_blank">%s</a>',route('user',$username),$username);

            $operation[$item->id]=sprintf('
                <a href="%s" class="px-1" target="_blank" title="修改" data-toggle="tooltip">
                    <i class="fa fa-edit" aria-hidden="true"></i>
                </a>
                <a href="%s" class="px-1" title="删除" data-toggle="tooltip">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </a>',
                route('user_edit',$username),
                'javascript:alert(\'暂不支持删除用户!\')');
        }
        return view('admin.list',compact('list','secTitle','thead','oper_checked','operation'));
    }

    public function change_revise_to(Request $request){
        if($request->ajax()){
            $uids=$request->input('uids')?:[];
            $revise=$request->input('revise');
            return DB::table('users')->whereIn('id',$uids)->update(['revise'=>$revise]);
        }
        return 0;
    }
}
