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

use GrahamCampbell\Manager\AbstractManager;
use Illuminate\Contracts\Config\Repository;

/**
 * This is the login manager class.
 *
 * @method \Illuminate\Http\RedirectResponse redirect(\Illuminate\Contracts\Session\Session $session, array $scopes = null)
 * @method \AltThree\Login\Models\User user(\Illuminate\Contracts\Session\Session $session, string $state, string $code)
 * @method \AltThree\Login\Models\Token getToken(string $code)
 * @method \AltThree\Login\Models\Token refreshToken(string $refresh)
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class LoginManager extends AbstractManager
{
    /**
     * The factory instance.
     *
     * @var \AltThree\Login\LoginFactory
     */
    protected $factory;

    /**
     * Create a new login manager instance.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \AltThree\Login\LoginFactory            $factory
     *
     * @return void
     */
    public function __construct(Repository $config, LoginFactory $factory)
    {
        parent::__construct($config);
        $this->factory = $factory;
    }

    /**
     * Create the connection instance.
     *
     * @param array $config
     *
     * @return \AltThree\Login\LoginClient
     */
    protected function createConnection(array $config)
    {
        return $this->factory->make($config);
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName()
    {
        return 'login';
    }

    /**
     * Get the factory instance.
     *
     * @return \AltThree\Login\LoginFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }
}
