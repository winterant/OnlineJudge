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
        'username', 'email', 'password', 'school', 'class', 'nick',
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

    public function privilege($power)
    {
        //teacher涵盖以下权限；即 只要数据库中查询到teacher权限，则该用户拥有以下权限
        if (in_array($power, ['admin_home', 'problem_list', 'problem_edit', 'problem_data', 'problem_tag', 'problem_rejudge', 'contest']))
            $power = array_merge((array)$power, ['teacher']);

        //admin涵盖所有权限
        $power = array_merge((array)$power, ['admin']);

        //查询该用户的权限
        if (DB::table('privileges')->where('user_id', $this->id)->whereIn('authority', $power)->exists()) {
            return true;
        } else {
            return false;
        }
    }
}
