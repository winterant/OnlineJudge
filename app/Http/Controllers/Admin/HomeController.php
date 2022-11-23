<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    public function home(Request $request)
    {
        if (!DB::table('privileges')->where('user_id', Auth::id())->exists())
            abort(404);

        //服务器相关信息
        $systemInfo = [
            '网站域名'      => $_SERVER["HTTP_HOST"],
            '服务器主机名'   =>  $_SERVER['SERVER_NAME'],
            '服务器IP地址'   =>  $_SERVER['SERVER_ADDR'],
            '服务器Web端口'  =>  $_SERVER['SERVER_PORT'],
            '服务器操作系统'  =>  php_uname(),
            '服务器当前时间'  => date("Y-m-d H:i:s"),
            'PHP版本'       =>  PHP_VERSION,
            'PHP安装路径'    =>  DEFAULT_INCLUDE_PATH,
            'PHP运行方式'    =>  php_sapi_name(),
            'Laravel版本'   =>  $laravel = app()::VERSION,
            '最大上传限制'   => get_cfg_var("upload_max_filesize"),
            '最大执行时间'   => get_cfg_var("max_execution_time") . "秒",
            '脚本运行最大内存' => get_cfg_var("memory_limit"),
            '服务器解译引擎'  => $_SERVER['SERVER_SOFTWARE'],
            '通信协议'       => $_SERVER['SERVER_PROTOCOL']
        ];
        $fpm_status = Http::get('http://localhost:8088/fpm-status', $_GET); // php-fpm实时状态
        return view('admin.home', compact('systemInfo', 'fpm_status'));
    }

    /**
     * 系统设置
     */
    public function settings(Request $request)
    {
        return view('admin.settings');
    }
}
