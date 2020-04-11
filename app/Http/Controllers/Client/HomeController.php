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
        $this_week=DB::table('solutions')
            ->join('users','users.id','=','solutions.user_id')
            ->select(['user_id','username','school','class','nick',DB::raw('count(distinct problem_id) as solved'),])
            ->where('submit_time','>',date('Y-m-d 00:00:00',time()-3600*24*$day))
            ->where('result',4)
            ->whereRaw("(select count(*) from privileges P where solutions.user_id=P.user_id and authority='admin')=0")
            ->groupBy(['user_id'])
            ->orderByDesc('solved')
            ->limit(10)->get();
        $last_week=DB::table('solutions')
            ->join('users','users.id','=','solutions.user_id')
            ->select(['user_id','username','school','class','nick',DB::raw('count(distinct problem_id) as solved')])
            ->where('submit_time','>',date('Y-m-d 00:00:00',time()-3600*24*($day+7)))
            ->where('submit_time','<',date('Y-m-d 00:00:00',time()-3600*24*$day))
            ->where('result',4)
            ->whereRaw("(select count(*) from privileges P where solutions.user_id=P.user_id and authority='admin')=0")
            ->groupBy(['user_id'])
            ->orderByDesc('solved')
            ->limit(10)->get();
        return view('client.home',compact('notices','this_week','last_week'));
    }

    public function get_notice(Request $request){
        $notice=DB::table('notices')->select(['title','content','created_at'])->find($request->input('id'));
        if ($notice->content==null)
            $notice->content='';
        return json_encode($notice);
    }
}
