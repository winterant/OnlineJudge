<?php

namespace App\Livewire\Problem;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class ExportProblems extends Component
{
    public string $problemIdsStr;
    public array $history_xml = []; // 题目历史导出记录
    public int $history_xml_unfinished = 0; // 进行中的历史任务数，用于自动刷新列表

    public function mount(): void
    {
        $this->listHistoryXml();
    }

    // 执行导出题目，实际导出过程放到队列任务中
    public function export()
    {
        // 处理题号为int数组
        $problem_ids = decode_str_to_array($this->problemIdsStr);

        // 文件夹不存在则创建
        $dir = "temp/exported_problems";
        if (!Storage::exists($dir))
            Storage::makeDirectory($dir);

        // 根据传入题号命名文件名
        $filename = str_replace(["\r\n", "\r", "\n"], ',', trim($this->problemIdsStr));
        $filename = sprintf('%s[%s]%s', date('YmdHis'), Auth::user()->username ?? "unknown", $filename);
        if (strlen($filename) > 36) // 文件名过长截断
            $filename = substr($filename, 0, 36);
        $filepath = sprintf('%s/%s.xml', $dir, $filename);

        Log::info("dispatch problem exporting job: ", [$problem_ids, $filepath]);
        $job = new \App\Jobs\Problem\ExportProblems($problem_ids, $filepath);
        dispatch($job);

        // 刷新历史记录
        $this->listHistoryXml();

        // 给前端js一个通知
        $this->dispatch('report', ['ok' => 1, 'msg' => sprintf("已发起导出任务，请在历史记录查看或下载文件%s.xml", $filename)]);
    }

    // 列出历史导出的xml文件
    public function listHistoryXml()
    {
        $this->history_xml = [];
        $this->history_xml_unfinished = 0;
        // 从文件系统中读取所有导出过的文件
        $files = Storage::allFiles('temp/exported_problems');
        $files = array_reverse($files);
        foreach ($files as $path) {
            if (time() - Storage::lastModified($path) > 3600 * 24 * 365) // 超过365天的数据删除掉
                Storage::delete($path);
            else {
                // 根据文件后缀分析任务状态
                $info = pathinfo($path);
                $statusDesc = [
                    'pending' => '等待中',
                    'running' => '运行中',
                    'saving' => '保存中',
                    'failed' => '失败',
                    'xml' => '成功'
                ];
                // 匹配出操作时间、创建者用户名
                preg_match('/^(\S+?)\[/', $info['filename'], $matches_datetime);
                preg_match('/\[(\S+?)\]/', $info['filename'], $matches_username);
                $this->history_xml[] = [
                    'name' => $info['basename'],
                    'filesize' => filesize(Storage::path($path)), // Byte
                    'status' => $statusDesc[$info['extension']],
                    'creator' => $matches_username[1] ?? '',
                    'created_at' => \DateTime::createFromFormat('YmdHis', $matches_datetime[1] ?? '')->format('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s', Storage::lastModified($path)),
                    'time_used' => date('i:s', Storage::lastModified($path) - \DateTime::createFromFormat('YmdHis', $matches_datetime[1] ?? '')->getTimestamp())
                ];
                if (str_ends_with($info['basename'], 'ing')) {
                    $this->history_xml_unfinished++; // 进行中的任务数
                }
            }
        }
    }

    // 清空历史导出过的xml
    #[On('Problem.ExportProblems.clearExportedXml')]
    public function clearExportedXml()
    {
        if (empty($this->history_xml)) {
            // 通知js
            $this->dispatch('notify', ['ok' => 0, 'msg' => "已经没有任何文件啦~"]);
        } else {
            Storage::delete(Storage::allFiles('temp/exported_problems'));
            $this->history_xml = [];
            // 通知js
            $this->dispatch('notify', ['ok' => 1, 'msg' => "已清空"]);
        }
    }

    // 渲染页面
    public function render()
    {
        return view('livewire.problem.export-problems');
    }
}
