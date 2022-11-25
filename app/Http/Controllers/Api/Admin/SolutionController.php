<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\CorrectSolutionsStatistics;
use Illuminate\Http\Request;

class SolutionController extends Controller
{
    // 矫正过题数
    public function correct_submitted_count()
    {
        dispatch(new CorrectSolutionsStatistics()); // 矫正过题数字段
        return [
            'ok' => 1,
            'msg' => '已发起任务：校正提交记录变动造成的数据统计误差'
        ];
    }
}
