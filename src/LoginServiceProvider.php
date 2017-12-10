<?php

declare(strict_types=1);

/*
 * This file is part of Alt Three Login.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AltThree\Login;

use GrahamCampbell\GuzzleFactory\GuzzleFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

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

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('login.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('login');
        }

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
        $this->app->singleton('login.provider', function (Container $app) {
            $request = $app['request'];
            $clientId = $app->config->get('login.id');
            $clientSecret = $app->config->get('login.secret');
            $redirectUrl = $app->config->get('login.redirect');
            $allowed = $app->config->get('login.allowed', []);
            $blocked = $app->config->get('login.blocked', []);
            $client = GuzzleFactory::make();

            $provider = new LoginProvider($request, $clientId, $clientSecret, $redirectUrl, $allowed, $blocked, $client);
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
