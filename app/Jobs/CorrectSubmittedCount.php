<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CorrectSubmittedCount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1000; // 最长执行时间

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * 矫正problems,users,contest_problems表的`solved`,`accepted`,`submitted`字段
     * @return void
     */
    public function handle()
    {
        // problems
        $count1 = DB::table('solutions')
            ->select(['problem_id', DB::raw('count(*) as submitted')])
            ->groupBy('problem_id')
            ->get();
        $count2 = DB::table('solutions')
            ->select([
                'problem_id',
                DB::raw('count(*) as accepted'),
                DB::raw('count(distinct `user_id`) as solved'),
            ])
            ->where('result', 4)
            ->groupBy('problem_id')
            ->get();
        $total = [];
        foreach ($count1 as $c) {
            if (!isset($total[$c->problem_id]))
                $total[$c->problem_id] = [];
            $total[$c->problem_id]['submitted'] = $c->submitted;
        }
        foreach ($count2 as $c) {
            if (!isset($total[$c->problem_id]))
                $total[$c->problem_id] = [];
            $total[$c->problem_id]['accepted'] = $c->accepted;
            $total[$c->problem_id]['solved'] = $c->solved;
        }
        foreach ($total as $pid => $c) {
            DB::table('problems')->where('id', $pid)->update([
                'accepted' => $c['accepted'] ?? 0,
                'solved' => $c['solved'] ?? 0,
                'submitted' => $c['submitted'] ?? 0
            ]);
        }
        // users
        $count1 = DB::table('solutions')
            ->select(['user_id', DB::raw('count(*) as submitted')])
            ->groupBy('user_id')
            ->get();
        $count2 = DB::table('solutions')
            ->select([
                'user_id',
                DB::raw('count(*) as accepted'),
                DB::raw('count(distinct `problem_id`) as solved'),
            ])
            ->where('result', 4)
            ->groupBy('user_id')
            ->get();
        $total = [];
        foreach ($count1 as $c) {
            if (!isset($total[$c->user_id]))
                $total[$c->user_id] = [];
            $total[$c->user_id]['submitted'] = $c->submitted;
        }
        foreach ($count2 as $c) {
            if (!isset($total[$c->user_id]))
                $total[$c->user_id] = [];
            $total[$c->user_id]['accepted'] = $c->accepted;
            $total[$c->user_id]['solved'] = $c->solved;
        }
        foreach ($total as $uid => $c) {
            DB::table('users')->where('id', $uid)->update([
                'accepted' => $c['accepted'] ?? 0,
                'solved' => $c['solved'] ?? 0,
                'submitted' => $c['submitted'] ?? 0
            ]);
        }
        // contest_problems
        $count1 = DB::table('solutions')
            ->select(['contest_id', 'problem_id', DB::raw('count(*) as submitted')])
            ->groupBy(['contest_id', 'problem_id'])
            ->get();
        $count2 = DB::table('solutions')
            ->select([
                'contest_id',
                'problem_id',
                DB::raw('count(*) as accepted'),
                DB::raw('count(distinct `user_id`) as solved'),
            ])
            ->where('result', 4)
            ->groupBy(['contest_id', 'problem_id'])
            ->get();
        foreach ($count1 as $c) {
            DB::table('contest_problems')
                ->where('contest_id', $c->contest_id)
                ->where('problem_id', $c->problem_id)
                ->update(['submitted' => $c->submitted]);
        }
        foreach ($count2 as $c) {
            DB::table('contest_problems')
                ->where('contest_id', $c->contest_id)
                ->where('problem_id', $c->problem_id)
                ->update([
                    'accepted' => $c->accepted,
                    'solved' => $c->solved
                ]);
        }
        // Done
    }
}
