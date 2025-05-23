<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ProductInput;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProductInputProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof ProductInput) {
            /** @var Product $product */
            $product = $context['previous_data'] ?? new Product();
            
            $product->setTitle($data->title)
                ->setShortDescription($data->shortDescription)
                ->setDescription($data->description)
                ->setAbout($data->about)
                ->setPrice($data->price)
                ->setThumbnailUrl($data->thumbnailUrl)
                ->setFileUrl($data->fileUrl);
            
            $category = $this->entityManager->getRepository(Category::class)->find($data->category);
            if (!$category) {
                throw new NotFoundHttpException(sprintf('Category with ID %d not found', $data->category));
            }
            $product->setCategory($category);
            
            if ($operation instanceof Post) {
                $token = $this->tokenStorage->getToken();
                /** @var User|null $user */
                $user = $token ? $token->getUser() : null;
                
                if ($user && is_object($user)) {
                    $product->setSeller($user);
                }
            }
            
            return $this->persistProcessor->process($product, $operation, $uriVariables, $context);
        }
        
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
