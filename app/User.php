<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password','school','class','nick',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function privilege($power){
        //如果查询的$power是以下之一，则视为老师，只查询数据库中当前账户是不是老师
        //这样做的原因是后续开发要求增加teacher这个权限，并涵盖题目的增改、竞赛管理等。
        //为了兼容以前的版本，尽量少改动原来的代码，在这里统一把这些权限视为teacher
        if(in_array($power, ['problem_list','edit_problem','problem_data','problem_tag','problem_rejudge','contest']))
            $power=array_merge((array)$power,['teacher']);

        //判断用户是否具有某项权限, admin一定有权
        if(DB::table('privileges')->where('user_id',$this->id)
            ->whereIn('authority',array_merge((array)$power,['admin']))->exists()){
            return true;
        }else{
            return false;
        }
    }
}
