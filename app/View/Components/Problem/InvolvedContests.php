<?php

namespace App\View\Components\Problem;

use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class InvolvedContests extends Component
{
    public $contests; // 使用了该题目的所有竞赛
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($problemId)
    {
        //查询引入这道题的竞赛
        $this->contests = DB::table('contest_problems')
            ->join('contests', 'contests.id', '=', 'contest_id')
            ->select('contests.id', 'title')
            ->distinct()
            ->where('problem_id', $problemId)
            ->get();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        if (!isset($this->contests[0]))
            return null;
        return view('components.problem.involved-contests');
    }
}
