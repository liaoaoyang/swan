<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Request::setTrustedProxies(explode(',', env('SWAN_TRUST_PROXIES', '192.168.0.0/16')));
        //
        \URL::forceScheme(env('SWAN_ADMIN_HTTPS', false) ? 'https' : 'http');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
