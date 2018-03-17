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

namespace AltThree\Login\Models;

/**
 * This is the config model class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
final class Config
{
    /**
     * The client id.
     *
     * @var string
     */
    public $clientId;

    /**
     * The client secret.
     *
     * @var string
     */
    public $clientSecret;

    /**
     * The redirect url.
     *
     * @var string
     */
    public $redirectUrl;

    /**
     * The allowed user ids.
     *
     * @var int[]
     */
    public $allowed;

    /**
     * The blocked user ids.
     *
     * @var int[]
     */
    public $blocked;

    /**
     * Create a new config model instance.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     * @param int[]  $allowed
     * @param int[]  $blocked
     *
     * @return void
     */
    public function __construct(string $clientId, string $clientSecret, string $redirectUrl, array $allowed = [], array $blocked = [])
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->allowed = $allowed;
        $this->blocked = $blocked;
    }
}
