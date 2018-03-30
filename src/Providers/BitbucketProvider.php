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
use AltThree\Login\Models\Metadata;
use AltThree\Login\Models\Token;
use AltThree\Login\Models\User;
use AltThree\Uuid\UuidConverter;
use Exception;
use GuzzleHttp\ClientInterface;

/**
 * This is the bitbucket provider interface.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class BitbucketProvider implements ProviderInterface
{
    /**
     * Get the authentication provider's redirect url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return 'https://bitbucket.org/site/oauth2/authorize';
    }

    /**
     * Get the authentication provider's token url.
     *
     * @return string
     */
    public function getTokenUrl()
    {
        return 'https://bitbucket.org/site/oauth2/access_token';
    }

    /**
     * Get the raw user for the given token.
     *
     * @param \GuzzleHttp\ClientInterface  $client
     * @param \AltThree\Login\Models\Token $token
     * @param callable                     $validator
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
    public function getUserByToken(ClientInterface $client, Token $token, callable $validator)
    {
        try {
            $response = $client->get(
                'https://api.bitbucket.org/2.0/user?access_token='.$token->access,
                ['headers' => ['Accept' => 'application/json']]
            );
        } catch (Exception $e) {
            throw new NoAccessTokenException('The provided access token was not valid.', $e->getCode(), $e);
        }

        $user = (array) json_decode((string) $response->getBody(), true);

        $id = UuidConverter::convert($user['uuid']);

        $validator($id);

        $metadata = new Metadata(static::getEmail($client, $token), $user['username'], $user['display_name'] ?? null);

        return new User($id, $token, $metadata);
    }

    /**
     * Get email address for the given token.
     *
     * @param \GuzzleHttp\ClientInterface  $client
     * @param \AltThree\Login\Models\Token $token
     *
     * @throws \AltThree\Login\Exceptions\CannotAccessEmailsException
     * @throws \AltThree\Login\Exceptions\InvalidEmailException
     * @throws \AltThree\Login\Exceptions\NoEmailException
     *
     * @return string
     */
    protected static function getEmail(ClientInterface $client, Token $token)
    {
        try {
            $response = $client->get(
                'https://bitbucket.org/api/2.0/user/emails?pagelen=100&access_token='.$token->access,
                ['headers' => ['Accept' => 'application/json']]
            );

            $emails = (array) json_decode((string) $response->getBody(), true);
        } catch (Exception $e) {
            throw new CannotAccessEmailsException('Unable to access the user\'s email addresses.', $e->getCode(), $e);
        }

        foreach ($emails['values'] as $email) {
            if ($email['is_primary'] && $email['is_confirmed'] && strpos($email['email'], '@') !== false) {
                if (strpos($email['email'], 'noreply') !== false) {
                    throw new InvalidEmailException('Unable to use a no reply primary email address.');
                }

                return $email['email'];
            }
        }

        throw new NoEmailException('Unable to find verified primary email address.');
    }
}
