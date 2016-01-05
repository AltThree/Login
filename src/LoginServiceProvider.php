<?php

/*
 * This file is part of Alt Three Login.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AltThree\Login;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * This is the login service provider class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class LoginServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
    }

    /**
     * Setup the config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../config/login.php');

        $this->publishes([$source => config_path('login.php')]);

        $this->mergeConfigFrom($source, 'login');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerLoginProvider();
    }

    /**
     * Register the login provider class.
     *
     * @return void
     */
    protected function registerLoginProvider()
    {
        $this->app->singleton('login.provider', function (Application $app) {
            $request = $app['request'];
            $clientId = $app->config->get('login.id');
            $clientSecret = $app->config->get('login.secret');
            $redirectUrl = $app->config->get('login.redirect');

            $provider = new LoginProvider($request, $clientId, $clientSecret, $redirectUrl);
            $app->refresh('request', $provider, 'setRequest');

            return $provider;
        });

        $this->app->alias('login.provider', LoginProvider::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'login.provider',
        ];
    }
}
