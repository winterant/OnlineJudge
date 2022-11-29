<?php

namespace App\View\Components\Solution;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class LineChart extends Component
{
    public array $x, $submitted, $accepted, $solved;

    public function __construct($userId = null, $contestId = null, $groupId = null, $defaultPast = '30d', $endTime = null)
    {
        // 获取参数
        $past = $_GET['past'] ?? $defaultPast; // 无参则取默认值
        if (!in_array($past, ['300i', '24h', '30d', '180d', '12m']))
            $past = $defaultPast; // 无效的past改为默认值
        $_GET['past'] = $past; // 前端还要用到该值

        // 结束时间
        $is_now = false; // 用于区分是否是实时的折线图（竞赛榜单中有可能是历史折线图）
        if ($endTime == null) {
            $endTime = time();
            $is_now = true;
        }

        // 声明时间规则
        $rules = [
            'i' => [
                'current' =>  $endTime - $endTime % 60,
                'format' => 'Y-m-d H:i',
                'unit' => 'minute'
            ],
            'h' => [
                'current' =>  $endTime - $endTime % 3600,
                'format' => 'Y-m-d H',
                'unit' => 'hour'
            ],
            'd' => [
                'current' => mktime(0, 0, 0, date('m', $endTime), date('d', $endTime), date('Y', $endTime)),
                'format' => 'Y-m-d',
                'unit' => 'day'
            ],
            'm' => [
                'current' => mktime(0, 0, 0, date('m'), 1, date('Y')), // 本月1号时间戳
                'format' => 'Y-m',
                'unit' => 'month'
            ],
        ];

        // 获取时长和规则
        $num = intval(substr($past, 0, strlen($past) - 1)); // 拿到数字
        $rule = $rules[$past[strlen($past) - 1]]; // 拿到单位所对应的规则

        // 遍历时间阶段，各个时间段分别缓存，提高响应速度
        $start_ts = strtotime(sprintf('-%d %s', $num, $rule['unit']), $rule['current']);
        for ($ts = $start_ts; $ts <= $endTime;) {
            $next_ts = strtotime(sprintf('+1 %s', $rule['unit']), $ts);
            // 缓存历史结果；注意，若发生重判，重判后必须清空这些缓存
            $counts = Cache::remember(
                sprintf('solution:line-chart:%s,%s,%s,%s,%s', $userId, $contestId, $groupId, $past, date(str_replace(' ', '_', $rule['format']), $ts)),
                $next_ts <= $endTime ? ($is_now ? $next_ts - $start_ts : 3600 * 24 * 30) : 15, // 已度过的阶段长期缓存，当前阶段缓存15秒
                function () use ($userId, $contestId, $groupId, $ts, $next_ts) {
                    return DB::table('solutions')
                        ->select([
                            DB::raw('count(*) as submitted'),
                            DB::raw('count(result=4 or null) as accepted'),
                            DB::raw('count(distinct (problem_id * 10 + (result=4 or null))) as solved'),
                        ])
                        ->when($userId !== null, function ($q) use ($userId) {
                            return $q->where('user_id', $userId);
                        })
                        ->when($contestId !== null, function ($q) use ($contestId) {
                            return $q->where('contest_id', $contestId);
                        })
                        ->when($groupId !== null, function ($q) use ($groupId) {
                            return $q->join('group_contests as gc', 'gc.contest_id', 'solutions.contest_id')
                                ->where('group_id', $groupId);
                        })
                        ->whereBetween('submit_time', [date('Y-m-d H:i:s', $ts), date('Y-m-d H:i:s', $next_ts)])
                        ->first();
                }
            );
            $this->x[] = date($rule['format'], $ts);
            $this->submitted[] = $counts->submitted;
            $this->accepted[] = $counts->accepted;
            $this->solved[] = $counts->solved;
            // 进入下一阶段
            $ts = $next_ts;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.solution.line-chart');
    }
}
