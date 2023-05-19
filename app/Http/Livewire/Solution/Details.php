<?php

namespace App\Http\Livewire\Solution;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Details extends Component
{
    public $solution_id;
    public $details;
    public $display; // 正在展示的结果
    public int $numAc, $numTests, $numRunning;
    public $showTip;

    protected $listeners = ['setSolutionId'];

    /**
     * 初始化一个评测点详情信息
     * @param $solution_id
     * @param $json_judge_result json格式的评测点信息，与solution_id之前必须有一个被赋值
     */
    public function mount(string $json_judge_result = null, bool $showTip=false)
    {
        $this->details = $this->process_details($json_judge_result);
        $this->solution_id = null;
        $this->display = null;
        $this->numTests = count($this->details ?? []);
        $this->numAc = $this->numRunning = 0;
        foreach ($this->details ?? [] as $d) {
            if ($d['result'] == 4) $this->numAc++;
            else if ($d['result'] < 4) $this->numRunning++;
        }
        $this->showTip = $showTip;
    }

    // livewire创建后，手动指定solution id
    public function setSolutionId($id)
    {
        $this->refresh($id);
    }

    // 根据solution_id刷新。如果制定了changeId，则顺便更新solution_id
    public function refresh($changeId = null)
    {
        if ($changeId != null)
            $this->solution_id = $changeId;
        else if ($this->solution_id == null) //即没有指定id，也不存在solution_id
            return;

        // 读取数据库中 所有测试数据的详细结果 {'testname':{'result':int, ...}, ...}
        $judge_result = DB::table('solutions')->find($this->solution_id)->judge_result ?? null;
        $this->details = $this->process_details($judge_result);
        if ($this->display['index'] ?? false)
            $this->display_detail($this->display['index']);

        // 刷新测试点通过数量
        $this->numTests = count($this->details ?? []);
        $this->numAc = $this->numRunning = 0;
        foreach ($this->details ?? [] as $d) {
            if ($d['result'] == 4) $this->numAc++;
            else if ($d['result'] < 4) $this->numRunning++;
        }
    }

    /**
     * 对json数据进行预处理，返回数组
     */
    private function process_details(string $judge_result = null)
    {
        if ($judge_result == null) return null;
        $judge_result = json_decode($judge_result, true);
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

    /**
     * 点击某一个detail时触发，将显示当前detail的错误细节
     */
    public function display_detail(int $index)
    {
        $this->display = $this->details[$index];
        $this->display['index'] = $index;
    }

    /**
     * 渲染前端
     */
    public function render()
    {
        return view('livewire.solution.details');
    }
}
