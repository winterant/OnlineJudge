<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
                // 转换非字符串类型的值
                if ($val === null)
                    $val = ''; // 前端传过来的空串会被laravel转为null，此处还原为空串
                else if ($val === 'true')
                    $val = true;
                else if ($val === 'false')
                    $val = false;
                else if (filter_var($val, FILTER_VALIDATE_INT) !== false)
                    $val = intval($val); // 如果是整数，则转为整数类型
                else if (filter_var($val, FILTER_VALIDATE_FLOAT) !== false)
                    $val = floatval($val); // 如果是小数，则转为浮点数类型
                //  更新
                get_setting($key, $val, true);
            }
        }
        return ['ok' => 1, 'msg' => 'Settings have updated.', 'data' => $modified];
    }

    public function set_icon(Request $request)
    {
        try {
            $msg = '[Successfully replaced icon]';
            foreach (['favicon', 'logo'] as $name) {
                if ($request->has($name)) {
                    $fav = $request->file($name);
                    if ($fav) {
                        Storage::putFileAs("public", $fav, "{$name}.ico");
                        $msg .= " Saved {$name}.ico";
                    } else { // 删除
                        Storage::delete("public/{$name}.ico");
                        $msg .= " Deleted {$name}.ico";
                    }
                    $replaced[] = $name;
                }
            }
            return ['ok' => 1, 'msg' => $msg];
        } catch (Exception $e) {
            return ['ok' => 0, 'msg' => 'Failed to replaced icon', 'data' => $e->getMessage()];
        }
    }
}
