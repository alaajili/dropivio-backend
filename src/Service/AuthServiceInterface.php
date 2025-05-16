<?php

namespace App\Service;

use App\DTO\LoginRequest;
use App\DTO\RegisterRequest;
use App\DTO\UserResponse;

interface AuthServiceInterface
{
    /**
     * Register a new user
     *
     * @param RegisterRequest $registerRequest User registration data
     * @return UserResponse|array UserResponse DTO with token, or array of errors if validation fails
     */
    public function register(RegisterRequest $registerRequest): UserResponse|array;
    
    /**
     * Authenticate a user and return their details with token
     *
     * @param LoginRequest $loginRequest User login data
     * @return UserResponse|null UserResponse DTO with token, or null if authentication fails
     */
    public function login(LoginRequest $loginRequest): ?UserResponse;
}
