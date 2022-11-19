<?php

namespace App\View\Components;

use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class Marquee extends Component
{
    public $notice;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->notice = DB::table('notices')
            ->select(['id', 'title'])
            ->find(get_setting('marquee_notice_id'));
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.marquee');
    }
}
