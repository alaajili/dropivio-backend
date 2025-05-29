<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);


namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\LoginRequest;
use App\Dto\UserResponse;
use ApiPlatform\OpenApi\Model\Operation;

#[ApiResource(
    shortName: 'Auth',
    operations: [
        new Post(
            uriTemplate: '/auth/login',
            controller: 'App\Controller\AuthController::login',
            name: 'api_auth_login',
            input: LoginRequest::class,
            output: UserResponse::class,
            openapi: new Operation(
                summary: 'User Login',
                description: 'Authenticates a user and returns a JWT token for subsequent requests. The request should include the user\'s email and password. If the credentials are valid, a JWT token is returned, which can be used for authenticated requests.',
            )
        )
    ]
)]
class AuthLogin extends LoginRequest
{
}
