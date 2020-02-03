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
        $contests=DB::table('contests')->orderByDesc('start_time')->paginate(10);
        return view('contest.contests',compact('contests'));
    }

    public function home($id){
        $contest=DB::table('contests')->find($id);
        $problems=DB::table('problems')
            ->join('contest_problems','contest_problems.problem_id','=','problems.id')
            ->where('contest_id',$id)
            ->select(['problems.id','problems.title','contest_problems.index','contest_problems.solved','contest_problems.submit'])
            ->orderBy('contest_problems.index')
            ->get();
        return view('contest.home',compact('contest','problems'));
    }

    public function problem($id,$pid){
        $contest=DB::table('contests')->find($id);
        $problem=DB::table('problems')
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
            ->where('contest_problems.contest_id',$id);
        if(isset($_GET['index'])&&$_GET['index']!='')
            $solutions=$solutions->where('index',$_GET['index']);
        if(isset($_GET['username'])&&$_GET['username']!='')
            $solutions=$solutions->where('username',$_GET['username']);
        if(isset($_GET['result'])&&$_GET['result']!=-1)
            $solutions=$solutions->where('result',$_GET['result']);
        $solutions=$solutions->orderByDesc('solutions.id')
            ->paginate(10);

        return view('contest.status',compact('contest','solutions'));
    }




    private static function get_solutions_rank($contest,$user_id,$pid){
//        rank 的辅助函数, 前缀共用代码，获取solutions
        $solutions=DB::table('solutions')
            ->where('contest_id',$contest->id)
            ->where('user_id',$user_id)
            ->where('problem_id',$pid); //默认包括补题
        if(!Auth::user()->is_admin()&&$contest->lock_rate>0
            || !(isset($_GET['buti'])?$_GET['buti']=='true':0) ) //非管理员严格封榜 || 不是补题榜单，则截止到封榜就行
        {
            $end=strtotime($contest->end_time)
                -( strtotime($contest->end_time)-strtotime($contest->start_time) )*$contest->lock_rate;
            $solutions=$solutions->where('submit_time','<',date('Y-m-d H:i:s',$end));
        }
        return $solutions;
    }
    private static function seconds_to_clock_rank($seconds){
        //rank的辅助函数，根据秒数转化为HH:mm:ss
        $clock=floor($seconds/3600);                            $seconds%=3600;
        $clock.=':'.($seconds/60<10?'0':'').floor($seconds/60); $seconds%=60;
        $clock.=':'.($seconds<10?'0':'').$seconds;
        return $clock;
    }
    public function rank($id){
        if(!isset($_GET['big'])&&Cookie::get('rank_table_lg')!=null) //有cookie
            $_GET['big']=Cookie::get('rank_table_lg');
        else if(isset($_GET['big']))
            Cookie::queue('rank_table_lg',$_GET['big']); //保存榜单是否全屏

        $contest=DB::table('contests')
            ->select(['id','title','description','access','start_time','end_time','lock_rate'])->find($id);

        //获得用户id
        if($contest->access == 'private'){
            //私有竞赛
            $user_ids=DB::table('contest_users')
                ->join('users','users.id','=','user_id')
                ->select(['users.id','username','nick'])
                ->distinct()
                ->where('contest_id',$id)
                ->get();
        }else{
            //从提交记录取得账号
            $user_ids=DB::table('users')
                ->join('solutions','solutions.user_id','=','users.id')
                ->select(['users.id','username','nick'])
                ->distinct()
                ->where('contest_id',$id);
            if(!Auth::user()->is_admin()&&$contest->lock_rate>0
                || !(isset($_GET['buti'])?$_GET['buti']=='true':0) ) //非管理员严格封榜 || 不是补题榜单，则截止到封榜就行
            {
                $end=strtotime($contest->end_time)
                    -( strtotime($contest->end_time)-strtotime($contest->start_time) )*$contest->lock_rate;
                $user_ids=$user_ids->where('submit_time','<',date('Y-m-d H:i:s',$end));
            }
            $user_ids=$user_ids->get();
        }

        //获得[index=>题号]
        $indexs=DB::table('contest_problems')->where('contest_id',$id)
            ->orderBy('index')
            ->pluck('problem_id','index');

        //构造榜单表格
        $users=[];
        foreach ($user_ids as $user) {
            $penalty=0;
            $AC_count=0;
            foreach ($indexs as $i=>$pid){
                //这是一个格子，即某人某题

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
                        self::seconds_to_clock_rank(strtotime($firstAC->submit_time)-strtotime($contest->start_time));
                    //AC罚时+额外罚时!
                    $penalty += strtotime($firstAC->submit_time)-strtotime($contest->start_time)
                        + $users[$user->id][$i]['wrong']*config('oj.main.penalty_acm');
                }
                else  //没有AC, 设置wrong
                {
                    $users[$user->id][$i]['wrong'] = self::get_solutions_rank($contest,$user->id,$pid)
                        ->whereIn('result', [5, 6, 7, 8, 9, 10])->count(); //获取AC前的错误提交次数
                }
            }

            $users[$user->id]['rank']=1;
            $users[$user->id]['username']=$user->username;
            $users[$user->id]['nick']=$user->nick;
            $users[$user->id]['AC']=$AC_count;
            $users[$user->id]['penalty']=$penalty;
        }

        uasort($users,function ($x,$y){
            if($x['AC']==$y['AC']){
                return $x['penalty']>$y['penalty'];
            }
            return $x['AC']<$y['AC'];
        });

        $rank=1; $last_user=null;
        foreach ($users as &$user){
            $user['penalty']=self::seconds_to_clock_rank($user['penalty']);
            if($last_user!=null && $last_user['AC']==$user['AC'] && $last_user['penalty']==$user['penalty'])
                $user['rank'] = $last_user['rank'];
            $user['rank'] = $rank;

            $last_user=$user;
            ++$rank;
        }
        return view('contest.rank',compact('contest','indexs','users'));
    }

    public function statistics($id){

    }

}
