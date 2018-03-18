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

namespace AltThree\Login\Providers;

use GuzzleHttp\ClientInterface;

/**
 * This is the login provider interface.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
interface ProviderInterface
{
    /**
     * Get the authentication provider's redirect url.
     *
     * @return string
     */
    public function getRedirectUrl();

    /**
     * Get the authentication provider's token url.
     *
     * @return string
     */
    public function getTokenUrl();

    /**
     * Get the raw user for the given access token.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param string                      $token
     * @param callable                    $validator
     *
     * @throws \AltThree\Login\Exceptions\CannotAccessEmailsException
     * @throws \AltThree\Login\Exceptions\InvalidEmailException
     * @throws \AltThree\Login\Exceptions\IsBlacklistedException
     * @throws \AltThree\Login\Exceptions\NoAccessTokenException
     * @throws \AltThree\Login\Exceptions\NoEmailException
     * @throws \AltThree\Login\Exceptions\NotWhitelistedException
     *
     * @return \AltThree\Login\Models\User
     */
    public function getUserByToken(ClientInterface $client, string $token, callable $validator);
}
