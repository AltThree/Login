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

use AltThree\Login\Exceptions\InvalidEmailException;
use AltThree\Login\Exceptions\NoAccessTokenException;
use AltThree\Login\Exceptions\NoEmailException;
use AltThree\Login\Models\User;
use GuzzleHttp\ClientInterface;

/**
 * This is the gitlab provider interface.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class GitLabProvider implements ProviderInterface
{
    /**
     * Get the authentication provider's redirect url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return 'https://gitlab.com/oauth/authorize';
    }

    /**
     * Get the authentication provider's token url.
     *
     * @return string
     */
    public function getTokenUrl()
    {
        return 'http://gitlab.com/oauth/token';
    }

    /**
     * Get any extra provider token params.
     *
     * @return string[]
     */
    public function getExtraTokenParams()
    {
        return ['grant_type' => 'authorization_code'];
    }

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
    public function getUserByToken(ClientInterface $client, string $token, callable $validator)
    {
        try {
            $response = $client->get(
                'https://gitlab.com/api/v4/user',
                ['headers' => ['Accept' => 'application/json', 'Authorization' => "Bearer {$token}"]]
            );
        } catch (Exception $e) {
            throw new NoAccessTokenException('The provided access token was not valid.', $e->getCode(), $e);
        }

        $user = (array) json_decode((string) $response->getBody(), true);

        $validator($user['id']);

        return new User($user['id'], $token, static::getEmail($user), $user['username'], $user['name'] ?? null);
    }

    /**
     * Extract the user's email address if valid.
     *
     * @param array $user
     *
     * @throws \AltThree\Login\Exceptions\NoEmailException
     * @throws \AltThree\Login\Exceptions\InvalidEmailException
     *
     * @return string
     */
    protected static function getEmail(array $user)
    {
        if (!isset($user['confirmed_at']) || !isset($user['email']) || strpos($user['email'], '@') === false) {
            throw new NoEmailException('Unable to find verified primary email address.');
        }

        if (strpos($user['email'], 'noreply') !== false) {
            throw new InvalidEmailException('Unable to use a no reply primary email address.');
        }

        return $user['email'];
    }
}
