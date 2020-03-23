<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\Input;

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
        //提交一条新题目
        if($request->isMethod('post')){
            $problem=$request->input('problem');
            if(!isset($problem['spj'])) $problem['spj']=0;
            $pid=DB::table('problems')->insertGetId($problem);
            $samp_ins =$request->input('sample_ins');
            $samp_outs=$request->input('sample_outs');
            save_problem_samples($pid,(array)$samp_ins,(array)$samp_outs);//保存样例
            $spjFile=$request->file('spj_file');
            if($spjFile!=null && $spjFile->isValid()) save_problem_spj($pid,file_get_contents($spjFile)); //保存spj
            $msg=sprintf('题目<a href="%s" target="_blank">%d</a>添加成功！请及时 <a href="#">上传测试数据</a>',route('problem',$pid),$pid);
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

        // 提交修改好的题目
        if($request->isMethod('post')){
            $problem=$request->input('problem');
            if(!isset($problem['spj'])) $problem['spj']=0;
            DB::table('problems')->where('id',$id)->update($problem);
            ///保存样例、spj
            $samp_ins =$request->input('sample_ins');
            $samp_outs=$request->input('sample_outs');
            $spjFile=$request->file('spj_file');
            save_problem_samples($id,(array)$samp_ins,(array)$samp_outs); //保存样例
            if($spjFile!=null && $spjFile->isValid())
                save_problem_spj($id,file_get_contents($spjFile));

            $msg=sprintf('题目<a href="%s" target="_blank">%d</a>修改成功！ <a href="#">上传测试数据</a>',route('problem',$id),$id);
            return view('admin.success',['msg'=>$msg]);
        }
    }

    public function upload_image(Request $request){
        $image=$request->file('upload');
        $fname=uniqid(date('Ymd_His_')).'.'.$image->getClientOriginalExtension();
        $image->move(storage_path('app/public/problem/images'),$fname);
        return json_encode(['uploaded'=>true,'url'=> Storage::url('public/problem/images/'.$fname)]);
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


    //测试数据管理
    public function test_data(){
        //读取数据文件
        $tests=[];
        if(isset($_GET['pid'])){
            foreach (Storage::allFiles('data/'.$_GET['pid'].'/test') as $filepath){
                $name=pathinfo($filepath,PATHINFO_FILENAME);  //文件名
                $ext=pathinfo($filepath,PATHINFO_EXTENSION);    //拓展名
                $tests[]=['index'=>$name,'filename'=>$name.'.'.$ext, 'size'=>Storage::size($filepath)];
            }
        }
        uasort($tests,function ($x,$y){
            return $x['index']>$y['index'];
        });
        return view('admin.problem.test_data',compact('tests'));
    }

    public function upload_data(Request $request){
        $pid=$request->input('pid');
        $files=$request->file('files')?:[];
        foreach ($files as $file) {     //保存文件
            $file->move(storage_path('app/data/'.$pid.'/test'),$file->getClientOriginalName());//保存附件
        }
        return back();
    }

    public function get_data(Request $request){
        $pid=$request->input('pid');
        $filename=$request->input('filename');
        $data=Storage::get('data/'.$pid.'/test/'.$filename);
        return json_encode($data);
    }

    public function update_data(Request $request){
        $pid=$request->input('pid');
        $filename=$request->input('filename');
        $content=$request->input('content');
        Storage::put('data/'.$pid.'/test/'.$filename,$content);
        return back();
    }

    public function delete_data(Request $request){
        $pid=$request->input('pid');
        $fnames=$request->input('fnames');
        foreach ($fnames as $filename)
            Storage::delete('data/'.$pid.'/test/'.$filename);
        return back();
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
                            $q->where('submit_time','>',str_replace('T',' ',$date[1]))
                                ->where('submit_time','<',str_replace('T',' ',$date[2]));
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
        //ajax post: 接收分片的xml大文件.
        $import=intval($request->input('import'));  //是否执行导入的指令
        if(!$import){
            $block_id=intval($request->input('block_id')); //块号
            $file_block=$request->file('file_block');  //文件块
            $file_block->move(storage_path('app/temp_xml'),$block_id);
            return $block_id;
        }


        //上传完成，下面合并切片，最后导入题库
        for($i=0;$i<intval($import);$i++){
            $block=Storage::get('temp_xml/'.$i);
            file_put_contents(storage_path('app/temp_xml/import_problems.xml'),$block,$i?FILE_APPEND:FILE_TEXT);//追加:覆盖
        }
        //读取xml->导入题库
        $xmlDoc=simplexml_load_file(storage_path('app/temp_xml/import_problems.xml'),null,LIBXML_NOCDATA|LIBXML_PARSEHUGE);
        $searchNodes = $xmlDoc->xpath ( "/fps/item" );
        $first_pid=null;
        foreach ($searchNodes as $node) {
            $problem=[
                'title'       => $node->title,
                'description' => $node->description,
                'input'       => $node->input,
                'output'      => $node->output,
                'hint'        => $node->hint,
                'source'      => $node->source,
                'spj'         => $node->spj?1:0,
                'time_limit'  => $node->time_limit * (strtolower($node->time_limit->attributes()->unit)=='s'?1000:1), //本oj用ms
                'memory_limit'=> $node->memory_limit / (strtolower($node->memory_limit->attributes()->unit)=='kb'?1024:1),
            ];
            //保存图片
            foreach($node->img as $img) {
                $ext=pathinfo($img->src,PATHINFO_EXTENSION); //后缀
                $save_path='public/problem/images/'.uniqid(date('Ymd_His_')).'.'.$ext; //路径
                Storage::put($save_path, base64_decode($img->base64)); //保存
                $problem['description']=str_replace($img->src,Storage::url($save_path),$problem['description']);
                $problem['input']      =str_replace($img->src,Storage::url($save_path),$problem['input']);
                $problem['output']     =str_replace($img->src,Storage::url($save_path),$problem['output']);
                $problem['hint']       =str_replace($img->src,Storage::url($save_path),$problem['hint']);
            }
            $pid=DB::table('problems')->insertGetId($problem);
            if (!$first_pid)$first_pid=$pid;
            //下面保存sample，test，spj
            $samp_inputs =(array)$node->children()->sample_input;
            $samp_outputs=(array)$node->children()->sample_output;
            $test_inputs =(array)$node->children()->test_input;
            $test_outputs=(array)$node->children()->test_output;
            save_problem_samples($pid,$samp_inputs,$samp_outputs);
            foreach ($test_inputs as $i=>$in){
                Storage::put(sprintf('data/%d/test/%d.in',$pid,$i),$in);
            }
            foreach ($test_outputs as $i=>$out){
                Storage::put(sprintf('data/%d/test/%d.out',$pid,$i),$out);
            }
            if($node->spj){
                //保存特判
                save_problem_spj($pid,$node->spj);
            }
            foreach($node->solution as $solu){
                $lang=array_search($solu->attributes()->language,include config_path('oj/lang.php'));
                if($lang!==false){
                    DB::table('solutions')->insert([
                        'problem_id'    => $pid,
                        'contest_id'    => -1,
                        'user_id'       => Auth::id(),
                        'result'        => 0,
                        'language'      => $lang,
                        'submit_time'   => date('Y-m-d H:i:s'),
                        'judge_type'    => 'acm', //acm,oi,exam
                        'ip'            => $request->getClientIp(),
                        'code_length'   => strlen($solu),
                        'code'          => $solu,
                    ]);
                }
            }
        }
        Storage::deleteDirectory('temp_xml'); //删除已经没用的xml文件
        return $first_pid.($first_pid<$pid?'-'.$pid:'');
    }

    public function export(Request $request){
        //todo
    }
}
