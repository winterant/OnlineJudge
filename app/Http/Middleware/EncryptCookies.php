<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        '^unencrypted.*$' // 不加密字段(正则表达式)
    ];

    public function isDisabled($name)
    {
        if (in_array($name, $this->except)) {
            return true;
        }
        foreach ($this->except as $pattern) {
            if (preg_match('/' . $pattern . '/', $name)) {
                return true;
            }
        }
        return false;
    }
}
