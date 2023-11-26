<?php

namespace App\Jobs\Solution;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CorrectSolutionsStatistics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 最长执行时间10分钟
    public $tries = 3;     // 最多尝试3次
    public $backoff = [3, 10, 60]; // 自定义重试间隔时间（以秒为单位）


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onQueue('default');
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
        // `contests`.`num_members`
        $num_members = DB::table('solutions')
            ->select(['contest_id', DB::raw('count(distinct `user_id`) as num_members')])
            ->where('contest_id', '>', 0)
            ->groupBy(['contest_id'])
            ->get()->toArray();
        foreach ($num_members as $item) {
            DB::table('contests')
                ->where('id', $item->contest_id)
                ->update(['num_members' => $item->num_members]);
        }
        // Done
    }

    /**
     * 处理失败作业
     */
    public function failed(Throwable $exception): void
    {
        // 向用户发送失败通知等...
        Log::error($exception);
    }
}
