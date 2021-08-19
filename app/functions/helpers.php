<?php

use Illuminate\Support\Facades\Storage;

// 获取测试数据保存路径
function testdata_path($path = null): string
{
    $testdata_path = config('app.JG_DATA_DIR');
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
 * @return array  返回二维字符串数组，第一维[test0,test1,...]，第二维[.in, .out]
 */
function read_problem_data($problem_id, $from_sample = true)
{
    $samples = [];
    $dir = testdata_path($problem_id . '/' . ($from_sample ? 'sample' : 'test'));
    foreach (readAllFilesPath($dir) as $item) {
        $name = pathinfo($item, PATHINFO_FILENAME);  //文件名
        $ext = pathinfo($item, PATHINFO_EXTENSION);    //拓展名
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
 * @param bool $as_sample
 * @param bool $del_old
 */
function save_problem_data($problem_id, $ins, $outs, $from_sample = true, $del_old = true)
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

/**
 * @param $problem_id
 * @param $code
 * @return mixed
 *  保存特判文件
 */
function save_problem_spj($problem_id, $code)
{
    $dir = testdata_path($problem_id . '/spj'); // 特判文件夹
    foreach (readAllFilesPath($dir) as $item)
        unlink($item); //删除原有文件
    if (!is_dir($dir))
        mkdir($dir, 0777, true);  // 文件夹不存在则创建
    file_put_contents($dir . '/spj.cpp', $code);
    $cmd = sprintf("sudo g++ %s/spj.cpp -o %s/spj -lm -std=c++17 2>&1", $dir, $dir);
    $out[] = $cmd;
    exec($cmd, $out);
    if (count($out) == 1)
        $out[] = "特判程序编译成功！";
    else
        $out[] = "特判程序编译出错，请根据报错信息修正后重新上传！";
    return implode('<br>', $out);
}

/**
 * 获取本题的特判代码
 * @param $problem_id
 * @return string
 */
function get_spj_code($problem_id)
{
    $filepath = testdata_path($problem_id . '/spj/spj.cpp');
    if (is_file($filepath))
        return file_get_contents($filepath);
    return null;
}

//将一个数字题号（从1开始）转为大写字母
function index2ch(int $index)
{
    if ($index < 26)
        return chr($index + 65);
    return $index + 1; //Z的下一题是27题
}

//从txt文件读取的内容转码
function autoiconv($text, $type = "gb2312//ignore")
{
    define('UTF32_BIG_ENDIAN_BOM', chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
    define('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
    define('UTF16_BIG_ENDIAN_BOM', chr(0xFE) . chr(0xFF));
    define('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));
    define('UTF8_BOM', chr(0xEF) . chr(0xBB) . chr(0xBF));
    $first2 = substr($text, 0, 2);
    $first3 = substr($text, 0, 3);
    $first4 = substr($text, 0, 3);
    $encodType = "";
    if ($first3 == UTF8_BOM)
        $encodType = 'UTF-8 BOM';
    else if ($first4 == UTF32_BIG_ENDIAN_BOM)
        $encodType = 'UTF-32BE';
    else if ($first4 == UTF32_LITTLE_ENDIAN_BOM)
        $encodType = 'UTF-32LE';
    else if ($first2 == UTF16_BIG_ENDIAN_BOM)
        $encodType = 'UTF-16BE';
    else if ($first2 == UTF16_LITTLE_ENDIAN_BOM)
        $encodType = 'UTF-16LE';
    //下面的判断主要还是判断ANSI编码的·
    if ($encodType == '') { //即默认创建的txt文本-ANSI编码的
//        $content = mb_convert_encoding($text,"UTF-8","auto");
        $content = iconv("GBK", "UTF-8//ignore", $text);
    } else if ($encodType == 'UTF-8 BOM') {//本来就是UTF-8不用转换
        $content = $text;
    } else {//其他的格式都转化为UTF-8就可以了
        $content = iconv($encodType, "UTF-8", $text);
    }
    return $content;
}
