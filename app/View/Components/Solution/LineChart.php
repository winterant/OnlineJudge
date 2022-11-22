<?php

namespace App\View\Components\Solution;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class LineChart extends Component
{
    public string $dom_id;
    public $x, $submitted, $accepted, $solved;

    public function __construct($defaultPast = '30d', $userId = null, $contestId = null, $groupId = null)
    {
        $this->dom_id = Str::random(64);

        // 筛选的起始时间
        $sub_sql = [
            '300i' => [
                'column' => DB::raw("DATE_FORMAT(`submit_time`, '%Y-%m-%d %H:%i:00') AS dt"),
                'start_time' => date("Y-m-d H:i:00", strtotime("-300 minute"))
            ],
            '24h' => [
                'column' => DB::raw("DATE_FORMAT(`submit_time`, '%Y-%m-%d %H:00') AS dt"),
                'start_time' => date("Y-m-d H:00:00", strtotime("-24 hour"))
            ],
            '30d' => [
                'column' => DB::raw("DATE_FORMAT(`submit_time`, '%Y-%m-%d') AS dt"),
                'start_time' => date("Y-m-d 00:00:00", strtotime("-30 day"))
            ],
            '180d' => [
                'column' => DB::raw("DATE_FORMAT(`submit_time`, '%Y-%m-%d') AS dt"),
                'start_time' => date("Y-m-d 00:00:00", strtotime("-30 day"))
            ],
            '12m' => [
                'column' => DB::raw("DATE_FORMAT(`submit_time`, '%Y-%m') AS dt"),
                'start_time' => date("Y-m-d 00:00:00", strtotime("-12 month"))
            ],
        ];
        if (!isset($_GET['past']))
            $_GET['past'] = $defaultPast;
        $option = $sub_sql[$_GET['past']];

        // 查询数据库
        $solutions = Cache::remember(
            sprintf('solution:line_chart:%s,%s,%s,%s', $_GET['past'], $userId, $contestId, $groupId),
            30, // 缓存30秒
            function () use ($userId, $contestId, $groupId, $option) {
                return DB::table('solutions as s')
                    ->select([
                        DB::raw('count(*) as submitted'),
                        DB::raw('count(result=4 or null) as accepted'),
                        DB::raw('count(distinct ((result=4 or null) * 10 + problem_id)) as solved'),
                        $option['column']
                    ])
                    ->when($userId !== null, function ($q) use ($userId) {
                        return $q->where('user_id', $userId);
                    })
                    ->when($contestId !== null, function ($q) use ($contestId) {
                        return $q->where('contest_id', $contestId);
                    })
                    ->when($groupId !== null, function ($q) use ($groupId) {
                        return $q->join('group_contests as gc', 'gc.contest_id', 's.contest_id')
                            ->where('group_id', $groupId);
                    })
                    ->where('submit_time', '>', $option['start_time'])
                    ->groupBy('dt')
                    ->get()->toArray();
            }
        );

        // 汇总数据
        $this->x = array_map(function ($v) {
            return $v->dt;
        }, $solutions);
        $this->submitted = array_map(function ($v) {
            return $v->submitted;
        }, $solutions);
        $this->accepted = array_map(function ($v) {
            return $v->accepted;
        }, $solutions);
        $this->solved = array_map(function ($v) {
            return $v->solved;
        }, $solutions);
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
