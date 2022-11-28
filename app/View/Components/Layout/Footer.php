<?php

namespace App\View\Components\Layout;

use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class Footer extends Component
{
    public ?string $footer_info, $web_version;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->footer_info = get_setting('footer_info');
        $this->web_version = Cache::remember('web:version', 3600 * 24 * 30, function () {
            if (file_exists(base_path('install/.version'))) {
                $f = fopen(base_path('install/.version'), 'r');
                return fgets($f);
            }
        });
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.layout.footer');
    }
}
