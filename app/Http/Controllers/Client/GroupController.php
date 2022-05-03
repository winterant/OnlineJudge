<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GroupController extends Controller
{
    public function groups(){
        return redirect(route('groups.my'));
    }
    public function mygroups(){
        $groups = DB::table('groups as g')
            ->leftJoin('users as u', 'u.id', '=', 'g.creator')
            ->join('group_users as gu','gu.group_id', '=', 'g.id')
            ->select('g.*', 'u.username')
            
            ->orderByDesc('id')
            ->paginate(isset($_GET['perPage'])?$_GET['perPage']:12);
        return view('group.groups', compact('groups'));
    }
    public function allgroups(){
        $groups = DB::table('groups as g')
            ->leftJoin('users as u', 'u.id', '=', 'g.creator')
            ->select('g.*', 'u.username')
            ->when(!privilege(Auth::user(), 'teacher'), function($q){
                return $q->where('hidden', 0);
            })

            ->orderByDesc('id')
            ->paginate(isset($_GET['perPage'])?$_GET['perPage']:12);
        return view('group.groups', compact('groups'));
    }

    public function home(Request $request, $id){
        return $id.'开发中，请耐心等候~';
    }
}
