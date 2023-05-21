<?php

namespace App\Http\Livewire\Solution;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Details extends Component
{
    public $solution_id; // solution id
    public $result;      // solution最终结果
    public $error_info; // 错误信息
    public array $details;    // 所有评测点
    public $detail;     // 正在展示的评测点
    public int $numAc, $numTests; // 测试点计数
    public $showTip;    // bool 是否展示提示条

    protected $listeners = ['setSolutionId'];

    // 初始化一个评测点详情信息
    public function mount(int $solution_id = null, bool $showTip = false)
    {
        $this->solution_id = $solution_id;
        $this->result = -1; // 初始化未提交状态
        $this->detail = null;
        $this->refresh();   // 刷新结果
        $this->showTip = $showTip;
    }

    // livewire创建后，手动指定solution id
    public function setSolutionId($id)
    {
        $this->solution_id = $id;
        $this->refresh();
    }

    // 根据solution_id刷新。如果制定了changeId，则顺便更新solution_id
    public function refresh()
    {
        if ($this->solution_id == null) // 既没有指定id，也不存在solution_id
            return;

        // 读取数据库中 所有测试数据的详细结果 {'testname':{'result':int, ...}, ...}
        $solution = DB::table('solutions')
            ->select(['result', 'error_info', 'judge_result'])->find($this->solution_id);
        if ($solution == null)
            return;
        $this->result = $solution->result ?? -1;
        $this->error_info = $solution->error_info ?? null;
        $this->details = $this->process_details($solution->judge_result ?? null);
        $this->display_detail();

        // 刷新测试点通过数量
        $this->numTests = count($this->details ?? []);
        $this->numAc = 0;
        foreach ($this->details ?? [] as $d) {
            if ($d['result'] == 4) $this->numAc++;
        }
    }

    // 点击某一个detail时触发，将显示当前detail的错误细节
    public function display_detail(int $index = null)
    {
        if ($index === null)
            $index = $this->detail['index'] ?? null; // 默认为上次的detail

        // 如果上次detail存在 且 有更新值，则重新获取；否则，置空
        if ($index !== null && ($this->details[$index] ?? false)) {
            $this->detail = $this->details[$index];
            $this->detail['index'] = $index;
        } else {
            $this->detail = null;
        }
    }

    // json数据进行预处理，返回数组
    private function process_details(string $judge_result = null)
    {
        $judge_result = json_decode($judge_result ?? '[]', true);
        foreach ($judge_result as $k => &$test) {
            $judge_result[$k]['result_desc'] = trans('result.' . config("judge.result." . $test['result'] ?? 0));
            if (!isset($judge_result[$k]['testname']))
                $judge_result[$k]['testname'] = $k; // 记下测试名，用于排序
        }
        uasort($judge_result, function ($a, $b) {
            return $a['testname'] < $b['testname'] ? -1 : 1; // 按测试名升序
        });
        return array_values($judge_result); // 转为数组
    }

    // 渲染前端
    public function render()
    {
        return view('livewire.solution.details');
    }
}
