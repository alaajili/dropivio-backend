<?php

namespace App\State;

use App\Entity\User;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Product;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class ProductStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $decorated,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Product) {
            // Get the token and check if a user is authenticated
            $token = $this->tokenStorage->getToken();

            /** @var User|null $user */
            $user = $token ? $token->getUser() : null;
            
            if ($user && is_object($user)) {
                if ($operation instanceof Post)
                $data->setSeller($user);
            }
        }
        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}
