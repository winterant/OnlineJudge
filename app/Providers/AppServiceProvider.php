<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['request']->server->set('HTTPS', ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null) == 'https' || config('app.HREF_FORCE_HTTPS'));
        Paginator::defaultView('vendor.pagination.bootstrap-4');
    }
}
