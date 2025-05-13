<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    #[Route('/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $user = new User();
            $user->setEmail($data['email'] ?? '');
            $user->setFirstName($data['firstName'] ?? '');
            $user->setLastName($data['lastName'] ?? '');
            $user->setPlainPassword($data['password'] ?? '');
            
            // Validate the user entity
            $errors = $this->validator->validate($user, null, ['user:create']);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }
            
            // Hash the password
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($hashedPassword);
            
            // Save the user
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            // Create JWT token
            $token = $this->jwtManager->create($user);
            
            return $this->json([
                'user' => $user,
                'token' => $token
            ], Response::HTTP_CREATED, [], ['groups' => ['user:read']]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Could not register user: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/login', name: 'app_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            // Find user by email
            $user = $this->userRepository->findByEmail($data['email'] ?? '');
            
            if (!$user) {
                return $this->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
            }
            
            // Verify password
            if (!$this->passwordHasher->isPasswordValid($user, $data['password'] ?? '')) {
                return $this->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
            }
            
            // Update last login
            $user->setLastLogin(new \DateTimeImmutable());
            $this->entityManager->flush();
            
            // Create JWT token
            $token = $this->jwtManager->create($user);
            
            return $this->json([
                'user' => $user,
                'token' => $token
            ], Response::HTTP_OK, [], ['groups' => ['user:read']]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Login failed: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/me', name: 'app_auth_me', methods: ['GET'])]
    public function getCurrentUser(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }
        
        return $this->json([
            'user' => $user
        ], Response::HTTP_OK, [], ['groups' => ['user:read']]);
    }

    #[Route('/logout', name: 'app_auth_logout', methods: ['POST'])]
    public function logout(TokenStorageInterface $tokenStorage): JsonResponse
    {
        $token = $tokenStorage->getToken();
        if ($token instanceof TokenInterface) {
            $tokenStorage->setToken(null);
        }
        
        return $this->json(['message' => 'Logged out successfully']);
    }
}
