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
use App\Dto\RegisterRequest;
use App\Dto\UserResponse;

#[ApiResource(
    shortName: 'Auth',
    operations: [
        new Post(
            uriTemplate: '/auth/register',
            controller: 'App\Controller\AuthController::register',
            description: 'Register a new user',
            input: RegisterRequest::class,
            output: UserResponse::class,
        )
    ]
)]
class AuthRegister extends RegisterRequest
{
}
