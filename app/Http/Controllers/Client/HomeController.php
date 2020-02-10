<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    //home page
    public function index(){
        $notices=DB::table('notices')
            ->where('state','!=',0)
            ->orderByDesc('state')
            ->orderByDesc('id')->paginate(6);

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
        return view('client.home',compact('notices','this_week','last_week'));
    }

    public function get_notice(Request $request){
        $notice=DB::table('notices')->select(['title','content'])->find($request->input('id'));
        return json_encode($notice);
    }
}
