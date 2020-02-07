<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function users(Request $request){
        $users=DB::table('users')->select(['id','username','email','nick','school','class','revise','created_at'])
            ->orderBy('id')->paginate(20);
        return view('admin.users',compact('users'));
    }

    public function change_revise_to(Request $request){
        if($request->ajax()){
            $uids=$request->input('uids')?:[];
            $revise=$request->input('revise');
            return DB::table('users')->whereIn('id',$uids)->update(['revise'=>$revise]);
        }
        return 0;
    }
}
