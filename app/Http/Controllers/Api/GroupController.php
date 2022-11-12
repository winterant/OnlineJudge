<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    // 当前用户申请加入群组
    public function join($group_id)
    {
        DB::table('group_users')->updateOrInsert(['group_id' => $group_id, 'user_id' => Auth::id()], ['identity' => 1]);
        return back();
    }
}
