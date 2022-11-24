<?php

namespace App\View\Components\Problem;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class CodeEditor extends Component
{
    public $problem;
    public $contest_id = null; // 竞赛编号，若为null则是在题库中
    public $allow_lang = null; // 允许使用的编程语言
    public $num_samples = 0; // 记下样例个数，本地测试时根据样例序号快速填入样例
    public $solution_code = null; // 可能请求了库中的代码
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($problem, $contestId = null, $allowLang = null, $numSamples = 0)
    {
        $this->problem = $problem;
        $this->contest_id = $contestId;
        $this->allow_lang = $allowLang;
        $this->num_samples = $numSamples;

        // 用户可能请求了已提交的代码
        if (isset($_GET['solution']) && Auth::check()) {
            $solution = DB::table('solutions')->select(['code', 'user_id'])->find($_GET['solution']);
            if ($solution->user_id == Auth::id() || privilege('admin.problem.solution'))
                $this->solution_code = $solution->code ?? null;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.problem.code-editor');
    }
}
