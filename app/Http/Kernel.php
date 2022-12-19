<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * 以下中间件将在收到请求之后，路由匹配之前执行
     *
     * @var array
     */
    protected $middleware = [
        // ===================== 以下是laravel官方提供的中间件 =======================
        // https://learnku.com/docs/the-laravel-way/5.6/Tao-5-6/2947
        // 根据项目需求，它们并不是必要的，因此取消它们以提高请求处理效率
        // \App\Http\Middleware\TrustProxies::class,  // 设置信任代理(不妨在nginx中设置)
        // \App\Http\Middleware\CheckForMaintenanceMode::class,  // 检查laravel是否处于维护状态(php artisan down) 没必要
        // \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,  // 验证post请求大小是否超出限制(php-fpm也会限制) 没必要
        // \App\Http\Middleware\TrimStrings::class,  // 删出请求中字符串两端的空白字符 没必要
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class, // '' to null
    ];

    /**
     * The application's route middleware groups.
     *
     * 以下中间件将在路由匹配之后执行
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \App\Http\Middleware\VerifyCsrfToken::class,  // Csrf攻击验证(掩耳盗铃，没必要)
            // \Illuminate\Routing\Middleware\SubstituteBindings::class,  // 根据请求参数绑定对应数据模型(本项目没使用ORM，故没用)

            \App\Http\Middleware\SetGlobalVariable::class,
            \App\Http\Middleware\CheckUserLocked::class,
            \App\Http\Middleware\CheckFormDecode::class,
        ],

        'api' => [
            'throttle:60,1',  // 限制请求次数；60,1 表示60次/分钟
            // 'bindings',
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,

            \App\Http\Middleware\SetGlobalVariable::class,
            \App\Http\Middleware\CheckUserLocked::class,
            \App\Http\Middleware\CheckFormDecode::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * 以下中间件在路由定义时手动调用
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // 自定义路由中间件
        'Permission' => \App\Http\Middleware\Permission::class,
        // 'CheckUserLocked' => \App\Http\Middleware\CheckUserLocked::class,
        'CheckContest' => \App\Http\Middleware\CheckContest::class,
        'CheckGroup' => \App\Http\Middleware\CheckGroup::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * 以下是自定义中间件的优先级，即以上所有中间件必然遵守的执行顺序
     *
     * @var array
     */
    protected $middlewarePriority = [
        // 业务需要，优先解密cookie以及设定全局配置
        \App\Http\Middleware\EncryptCookies::class,
        \App\Http\Middleware\SetGlobalVariable::class,
        \App\Http\Middleware\Permission::class, // 权限中间件需要在它之前执行：\App\Http\Middleware\Authenticate::class

        // laravel 默认中间件顺序
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
