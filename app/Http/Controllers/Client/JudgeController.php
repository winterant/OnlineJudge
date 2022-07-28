<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JudgeController extends Controller
{
    // 评判一个提交记录
    public function judge($solution)
    {
        // dump($this->gather_tests(10741));
        // 搜集测试数据
        // 遍历测试数据，一一评测
        // 计算判题结果，时间、空间、通过率等
    }

    // 运行一次用户代码
    private function run(string $code, string $in_path, string $out_path)
    {
        # code...
    }

    // 给定题号，搜集目录下所有的.in/.out/.ans数据对，返回路径列表
    private function gather_tests(string $pid)
    {
        $samples = [];
        $temp = [];
        $dir = testdata_path($pid . '/test');
        foreach (readAllFilesPath($dir) as $item) {
            $name = pathinfo($item, PATHINFO_FILENAME);  //文件名
            $ext = pathinfo($item, PATHINFO_EXTENSION);  //拓展名
            if ($ext === 'in')
                $temp[$name]['in'] = $item;
            if ($ext === 'out' || $ext === 'ans')
                $temp[$name]['out'] = $item;
            if (count($temp[$name]) == 2)
                $samples[$name] = $temp[$name];
        }
        return $samples;
    }

    // 向judge0发送判题指令，返回判题结果
    private function send(array $data)
    {
        # code...
    }
}
