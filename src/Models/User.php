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
 * This is the user model class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
final class User
{
    /**
     * The user's id.
     *
     * @param int
     */
    public $id;

    /**
     * The user's access token.
     *
     * @param string
     */
    public $token;

    /**
     * The user's email address.
     *
     * @param string
     */
    public $email;

    /**
     * The user's username.
     *
     * @param string
     */
    public $username;

    /**
     * The user's real name.
     *
     * @param string|null
     */
    public $name;

    /**
     * Create a new user model instance.
     *
     * @param int         $id
     * @param string      $token
     * @param string      $email
     * @param string      $username
     * @param string|null $name
     *
     * @return void
     */
    public function __construct(int $id, string $token, string $email, string $username, string $name = null)
    {
        $this->id = $id;
        $this->token = $token;
        $this->email = $email;
        $this->username = $username;
        $this->name = $name;
    }
}
