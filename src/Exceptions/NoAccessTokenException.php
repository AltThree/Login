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

namespace AltThree\Login\Exceptions;

use InvalidArgumentException;

/**
 * This is the no access token exception class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class NoAccessTokenException extends InvalidArgumentException implements LoginExceptionInterface
{
    //
}
