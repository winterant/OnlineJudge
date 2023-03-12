<?php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\DB;

class ProblemHelper
{
    /**
     * 读取样例
     * @param $problem_id
     * @return array [{'in':'***','out':'***'}, ...]
     */
    public static function readSamples($problem_id): array
    {
        $samples = DB::table('problems')->where('id', $problem_id)->value('samples') ?? "[]";
        $samples = json_decode($samples, true);
        if (empty($samples)) {
            // 由于数据库中没有样例，故读取文件中的样例,并转存到数据库
            // 这个if是为了兼顾一个历史遗留问题，2023.3.5之前，样例全都保存为文件
            // 新版本样本转存到数据库 problems表samples字段
            $files = [];
            $dir = testdata_path($problem_id . '/sample');
            foreach (readAllFilesPath($dir) as $item) {
                $name = pathinfo($item, PATHINFO_FILENAME);  //文件名
                $ext = pathinfo($item, PATHINFO_EXTENSION);  //拓展名
                if (!isset($files[$name])) //发现新样本
                    $files[$name] = [];
                if ($ext === 'in')
                    $files[$name]['in'] = file_get_contents($item);
                if ($ext === 'out' || $ext === 'ans')
                    $files[$name]['out'] = file_get_contents($item);
            }
            $samples = array_values($files);
        }
        DB::table('problems')->where('id', $problem_id)->update(['samples' => $samples]);
        return $samples;
    }

    /**
     * 保存样例到文件
     * @param $problem_id
     * @param $ins  字符串列表
     * @param $outs  字符串列表
     */
    public static function saveSamples($problem_id, array $ins, array $outs): bool
    {
        $samples = [];
        foreach ($ins as $k => $si) {
            $so = $outs[$k];
            $samples[] = ['in' => $si, 'out' => $so];
        }
        DB::table('problems')->where('id', $problem_id)->update(['samples' => $samples]);
        return true;
    }


    /**
     * 从文件读取测试数据
     * @param $problem_id
     * @return array  ['filename':{'in':'***','out':'***'}, ...]
     */
    public static function readTestData($problem_id): array
    {
        $testdata = [];
        $dir = testdata_path($problem_id . '/test');
        foreach (readAllFilesPath($dir) as $item) {
            $name = pathinfo($item, PATHINFO_FILENAME);  //文件名
            $ext = pathinfo($item, PATHINFO_EXTENSION);  //拓展名
            if (!isset($testdata[$name])) //发现新样本
                $testdata[$name] = [];
            if ($ext === 'in')
                $testdata[$name]['in'] = file_get_contents($item);
            if ($ext === 'out' || $ext === 'ans')
                $testdata[$name]['out'] = file_get_contents($item);
        }

        $testdata = array_filter($testdata, function ($v) {
            return count($v) == 2;
        });  // 过滤掉不完整的数据（in、out匹配才算完整）

        return $testdata;
    }


    /**
     * 保存测试数据到文件
     * @param $problem_id
     * @param $ins  字符串列表
     * @param $outs  字符串列表
     */
    public static function saveTestData($problem_id, array $ins, array $outs)
    {
        $dir = testdata_path($problem_id . '/test'); // 测试数据文件夹
        foreach (readAllFilesPath($dir) as $item)
            unlink($item); //删除原有文件
        if (!is_dir($dir))
            mkdir($dir, 0777, true);  // 文件夹不存在则创建
        foreach ($ins as $i => $in)
            file_put_contents(sprintf('%s/%s.in', $dir, $i), $in);
        foreach ($outs as $i => $out)
            file_put_contents(sprintf('%s/%s.out', $dir, $i), $out);
    }
}
