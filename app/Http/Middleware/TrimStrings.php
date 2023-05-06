<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
        'sample_ins*',
        'sample_outs*',
        'testdata_content',
    ];

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        // 排除except字段
        foreach ($this->except as $k) {
            if ($this->is_match($k, $key))
                return $value;
        }
        return parent::transform($key, $value);
    }

    /**
     * 实现'video.*.info'能匹配'video.asdfa.info';
     * @return bool
     */
    function is_match($pattern, $value)
    {
        $pattern = preg_quote($pattern, '/');         // 特殊字符转义一下(/也会转义)
        $pattern = str_replace('\*', '.*', $pattern); // *号替换为.*以正则匹配
        return preg_match("/^{$pattern}$/i", $value); // 正则匹配
    }
}
