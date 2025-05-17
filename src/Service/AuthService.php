<?php

namespace App\Service;

use App\Dto\LoginRequest;
use App\Dto\RegisterRequest;
use App\Dto\UserResponse;
use App\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function register(RegisterRequest $registerRequest): UserResponse|array
    {
        $errors = $this->validator->validate($registerRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return ['errors' => $errorMessages];
        }
        
        if ($this->userRepository->findByEmail($registerRequest->email)) {
            return ['errors' => ['email' => 'Email already exists']];
        }

        $user = RegisterRequest::toUser($registerRequest);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
        $user->setPassword($hashedPassword);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $token = $this->jwtManager->create($user);
        
        return UserResponse::fromEntity($user, $token);
    }

    public function login(LoginRequest $loginRequest): ?UserResponse
    {
        $errors = $this->validator->validate($loginRequest);
        if (count($errors) > 0) {
            return null;
        }
        
        $user = $this->userRepository->findByEmail($loginRequest->email);
        
        if (!$user) {
            return null;
        }
        
        if (!$this->passwordHasher->isPasswordValid($user, $loginRequest->password)) {
            return null;
        }
        
        $user->setLastLogin(new \DateTimeImmutable());
        $this->entityManager->flush();
        
        $token = $this->jwtManager->create($user);
        
        return UserResponse::fromEntity($user, $token);
    }
}
