<?php

namespace App\View\Components\Problem;

use Illuminate\View\Component;


class Disscussions extends Component
{
    public int $problem_id;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($problemId)
    {
        $this->problem_id = $problemId;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        if (!get_setting('show_disscussions'))
            return null;
        return view('components.problem.disscussions');
    }
}
