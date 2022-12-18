<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/************************ 前台 ***********************************/

/**
 * 给定缓存key，清除该key所缓存的已无效的solutions统计数据
 * 解释：若重判码变了，说明缓存过的数据已经不再准确，需要重新查库
 * 关于重判标识的生成，请参考/Http/Jobs/GenerateRejudgedCode.php
 */
function clear_cache_if_rejudged($cache_key)
{
    if (Cache::get($cache_key . ':cached:rejudged_code') != Cache::get('solution:rejudged_code')) {
        Cache::put($cache_key . ':cached:rejudged_code', Cache::get('solution:rejudged_code'));
        Cache::forget($cache_key);
    }
    return $cache_key;
}

/************************* 后台管理 *****************************/
//获取配置值
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

// todo 要区分web和api获取user的方式不同
//查询用户权限: 查询user是否具有power权限
function privilege($power, $user = null)
{
    // 默认为当前用户
    if ($user == null)
        $user = Auth::user();
    // 无效的user
    if (!$user || !isset($user->id))
        return false;
    // 验证权限代号的有效性
    if (!array_key_exists($power, config('init.authority')))
        abort(502, '[系统错误] 不存在的权限：' . $power);
    /*
    权限说明：
        admin涵盖所有权限
        admin.home为进入后台页面的权限
        admin.problem包含admin.problem.*所有权限，其它类同
        只要数据库中含有$power的前缀，则说明具有当前权限.
    */
    // 从数据库中查询出该用户已有权限
    $powers = DB::table('privileges')->where('user_id', $user->id)->pluck('authority');

    // teacher应当具有的权限
    $teacher_power = [
        'admin.home',
        'admin.problem',
        'admin.contest',
        'admin.group',
    ];
    // teacher具有的权限中，应当排除的，即不应当具有的
    $teacher_expower = [
        'admin.problem.import_export',
        'admin.problem.tag',
    ];

    foreach ($powers as $p) {
        $starts_with = function ($str, $prefix) {
            return substr($str, 0, strlen($prefix)) == $prefix;
        };
        // 数据库中具有当前权限，或者上层权限，则验证通过
        if ($starts_with($power, $p))
            return true;
        // 如果数据库中含有teacher（也就是当前用户是老师）
        // 则在查询某些权限时，均通过
        if ($p == 'teacher') {
            foreach ($teacher_power as $i) {
                if ($starts_with($power, $i)) {

                    $exclude = false; // 查询一下该权限是不是被除外的
                    foreach ($teacher_expower as $j)
                        if ($starts_with($power, $j))
                            $exclude = true;

                    if (!$exclude)
                        return true;
                }
            }
        }
    }
    return false;
}
