<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        if(get_setting('login_reg_captcha')){
            return Validator::make($data, [
                'username' => ['required', 'string','max:30','min:4','unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'email' => ['max:255'],
                'captcha' => ['required', 'captcha'], // 验证码
            ],[
                'captcha.required' => '请输入验证码',
                'captcha.captcha'  => '验证码错误! 请重新输入验证码'
            ]);
        }else{
            return Validator::make($data, [
                'username' => ['required', 'string','max:30','min:4','unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'email' => ['max:255']
            ]);
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user=User::create([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'email' => $data['email'],
            'school'   => $data['school'],
            'class'   => $data['class'],
            'nick'   => $data['nick'],
        ]);
        if($data['username']=='admin')//默认管理员
            DB::table('privileges')->insert(['user_id'=>$user->getAttributes()['id'],'authority'=>'admin']);
        return $user;
    }
}
