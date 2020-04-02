<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $solutions=DB::table('solutions')
            ->join('users','solutions.user_id','=','users.id')
            ->leftJoin('contests','solutions.contest_id','=','contests.id')  //非必须，left
            ->leftJoin('contest_problems',function ($q){
                $q->on('solutions.contest_id','=','contest_problems.contest_id')->on('solutions.problem_id','=','contest_problems.problem_id');
            })
            ->select('solutions.id','solutions.contest_id','contest_problems.index','solutions.problem_id','solutions.user_id','nick','username',
                'result','time','memory','language', 'submit_time', 'solutions.judge_type', 'pass_rate','judger')
            ->when(isset($_GET['inc_contest']),function ($q){
                if(Auth::check()&&Auth::user()->privilege('solution'))
                    return $q;
                return $q->where('solutions.contest_id',-1)
                    ->orWhere('end_time','<',date('Y-m-d H:i:s'));//普通用户只能查看结束比赛的solution
            })
            ->when(!isset($_GET['inc_contest']),function ($q){return $q->where('solutions.contest_id',-1);})
            ->when(isset($_GET['pid'])&&$_GET['pid']!='',function ($q){return $q->where('solutions.problem_id',$_GET['pid']);})
            ->when(isset($_GET['username'])&&$_GET['username']!='',function ($q){return $q->where('username','like',$_GET['username'].'%');})
            ->when(isset($_GET['result'])&&$_GET['result']!='-1',function ($q){return $q->where('result',$_GET['result']);})
            ->when(isset($_GET['language'])&&$_GET['language']!='-1',function ($q){return $q->where('language',$_GET['language']);})
            ->orderByDesc('solutions.id')
            ->paginate(10);

        return view('client.status',compact('solutions'));
    }

    public function ajax_get_status(Request $request){
        if($request->ajax()){
            $sids=$request->input('sids');
            $solutions=DB::table('solutions')->select(['id','judge_type','result','pass_rate'])->whereIn('id',$sids)->get();
            $ret=[];
            foreach ($solutions as $item){
                $ret[]=[
                    'id'=>$item->id,
                    'result'=>$item->result,
                    'color'=>config('oj.resColor.'.$item->result),
                    'text'=>config('oj.result.'.$item->result).($item->judge_type=='oi' ? sprintf(' (%s)',round($item->pass_rate*100)) : null)
                ];
            }
            return json_encode($ret);
        }
        return json_encode([]);
    }

    public function solution($id){

        $solution=DB::table('solutions')
            ->join('users','solutions.user_id','=','users.id')
            ->select(['solutions.id','problem_id','contest_id','user_id','username','result','pass_rate','time','memory',
                'judge_type','submit_time','judge_time','code','code_length','language','error_info'])
            ->where('solutions.id',$id)->first();
        if(!Auth::user()->privilege('solution')&&Auth::id()!=$solution->user_id)
            return view('client.fail',['msg'=>trans('sentence.Permission denied')]);
        return view('client.solution',compact('solution'));
    }

    //从txt文件读取的内容转码
    function autoiconv($text,$type = "gb2312//ignore"){
        define('UTF32_BIG_ENDIAN_BOM', chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
        define('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
        define('UTF16_BIG_ENDIAN_BOM', chr(0xFE) . chr(0xFF));
        define('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));
        define('UTF8_BOM', chr(0xEF) . chr(0xBB) . chr(0xBF));
        $first2 = substr($text, 0, 2);
        $first3 = substr($text, 0, 3);
        $first4 = substr($text, 0, 3);
        $encodType = "";
        if ($first3 == UTF8_BOM)
            $encodType = 'UTF-8 BOM';
        else if ($first4 == UTF32_BIG_ENDIAN_BOM)
            $encodType = 'UTF-32BE';
        else if ($first4 == UTF32_LITTLE_ENDIAN_BOM)
            $encodType = 'UTF-32LE';
        else if ($first2 == UTF16_BIG_ENDIAN_BOM)
            $encodType = 'UTF-16BE';
        else if ($first2 == UTF16_LITTLE_ENDIAN_BOM)
            $encodType = 'UTF-16LE';
        //下面的判断主要还是判断ANSI编码的·
        if ($encodType == '') { //即默认创建的txt文本-ANSI编码的
            $content = mb_convert_encoding($text,"UTF-8","auto");
//            $content = iconv("GBK", "UTF-8//ignore", $text);
        } else if ($encodType == 'UTF-8 BOM') {//本来就是UTF-8不用转换
            $content = $text;
        } else {//其他的格式都转化为UTF-8就可以了
            $content = iconv($encodType, "UTF-8", $text);
        }
        return $content;
    }
    /*
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if(!Auth::check()) //未登录 => 请先登录
            return view('client.fail',['msg'=>trans('sentence.Please login first')]);

        //获取前台提交的solution信息
        $data = $request->input('solution');

        //拦截频繁提交
        $last_submit_time = DB::table('solutions')
            ->where('user_id',Auth::id())
            ->orderByDesc('submit_time')
            ->value('submit_time');
        if(time()-strtotime($last_submit_time)<intval(config('oj.main.submit_interval')))
            return view('client.fail',['msg'=>trans('sentence.submit_frequently',['sec'=>config('oj.main.submit_interval')])]);

        if(!isset($data['cid'])) //通过题库提交
        {
            $hidden=DB::table('problems')->where('id',$data['pid'])->value('hidden');
            if(!Auth::user()->privilege('problem') && $hidden==1) //不是管理员&&问题隐藏 => 不允许提交
                return view('client.fail',['msg'=>trans('main.Problem').$data['pid'].'：'.trans('main.Hidden')]);
        }

        if(null!=($file=$request->file('code_file')))//用户提交了文件,从临时文件中直接提取文本
            $data['code']=self::autoiconv(file_get_contents($file->getRealPath()));

        //竞赛提交&&不允许提交的代码语言
        if(isset($data['cid']) && !((1<<$data['language'])&DB::table('contests')->find($data['cid'])->allow_lang) )
            return view('client.fail',['msg'=>'A not allowed language!']);

        DB::table('solutions')->insert([
            'problem_id'    => $data['pid'],
            'contest_id'    => isset($data['cid'])?$data['cid']:-1,
            'user_id'       => Auth::id(),
            'result'        => 0,
            'language'      => ($data['language']!=null)?$data['language']:0,
            'submit_time'   => date('Y-m-d H:i:s'),

            'judge_type'    => isset($data['judge_type'])?$data['judge_type']:'acm', //acm,oi

            'ip'            => $request->getClientIp(),
            'code_length'   => strlen($data['code']),
            'code'          => $data['code']
            ]);

        Cookie::queue('submit_language',$data['language']);
        if(isset($data['cid'])) //竞赛提交
            return redirect(route('contest.status',[$data['cid'],'index'=>$data['index'],'username'=>Auth::user()->username]));

        return redirect(route('status',['pid'=>$data['pid'],'username'=>Auth::user()->username]));
    }
}
