<?php

namespace App\Service;

use App\Entity\User;
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
    
    /**
     * Register a new user
     *
     * @param array $userData User registration data
     * @return array Array containing user and token, or errors
     */
    public function register(array $userData): array
    {
        $user = new User();
        $user->setEmail($userData['email'] ?? '');
        $user->setFirstName($userData['firstName'] ?? '');
        $user->setLastName($userData['lastName'] ?? '');
        $user->setPlainPassword($userData['password'] ?? '');
        
        $errors = $this->validator->validate($user, null, ['user:create']);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return ['errors' => $errorMessages];
        }
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
        $user->setPassword($hashedPassword);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $token = $this->jwtManager->create($user);
        
        return [
            'user' => $user,
            'token' => $token
        ];
    }
    
    /**
     * Authenticate a user and return their details with token
     *
     * @param string $email User email
     * @param string $password User password
     * @return array|null Array containing user and token, or null if authentication fails
     */
    public function login(string $email, string $password): ?array
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            return null;
        }
        
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            return null;
        }
        
        $user->setLastLogin(new \DateTimeImmutable());
        $this->entityManager->flush();
        
        $token = $this->jwtManager->create($user);
        
        return [
            'user' => $user,
            'token' => $token
        ];
    }
}
