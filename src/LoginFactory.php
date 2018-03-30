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

use AltThree\Login\Models\Config;
use AltThree\Login\Providers\BitbucketProvider;
use AltThree\Login\Providers\GitHubProvider;
use AltThree\Login\Providers\GitLabProvider;
use GrahamCampbell\GuzzleFactory\GuzzleFactory;
use InvalidArgumentException;

/**
 * This is the login factory class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class LoginFactory
{
    /**
     * Make a new login client.
     *
     * @param string[] $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \AltThree\Login\LoginClient
     */
    public function make(array $config)
    {
        return new LoginClient(static::getProvider($config), static::getConfig($config), GuzzleFactory::make());
    }

    /**
     * Get the login provider.
     *
     * @param string[] $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \AltThree\Login\Providers\ProviderInterface
     */
    protected static function getProvider(array $config)
    {
        switch ($config['provider'] ?? null) {
            case 'github':
                return new GitHubProvider();
            case 'gitlab':
                return new GitLabProvider();
            case 'bitbucket':
                return new BitbucketProvider();
        }

        throw new InvalidArgumentException('The login factory requires a valid provider.');
    }

    /**
     * Get the login config.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \AltThree\Login\Models\Config
     */
    protected static function getConfig(array $config)
    {
        if (!array_key_exists('id', $config)) {
            throw new InvalidArgumentException('The login factory requires a client id.');
        }

        if (!array_key_exists('secret', $config)) {
            throw new InvalidArgumentException('The login factory requires a client secret.');
        }

        if (!array_key_exists('redirect', $config)) {
            throw new InvalidArgumentException('The login factory requires a redirection url.');
        }

        return new Config($config['id'], $config['secret'], $config['redirect'], $config['allowed'] ?? [], $config['blocked'] ?? []);
    }
}
