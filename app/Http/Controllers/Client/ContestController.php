<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class ContestController extends Controller
{
    public function contests(){
//        DB::connection()->enableQueryLog();
        $contests=DB::table('contests')
            ->select(['id','type','title','start_time','end_time','access','password',
                DB::raw("case when end_time<now() then 3 when start_time>now() then 2 else 1 end as state"),
                DB::raw("case access when 'public'
                    then (select count(DISTINCT B.user_id) from solutions B where B.contest_id=contests.id)
                    else (select count(DISTINCT C.user_id) from contest_users C where C.contest_id=contests.id)
                    end as number")])
            ->orderBy('state')
            ->orderBy('id')
            ->paginate(10);
//        dump(DB::getQueryLog());
//        dd($contests);
        return view('contest.contests',compact('contests'));
    }

    public function password(Request $request,$id){
        // 验证密码
        $contest=DB::table('contests')->find($id);
        if ($request->isMethod('get')){
            return view('contest.password',compact('contest'));
        }
        if ($request->isMethod('post'))//接收提交的密码
        {
            if($request->input('pwd')==$contest->password) //通过验证
            {
                DB::table('contest_users')->updateOrInsert(['contest_id'=>$contest->id,'user_id'=>Auth::id()]);//保存
                return redirect(route('contest.home',$contest->id));
            }
            else
            {
                $msg=trans('sentence.pwd wrong');
                return view('contest.password',compact('contest','msg'));
            }
        }
    }

    public function home($id){
        $contest=DB::table('contests')
            ->select(['id','type','title','start_time','end_time','access','password','description',
                DB::raw("case when end_time<now() then 3 when start_time>now() then 2 else 1 end as state"),
                DB::raw("case access when 'public'
                    then (select count(DISTINCT B.user_id) from solutions B where B.contest_id=contests.id)
                    else (select count(DISTINCT C.user_id) from contest_users C where C.contest_id=contests.id)
                    end as number")])->find($id);
        $problems=DB::table('problems')
            ->join('contest_problems','contest_problems.problem_id','=','problems.id')
            ->where('contest_id',$id)
            ->select(['problems.id','problems.title','contest_problems.index',
                DB::raw("(select count(*) from solutions where contest_id=".$contest->id." and problem_id=problems.id and result=4) as solved"),
                DB::raw("(select count(*) from solutions where contest_id=".$contest->id." and problem_id=problems.id) as submit"),
                ])
            ->orderBy('contest_problems.index')
            ->get();
        return view('contest.home',compact('contest','problems'));
    }

    public function problem($id,$pid){
        $contest=DB::table('contests')->find($id);
        $problem=DB::table('problems')
            ->select('*',
                DB::raw("(select count(id) from solutions where problem_id=problems.id) as submit"),
                DB::raw("(select count(id) from solutions where problem_id=problems.id and result=4) as solved"))
            ->join('contest_problems','contest_problems.problem_id','=','problems.id')
            ->where('contest_id',$id)
            ->where('index',$pid)
            ->first();
        $samples=read_problem_samples($problem->problem_id);

        $hasSpj=file_exists(base_path('storage/data/'.$problem->id.'/spj/spj.cpp'));
        return view('contest.problem',compact('contest','problem','samples','hasSpj'));
    }

    public function status($id){
        $contest=DB::table('contests')->find($id);
        if(!Auth::user()->is_admin() && time()<strtotime($contest->end_time)) //比赛没结束，只能看自己
            $_GET['username']=Auth::user()->username;

        $solutions=DB::table('solutions')
            ->join('users','solutions.user_id','=','users.id')
            ->join('contest_problems','solutions.problem_id','=','contest_problems.problem_id')
            ->select(['solutions.id','index','user_id','username','nick','result','time','memory','language','submit_time'])
            ->where('solutions.contest_id',$id)
            ->where('contest_problems.contest_id',$id)
            ->when(isset($_GET['pid'])&&$_GET['pid']!='',function ($q){return $q->where('problem_id',$_GET['pid']);})
            ->when(isset($_GET['username'])&&$_GET['username']!='',function ($q){return $q->where('username','like',$_GET['username'].'%');})
            ->when(isset($_GET['result'])&&$_GET['result']!='',function ($q){return $q->where('result',$_GET['result']);})
            ->orderByDesc('solutions.id')
            ->paginate(10);

        return view('contest.status',compact('contest','solutions'));
    }




    private static function get_rank_end_time($contest){
        //rank的辅助函数，获取榜单的截止时间
        if(Auth::user()->is_admin()){
            if(isset($_GET['buti'])?$_GET['buti']=='true':false) //全榜
                $end=time();
            else //终榜
                $end=strtotime($contest->end_time);
        }else{
            if($contest->lock_rate==0 && isset($_GET['buti'])?$_GET['buti']=='true':false) //没封榜 && 查看全榜
                $end=time();
            else //终榜or封榜
                $end=strtotime($contest->end_time)
                    -( strtotime($contest->end_time)-strtotime($contest->start_time) )*$contest->lock_rate;
        }
        return date('Y-m-d H:i:s',$end);
    }
    private static function get_solutions_rank($contest,$user_id,$pid){
//        rank 的辅助函数, 共用前缀代码，获取solutions
        $solutions=DB::table('solutions')
            ->where('contest_id',$contest->id)
            ->where('user_id',$user_id)
            ->where('problem_id',$pid)
            ->where('submit_time','<',self::get_rank_end_time($contest));
        return $solutions;
    }
    private static function seconds_to_clock($seconds){
        //rank的辅助函数，根据秒数转化为HH:mm:ss
        $clock=floor($seconds/3600);                            $seconds%=3600;
        $clock.=':'.($seconds/60<10?'0':'').floor($seconds/60); $seconds%=60;
        $clock.=':'.($seconds<10?'0':'').$seconds;
        return $clock;
    }
    public function rank($id){

        //查看Cookie是否保存了全屏显示的标记
        if(!isset($_GET['big'])&&Cookie::get('rank_table_lg')!=null) //有cookie
            $_GET['big']=Cookie::get('rank_table_lg');
        else if(isset($_GET['big']))
            Cookie::queue('rank_table_lg',$_GET['big']); //保存榜单是否全屏

        $contest=DB::table('contests')
            ->select(['id','title','description','access','start_time','end_time','lock_rate'])->find($id);

        //获得榜单要显示的用户
        if($contest->access != 'public'){  //私有竞赛or密码竞赛，从表获取
            $users_temp=DB::table('contest_users')
                ->leftJoin('users','users.id','=','user_id')
                ->select(['users.id','username','nick','school'])->distinct()
                ->where('contest_id',$id)->get();
        }else{   //从提交记录获取账号
            $users_temp=DB::table('users')
                ->join('solutions','solutions.user_id','=','users.id')
                ->select(['users.id','username','nick','school'])->distinct()
                ->where('contest_id',$id)
                ->where('submit_time','<',self::get_rank_end_time($contest))->get();
        }

        //获得[index=>题号]
        $index_map=DB::table('contest_problems')->where('contest_id',$id)
            ->orderBy('index')
            ->pluck('problem_id','index');

        //构造榜单表格
        $users=[];
        foreach ($users_temp as $user) {
            $penalty=0; //罚时
            $AC_count=0; //AC数量
            foreach ($index_map as $i=>$pid){     //这是一个格子，即某人某题
                // 获取第一次AC记录
                $firstAC=self::get_solutions_rank($contest,$user->id,$pid)
                    ->where('result',4)
                    ->orderBy('id')
                    ->first(['id','submit_time']);

                //计算AC时间与罚时
                if($firstAC!=null) //已AC, 设置wrong，AC_time
                {
                    $users[$user->id][$i]['wrong']=self::get_solutions_rank($contest,$user->id,$pid)
                        ->whereIn('result',[5,6,7,8,9,10])->where('id','<',$firstAC->id)->count();
                    $AC_count++; //AC数量+1
                    //计算AC时间
                    $users[$user->id][$i]['AC_time']=
                        self::seconds_to_clock(strtotime($firstAC->submit_time)-strtotime($contest->start_time));
                    //AC罚时+额外罚时!
                    $penalty += strtotime($firstAC->submit_time)-strtotime($contest->start_time)
                        + $users[$user->id][$i]['wrong']*config('oj.main.penalty_acm');

                    //标记是不是第一个AC此题
                    if(DB::table('solutions')->where('contest_id',$id)
                            ->where('problem_id',$pid)->where('result',4)
                            ->where('id','<',$firstAC->id)->doesntExist())
                        $users[$user->id][$i]['first']=true;
                }
                else  //没有AC, 设置wrong
                {
                    $users[$user->id][$i]['wrong'] = self::get_solutions_rank($contest,$user->id,$pid)
                        ->whereIn('result', [5, 6, 7, 8, 9, 10])->count(); //获取AC前的错误提交次数
                }
            }

            $users[$user->id]['username']=$user->username;
            $users[$user->id]['school']=$user->school;
            $users[$user->id]['nick']=$user->nick;
            $users[$user->id]['AC']=$AC_count;
            $users[$user->id]['penalty']=$penalty;
        }

        uasort($users,function ($x,$y){  //排序
            if($x['AC']==$y['AC']){
                return $x['penalty']>$y['penalty'];
            }
            return $x['AC']<$y['AC'];
        });

        $rank=1; $last_user=null;
        foreach ($users as &$user){  //填写名次和罚时
            if($last_user!=null && $last_user['AC']==$user['AC'] && $last_user['penalty']==$user['penalty'])
                $user['rank'] = $last_user['rank'];
            $user['rank'] = $rank;
            $user['penalty']=self::seconds_to_clock($user['penalty']);

            $last_user=$user;
            ++$rank;
        }

        $end=strtotime($contest->end_time)
            -( strtotime($contest->end_time)-strtotime($contest->start_time) )*$contest->lock_rate;
        $lock_time=date('Y-m-d H:i:s',$end);
        return view('contest.rank',compact('contest','lock_time','users','index_map'));
    }

    public function cancel_lock($id){
        //管理员取消
        if(Auth::user()->privilege('contest'))
            DB::table('contests')->where('id',$id)->update(['lock_rate'=>0]);
        return back();
    }


    public function statistics($id){

    }

}
