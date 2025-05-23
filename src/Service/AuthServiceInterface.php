<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Dto\LoginRequest;
use App\Dto\RegisterRequest;
use App\Dto\UserResponse;

interface AuthServiceInterface
{
    /**
     * Register a new user
     *
     * @param RegisterRequest $registerRequest User registration data
     * @return UserResponse|array UserResponse Dto with token, or array of errors if validation fails
     */
    public function register(RegisterRequest $registerRequest): UserResponse|array;
    
    /**
     * Authenticate a user and return their details with token
     *
     * @param LoginRequest $loginRequest User login data
     * @return UserResponse|null UserResponse Dto with token, or null if authentication fails
     */
    public function login(LoginRequest $loginRequest): ?UserResponse;
}
