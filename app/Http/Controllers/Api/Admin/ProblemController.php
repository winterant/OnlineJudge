<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProblemController extends Controller
{
    // 下载xml文件
    public function download_exported_xml(Request $request)
    {
        return Storage::download('temp/exported/' . $_GET['filename']);
    }

    // 清空历史xml
    public function clear_exported_xml(Request $request)
    {
        Storage::delete(Storage::allFiles('temp/exported'));
        return ['ok' => 1, 'msg' => '已清空'];
    }
}
