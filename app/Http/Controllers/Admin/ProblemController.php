<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProblemController extends Controller
{
    //管理员显示题目列表
    public function problems(){
        //*表格标题
        $secTitle='问题列表';
        //*表头
        $thead=['id'=>'题号',
            'title'=>'题目',
            'source'=>'出处',
            'spj'=>'特判',
            'submit'=>'提交',
            'solved'=>'解决',
            'in_date'=>'添加时间',
            'state'=>'状态',
        ];
        //可无。个别列的数字含义转化
        $intTrans=[
            'spj'   =>[0=>'否',1=>'特判'],
            'state'=>[0=>'*隐藏',1=>'公开'],
        ];
        //可无。为td添加链接 $key=>[是否使用带一个参数的route，地址 ，a标签target]
        $links=[
            'title'=>[true,'problem','_blank'],
            'update'=>[true,'admin.update_problem_withId','_blank'],
            'delete'=>[false,'javascript:alert("为保证系统稳定运行，不允许删除题目。您可以修改题目或将题目状态改为隐藏!");','_self'],
        ];
        //可无。附加批量操作按钮
        $checkbox_action=[
            [   'href'=>'javascript:change_state_to(1);',
                'title'=>'选中的题目将启用，允许普通用户在题库中查看和提交!',
                'content'=>'状态设为公开'
            ],
            [   'href'=>'javascript:change_state_to(0);',
                'title'=>'选中的题目将密封，普通用户无法在题库中查看和提交，但不会影响竞赛!!',
                'content'=>'状态设为隐藏'
            ],
        ];
        $list=DB::table('problems')->select(array_keys($thead))->orderBy('id')->paginate(100);
        return view('admin.list',compact('list','secTitle','thead','intTrans','links','checkbox_action'));
    }

    //管理员添加题目
    public function add_problem(Request $request){
        //提供加题界面
        if($request->isMethod('get')){
            $pageTitle='添加题目 - 程序设计';
            return view('admin.edit_problem',compact('pageTitle'));
        }
        //提交一条新数据
        if($request->isMethod('post')){
            $problem=$request->input('problem');
            unset($problem['id']);
            $id=DB::table('problems')->insertGetId($problem);
            save_problem_samples($id,(array)$request->input('samples'));
            $msg=sprintf('题目<a href="%s" target="_blank">%d</a>添加成功',route('problem',$id),$id);
            return view('admin.success',compact('msg'));
        }
    }

    //管理员修改题目
    public function update_problem(Request $request,$id=-1)
    {
        //get提供修改界面
        if ($request->isMethod('get')) {

            $pageTitle='修改题目 - 程序设计';
            if($id==-1) {
                if(isset($_GET['id']))//用户手动输入了题号
                    return redirect(route('admin.update_problem_withId',$_GET['id']));
                return view('admin.edit_problem',compact('pageTitle'))->with('lack_id',true);
            } //询问要修改的题号
            $problem=DB::table('problems')->find($id);
            if($problem==null)
                return view('admin.fail',['msg'=>'该题目不存在或操作有误!']);

            $samples=read_problem_samples($problem->id);

            //看看有没有特判文件
            $hasSpj=false;
            $spjPath = base_path(config('oj.main.judgeDataPath') . '/' . $problem->id . '/spj');
            if (is_dir($spjPath)) {
                foreach (scandir($spjPath) as $filename) {
                    if ($filename === 'spj.cpp'){
                        $hasSpj=true;break;
                    }
                }
            }

            return view('admin.edit_problem',compact('pageTitle','hasSpj','problem','samples'));
        }

        // 提交修改好的题目数据
        if($request->isMethod('post')){
            $problem=$request->input('problem');
            $samples=$request->input('samples');

            save_problem_samples($problem['id'],(array)$samples);

            DB::table('problems')->where('id',$problem['id'])->update($problem);
            $msg=sprintf('题目<a href="%s" target="_blank">%d</a>修改成功',route('problem',$problem['id']),$problem['id']);
            return view('admin.success',['msg'=>$msg]);
        }
    }

    //管理员修改题目状态  0密封 or 1公开
    public function change_state_to(Request $request){
        if($request->ajax() && Auth::user()->is_admin()){
            $pids=$request->input('pids')?:[];
            $state=$request->input('state');
            return DB::table('problems')->whereIn('id',$pids)->update(['state'=>$state]);
        }
        return 0;
    }

}
