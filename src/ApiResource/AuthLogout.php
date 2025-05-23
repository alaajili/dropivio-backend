<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;

#[ApiResource(
    shortName: 'Auth',
    operations: [
        new Post(
            uriTemplate: '/auth/logout',
            security: 'is_granted("ROLE_USER")',
            controller: 'App\Controller\AuthController::logout',
            name: 'Logout',
            description: 'Logout the current authenticated user',
        )
    ]
)]
class AuthLogout
{
}
