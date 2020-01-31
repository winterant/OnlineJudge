<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContestController extends Controller
{
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
        $solutions=DB::table('solutions')
            ->join('users','solutions.user_id','=','users.id')
            ->join('contest_problems','solutions.problem_id','=','contest_problems.problem_id')
            ->select(['solutions.id','index','username','result','time','memory','language','submit_time'])
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

        $contest=DB::table('contests')->find($id);
        return view('contest.status',compact('contest','solutions'));
    }

    public function rank($id){

    }

    public function statistics($id){

    }

}
