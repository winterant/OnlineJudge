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
        return view('admin.home');
    }
}
