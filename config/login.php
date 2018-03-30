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

return [

    /*
    |--------------------------------------------------------------------------
    | Default Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the connections below you wish to use as
    | your default connection for all work. Of course, you may use many
    | connections at once using the manager class.
    |
    */

    'default' => 'github',

    /*
    |--------------------------------------------------------------------------
    | Login Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the connections setup for your application. Example
    | configuration has been included, but you may add as many connections as
    | you would like. Note that the 2 supported providers are:
    | "github" and "gitlab".
    |
    */

    'connections' => [

        'github' => [
            'provider' => 'github',
            'id'       => 'your-client-id',
            'secret'   => 'your-client-secret',
            'redirect' => 'your-redirection-url',
            // 'allowed'  => [],
            // 'blocked'  => [],
        ],

        'gitlab' => [
            'provider' => 'gitlab',
            'id'       => 'your-client-id',
            'secret'   => 'your-client-secret',
            'redirect' => 'your-redirection-url',
            // 'allowed'  => [],
            // 'blocked'  => [],
        ],

        'bitbucket' => [
            'provider' => 'bitbucket',
            'id'       => 'your-client-id',
            'secret'   => 'your-client-secret',
            'redirect' => 'your-redirection-url',
            // 'allowed'  => [],
            // 'blocked'  => [],
        ],

    ],

];
