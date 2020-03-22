<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(){
        if (!DB::table('privileges')->where('user_id',Auth::id())->exists())
            abort(404);
        exec('ps -e|grep polling 2>&1',$out,$status);
//        exec('bash '.base_path('judge/startup.sh').' 2>&1',$out,$status);
        dd($out,$status);
        return view('admin.home');
    }
}
