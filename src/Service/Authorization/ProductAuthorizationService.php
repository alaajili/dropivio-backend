<?php

namespace App\Service\Authorization;

use App\Entity\Product;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductAuthorizationService implements ProductAuthorizationServiceInterface
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    public function checkUpdatePermission(?Product $product, User $user): void
    {
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        if (!$this->security->isGranted('ROLE_ADMIN') && $product->getSeller() !== $user) {
            throw new AccessDeniedHttpException('You can only update your own products');
        }
    }

    public function checkDeletePermission(?Product $product, User $user): void
    {
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        if (!$this->security->isGranted('ROLE_ADMIN') && $product->getSeller() !== $user) {
            throw new AccessDeniedHttpException('You can only delete your own products');
        }
    }
}
