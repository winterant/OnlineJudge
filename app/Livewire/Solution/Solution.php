<?php

namespace App\Livewire\Solution;

use App\Http\Helpers\ProblemHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class Solution extends Component
{
    public ?int $sid = null;        // 提交记录的唯一编号
    public ?array $solution = null; // 提交记录所有详细信息
    public ?array $detail = null;   // 正在展示的评测点详细信息{'time':**, 'memory':**, ...}
    public ?string $msg = null;     // 可能的报错信息，例如权限不足,默认null
    public int $numAccepted, $numDetails; // 测试点计数
    public bool $isJudged = false; // 是否在判题完成
    public bool $only_details = false; // 标记仅展示测试点信息，其他信息如提交时间、代码等都不展示


    public string $aiChatResult = '';

    public function mount($id = null, $only_details = false)
    {
        $this->only_details = $only_details;
        $this->resetSolutionId($id);
    }

    // 监听前端，清空当前solution信息
    #[On('Solution.Solution.clearDetails')]
    public function clearDetails(): void
    {
        $this->sid = null;
        $this->solution = null;
        $this->detail = null;
        $this->msg = null;
    }

    // 重设solution id，并重新刷新solution结果
    #[On('Solution.Solution.resetSolutionId')]
    public function resetSolutionId($id): void
    {
        $this->sid = $id;
        $this->solution = null;
        $this->detail = null;
        $this->msg = null;
        $this->refresh();   // 刷新结果
    }

    // 根据solution id刷新当前solution结果
    public function refresh(): void
    {
        if ($this->sid == null)
            return;

        // 判断权限
        /** @var App/Model/User */
        $user = Auth::user();
        if ($user == null || !$user->can_view_solution($this->sid)) {
            $this->msg = __('sentence.Permission denied');
            return;
        }

        // 读取数据库中 所有测试数据的详细结果 {'testname':{'result':int, ...}, ...}
        $db_solution = DB::table('solutions')
            ->select($this->only_details ? ['id', 'result', 'error_info', 'wrong_data', 'judge_result', 'user_id', 'pass_rate'] : ['*'])
            ->find($this->sid);
        if ($db_solution ?? false) {
            // ========================= 先查询所在竞赛的必要信息 ==========================
            if ($db_solution->contest_id ?? false) {
                $contest = DB::table('contests as c')
                    ->join('contest_problems as cp', 'c.id', 'cp.contest_id')
                    ->select(['c.end_time', 'cp.index'])
                    ->where('c.id', $db_solution->contest_id)
                    ->where('cp.problem_id', $db_solution->problem_id)
                    ->first();
                if ($contest) {
                    $db_solution->index = $contest->index; // 记下该代码在竞赛中的题号
                    $db_solution->end_time = $contest->end_time; // 记下所在竞赛的结束时间
                } else
                    $db_solution->contest_id = -1; // 这条solution以前是竞赛中的，但题目现在被从竞赛中删除了
            }
            // 转为数组，前端读取
            $this->solution = json_decode(json_encode($db_solution), true);

            $this->solution['username'] = DB::table('users')->find($this->solution['user_id'])->username ?? null;

            $this->solution['judge_result'] = $this->process_details($this->solution['judge_result']);
            // 刷新测试点通过数量
            $this->numDetails = count($this->solution['judge_result']);
            $this->numAccepted = 0;
            foreach ($this->solution['judge_result'] ?? [] as $d) {
                if ($d['result'] == 4)
                    $this->numAccepted++;
            }
            // 展示详情点
            $this->display_detail();

            // 标记判题是否完成
            $this->isJudged = (($this->solution['result'] ?? PHP_INT_MAX) >= 4);
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

    // 点击某一个detail时触发，将显示当前detail的错误细节
    public function display_detail(int $index = null)
    {
        if ($index === null)
            $index = $this->detail['index'] ?? null; // 默认为上次的detail

        // 如果上次detail存在 且 有更新值，则重新获取；否则，置空
        if ($index !== null && ($this->solution['judge_result'][$index] ?? false)) {
            $this->detail = $this->solution['judge_result'][$index];
            $this->detail['index'] = $index;
            $this->dispatch("solution.detail.display");
        } else {
            $this->detail = null;
        }
    }

    public function ai_chat()
    {
        $lock = Cache::lock('solution.ai_chat.' . Auth::user()->username, 180);
        if (!$lock->get()) {
            $this->aiChatResult = implode("\n\n", ["3分钟内只能使用1次AI答疑哦~", $this->aiChatResult]);
            $this->dispatch("solution.ai_chat_result", trim($this->aiChatResult));
            return;
        }

        // 构造问题
        $problem = DB::table('problems')->select(['title', 'description', 'input', 'output', 'hint', 'time_limit', 'memory_limit',])->find($this->solution['problem_id']);
        $samples = ProblemHelper::readSamples($this->solution['problem_id']);
        $samplesStr = implode("\n", array_map(function ($sample) {
            return "输入: " . $sample['in'] . "\n输出: " . $sample['out'];
        }, $samples));
        $question = "### 问题描述\n" .
            $problem->description . "\n\n" .
            "### 输入描述\n" .
            $problem->input . "\n\n" .
            "### 输出描述\n" .
            $problem->output . "\n\n" .
            "### 样例\n" .
            $samplesStr . "\n\n" .
            "### 我的代码\n" .
            "```\n" .
            $this->solution['code'] .
            "```\n\n" .
            "### 错误信息\n" .
            $this->solution['error_info'] . "\n\n" .
            "请问我的代码错在哪里？指出错误之处，以及可能得解决方法，无需给出完整答案";

        // 请求大模型
        $endpoint = get_setting('openai_chat_endpoint');
        $model = get_setting('openai_chat_model');
        $apiKey = get_setting('openai_api_key');
        $client = new Client();
        try {
            Log::info("AI chat for solution " . $this->solution['id']);
            $response = $client->post($endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                'json' => [
                    'model' => $model,
                    // 'stream' => true,
                    'messages' => [
                        ["role" => "system", "content" => "你是一个编程老师，热衷于指导学生解决编程问题"],
                        ["role" => "user", "content" => $question],
                    ],
                ],
                'timeout' => 180,
                // 'stream' => true,
            ]);
            $body = json_decode($response->getBody(), true);
            Log::info("AI chat for solution " . $this->solution['id'] . ": " . $response->getBody());
            $this->aiChatResult = $body['choices'][0]['message']['content'] ?? $body;
            $this->dispatch("solution.ai_chat_result", trim($this->aiChatResult));
        } catch (RequestException|GuzzleException $e) {
            $this->dispatch("solution.ai_chat_result", $e->getMessage());
        }
    }

    public function render()
    {
        if ($this->msg != null)
            return view('livewire.message', ['msg' => $this->msg])->extends('layouts.client')->section('content');
        if ($this->only_details)
            return view('livewire.solution.solution');
        return view('livewire.solution.solution')->extends('layouts.client')->section('content');
    }
}
