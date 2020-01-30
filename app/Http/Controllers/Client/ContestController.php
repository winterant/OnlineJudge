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

    }

    public function status($id){

    }

    public function rank($id){

    }

    public function statistics($id){

    }

}
