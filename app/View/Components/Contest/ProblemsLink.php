<?php

namespace App\View\Components\Contest;

use App\Http\Helpers\CacheHelper;
use App\Http\Helpers\ProblemHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class ProblemsLink extends Component
{
    public int $contest_id;
    public ?int $group_id;
    public ?int $problem_index;
    public $problems;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(int $contestId, ?int $userId = null, ?int $groupId = null, ?int $problemIndex = null)
    {
        $this->contest_id = $contestId;
        $userId = $userId ?? Auth::id(); // 默认为自己
        $this->group_id = $groupId;
        $this->problem_index = $problemIndex;
        $this->problems = DB::table('contest_problems as cp')
            ->join('problems as p', 'p.id', 'cp.problem_id')
            ->select([
                'p.id', 'p.type', 'p.title',
                'cp.index',
                'cp.accepted', 'cp.solved', 'cp.submitted',
            ])
            ->where('contest_id', $contestId)
            ->orderBy('cp.index')
            ->get();

        foreach ($this->problems as &$item) {
            // 当前用户在本题的提交结果。null,0，1，2，3都视为没做； 4视为Accepted；其余视为答案错误（尝试中）
            $item->result = ProblemHelper::getUserResult($item->id, $contestId);
        }
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
