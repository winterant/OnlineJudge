<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'username';
    }

    public function showLoginForm()
    {
        session(['url.intended'=>url()->previous()]); //登录后跳转回上一页
        return view('auth.login');
    }

    /**
     * Validate the user login request.
     * 验证用户登陆信息
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        if(get_setting("login_reg_captcha")){
            $request->validate([
                $this->username() => 'required|string',
                'password' => 'required|string',
                'captcha' => ['required', 'captcha'], // 验证码
            ],[
                'captcha.required' => '请输入验证码',
                'captcha.captcha'  => '验证码错误! 请重新输入验证码'
            ]);
        }else{
            $request->validate([
                $this->username() => 'required|string',
                'password' => 'required|string'
            ]);
        }
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        return $this->loggedOut($request) ?: redirect(url()->previous()); //登出后返回上一页
    }
}
