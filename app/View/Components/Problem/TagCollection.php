<?php

namespace App\View\Components\Problem;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class TagCollection extends Component
{
    public int $problem_id;
    public $tags; // 此题已有的标签
    public bool $user_has_ac;
    public $tag_pool = null;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($problemId, $tags)
    {
        $this->problem_id = $problemId;
        $this->tags = $tags;
        // 当前用户是否已AC当前题目
        $this->user_has_ac = Auth::check()
            && !DB::table('tag_marks')
                ->where('user_id', '=', Auth::id())
                ->where('problem_id', '=', $problemId)
                ->exists()
            && DB::table('solutions')
            ->where('user_id', Auth::id())
            ->where('problem_id', $problemId)
            ->where('result', 4)
            ->exists();
        // 如果已经AC，则提供候选的标签
        if ($this->user_has_ac)
            $this->tag_pool = DB::table('tag_pool')
                ->select('id', 'name')
                ->where('hidden', 0)
                ->orderBy('id')
                ->get();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        if(!$this->user_has_ac)
            return null;
        return view('components.problem.tag-collection');
    }
}
