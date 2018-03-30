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
     * The user's token.
     *
     * @param \AltThree\Login\Models\Token
     */
    public $token;

    /**
     * The user's metadata.
     *
     * @param \AltThree\Login\Models\Metadata
     */
    public $metadata;

    /**
     * Create a new user model instance.
     *
     * @param int                             $id
     * @param \AltThree\Login\Models\Token    $token
     * @param \AltThree\Login\Models\Metadata $metadata
     *
     * @return void
     */
    public function __construct(int $id, Token $token, Metadata $metadata)
    {
        $this->id = $id;
        $this->token = $token;
        $this->metadata = $metadata;
    }
}
