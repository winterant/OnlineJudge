<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolutionController extends Controller
{
    public function submit(Request $request){
        return ['ok'=>1, 'msg'=>'OK', 'data'=>$request->input()];
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
}
