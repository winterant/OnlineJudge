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
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrimStrings::class
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

            \App\Http\Middleware\SetGlobalVariable::class,
            \App\Http\Middleware\CheckUserLocked::class,
            \App\Http\Middleware\DecodeValue::class,
        ],

        'api' => [
            'throttle:60,1',  // 限制请求次数；60,1 表示60次/分钟

            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            // 允许api使用session来获取用户。session令牌可以通过cookie获得。
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,

            \App\Http\Middleware\SetGlobalVariable::class,
            \App\Http\Middleware\CheckUserLocked::class,
            \App\Http\Middleware\DecodeValue::class,
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
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        'Permission' => \App\Http\Middleware\Permission::class,
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
        \App\Http\Middleware\EncryptCookies::class,
        \App\Http\Middleware\SetGlobalVariable::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Permission::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \App\Http\Middleware\Authenticate::class, // auth
        \Illuminate\Auth\Middleware\Authorize::class, // can
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ];
}
