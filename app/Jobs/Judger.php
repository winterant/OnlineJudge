<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Judger implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60; // 最长执行时间30秒
    public $tries = 3;    // 最多尝试3次

    private $solution;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($solution)
    {
        $this->onQueue('sending_solution');
        $this->solution = $solution;
    }

    /**
     * Execute the job.
     * 根据solution id，发起判题请求
     * 随后轮询judge0，更新判题结果
     *
     * @return void
     */
    public function handle()
    {
        // 1. 更新solution结果为Queueing
        // todo

        // 2. 获取提交记录的相关属性
        $solution = DB::table('solutions')
            ->select(['contest_id', 'problem_id', 'user_id', 'language', 'code'])
            ->find($this->solution_id);
        $problem = DB::table('problems')
            ->select(['time_limit', 'memory_limit', 'spj'])
            ->find($solution->problem_id);

        // 3. 向judge server发送判题请求
        // todo
        // Http::post(null, $solution);

        // 4. 启动结果查询任务（如果判题机有回调url则无需此步）
        // todo
    }
}
