<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\ApiResource\Auth;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Dto\UserResponse;
use ApiPlatform\OpenApi\Model\Operation;


#[ApiResource(
    shortName: 'Auth',
    operations: [
        new Get(
            uriTemplate: '/auth/me',
            security: 'is_granted("ROLE_USER")',
            controller: 'App\Controller\AuthController::getCurrentUser',
            name: 'api_auth_me',
            output: UserResponse::class,
            openapi: new Operation(
                summary: 'Get current authenticated user',
                description: 'Retrieves the details of the currently authenticated user. This operation requires a valid JWT token in the Authorization header. The response includes user information such as email, roles, and other profile details.',
            )
        )
    ]
)]
class AuthProfile extends UserResponse
{
}
