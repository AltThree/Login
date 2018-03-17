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
        $source = realpath($raw = __DIR__.'/../config/login.php') ?: $raw;

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
        $this->registerLoginFactory();
        $this->registerManager();
        $this->registerBindings();
    }

    /**
     * Register the login factory class.
     *
     * @return void
     */
    protected function registerLoginFactory()
    {
        $this->app->singleton('login.factory', function () {
            return new LoginFactory();
        });

        $this->app->alias('login.factory', LoginFactory::class);
    }

    /**
     * Register the manager class.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('login', function (Container $app) {
            $config = $app['config'];
            $factory = $app['login.factory'];

            return new LoginManager($config, $factory);
        });

        $this->app->alias('login', LoginManager::class);
    }

    /**
     * Register the bindings.
     *
     * @return void
     */
    protected function registerBindings()
    {
        $this->app->bind('login.connection', function (Container $app) {
            $manager = $app['login'];

            return $manager->connection();
        });

        $this->app->alias('login.connection', LoginClient::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'login.factory',
            'login',
            'login.connection',
        ];
    }
}
