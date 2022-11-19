<?php

namespace App\View\Components\Contest;

use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class Navbar extends Component
{
    public $contest, $group = null;
    public $category = null, $father_category = null;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($contest, $groupId = null)
    {
        $this->contest = $contest;
        // 指定要显示的群组
        $this->group = DB::table('groups')->find($groupId); //不存在则为null
        // 如果群组不存在，则显示竞赛类别
        if (!$this->group) {
            $this->category = DB::table('contest_cate')->find($contest->cate_id);
            if (!$this->category)
                $this->father_category = DB::table('contest_cate')->find($this->category->parent_id ?? -1);
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.contest.navbar');
    }
}
