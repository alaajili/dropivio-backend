<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Dto\UserResponse;

#[ApiResource(
    shortName: 'Auth',
    operations: [
        new Get(
            uriTemplate: '/auth/me',
            security: 'is_granted("ROLE_USER")',
            controller: 'App\Controller\AuthController::getCurrentUser',
            description: 'Get the current authenticated user',
            output: UserResponse::class,
        )
    ]
)]
class AuthProfile extends UserResponse
{
}
