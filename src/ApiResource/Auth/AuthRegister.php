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
use App\Dto\RegisterRequest;
use App\Dto\UserResponse;
use ApiPlatform\OpenApi\Model\Operation;


#[ApiResource(
    shortName: 'Auth',
    operations: [
        new Post(
            uriTemplate: '/auth/register',
            controller: 'App\Controller\AuthController::register',
            name: 'api_auth_register',
            input: RegisterRequest::class,
            output: UserResponse::class,
            openapi: new Operation(
                summary: 'Register a new user',
                description: 'Creates a new user account with the provided registration details. The request should include the user\'s email, password, and any other required fields. If successful, it returns the newly created user\'s details along with a JWT token for authentication.',
            )
        )
    ]
)]
class AuthRegister extends RegisterRequest
{
}
