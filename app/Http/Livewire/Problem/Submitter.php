<?php

namespace App\Http\Livewire\Problem;

use App\Http\Helpers\ProblemHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Submitter extends Component
{
    public array $problem;
    public $contest_id = null; // 竞赛编号，若为null则是在题库中
    public $allow_lang = null; // 允许使用的编程语言
    public $samples = []; // 本地测试时快速填入样例
    public $solution_code = null; // 可能请求了库中的代码
    public $solution_lang = null; // 若请求了库中代码，则记住编程语言

    public function mount(array $problem, int $contest_id = null, int $allow_lang = null)
    {
        $this->problem = $problem;
        $this->contest_id = $contest_id;
        $this->allow_lang = $allow_lang;
        //读取样例文件
        $this->samples = ProblemHelper::readSamples($problem['id']);

        // 用户可能请求了已提交的代码
        if (request()->has('solution') && Auth::check()) {
            $solution = DB::table('solutions')->select(['code', 'language', 'user_id'])->find(request('solution'));
            /** @var \App\Models\User */
            $user = Auth::user();
            if ($solution->user_id == Auth::id() || $user->can('admin.solution.view')) {
                $this->solution_code = $solution->code ?? null;
                $this->solution_lang = $solution->language ?? null;
            }
        }
    }

    public function render()
    {
        return view('livewire.problem.submitter');
    }
}
