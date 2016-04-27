<?php

namespace Irazasyed\JwtAuthGuard;

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
        $this->app['auth']->extend('jwt-auth', function ($app, $name, array $config) {
            $guard = new JwtAuthGuard(
                $app['tymon.jwt'],
                $app['auth']->createUserProvider($config['provider']),
                $app['request']
            );

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }
}
