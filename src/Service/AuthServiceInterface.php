<?php

namespace App\Service;

interface AuthServiceInterface
{
    /**
     * Register a new user
     *
     * @param array $userData User registration data
     * @return array Array containing user and token, or errors
     */
    public function register(array $userData): array;
    
    /**
     * Authenticate a user and return their details with token
     *
     * @param string $email User email
     * @param string $password User password
     * @return array|null Array containing user and token, or null if authentication fails
     */
    public function login(string $email, string $password): ?array;
}
