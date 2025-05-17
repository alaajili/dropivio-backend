<?php

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

class UserResponse
{
    #[Groups(['user:read'])]
    public int $id;
    
    #[Groups(['user:read'])]
    public string $email;
    
    #[Groups(['user:read'])]
    public string $firstName;
    
    #[Groups(['user:read'])]
    public string $lastName;
    
    #[Groups(['user:read'])]
    public array $roles;
    
    #[Groups(['user:read'])]
    public string $createdAt;
    
    public string $token;

    public static function fromEntity(User $user, string $token): self
    {
        $instance = new self();
        $instance->id = $user->getId();
        $instance->email = $user->getEmail();
        $instance->firstName = $user->getFirstName();
        $instance->lastName = $user->getLastName();
        $instance->roles = $user->getRoles();
        $instance->createdAt = $user->getCreatedAt()->format(\DateTimeInterface::ATOM);
        $instance->token = $token;

        return $instance;
    }
}
