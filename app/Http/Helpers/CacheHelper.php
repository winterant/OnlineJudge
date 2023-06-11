<?php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * 凡是依赖solutions表统计的数据，放入缓存中（key-value），key需要记住此时的solution stamp（如下代码）
     * 从缓存中取出该key使用时，必须执行该函数进行检查：
     *      如果solutions数据发生了变化（也就是重判了），那么要立即清除该key的数据及其solution stamp，
     *      这会迫使下游业务重新统计数据并缓存。
     * 关于solution stamp的生成，请参考/Http/Jobs/ResetSolutionStamp.php
     */
    public static function has_key_with_autoclear_if_rejudged($cache_key)
    {
        // 自动清除过期的缓存
        if (Cache::get($cache_key . ':cached:solution_stamp') != Cache::get('solution:solution_stamp')) {
            Cache::put($cache_key . ':cached:solution_stamp', Cache::get('solution:solution_stamp'));
            Cache::forget($cache_key);
            return false; // 已清除缓存，自然无key
        }
        return Cache::has($cache_key); // 此函数内未发生清除缓存操作，以Cache结果为准。
    }
}
