<?php

namespace App\Controller;

use App\Dto\LoginRequest;
use App\Dto\RegisterRequest;
use App\Dto\UserResponse;
use App\Entity\User;
use App\Service\AuthServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $registerRequest = $this->serializer->deserialize(
                $request->getContent(),
                RegisterRequest::class,
                'json'
            );
            
            $result = $this->authService->register($registerRequest);
            if (isset($result['errors'])) {
                return $this->json(['errors' => $result['errors']], Response::HTTP_BAD_REQUEST);
            }
            
            return $this->json($result, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Could not register user: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/login', name: 'app_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $loginRequest = $this->serializer->deserialize(
                $request->getContent(), 
                LoginRequest::class, 
                'json'
            );
            
            $userResponse = $this->authService->login($loginRequest);
            
            if (!$userResponse) {
                return $this->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
            }
            
            return $this->json(['user' => $userResponse], Response::HTTP_OK);
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
        
        $userResponse = UserResponse::fromEntity($user, '');
        
        return $this->json([
            'user' => $userResponse
        ], Response::HTTP_OK);
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
