<?php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * 给定缓存key，清除该key所缓存的已无效的solutions统计数据
     * 解释：若重判码变了，说明缓存过的数据已经不再准确，需要重新查库
     * 关于重判标识的生成，请参考/Http/Jobs/GenerateRejudgedCode.php
     */
    public static function clear_cache_if_rejudged($cache_key)
    {
        if (Cache::get($cache_key . ':cached:rejudged_code') != Cache::get('solution:rejudged_code')) {
            Cache::put($cache_key . ':cached:rejudged_code', Cache::get('solution:rejudged_code'));
            Cache::forget($cache_key);
        }
        return $cache_key;
    }
}
