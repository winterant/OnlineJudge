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
            'created_at'=>'添加时间',
            'state'=>'状态',
        ];
        //可无。附加批量操作按钮
        $oper_checked=[
            sprintf('<a href="javascript:change_state_to(1);" class="px-1"
                    title="选中的题目将启用，允许普通用户在题库中查看和提交!"
                    data-toggle="tooltip">题目状态公开</a>'),
            sprintf('<a href="javascript:change_state_to(0);" class="px-1"
                    title="选中的题目将密封，普通用户无法在题库中查看和提交，但不会影响竞赛!"
                    data-toggle="tooltip">状态设为隐藏</a>')
        ];

        $list=DB::table('problems')->select(array_keys($thead))->orderBy('id')->paginate(100);

        $operation=[];//操作
        foreach ($list as $item){
            $item->title=sprintf('<a href="%s" target="_blank">%s</a>',route('problem',$item->id),$item->title);
            $item->spj = ($item->spj==1)?'特判':'-';
            $item->state = ($item->state==1)?'公开':'隐藏☆私有';
            $operation[$item->id]=sprintf('
                <a href="%s" target="_blank" class="px-1"
                    data-toggle="tooltip" title="修改">
                    <i class="fa fa-edit" aria-hidden="true"></i></a>
                <a href="%s" class="px-1"
                    data-toggle="tooltip" title="删除">
                    <i class="fa fa-trash" aria-hidden="true"></i></a>
                <a href="#" target="_blank" class="px-1"
                    data-toggle="tooltip" title="测试数据">
                    <i class="fa fa-file" aria-hidden="true"></i></a>',
                route('admin.update_problem_withId',$item->id),
                'javascript:alert(\'为保证系统稳定，不允许删除题目，您可以修改它！\')'
            );
        }
        return view('admin.list',compact('list','secTitle','thead','oper_checked','operation'));
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
            save_problem_samples($id,(array)$request->input('samples'));//保存样例
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
            $spjPath = base_path('storage/data/'.$problem->id.'/spj/spj.cpp');
            $hasSpj=file_exists($spjPath);

            return view('admin.edit_problem',compact('pageTitle','problem','samples','hasSpj'));
        }

        // 提交修改好的题目数据
        if($request->isMethod('post')){
            $problem=$request->input('problem');
            if(!isset($problem['spj']))
                $problem['spj']=0;
            $samples=$request->input('samples');
            $spjFile=$request->file('spj_file');

            save_problem_samples($problem['id'],(array)$samples);
            if($spjFile!=null && $spjFile->isValid())
                save_problem_spj_code($problem['id'],$spjFile);

            DB::table('problems')->where('id',$problem['id'])->update($problem);
            $msg=sprintf('题目<a href="%s" target="_blank">%d</a>修改成功',route('problem',$problem['id']),$problem['id']);
            return view('admin.success',['msg'=>$msg]);
        }
    }

    //管理员修改题目状态  0密封 or 1公开
    public function change_state_to(Request $request){
        if($request->ajax()){
            $pids=$request->input('pids')?:[];
            $state=$request->input('state');
            return DB::table('problems')->whereIn('id',$pids)->update(['state'=>$state]);
        }
        return 0;
    }

    //重判题目|竞赛|提交记录
    public function rejudge(Request $request){

        if($request->isMethod('get')){
            $pageTitle='重判';
            return view('admin.rejudge',compact('pageTitle'));
        }

        if($request->isMethod('post')){
            $pid=$request->input('pid');
            $cid=$request->input('cid');
            $sid=$request->input('sid');

            $count=DB::table('solutions')->where('problem_id',$pid)
                ->orWhere('contest_id',$cid)
                ->orWhere('id',$sid)
                ->update(['result'=>0]);

            return view('admin.success',['msg'=>sprintf('已重判%d条提交记录，可前往状态查看',$count)]);
        }
    }

}
