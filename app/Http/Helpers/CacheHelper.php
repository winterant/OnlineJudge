<?php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * 若发生过重判，清除指定的key。
     * 竞赛榜单、提交记录曲线统计等功能，由于比较耗时，将结果放在了缓存中。如果有管理员进行了代码重判，会导致缓存中的统计结果不再准确。
     * 为了清理不再准确的数据，每次重判完成后，重新生成一个「重判时间戳」，用于标记发生过重判。
     * 具体工作流程：
     *  从缓存中取某个统计结果(如竞赛榜单)前，先调用该函数进行验证。
     *  如果key对应的重判时间戳发生了变化，那么遗忘掉key；
     *  并且记住新的重判时间戳，以标记该key接下来的value都是基于该「重判时间戳」有效的。
     *  关于「重判时间戳」的生成，请参考ResetSolutionStamp.php
     *
     * @return bool 若成功清除了key则返回true；若没有任何操作则返回false
     */
    public static function forgetIfRejudged($cache_key): bool
    {
        // 判断缓存的「重判时间戳」是否已变；若变了说明缓存不再可信，强制清除过期的缓存，并记住新的「重判时间戳」
        if (Cache::get($cache_key . ':cached:rejudged_datetime') != Cache::get('solution:rejudged_datetime')) {
            Cache::forget($cache_key);  // 清除不再可信的key
            Cache::put($cache_key . ':cached:rejudged_datetime', Cache::get('solution:rejudged_datetime')); // 标记新的重判时间戳
            return true; // 成功清除了key
        }
        return false; // 没有任何操作
    }
}
