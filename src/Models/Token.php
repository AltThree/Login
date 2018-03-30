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
 * This is the token model class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
final class Token
{
    /**
     * The required access token.
     *
     * @param string
     */
    public $access;

    /**
     * The optional refresh token.
     *
     * @param string|null
     */
    public $refresh;

    /**
     * The access token lifetime in seconds.
     *
     * @param int|null
     */
    public $lifetime;

    /**
     * Create a new token model instance.
     *
     * @param string      $access
     * @param string|null $refresh
     * @param int|null    $lifetime
     *
     * @return void
     */
    public function __construct(string $access, string $refresh = null, int $lifetime = null)
    {
        $this->access = $access;
        $this->refresh = $refresh;
        $this->lifetime = $lifetime;
    }
}
