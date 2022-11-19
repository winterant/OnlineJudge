<?php

namespace App\View\Components\Contest;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class ProblemsLink extends Component
{
    public int $contest_id;
    public int $problem_index;
    public $problems;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($contestId, $problemIndex)
    {
        $this->contest_id = $contestId;
        $this->problem_index = $problemIndex;
        $this->problems = DB::table('contest_problems as cp')
            ->join('problems as p', 'p.id', 'problem_id')
            ->select([
                'cp.index', 'p.title',
                //查询本人是否通过此题；4:Accepted, >4:Attempting, 0:没做
                DB::raw('(select min(result) from solutions where contest_id=' . $contestId . '
                    and problem_id=p.id
                    and user_id=' . Auth::id() . '
                    and result>=4) as result')
            ])
            ->where('contest_id', $contestId)
            ->orderBy('index')
            ->get();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        if (!isset($this->problems[0]))
            return null;
        return view('components.contest.problems-link');
    }
}
