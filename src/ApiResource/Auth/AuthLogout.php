<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\ApiResource\Auth;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;

#[ApiResource(
    shortName: 'Auth',
    operations: [
        new Post(
            uriTemplate: '/auth/logout',
            security: 'is_granted("ROLE_USER")',
            controller: 'App\Controller\AuthController::logout',
            name: 'api_auth_logout',
            openapi: new Operation(
                summary: 'User Logout',
                description: 'Logs out the user by invalidating their JWT token. This operation does not require any input and will remove the token from the server-side session or blacklist it.'
            )
        )
    ]
)]
class AuthLogout
{
}
