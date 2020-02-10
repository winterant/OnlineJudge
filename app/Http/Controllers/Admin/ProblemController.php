<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProblemController extends Controller
{
    //管理员显示题目列表
    public function list(){
        $problems=DB::table('problems')->select('id','title','source','spj','created_at','hidden',
            DB::raw("(select count(id) from solutions where problem_id=problems.id) as submit"),
            DB::raw("(select count(id) from solutions where problem_id=problems.id and result=4) as  solved")
            )->orderBy('id')->paginate(100);
        return view('admin.problem.list',compact('problems'));
    }

    //管理员添加题目
    public function add(Request $request){
        //提供加题界面
        if($request->isMethod('get')){
            $pageTitle='添加题目 - 程序设计';
            return view('admin.problem.edit',compact('pageTitle'));
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
    public function update(Request $request,$id=-1)
    {
        //get提供修改界面
        if ($request->isMethod('get')) {

            $pageTitle='修改题目 - 程序设计';
            if($id==-1) {
                if(isset($_GET['id']))//用户手动输入了题号
                    return redirect(route('admin.problem.update_withId',$_GET['id']));
                return view('admin.problem.edit',compact('pageTitle'))->with('lack_id',true);
            } //询问要修改的题号
            $problem=DB::table('problems')->find($id);
            if($problem==null)
                return view('admin.fail',['msg'=>'该题目不存在或操作有误!']);

            $samples=read_problem_samples($problem->id);

            //看看有没有特判文件
            $hasSpj=Storage::exists('data/'.$problem->id.'/spj/spj.cpp');
            return view('admin.problem.edit',compact('pageTitle','problem','samples','hasSpj'));
        }

        // 提交修改好的题目数据
        if($request->isMethod('post')){
            $problem=$request->input('problem');
            if(!isset($problem['spj']))
                $problem['spj']=0;
            $samples=$request->input('samples');
            $spjFile=$request->file('spj_file');

            save_problem_samples($id,(array)$samples);
            if($spjFile!=null && $spjFile->isValid())
                save_problem_spj($id,$spjFile);

            DB::table('problems')->where('id',$id)->update($problem);
            $msg=sprintf('题目<a href="%s" target="_blank">%d</a>修改成功',route('problem',$id),$id);
            return view('admin.success',['msg'=>$msg]);
        }
    }

    //管理员修改题目状态  0密封 or 1公开
    public function update_hidden(Request $request){
        if($request->ajax()){
            $pids=$request->input('pids')?:[];
            $hidden=$request->input('hidden');
            return DB::table('problems')->whereIn('id',$pids)->update(['hidden'=>$hidden]);
        }
        return 0;
    }

    //重判题目|竞赛|提交记录
    public function rejudge(Request $request){

        if($request->isMethod('get')){
            $pageTitle='重判';
            return view('admin.problem.rejudge',compact('pageTitle'));
        }

        if($request->isMethod('post')){
            $pid=$request->input('pid');
            $cid=$request->input('cid');
            $sid=$request->input('sid');
            $date=$request->input('date');
            if($pid||$cid||$sid||($date[1]&&$date[2])){
                $count=DB::table('solutions')
                    ->when($pid,function ($q)use($pid){$q->where('problem_id',$pid);})
                    ->when($cid,function ($q)use($cid){$q->where('contest_id',$cid);})
                    ->when($sid,function ($q)use($sid){$q->where('id',$sid);})
                    ->when($date[1],function ($q)use($date){
                            foreach ($date as &$d){$d=str_replace('T',' ',$d);}
                            $q->where('submit_time','>',$date[1])->where('submit_time','<',$date[2]);
                        })
                    ->update(['result'=>0]);
            }
            return view('admin.success',['msg'=>sprintf('已重判%d条提交记录，可前往状态查看',isset($count)?$count:0)]);
        }
    }

    public function import_export(){
        return view('admin.problem.import_export');
    }
    public function import(Request $request){
        $file=$request->file('import_xml');
        $xmlDoc=simplexml_load_file($file->getRealPath(),'SimpleXMLElement',LIBXML_PARSEHUGE);
        $searchNodes = $xmlDoc->xpath ( "/fps/item" );
        foreach ($searchNodes as $node) {
            $problem=[
                'title'       => ''.$node->title,
                'description' => ''.$node->description,
                'input'       => ''.$node->input,
                'output'      => ''.$node->output,
                'hint'        => ''.$node->hint,
                'source'      => ''.$node->source,
                'spj'         => ''.$node->spj?:0,
                'time_limit'  => ''.$node->time_limit / ($node->time_limit->attributes()->unit==='ms'?1000:1),
                'memory_limit'=> ''.$node->memory_limit / ($node->memory_limit->attributes()->unit==='kb'?1024:1),
            ];
            $pid=DB::table('problems')->insertGetId($problem);
            //下面保存sample，spj，test
            $test_inputs =$node->children()->test_input;
            $test_outputs=$node->children()->test_output;
            $test_count=0;
            foreach ($test_inputs as $test_in){
                var_dump($test_in);
            print_r("<p style='margin-left:300px;margin-top: 80px;'>".$test_in.'</p>');
//                Storage::put(sprintf('data/%d/test/test%d.in',$pid,$test_count++), json_decode(json_encode($test_in),true));
            }
//            foreach ($test as $test1) {
//            print_r("<p style='margin-left:300px;margin-top: 80px;'>".$test1.'</p>');
//
//            }
            dd(1);
//            dump($problem);
//            $test=$node->time_limit->attributes()->unit;
//            print_r("<p style='margin-left:300px;margin-top: 80px;'>".$test.'</p>');
        }
        return view('admin.success',['msg'=>'已导入题目']);
    }
    public function export(Request $request){

    }
}
