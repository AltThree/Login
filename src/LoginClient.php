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

use AltThree\Login\Exceptions\InvalidStateException;
use AltThree\Login\Exceptions\IsBlacklistedException;
use AltThree\Login\Exceptions\NoAccessTokenException;
use AltThree\Login\Exceptions\NotWhitelistedException;
use AltThree\Login\Models\Config;
use AltThree\Login\Models\Token;
use AltThree\Login\Providers\ProviderInterface;
use Exception;
use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

/**
 * This is the login client class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class LoginClient
{
    /**
     * The underlying provider.
     *
     * @var \AltThree\Login\Providers\ProviderInterface
     */
    protected $provider;

    /**
     * The provider config.
     *
     * @var \AltThree\Login\Models\Config
     */
    protected $config;

    /**
     * The guzzle http client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * Create a new login client instance.
     *
     * @param \AltThree\Login\Providers\ProviderInterface $provider
     * @param \AltThree\Login\Models\Config               $config
     * @param \GuzzleHttp\ClientInterface                 $client
     *
     * @return void
     */
    public function __construct(ProviderInterface $provider, Config $config, ClientInterface $client)
    {
        $this->provider = $provider;
        $this->config = $config;
        $this->client = $client;
    }

    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @param \Illuminate\Contracts\Session\Session $session
     * @param string[]|null                         $scopes
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(Session $session, array $scopes = null)
    {
        $state = Str::random(40);

        $session->put('state', $state);

        return new RedirectResponse($this->buildAuthUrlFromBase($this->provider->getRedirectUrl(), $state, $scopes));
    }

    /**
     * Get the authentication url for the provider.
     *
     * @param string        $url
     * @param string        $state
     * @param string[]|null $scopes
     *
     * @return string
     */
    protected function buildAuthUrlFromBase(string $url, string $state, array $scopes = null)
    {
        $query = [
            'client_id'     => $this->config->clientId,
            'redirect_uri'  => $this->config->redirectUrl,
            'response_type' => 'code',
            'state'         => $state,
        ];

        if ($scopes !== null) {
            $query['scope'] = implode(',', $scopes);
        }

        return $url.'?'.http_build_query($query, '', '&');
    }

    /**
     * Get the authenticated user's details.
     *
     * @param \Illuminate\Contracts\Session\Session $session
     * @param string                                $state
     * @param string                                $code
     *
     * @throws \AltThree\Login\Exceptions\CannotAccessEmailsException
     * @throws \AltThree\Login\Exceptions\InvalidEmailException
     * @throws \AltThree\Login\Exceptions\InvalidStateException
     * @throws \AltThree\Login\Exceptions\IsBlacklistedException
     * @throws \AltThree\Login\Exceptions\NoAccessTokenException
     * @throws \AltThree\Login\Exceptions\NoEmailException
     * @throws \AltThree\Login\Exceptions\NotWhitelistedException
     *
     * @return \AltThree\Login\Models\User
     */
    public function user(Session $session, string $state, string $code)
    {
        $sessionState = (string) $session->pull('state');

        // checking the session state is a sanity check to ensure we don't end
        // up matching the empty string against the empty string due to expiry
        if (strlen($sessionState) !== 40 || !hash_equals($sessionState, $state)) {
            throw new InvalidStateException('We could not verify the request was genuine.');
        }

        // get the user model from the underlying provider
        return $this->getUserByToken($this->getToken($code));
    }

    /**
     * Get a new token, given an authorization code.
     *
     * @param string $code
     *
     * @throws \AltThree\Login\Exceptions\NoAccessTokenException
     *
     * @return \AltThree\Login\Models\Token
     */
    public function getToken(string $code)
    {
        return $this->requestToken([
            'code'         => $code,
            'grant_type'   => 'authorization_code',
            'redirect_uri' => $this->config->redirectUrl,
        ]);
    }

    /**
     * Get a new token, given a refresh token.
     *
     * @param string $refresh
     *
     * @throws \AltThree\Login\Exceptions\NoAccessTokenException
     *
     * @return \AltThree\Login\Models\Token
     */
    public function refreshToken(string $refresh)
    {
        return $this->requestToken([
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh,
        ]);
    }

    /**
     * Request the token for the given params.
     *
     * @param array $params
     *
     * @throws \AltThree\Login\Exceptions\NoAccessTokenException
     *
     * @return \AltThree\Login\Models\Token
     */
    protected function requestToken(array $params)
    {
        $client = [
            'client_id'     => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
        ];

        try {
            $response = $this->client->post($this->provider->getTokenUrl(), [
                'headers'     => ['Accept' => 'application/json'],
                'form_params' => array_merge($client, $params),
            ]);
        } catch (Exception $e) {
            throw new NoAccessTokenException('We were unable to retrieve your access token.', $e->getCode(), $e);
        }

        $data = (array) json_decode((string) $response->getBody(), true);

        // ensure that a bearer access token was returned
        if (!isset($data['access_token']) || !isset($data['token_type']) || $data['token_type'] !== 'bearer') {
            throw new NoAccessTokenException('No access token was provided.');
        }

        return new Token($data['access_token'], $data['refresh_token'] ?? null, $data['expires_in'] ?? null);
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param \AltThree\Login\Models\Token $token
     *
     * @throws \AltThree\Login\Exceptions\CannotAccessEmailsException
     * @throws \AltThree\Login\Exceptions\InvalidEmailException
     * @throws \AltThree\Login\Exceptions\IsBlacklistedException
     * @throws \AltThree\Login\Exceptions\NoEmailException
     * @throws \AltThree\Login\Exceptions\NotWhitelistedException
     *
     * @return \AltThree\Login\Models\User
     */
    protected function getUserByToken(Token $token)
    {
        return $this->provider->getUserByToken($this->client, $token, function (int $id) {
            if ($this->config->allowed && !in_array($id, $this->config->allowed)) {
                throw new NotWhitelistedException("The user {$id} is not whitelisted.");
            }

            if (in_array($id, $this->config->blocked)) {
                throw new IsBlacklistedException("The user {$id} is blacklisted.");
            }
        });
    }
}
