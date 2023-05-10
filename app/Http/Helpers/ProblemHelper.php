<?php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\Cache;
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
        // 读取数据库中保存的样例
        $samples = DB::table('problems')->where('id', $problem_id)->value('samples') ?? "[]";
        $samples = json_decode($samples, true);
        // 防止类型错误，筛选一遍
        $samples = array_filter($samples, function ($e) {
            return is_string($e['in']) && is_string($e['out']);
        });
        // 兼容老版本（样例保存在文件）
        if (empty($samples)) {
            // 由于数据库中没有样例，故读取文件中的样例,并转存到数据库
            // 这个if是为了兼顾一个历史遗留问题，2023.3.5之前，样例全都保存为文件
            // 新版本样本转存到数据库 problems表samples字段
            $files = [];
            $dir = testdata_path($problem_id . '/sample');
            foreach (getAllFilesPath($dir) as $item) {
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
            DB::table('problems')->where('id', $problem_id)->update(['samples' => $samples]);
        }
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
        $size = min(count($ins), count($outs)); // 样例个数
        for ($k = 0; $k < $size; ++$k) {
            if (is_string($ins[$k]) && is_string($outs[$k]))
                $samples[] = ['in' => $ins[$k], 'out' => $outs[$k]];
        }
        DB::table('problems')->where('id', $problem_id)->update(['samples' => $samples]);
        return true;
    }

    /**
     * 从文件系统读取测试数据的文件名
     * @param $problem_id
     * @return array  ['1'=>['in'=>'1.in', 'out'=>'1.ans'], ...]
     */
    public static function getTestDataFilenames($problem_id): array
    {
        $testdata = [];
        $dir = testdata_path($problem_id . '/test');
        foreach (getAllFilesPath($dir) as $item) {
            $name = pathinfo($item, PATHINFO_FILENAME);  //文件名
            $ext = pathinfo($item, PATHINFO_EXTENSION);  //拓展名
            if (!isset($testdata[$name])) //发现新样本
                $testdata[$name] = ['filesize' => filesize($item)];
            if ($ext === 'in')
                $testdata[$name]['in'] = $name . '.' . $ext;
            if ($ext === 'out' || $ext === 'ans')
                $testdata[$name]['out'] = $name . '.' . $ext;
        }

        $testdata = array_filter($testdata, function ($v) {
            return isset($v['in']) && isset($v['out']);
        });  // 过滤掉不完整的数据（in、out匹配才算完整）

        ksort($testdata);
        return $testdata;
    }

    /**
     * 保存测试数据到文件
     * @param $problem_id
     * @param $texts = ['filename'=>'content', ...]
     * @param $clear_old 是否清空原有文件再存入新文件
     */
    public static function saveTestDatas($problem_id, array $texts, bool $clear_old = false)
    {
        $dir = testdata_path($problem_id . '/test'); // 测试数据文件夹
        if (!is_dir($dir))
            mkdir($dir, 0777, true);  // 文件夹不存在则创建
        if ($clear_old) { // 清除旧文件
            foreach (getAllFilesPath($dir) as $item)
                unlink($item); //删除原有文件
        }
        // 挨个保存文件
        foreach ($texts as $fname => $text) {
            if (is_numeric($fname)) // 没提供文件名，则默认序号
                $fname .= (file_exists($dir . '/' . $fname . '.in') ? '.out' : '.in');
            file_put_contents(sprintf('%s/%s', $dir, $fname), $text);
        }
    }

    /**
     * 读取某题的spj代码
     *  版本兼容1.x ==> 2.x   spj代码由文件转存到数据库
     */
    public static function readSpj($problem_id)
    {
        // 读取数据库中保存的样例
        // $spj = DB::table('problems')->where('id', $problem_id)->value('spj_code') ?? "";
        // 兼容老版本（spj在文件）
        // if (empty($spj)) {
        // 由于数据库中没有spj，故读取文件中的spj,并转存到数据库
        // 这个if是为了兼顾一个历史遗留问题，2023.5.6之前，spj全都保存为文件
        // 新版本spj转存到数据库 problems表spj_code字段
        $filepath = testdata_path($problem_id . '/spj/spj.cpp');
        $spj = is_file($filepath) ? file_get_contents($filepath) : '';
        // DB::table('problems')->where('id', $problem_id)->update(['spj_code' => $spj]);
        // }
        return $spj;
    }

    /**
     * 保存某题的spj代码
     */
    public static function saveSpj($problem_id, $code)
    {
        $dir = testdata_path($problem_id . '/spj'); // spj文件夹
        if (!is_dir($dir))
            mkdir($dir, 0777, true);  // 文件夹不存在则创建
        file_put_contents(sprintf('%s/spj.cpp', $dir), $code);
        return true;
    }


    /**
     * 获取某题目的标签，官方+用户收集
     * @return [[id=>int,name=>string,count=>int], ...]
     */
    public static function getTags(int $problem_id, bool $official = true, bool $informal = true, int $informal_limit = 3)
    {
        $tags = [];  // [[id=>int,name=>string,count=>int],...]
        $tag_names = [];
        if ($official) {
            $res = json_decode(DB::table('problems')->find($problem_id)->tags ?? '[]', true); // json => array
            foreach ($res as $tag) {
                $tags[] = [
                    'id' => DB::table('tag_pool')->where('name', $tag)->value('id') ?? 0,
                    'name' => $tag, 'count' => 0
                ];
                $tag_names[] = $tag; // 记下标签名
            }
        }
        if ($informal) {
            $informal_tags = Cache::remember(
                sprintf('problem:%d:user-tags:limit:%d', $problem_id, $informal_limit),
                300, // 缓存5分钟
                function () use ($problem_id, $informal_limit) {
                    $tags = DB::table('tag_marks as tm')
                        ->join('tag_pool as tp', 'tp.id', '=', 'tm.tag_id')
                        ->select(['tp.id', 'tp.name', DB::raw('count(*) as count')])
                        ->where('tm.problem_id', $problem_id)
                        ->where('tp.hidden', 0)
                        ->groupBy('tp.id')
                        ->orderByDesc('count')
                        ->limit($informal_limit)
                        ->get()
                        ->map(function ($value) {
                            return (array)$value; // 每个对象都转为数组
                        })
                        ->toArray();
                    return $tags ?? [];
                }
            );
            // 官方标签与民间收集标签去重
            foreach ($informal_tags as $t) {
                if (!in_array($t['name'], $tag_names)) // 官方标签有了的，就不用了
                    $tags[] = $t;
            }
        }
        return $tags;
    }
}
