<?php

namespace App\View\Components\group\group;

use Illuminate\View\Component;

class navbar extends Component
{
    public $groupId;
    public $groupName;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($groupId, $groupName)
    {
        $this->groupId = $groupId;
        $this->groupName = $groupName;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.group.group.navbar');
    }
}
