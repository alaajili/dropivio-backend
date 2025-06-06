<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Product;

use App\Dto\Product\ProductCreateDto;
use App\Dto\Product\ProductUpdateDto;
use App\Dto\Product\ProductResponseDto;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Service\Authorization\ProductAuthorizationServiceInterface;
use App\Service\Database\TransactionManagerInterface;
use App\Service\Product\Builder\ProductBuilderInterface;
use App\Service\Product\Validator\ProductValidatorInterface;
use App\Service\Product\FileManager\ProductFileManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Repository\ProductRepository;

class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TransactionManagerInterface $transactionManager,
        private readonly ProductBuilderInterface $productBuilder,
        private readonly ProductValidatorInterface $productValidator,
        private readonly ProductFileManagerInterface $fileManager,
        private readonly ProductAuthorizationServiceInterface $authorizationService,
        private readonly CategoryRepository $categoryRepository,
        private readonly LoggerInterface $logger,
        private readonly ProductRepository $productRepository
    ) {
    }

    public function getProducts(int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        $products = $this->productRepository->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
        
        return array_map(
            fn(Product $product) => ProductResponseDto::fromEntity($product),
            $products
        );
    }

    public function getProductsCount(): int
    {
        return $this->productRepository->count([]);
    }

    public function getProductById(int $id): ?ProductResponseDto
    {
        $product = $this->productRepository->find($id);
        
        if ($product === null) {
            return null;
        }
        
        return ProductResponseDto::fromEntity($product);
    }

    public function createProduct(ProductCreateDto $productDto, User $seller): ProductResponseDto
    {
        $this->logger->info('Starting product creation', [
            'seller_id' => $seller->getId(),
            'title' => $productDto->title,
        ]);

        $product = $this->transactionManager->executeInTransaction(function () use ($productDto, $seller) {
            $this->productValidator->validateDto($productDto);
            
            $category = $this->categoryRepository->find($productDto->categoryId);
            $this->productValidator->validateCategory($category, $productDto->categoryId);

            $fileUrls = $this->fileManager->uploadFiles($productDto);
            
            try {
                $product = $this->productBuilder->build($productDto, $seller, $category, $fileUrls);
                $this->productValidator->validateProduct($product);

                $this->entityManager->persist($product);
                $this->entityManager->flush();

                $this->logger->info('Product created successfully', [
                    'product_id' => $product->getId(),
                    'seller_id' => $seller->getId(),
                ]);

                return $product;
            } catch (\Exception $e) {
                $this->fileManager->cleanupFiles($fileUrls);
                throw $e;
            }
        });

        return ProductResponseDto::fromEntity($product);
    }

    public function updateProduct(Product $product, ProductUpdateDto $productDto): ProductResponseDto
    {
        $updatedProduct = $this->transactionManager->executeInTransaction(function () use ($product, $productDto) {
            $oldFileUrls = $this->fileManager->getProductFileUrls($product);
            
            $category = $product->getCategory();
            if ($productDto->categoryId !== null) {
                $category = $this->categoryRepository->find($productDto->categoryId);
                $this->productValidator->validateCategory($category, $productDto->categoryId);
            }

            $newFileUrls = $this->fileManager->uploadFiles($productDto);
            
            $this->productBuilder->updateProduct($product, $productDto, $category, $newFileUrls);
            $this->entityManager->flush();

            $this->fileManager->cleanupOldFiles($oldFileUrls, $newFileUrls, $productDto);

            $this->logger->info('Product updated successfully', [
                'product_id' => $product->getId(),
            ]);

            return $product;
        });

        return ProductResponseDto::fromEntity($updatedProduct);
    }

    public function deleteProduct(Product $product): void
    {
        $this->logger->info('Starting product deletion', [
            'product_id' => $product->getId(),
        ]);

        $this->transactionManager->executeInTransaction(function () use ($product) {
            $fileUrls = $this->fileManager->getProductFileUrls($product);

            $this->entityManager->remove($product);
            $this->entityManager->flush();

            $this->fileManager->cleanupFiles($fileUrls);

            $this->logger->info('Product deleted successfully', [
                'product_id' => $product->getId(),
            ]);
        });
    }

    public function checkUpdatePermission(Product $product, User $user): void
    {
        $this->authorizationService->checkUpdatePermission($product, $user);
    }

    public function checkDeletePermission(Product $product, User $user): void
    {
        $this->authorizationService->checkDeletePermission($product, $user);
    }
}
