<?php

/*
 * This file is part of Alt Three Login.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AltThree\Login;

use AltThree\Login\Exceptions\CannotAccessEmailsException;
use AltThree\Login\Exceptions\InvalidEmailException;
use AltThree\Login\Exceptions\InvalidStateException;
use AltThree\Login\Exceptions\NoAccessTokenException;
use AltThree\Login\Exceptions\NoEmailException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

/**
 * This is the login provider class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class LoginProvider
{
    /**
     * The http request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The client id.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * The redirect url.
     *
     * @var string
     */
    protected $redirectUrl;

    /**
     * The guzzle http client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * Create a new provider instance.
     *
     * @param \Illuminate\Http\Request         $request
     * @param string                           $clientId
     * @param string                           $clientSecret
     * @param string                           $redirectUrl
     * @param \GuzzleHttp\ClientInterface|null $client
     *
     * @return void
     */
    public function __construct(Request $request, $clientId, $clientSecret, $redirectUrl, ClientInterface $client = null)
    {
        $this->request = $request;
        $this->clientId = $clientId;
        $this->redirectUrl = $redirectUrl;
        $this->clientSecret = $clientSecret;
        $this->client = $client ?: new Client();
    }

    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @param string[]|null $scopes
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(array $scopes = null)
    {
        $state = Str::random(40);

        $this->request->getSession()->set('state', $state);

        return new RedirectResponse($this->buildAuthUrlFromBase('https://github.com/login/oauth/authorize', $state, $scopes));
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
    protected function buildAuthUrlFromBase($url, $state, array $scopes = null)
    {
        $query = [
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUrl,
            'state'         => $state,
            'response_type' => 'code',
        ];

        if ($scopes !== null) {
            $query['scope'] = implode(',', $scopes);
        }

        return $url.'?'.http_build_query($query, '', '&');
    }

    /**
     * Get the authenticated user's details.
     *
     * @throws \AltThree\Login\Exceptions\InvalidStateException
     *
     * @return string[]
     */
    public function user()
    {
        $state = $this->request->getSession()->pull('state');

        if (strlen($state) !== 40 || $this->request->input('state') !== $state) {
            throw new InvalidStateException('We could not verify the request was genuine.');
        }

        $token = $this->getAccessToken($this->request->input('code'));

        return $this->getUserByToken($token);
    }

    /**
     * Get the access token for the given code.
     *
     * @param string $code
     *
     * @throws \AltThree\Login\Exceptions\NoAccessTokenException
     *
     * @return string
     */
    protected function getAccessToken($code)
    {
        $data = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'redirect_uri'  => $this->redirectUrl,
        ];

        $key = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';

        $response = $this->client->post('https://github.com/login/oauth/access_token', [
            'headers' => ['Accept' => 'application/json'], $key => $data,
        ]);

        $data = json_decode((string) $response->getBody(), true);

        if (!isset($data['access_token'])) {
            throw new NoAccessTokenException('No access token was provided.');
        }

        return $data['access_token'];
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param string $token
     *
     * @throws \AltThree\Login\Exceptions\CannotAccessEmailsException
     * @throws \AltThree\Login\Exceptions\InvalidEmailException
     * @throws \AltThree\Login\Exceptions\NoEmailException
     *
     * @return string[]
     */
    protected function getUserByToken($token)
    {
        $options = ['headers' => ['Accept' => 'application/vnd.github.v3+json']];

        $response = $this->client->get('https://api.github.com/user?access_token='.$token, $options);

        $user = (array) json_decode((string) $response->getBody(), true);

        return array_merge($user, ['email' => $this->getEmail($token), 'token' => $token]);
    }

    /**
     * Get email address for the given access token.
     *
     * @param string $token
     *
     * @throws \AltThree\Login\Exceptions\CannotAccessEmailsException
     * @throws \AltThree\Login\Exceptions\InvalidEmailException
     * @throws \AltThree\Login\Exceptions\NoEmailException
     *
     * @return string
     */
    protected function getEmail($token)
    {
        try {
            $options = ['headers' => ['Accept' => 'application/vnd.github.v3+json']];
            $response = $this->client->get('https://api.github.com/user/emails?access_token='.$token, $options);
            $emails = (array) json_decode((string) $response->getBody(), true);
        } catch (Exception $e) {
            throw new CannotAccessEmailsException('Unable to access the user\'s email addresses.');
        } catch (Throwable $e) {
            throw new CannotAccessEmailsException('Unable to access the user\'s email addresses.');
        }

        foreach ($emails as $email) {
            if ($email['primary'] && $email['verified']) {
                if (strpos($email['email'], 'noreply') !== false) {
                    throw new InvalidEmailException('Unable to use a no reply primary email address.');
                }

                return $email['email'];
            }
        }

        throw new NoEmailException('Unable to find verified primary email address.');
    }

    /**
     * Set the request instance.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
}
