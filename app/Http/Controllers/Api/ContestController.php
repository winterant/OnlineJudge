<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContestController extends Controller
{
    //交换两个竞赛的order字段
    public function exchange_order(Request $request)
    {
        //todo
        return 1;
        return json_encode($request->all());
        return json_encode($request->get('contests_ids'));
        $contests = DB::table('contests')->whereIn('id', $request->get('contests_ids'));
    }

    //竞赛所在的类别内部，使该竞赛置顶，只需修改order字段
    public function order_to_top($request)
    {

    }
}
