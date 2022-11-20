<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
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

        $this_week = Cache::remember('home:cache:this_week_top10', 3600, function () use ($monday_time) {
            $this_week = DB::table('solutions')
                ->join('users', 'users.id', '=', 'solutions.user_id')
                ->select(['user_id', 'username', 'school', 'class', 'nick', DB::raw('count(distinct problem_id) as solved'),])
                ->where('submit_time', '>', date('Y-m-d H:i:s', $monday_time))
                ->where('result', 4)
                ->groupBy(['user_id'])
                ->orderByDesc('solved')
                ->limit(10)->get();
            return $this_week; // 缓存有效期1小时
        });

        $last_week = Cache::remember(
            'home:cache:last_week_top10',
            $next_monday_time - time(),
            function () use ($monday_time, $last_monday_time) {
                $last_week = DB::table('solutions')
                    ->join('users', 'users.id', '=', 'solutions.user_id')
                    ->select(['user_id', 'username', 'school', 'class', 'nick', DB::raw('count(distinct problem_id) as solved')])
                    ->where('submit_time', '>', date('Y-m-d H:i:s', $last_monday_time))
                    ->where('submit_time', '<', date('Y-m-d H:i:s', $monday_time))
                    ->where('result', 4)
                    ->groupBy(['user_id'])
                    ->orderByDesc('solved')
                    ->limit(10)->get();
                return $last_week; // 缓存有效至周日晚24:00
            }
        );

        return view('layouts.home', compact('notices', 'this_week', 'last_week'));
    }
}
