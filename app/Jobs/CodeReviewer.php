<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CodeReviewer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $sid;
    /**
     * Create a new job instance.
     */
    public function __construct($sid)
    {
        $this->sid = $sid;
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $solution = DB::table('solutions')->find($this->sid);
        // 读取以前AC的代码，用于对比
        $prev_sids = DB::table('solutions')
            ->where('id', '<', $this->sid)
            ->where('problem_id', $solution->problem_id)
            ->where('result', 4)
            ->where('user_id', '!=', $solution->user_id)
            ->select(['id', 'code'])
            ->orderByDesc('id')
            ->limit(500) // 最多查500份
            ->get()->toArray();

        // 保存当前代码为文件
        $curr_ext = pathinfo(config('judge.language.' . $solution->language)['filename'], PATHINFO_EXTENSION);
        $curr_path = "temp/codes/{$solution->id}.{$curr_ext}";
        if (!Storage::exists($curr_path))
            Storage::put($curr_path, $solution->code . "\n");
        // 根据语言的不同，选择不同的对比工具
        $sim = 'sim_text'; // 默认以文本形式比较
        if ($curr_ext == 'c')
            $sim = 'sim_c';
        else if ($curr_ext == 'cpp')
            $sim = 'sim_c++';
        else if ($curr_ext == 'java')
            $sim = 'sim_java';

        // 逐个代码对比
        [$sim_rate, $sim_sid, $sim_report] = [0, null, []];
        foreach (array_reverse($prev_sids) as $i => $s) {
            $ext = pathinfo(config('judge.language.' . $solution->language)['filename'], PATHINFO_EXTENSION);
            if ($curr_ext != $ext) // 不同语言无需对比
                continue;

            $path = "temp/codes/{$s->id}.{$ext}";
            if (!Storage::exists($path))
                Storage::put($path, $s->code . "\n");

            $res = Process::run(sprintf(
                "%s -p %s %s |grep consists|head -1|awk '{print $4}'",
                $sim,
                Storage::path($curr_path),
                Storage::path($path)
            ))->output();

            // 记录查重报告
            if ($res ?? false) {
                $sim_report[] = [
                    'sim_sid' => $prev_sids[$i]->id,
                    'sim_rate' => (int)$res,
                ];
            }

            // 只记录最大相似度
            if ($sim_rate < ($res ?? 0)) {
                $sim_rate = (int)$res;
                $sim_sid = $prev_sids[$i]->id ?? null;
            }
        }
        if ($sim_sid)
            DB::table('solutions')->where('id', $this->sid)
                ->update(['sim_rate' => $sim_rate, 'sim_sid' => $sim_sid, 'sim_report' => $sim_report]);
    }

    /**
     * 处理失败作业
     */
    public function failed(Throwable $exception): void
    {
        echo $exception;
        echo PHP_EOL;
    }
}
