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

namespace AltThree\Tests\Login;

use AltThree\Login\LoginClient;
use AltThree\Login\LoginFactory;
use AltThree\Login\LoginManager;
use AltThree\Login\LoginServiceProvider;
use GrahamCampbell\TestBench\AbstractPackageTestCase;
use GrahamCampbell\TestBenchCore\ServiceProviderTrait;

/**
 * This is the service provider test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class ServiceProviderTest extends AbstractPackageTestCase
{
    use ServiceProviderTrait;

    protected function getServiceProviderClass($app)
    {
        return LoginServiceProvider::class;
    }

    public function testLoginFactoryIsInjectable()
    {
        $this->assertIsInjectable(LoginFactory::class);
    }

    public function testLoginManagerIsInjectable()
    {
        $this->assertIsInjectable(LoginManager::class);
    }

    public function testBindings()
    {
        $this->assertIsInjectable(LoginClient::class);

        $original = $this->app['login.connection'];
        $this->app['login']->reconnect();
        $new = $this->app['login.connection'];

        $this->assertNotSame($original, $new);
        $this->assertEquals($original, $new);
    }
}
