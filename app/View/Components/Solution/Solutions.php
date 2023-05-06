<?php

namespace App\View\Components\Solution;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class Solutions extends Component
{
    public \Illuminate\Support\Collection $solutions;
    public ?int $contest_id, $group_id;
    public int $num_problems = 0;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($contestId = null, $groupId = null)
    {
        assert($groupId == null || $contestId == null); // 不可能同时有值！

        $this->contest_id = $contestId;
        $this->group_id = $groupId;

        /** @var \App\Models\User */
        $user = Auth::user();

        // cookie记下默认每页显示的条数
        $_GET['perPage'] = min(request('perPage') ?? 10, 100); // 防止传参>100导致服务器压力大
        if (request()->has('perPage')) {
            Cookie::queue('unencrypted_solutions_default_perpage', request('perPage'), 5256000); // 10 years
        } else {
            $_GET['perPage'] = (request()->cookie('unencrypted_solutions_default_perpage') ?? 10);
        }

        // ====================== 读取提交记录 ========================
        $this->solutions = DB::table('solutions as s')
            // 获取用户名及其昵称
            ->join('users as u', 'u.id', '=', 's.user_id')
            // 对于普通用户，只允许查看：非竞赛/已结束竞赛/自己的提交
            ->when(!$user || !$user->canAny(['admin.solution.view', 'admin.contest.view']), function ($q) {
                $q->leftJoin('contests as c', 'c.id', '=', 's.contest_id')
                    ->where(function ($sq) {
                        $sq->where('c.end_time', '<', date('Y-m-d H:i:s')) // 已结束竞赛
                            ->orWhereNull('c.end_time') // 非竞赛（普通题库提交的）
                            ->orWhere('s.user_id', Auth::id()); // 当前用户本人提交的
                    });
            })
            // 限定到具体的某群组
            ->when($groupId != null, function ($q) use ($groupId) {
                $q->join('group_contests as gc', 'gc.contest_id', '=', 's.contest_id')
                    ->where('gc.group_id', $groupId); // 限定群组
            })
            // 限定到具体的某竞赛
            ->when($contestId != null, function ($q) use ($contestId) {
                $q->where('s.contest_id', $contestId); // 限定竞赛
            })
            // 获取题目在竞赛中的字母题号
            ->leftJoin('contest_problems as cp', function ($q) {
                $q->on('cp.contest_id', '=', 's.contest_id')
                    ->on('cp.problem_id', '=', 's.problem_id');
            })
            ->select([
                's.user_id', 'u.username', 'u.nick', // 用户信息
                's.id', 's.contest_id', 's.problem_id', 'cp.index',
                's.language', 's.submit_time',
                's.ip', 's.ip_loc',
                'result', 'time', 'memory', 'pass_rate', 'judger', 'sim_rate', 'sim_sid',
            ])
            // 以下是各个查询条件
            ->when(request()->has('pid') && request('pid') != null, function ($q) {
                $q->where('s.problem_id', request('pid')); // 限定实际题号
            })
            ->when(request()->has('index') && request('index') != null, function ($q) {
                $q->where('cp.index', request('index')); // 限定竞赛中的题号
            })
            ->when(request()->has('sid') && request('sid') != null, function ($q) {
                $q->where('s.id', request('sid')); // 限定提交编号
            })
            ->when(intval(request('sim_rate') ?? 0) > 0, function ($q) {
                $q->where('sim_rate', '>=', request('sim_rate')); // 查重率 0~100
            })
            ->when(request()->has('username') && request('username') != null, function ($q) {
                $q->where('username', 'like', '%' . request('username') . '%'); // 用户名
            })
            ->when(request()->has('result') && request('result') >= 0, function ($q) {
                $q->where('result', request('result')); // 判题结果
            })
            ->when(request()->has('language') && request('language') >= 0, function ($q) {
                $q->where('language', request('language')); // 编程语言
            })
            ->when(request()->has('ip') && request('ip') != null, function ($q) {
                $q->where('ip', request('ip'));
            })
            // 翻页相关
            ->when(request()->has('top_id'), function ($q) {
                $q->where('s.id', '<=', request('top_id'));
            })
            ->when(request()->has('bottom_id'), function ($q) {
                $q->where('s.id', '>=', request('bottom_id'));
            })
            ->orderBy('s.id', request()->has('bottom_id') ? 'asc' : 'desc')
            ->limit(request('perPage') ?? 10)
            ->get();
        if (request()->has('bottom_id'))
            $this->solutions = $this->solutions->reverse();

        // ====================== 竞赛特别处理 ===================
        if ($contestId) {
            // 计算当前竞赛中的题目数量
            $this->num_problems = DB::table('contest_problems')
                ->where('contest_id', $contestId)->count();
        }

        // ===================== 处理显示信息 =====================
        if ($user == null || !$user->can('admin.solution.view')) {
            // 非管理员，抹掉重要信息
            foreach ($this->solutions as $s) {
                // $s->nick = null;
                $s->ip = '-';
                $s->ip_loc = '';
            }
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.solution.solutions');
    }
}
