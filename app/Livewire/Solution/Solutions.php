<?php

namespace App\Livewire\Solution;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Solutions extends Component
{
    public $solutions;
    public ?int $contest_id = null, $group_id = null;
    public int $num_problems = 0;

    public int $num_judging = 0;

    // query strings
    public int $index = -1; // 题目在竞赛中的序号
    public ?string $pid = null; // 题目真实编号
    public ?string $username = null;
    public int $result = -1; // 判题结果
    public int $sim_rate = 0;
    public int $language = -1;
    public ?string $ip = null;
    public ?int $perPage = null;
    // 翻页
    public ?int $top_id = null;
    public ?int $bottom_id = null;

    protected $queryString = [
        'index' => ['except' => -1],
        'pid'   => ['except' => ''],
        'username' => ['except' => ''],
        'result' => ['except' => -1],
        'sim_rate' => ['except' => 0],
        'language' => ['except' => -1],
        'ip' => ['except' => ''],
        'perPage' => ['except' => 10],
        'top_id' => ['except' => ''],
        'bottom_id' => ['except' => ''],
    ];

    public function mount($contestId = null, $groupId = null)
    {
        assert($groupId == null || $contestId == null); // 不可能同时有值！

        $this->contest_id = $contestId;
        $this->group_id = $groupId;
    }

    public function refresh()
    {

        /** @var \App\Models\User */
        $user = Auth::user();

        // cookie记下默认每页显示的条数
        if ($this->perPage == null) {
            $this->perPage = (request()->cookie('unencrypted_solutions_default_perpage') ?? 10); // 从cookie读取
        } else {
            $perPage = min($this->perPage, 100); // 防止传参>100导致服务器压力大
            Cookie::queue('unencrypted_solutions_default_perpage', $perPage, 525600); // 1 years
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
            ->when($this->group_id != null, function ($q) {
                $q->join('group_contests as gc', 'gc.contest_id', '=', 's.contest_id')
                    ->where('gc.group_id', $this->group_id); // 限定群组
            })
            // 限定到具体的某竞赛
            ->when($this->contest_id != null, function ($q) {
                $q->where('s.contest_id', $this->contest_id); // 限定竞赛
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
            ->when($this->pid > 0, function ($q) {
                $q->where('s.problem_id', $this->pid); // 限定实际题号
            })
            ->when($this->index >= 0, function ($q) {
                $q->where('cp.index', $this->index); // 限定竞赛中的题号
            })
            ->when(request()->has('sid') && request('sid') != null, function ($q) {
                $q->where('s.id', request('sid')); // 限定提交编号。特殊
            })
            ->when($this->sim_rate > 0, function ($q) {
                $q->where('sim_rate', '>=', $this->sim_rate); // 查重率 0~100
            })
            ->when($this->username != null, function ($q) {
                $q->where('username', 'like', '%' . $this->username . '%'); // 用户名
            })
            ->when($this->result >= 0, function ($q) {
                $q->where('result', $this->result); // 判题结果
            })
            ->when($this->language >= 0, function ($q) {
                $q->where('language', $this->language); // 编程语言
            })
            ->when($this->ip != null, function ($q) {
                $q->where('ip', 'like', $this->ip . '%');
            })
            // 翻页相关
            ->when($this->top_id, function ($q) {
                $q->where('s.id', '<=', $this->top_id);
            })
            ->when($this->bottom_id, function ($q) {
                $q->where('s.id', '>=', $this->bottom_id);
            })
            ->orderBy('s.id', $this->bottom_id ? 'asc' : 'desc')
            ->limit($this->perPage ?? 10)
            ->get();

        if ($this->bottom_id) {
            $this->solutions = $this->solutions->reverse()->values();
        }

        // ====================== 竞赛特别处理 ===================
        if ($this->contest_id) {
            // 计算当前竞赛中的题目数量
            $this->num_problems = DB::table('contest_problems')
                ->where('contest_id', $this->contest_id)->count();
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

        // 统计判题中的数量，决定是否刷新页面
        $this->num_judging = 0;
        foreach ($this->solutions as $s) {
            // $s->nick = null;
            if ($s->result < 4) $this->num_judging++;
        }
        $this->solutions = json_decode(json_encode($this->solutions), true);
    }

    public function next_page()
    {
        $this->top_id = min($this->solutions[0]['id'], $this->solutions[count($this->solutions) - 1]['id']) - 1;
        $this->bottom_id = null;
    }
    public function prev_page()
    {
        $this->bottom_id = max($this->solutions[0]['id'], $this->solutions[count($this->solutions) - 1]['id']) + 1;
        $this->top_id = null;
    }

    public function render()
    {
        $this->refresh();
        return view('livewire.solution.solutions');
    }
}
