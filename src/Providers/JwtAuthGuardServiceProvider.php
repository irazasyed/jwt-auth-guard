<?php

namespace Irazasyed\JwtAuthGuard\Providers;

use Irazasyed\JwtAuthGuard\JwtAuthGuard;
use Illuminate\Support\ServiceProvider;

class JwtAuthGuardServiceProvider extends ServiceProvider
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
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->extend('jwt', function ($app, $name, array $config) {
            return new JwtAuthGuard($app['auth']->createUserProvider($config['provider']));
        });
    }
}
