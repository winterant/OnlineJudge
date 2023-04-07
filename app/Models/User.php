<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password', 'school', 'class', 'nick', 'api_token' // 注册时需要填充api_token
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * 获取该用户的api_token
     */
    // public function get_api_token(bool $regenerate = false)
    // {
    //     $redis_key = 'user:' . $this->id . ':api_token';
    //     if ($regenerate)
    //         Cache::forget($redis_key);
    //     return Cache::remember($redis_key, 3600 * 24 * 30, function () {
    //         DB::table('users')->where('id', $this->id)
    //             ->update(['api_token' => hash('sha256', $api_token = Str::random(64))]); // hash 64 bits
    //         return $api_token;
    //     });
    // }


    /**
     * 判断群组管理者
     */
    public function has_group_permission($group, $permission = null, $or_identity_manager = true)
    {
        if ($group == null)
            return false; // 群组不存在，所以没有权限
        if ($permission != null && $this->can($permission)) // 用户拥有某具体权限，则直接通过
            return true;
        if ($group->creator == $this->id) // 当前用户是创建者，直接通过
            return true;
        if (
            $or_identity_manager &&
            DB::table('group_users')->where('group_id', $group->id)
            ->where('user_id', $this->id)->where('identity', 4)->exists()
        )
            return true; // 当前用户是该群组的一位管理员
        return false;
    }
}
