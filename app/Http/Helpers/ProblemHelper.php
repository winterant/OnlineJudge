<?php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\Auth;
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
            foreach (get_all_files_path($dir) as $item) {
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
        foreach (get_all_files_path($dir) as $item) {
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
            foreach (get_all_files_path($dir) as $item)
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
     */
    public static function readSpj($problem_id)
    {
        $filepath = testdata_path($problem_id . '/spj/spj.cpp');
        $spj = is_file($filepath) ? file_get_contents($filepath) : null;
        if ($spj) {
            $encode = mb_detect_encoding($spj, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
            $spj = mb_convert_encoding($spj, 'UTF-8', $encode);
        }
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

    // 查询某用户是否通过了某题，返回结果为4则AC，<4则判题中，>4则答案错误（尝试中）
    public static function getUserResult($problem_id, $contest_id = null, $user_id = null)
    {
        // null,0，1，2，3都视为没做； 4视为Accepted；其余视为答案错误（尝试中）
        $key = sprintf('problem:%d:user:%d:result', $problem_id, $user_id ?? Auth::id());
        if ($contest_id)
            $key = "contest:{$contest_id}:" . $key;
        CacheHelper::has_key_with_autoclear_if_rejudged($key); // 若发生了重判，会强制清除缓存，然后下面重新查库
        if (!Cache::has($key)) {
            $result = DB::table('solutions')
                ->when($contest_id, function ($q) use ($contest_id) {
                    $q->where('contest_id', $contest_id);
                })
                ->where('problem_id', $problem_id)
                ->where('user_id', $user_id ?? Auth::id())
                ->where('result', '>=', 4)
                ->min('result');
            if ($result == 4) // 已经AC，长期保存
                Cache::put($key, $result, 3600 * 24 * 30);
            else if ($result !== null)
                Cache::put($key, $result, 30);
            return $result;
        } else {
            return Cache::get($key);
        }
    }
}
