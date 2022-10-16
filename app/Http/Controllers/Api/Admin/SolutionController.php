<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\CorrectSubmittedCount;
use Illuminate\Http\Request;

class SolutionController extends Controller
{
    // 矫正过题数
    public function correct_submitted_count()
    {
        dispatch(new CorrectSubmittedCount())->onQueue('CorrectSubmittedCount'); // 矫正过题数字段
        return [
            'ok' => 1,
            'msg' => '已发起任务'
        ];
    }
}
