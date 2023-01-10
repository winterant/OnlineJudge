<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * patch request:{
     *   `key`:`value`,
     *   ...
     * }
     */
    public function settings(Request $request)
    {
        $modified = $request->all();
        foreach ($modified as $key => $val) {
            // 只允许系统配置项传入
            if (in_array($key, array_keys(config('init.settings')))) {
                if ($val === null) $val = ''; // 前端传过来的空串会被laravel转为null，此处还原为空串
                if ($val === 'true') $val = true;
                if ($val === 'false') $val = false;
                if (is_numeric($val)) $val = intval($val);
                get_setting($key, $val, true);
            }
        }
        return ['ok' => 1, 'msg' => 'Settings have updated.'];
    }
}
