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
        'username', 'email', 'password',
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

    public function is_admin(){
        //判断是否有管理员权限
        if(DB::table('privileges')->where('user_id',$this->id)
            ->where('authority','admin')->exists()){
            return true;
        }else if($this->username == 'admin'){
            //就算admin误删了自己的权限，该处也会将其恢复
            DB::table('privileges')->insert(['user_id'=>$this->id,'authority'=>'admin']);
            return true;
        }else{
            return false;
        }
    }
    public function privilege($power){
        //判断用户是否具有某项权限, admin一定有权
        if(DB::table('privileges')->where('user_id',$this->id)
            ->whereIn('authority',[$power,'admin'])->exists()){
            return true;
        }else{
            return false;
        }
    }
}
