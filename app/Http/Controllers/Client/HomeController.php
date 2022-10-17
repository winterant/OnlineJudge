<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function test(Request $request)
    {
        return 'test api';
    }

    public function home()
    {
        $notices = DB::table('notices')
            ->leftJoin('users', 'users.id', '=', 'user_id')
            ->select(['notices.id', 'title', 'state', 'notices.created_at', 'username'])
            ->where('state', '>', 0)
            ->orderByDesc('state')
            ->orderByDesc('id')->paginate(6);

        // 获取周一时间. 如果今天是周一则为今天，否则为最近一次周一
        $monday_time = (date('w') == 1 ? strtotime('today') : strtotime('last monday'));
        $last_monday_time = $monday_time - 3600 * 24 * 7;
        $next_monday_time = $monday_time + 3600 * 24 * 7;
        if (Redis::exists('home:cache:this_week_top10')) {
            $this_week = json_decode(Redis::get('home:cache:this_week_top10'));
        } else {
            $this_week = DB::table('solutions')
                ->join('users', 'users.id', '=', 'solutions.user_id')
                ->select(['user_id', 'username', 'school', 'class', 'nick', DB::raw('count(distinct problem_id) as solved'),])
                ->where('submit_time', '>', date('Y-m-d H:i:s', $monday_time))
                ->where('result', 4)
                ->groupBy(['user_id'])
                ->orderByDesc('solved')
                ->limit(10)->get();
            // 缓存有效期1小时
            Redis::setex('home:cache:this_week_top10', 3600, json_encode($this_week));
        }

        if (Redis::exists('home:cache:last_week_top10')) {
            $last_week = json_decode(Redis::get('home:cache:last_week_top10'));
        } else {
            $last_week = DB::table('solutions')
                ->join('users', 'users.id', '=', 'solutions.user_id')
                ->select(['user_id', 'username', 'school', 'class', 'nick', DB::raw('count(distinct problem_id) as solved')])
                ->where('submit_time', '>', date('Y-m-d H:i:s', $last_monday_time))
                ->where('submit_time', '<', date('Y-m-d H:i:s', $monday_time))
                ->where('result', 4)
                // ->whereRaw("(select count(*) from privileges P where solutions.user_id=P.user_id and authority='admin')=0")
                ->groupBy(['user_id'])
                ->orderByDesc('solved')
                ->limit(10)->get();
            // 缓存有效期至下周一
            Redis::setex('home:cache:last_week_top10', $next_monday_time - time(), json_encode($last_week));
        }
        return view('client.home', compact('notices', 'this_week', 'last_week'));
    }
}
