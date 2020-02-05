<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    //home page
    public function index(){
        $day=(date("w")+6)%7; //昨天是周几，周日=0
        $this_week=DB::table('solutions')->join('users','users.id','=','solutions.user_id')
            ->where('submit_time','>',date('Y-m-d 00:00:00',time()-3600*24*$day))
            ->where('result',4)
            ->select(['user_id','username','school','class','nick',DB::raw('count(problem_id) as solved')])
            ->groupBy(['user_id'])
            ->orderByDesc('solved')
            ->limit(10)->get();
        $last_week=DB::table('solutions')->join('users','users.id','=','solutions.user_id')
            ->where('submit_time','>',date('Y-m-d 00:00:00',time()-3600*24*($day+7)))
            ->where('submit_time','<',date('Y-m-d 00:00:00',time()-3600*24*$day))
            ->where('result',4)
            ->select(['user_id','username','school','class','nick',DB::raw('count(problem_id) as solved')])
            ->groupBy(['user_id'])
            ->orderByDesc('solved')
            ->limit(10)->get();
        return view('client.home',compact('this_week','last_week'));
    }
}
