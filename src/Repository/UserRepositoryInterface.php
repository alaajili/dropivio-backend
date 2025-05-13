<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Interface for User Repository operations
 */
interface UserRepositoryInterface extends ObjectRepository
{
    /**
     * Saves a user entity
     * 
     * @param User $user The user to save
     * @param bool $flush Whether to flush changes immediately
     * @return void
     */
    public function save(User $user, bool $flush = true): void;
    
    /**
     * Removes a user entity
     * 
     * @param User $user The user to remove
     * @param bool $flush Whether to flush changes immediately
     * @return void
     */
    public function remove(User $user, bool $flush = true): void;
    
    /**
     * Used to upgrade (rehash) the user's password automatically over time
     * 
     * @param PasswordAuthenticatedUserInterface $user The user
     * @param string $newHashedPassword The new hashed password
     * @return void
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void;
    
    /**
     * Find a user by email
     * 
     * @param string $email The email to search for
     * @return User|null The user or null if not found
     */
    public function findByEmail(string $email): ?User;
    
    /**
     * Find users by role
     * 
     * @param string $role The role to search for
     * @return User[] Array of users with the specified role
     */
    public function findByRole(string $role): array;
}
