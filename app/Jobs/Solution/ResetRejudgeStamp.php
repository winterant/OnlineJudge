<?php

namespace App\Jobs\Solution;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ResetRejudgeStamp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 5;
    public $tries = 3;
    public $backoff=5;

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
     *
     * @return void
     */
    public function handle()
    {
        // 重新生成 solution stamp；请保持key名与App\Http\CacheHelper::has_key_with_autoclear_if_rejudged()中一致
        Cache::put('solution:rejudged_datetime', date('Y-m-d H:i:s'));
    }
}
