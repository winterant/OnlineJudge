<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function teams(Request $request)
    {
        $msg = '这是一个正在开发中的功能，“团队”，也可称为“班级”，支持将一批用户组成一个团队，
            以便在创建竞赛、课程时指定可参与团队（即团队内用户均获得参赛权限）。';
        return view('client.success', compact('msg'));
    }
}
