<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContestController extends Controller
{
    public function list(){
        $contests=DB::table('contests')
            ->leftJoin('users','users.id','=','user_id')
            ->select('contests.*','username')
            ->orderBy('id')->paginate();
        return view('admin.contest.list',compact('contests'));
    }

    public function add(){
        //todo
    }

    public function update(Request $request,$id=-1){
        //todo
    }

    public function delete(){
        //todo
    }

    public function update_hidden(){
        //todo
    }
}
