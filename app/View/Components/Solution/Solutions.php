<?php

namespace App\View\Components\Solution;

use Illuminate\Support\Facades\Auth;
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

        // ====================== 读取提交记录 ========================
        $this->solutions = DB::table('solutions as s')
            // 获取用户名及其昵称
            ->join('users as u', 'u.id', '=', 's.user_id')
            // 对于普通用户，只允许查看：非竞赛/已结束竞赛/自己的提交
            ->when(!$user->canAny(['admin.solution.view', 'admin.contest.view']), function ($q) {
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
            ->when(isset($_GET['pid']) && $_GET['pid'] != null, function ($q) {
                $q->where('s.problem_id', $_GET['pid']); // 限定实际题号
            })
            ->when(isset($_GET['index']) && $_GET['index'] != null, function ($q) {
                $q->where('cp.index', $_GET['index']); // 限定竞赛中的题号
            })
            ->when(isset($_GET['sid']) && $_GET['sid'] != null, function ($q) {
                $q->where('s.id', $_GET['sid']); // 限定提交编号
            })
            ->when(intval($_GET['sim_rate'] ?? 0) > 0, function ($q) {
                $q->where('sim_rate', '>=', $_GET['sim_rate']); // 查重率 0~100
            })
            ->when(isset($_GET['username']) && $_GET['username'] != null, function ($q) {
                $q->where('username', 'like', $_GET['username'] . '%'); // 用户名
            })
            ->when(isset($_GET['result']) && $_GET['result'] >= 0, function ($q) {
                $q->where('result', $_GET['result']); // 判题结果
            })
            ->when(isset($_GET['language']) && $_GET['language'] >= 0, function ($q) {
                $q->where('language', $_GET['language']); // 编程语言
            })
            ->when(isset($_GET['ip']) && $_GET['ip'] != null, function ($q) {
                $q->where('ip', $_GET['ip']);
            })
            // 翻页相关
            ->when(isset($_GET['top_id']), function ($q) {
                $q->where('s.id', '<=', $_GET['top_id']);
            })
            ->when(isset($_GET['bottom_id']), function ($q) {
                $q->where('s.id', '>=', $_GET['bottom_id']);
            })
            ->orderBy('s.id', isset($_GET['bottom_id']) ? 'asc' : 'desc')
            ->limit(10)
            ->get();
        if (isset($_GET['bottom_id']))
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
