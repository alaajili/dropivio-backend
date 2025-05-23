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

#[ApiResource(
    shortName: 'Auth',
    description: 'Authentication operations',
    operations: [
        new Post(
            uriTemplate: '/auth/login',
            controller: 'App\Controller\AuthController::login',
            description: 'Login an existing user',
            input: LoginRequest::class,
            output: UserResponse::class,
        )
    ]
)]
class AuthLogin extends LoginRequest
{
}
