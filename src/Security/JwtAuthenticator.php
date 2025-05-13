<?php

namespace App\Security;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;

class JwtAuthenticator extends AbstractAuthenticator
{   
    public function __construct(
        private readonly TokenExtractorInterface $tokenExtractor,
        private readonly JWTTokenManagerInterface $jwtManager
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $this->tokenExtractor->extract($request) !== false;
    }

    public function authenticate(Request $request): Passport
    {
        $token = $this->tokenExtractor->extract($request);
        
        if (false === $token) {
            throw new AuthenticationException('No JWT token found');
        }
        
        try {
            $payload = $this->jwtManager->parse($token);
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid JWT token');
        }
        
        return new SelfValidatingPassport(
            new UserBadge($payload['username'] ?? $payload['email'])
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Allow the request to continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
    }
}
