<?php

namespace App\View\Components\Group;

use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class Info extends Component
{
    public $group;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($groupId)
    {
        $this->group = DB::table('groups as g')
            ->leftJoin('users as u', 'u.id', '=', 'g.creator')
            ->select(['g.type', 'g.grade', 'g.major', 'g.class', 'u.username as creator_username'])
            ->where('g.id', $groupId)
            ->first();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.group.info');
    }
}
