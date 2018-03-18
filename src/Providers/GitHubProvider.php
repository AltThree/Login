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

use AltThree\Login\Exceptions\CannotAccessEmailsException;
use AltThree\Login\Exceptions\InvalidEmailException;
use AltThree\Login\Exceptions\NoAccessTokenException;
use AltThree\Login\Exceptions\NoEmailException;
use AltThree\Login\Models\User;
use Exception;
use GuzzleHttp\ClientInterface;

/**
 * This is the github provider interface.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class GitHubProvider implements ProviderInterface
{
    /**
     * Get the authentication provider's redirect url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return 'https://github.com/login/oauth/authorize';
    }

    /**
     * Get the authentication provider's token url.
     *
     * @return string
     */
    public function getTokenUrl()
    {
        return 'https://github.com/login/oauth/access_token';
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
                'https://api.github.com/user?access_token='.$token,
                ['headers' => ['Accept' => 'application/vnd.github.v3+json']]
            );
        } catch (Exception $e) {
            throw new NoAccessTokenException('The provided access token was not valid.', $e->getCode(), $e);
        }

        $user = (array) json_decode((string) $response->getBody(), true);

        $validator($user['id']);

        return new User($user['id'], $token, static::getEmail($client, $token), $user['login'], $user['name'] ?? null);
    }

    /**
     * Get email address for the given access token.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param string                      $token
     *
     * @throws \AltThree\Login\Exceptions\CannotAccessEmailsException
     * @throws \AltThree\Login\Exceptions\InvalidEmailException
     * @throws \AltThree\Login\Exceptions\NoEmailException
     *
     * @return string
     */
    protected static function getEmail(ClientInterface $client, string $token)
    {
        try {
            $response = $client->get(
                'https://api.github.com/user/emails?access_token='.$token,
                ['headers' => ['Accept' => 'application/vnd.github.v3+json']]
            );

            $emails = (array) json_decode((string) $response->getBody(), true);
        } catch (Exception $e) {
            throw new CannotAccessEmailsException('Unable to access the user\'s email addresses.', $e->getCode(), $e);
        }

        foreach ($emails as $email) {
            if ($email['primary'] && $email['verified']) {
                if (strpos($email['email'], '@') !== false) {
                    if (strpos($email['email'], 'noreply') !== false) {
                        throw new InvalidEmailException('Unable to use a no reply primary email address.');
                    }

                    return $email['email'];
                }
            }
        }

        throw new NoEmailException('Unable to find verified primary email address.');
    }
}
