<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 获取当前项目的版本号，原理是读取文件`install/.version`首行，有缓存(每次重启时会自动清空)
 */
function get_oj_version()
{
    return Cache::remember('web:version', 3600 * 24 * 30, function () {
        if (file_exists(base_path('install/.version'))) {
            $f = fopen(base_path('install/.version'), 'r');
            return fgets($f);
        }
    });
}


/**
 * 获取配置项的值。
 * 当$update===true时，配置项值将强制更新为$default
 */
function get_setting($key, $default = null, bool $update = false)
{
    $redis_key = 'website:' . $key;

    if ($update) {
        Cache::forever($redis_key, $default); // 缓存
        if (Schema::hasTable('settings')) // 持久化到数据库
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $default]);
    }

    // 查询设置值，查询顺序：cache，database，file
    return Cache::rememberForever($redis_key, function () use ($key, $default) {
        if (Schema::hasTable('settings') && ($val = DB::table('settings')->where('key', $key)->value('value')) !== null)
            return $val;
        else if ($val = config('init.settings.' . $key))
            return $val;   // 尝试从配置文件中读取初始配置项
        return $default;
    });
}


// 获取用户真实ip（参考https://blog.csdn.net/m0_46266407/article/details/107222142）
function get_client_real_ip()
{
    $clientip = '';
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $clientip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $clientip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $clientip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $clientip = $_SERVER['REMOTE_ADDR'];
    }
    preg_match("/[\d.]{7,15}/", $clientip, $clientipmatches);
    $clientip = $clientipmatches[0] ? $clientipmatches[0] : 'unknown';
    return $clientip;
}


// 获取ip属地
function getIpAddress(string $ip = '')
{
    // 可以自己找第三方接口，返回数据不一样
    $url = "http://whois.pconline.com.cn/ip.jsp?ip=" . $ip;
    try {
        $res = file_get_contents($url);
    } catch (Exception $e) {
        echo $e->getMessage();
        $res = null;
    }
    // 因为这个接口返回的值gb2312编码，且有换行符，所以做以下处理
    return preg_replace("/\s/", "", iconv("gb2312", "utf-8", $res));
}


// 获取测试数据保存路径
function testdata_path($path = null): string
{
    $testdata_path = config('app.JUDGE_DATA_DIR');
    if ($testdata_path[0] != '/')  # 如果是相对路径，那就加上当前项目的绝对路径
        $testdata_path = base_path($testdata_path);
    if ($path != null)
        $testdata_path .= '/' . $path;
    return $testdata_path;
}


//读取一个文件夹下所有文件，返回路径列表
function readAllFilesPath($dir_path): array
{
    clearstatcache(); //清除缓存
    $files = [];
    if (is_dir($dir_path)) {
        foreach (scandir($dir_path) as $item) {
            $real_item = $dir_path . '/' . $item;
            if (is_file($real_item)) {
                $files[] = $real_item;
            }
        }
    }
    return $files;
}


/**
 * 读取样例/测试文件
 * @param $problem_id
 * @param bool $from_sample
 * @return array  返回二维字符串数组(直接读取文件内容)，a[filename][.in/.out]=string
 */
function read_problem_data($problem_id, $from_sample = true): array
{
    $samples = [];
    $dir = testdata_path($problem_id . '/' . ($from_sample ? 'sample' : 'test'));
    foreach (readAllFilesPath($dir) as $item) {
        $name = pathinfo($item, PATHINFO_FILENAME);  //文件名
        $ext = pathinfo($item, PATHINFO_EXTENSION);  //拓展名
        if (!isset($samples[$name])) //发现新样本
            $samples[$name] = ['', ''];
        if ($ext === 'in')
            $samples[$name][0] = file_get_contents($item);
        if ($ext === 'out' || $ext === 'ans')
            $samples[$name][1] = file_get_contents($item);
    }
    return $samples;
}


/**
 * 保存样例/测试到文件
 * @param $problem_id
 * @param $ins
 * @param $outs
 * @param bool $from_sample
 */
function save_problem_data($problem_id, $ins, $outs, $from_sample = true)
{
    $dir = testdata_path($problem_id . '/' . ($from_sample ? 'sample' : 'test')); // 测试数据文件夹
    foreach (readAllFilesPath($dir) as $item)
        unlink($item); //删除原有文件
    if (!is_dir($dir))
        mkdir($dir, 0777, true);  // 文件夹不存在则创建
    foreach ($ins as $i => $in)
        file_put_contents(sprintf('%s/%s.in', $dir, $i), $in);
    foreach ($outs as $i => $out)
        file_put_contents(sprintf('%s/%s.out', $dir, $i), $out);
}


//将一个数字题号转为大写字母 A~Z(0~25), 27, 28, 29, ...
function index2ch(int $index)
{
    if ($index < 26)
        return sprintf("%s (%d)", chr($index + 65), $index + 1);
    return $index + 1; //Z的下一题是27题
}


/**
 * 给定文本字符串，按行进行分割。对于每一行，有一些特殊规则：
 * 1、若存在减号，且前后均为正数，如16-20，则表示连续数列，解析为16、17、18、19、20这5个数字字符串
 * 2、若存在空白，且空格后是数字，如xxx 3，则表示复制，解析为xxx、xxx、xxx这3个相等字符串
 * 当且仅当 $special_rule = true 时特殊规则生效，例如：
 * 1000-1002
 * 1010
 * 1024 3
 * $special_rule = true 解析为:
 * ['1000','1001','1002','1010','1024','1024','1024']
 * $special_rule = false 解析为:
 * ['1000-1002', '1010', '1024 3']
 */
function decode_str_to_array($text, $special_rule = true)
{
    if ($text == null)
        return [];

    $rows = explode(PHP_EOL, $text); // 按行分割
    $data = [];
    foreach ($rows as $row) {
        $row = trim($row);
        if ($special_rule && preg_match('/^\d+\s*-\s*\d+$/', $row)) { // 特殊规则1
            $values = preg_split('/\s*-\s*/', $row);
            $range = array_map(function ($x) {
                return (string)$x;
            }, range($values[0], $values[1]));
            $data = array_merge($data, $range);
        } else if ($special_rule && preg_match('/^\S+\s+\d+$/', $row, $arr)) { // 特殊规则2
            $values = preg_split('/\s+/', $row);
            $data = array_merge($data, array_fill(0, $values[1], $values[0]));
        } else { // 无规则
            $data[] = $row;
        }
    }
    return $data;
}
