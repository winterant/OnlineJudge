<?php

namespace App\View\Components\Contest;

use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class Info extends Component
{
    public $contest, $groups, $num_problems;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($contest)
    {
        $this->contest = $contest;
        // 读取当前竞赛所在的群组（多个）
        $this->groups = DB::table('group_contests as gc')
            ->join('groups as g', 'g.id', '=', 'gc.group_id')
            ->select(['g.id', 'g.name'])
            ->where('gc.contest_id', $contest->id)
            ->get();
        // 题目个数
        $this->num_problems = DB::table('problems as p')
            ->join('contest_problems as cp', 'cp.problem_id', '=', 'p.id')
            ->where('contest_id', $contest->id)
            ->count();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.contest.info');
    }
}
