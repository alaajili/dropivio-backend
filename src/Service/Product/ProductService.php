<?php

namespace App\Service\Product;

use App\Dto\Product\ProductCreateDto;
use App\Dto\Product\ProductUpdateDto;
use App\Dto\Product\ProductResponseDto;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\Authorization\ProductAuthorizationServiceInterface;
use App\Service\Database\TransactionManagerInterface;
use App\Service\Product\Builder\ProductBuilderInterface;
use App\Service\Product\Validator\ProductValidatorInterface;
use App\Service\Product\FileManager\ProductFileManagerInterface;
use App\Service\Cache\CachedThumbnailService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Repository\ProductRepository;
use App\Service\FileUpload\FileUploadServiceInterface;

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
        private readonly ProductRepository $productRepository,
        private readonly FileUploadServiceInterface $fileUploadService,
        private readonly CachedThumbnailService $cachedThumbnailService
    ) {
    }

    public function getProducts(int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        $products = $this->productRepository->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
        
        return array_map(
            fn(Product $product) => ProductResponseDto::fromEntity(
                $product, 
                $this->cachedThumbnailService->getThumbnailUrl($product->getThumbnailKey())
            ),
            $products
        );
    }

    public function getProductsCount(): int
    {
        return $this->productRepository->count([]);
    }

    public function getProductById(int $id): ?ProductResponseDto
    {
        /** @var Product $product */
        $product = $this->productRepository->find($id);
        
        if ($product === null) {
            return null;
        }
        
        return ProductResponseDto::fromEntity(
            $product,
            $this->cachedThumbnailService->getThumbnailUrl($product->getThumbnailKey())
        );
    }

    public function createProduct(ProductCreateDto $productDto, User $seller): ProductResponseDto
    {
        $this->logger->info('Starting product creation', [
            'seller_id' => $seller->getId(),
            'title' => $productDto->title,
        ]);

        /** @var Product $product */
        $product = $this->transactionManager->executeInTransaction(function () use ($productDto, $seller) {
            $this->productValidator->validateDto($productDto);
            
            /** @var Category $category */
            $category = $this->categoryRepository->find($productDto->categoryId);
            $this->productValidator->validateCategory($category, $productDto->categoryId);

            $fileKeys = $this->fileManager->uploadFiles($productDto);
            
            try {
                $product = $this->productBuilder->build($productDto, $seller, $category, $fileKeys);
                $this->productValidator->validateProduct($product);

                $this->entityManager->persist($product);
                $this->entityManager->flush();

                $this->logger->info('Product created successfully', [
                    'product_id' => $product->getId(),
                    'seller_id' => $seller->getId(),
                ]);

                return $product;
            } catch (\Exception $e) {
                $this->fileManager->cleanupFiles($fileKeys);
                throw $e;
            }
        });

        $thumbnailUrl = $this->cachedThumbnailService->getThumbnailUrl($product->getThumbnailKey());
        return ProductResponseDto::fromEntity($product, $thumbnailUrl);
    }

    public function updateProduct(Product $product, ProductUpdateDto $productDto): ProductResponseDto
    {
        $updatedProduct = $this->transactionManager->executeInTransaction(function () use ($product, $productDto) {
            $oldFileUrls = $this->fileManager->getProductFileKeys($product);
            $oldThumbnailKey = $product->getThumbnailKey();
            
            $category = $product->getCategory();
            if ($productDto->categoryId !== null) {
                $category = $this->categoryRepository->find($productDto->categoryId);
                $this->productValidator->validateCategory($category, $productDto->categoryId);
            }

            $newFileUrls = $this->fileManager->uploadFiles($productDto);
            
            $this->productBuilder->updateProduct($product, $productDto, $category, $newFileUrls);
            $this->entityManager->flush();

            if (isset($newFileUrls['thumbnail']) && $oldThumbnailKey !== $product->getThumbnailKey()) {
                $this->cachedThumbnailService->invalidateThumbnailCache($oldThumbnailKey);
            }

            $this->fileManager->cleanupOldFiles($oldFileUrls, $newFileUrls, $productDto);

            $this->logger->info('Product updated successfully', [
                'product_id' => $product->getId(),
            ]);

            return $product;
        });

        $thumbnailUrl = $this->cachedThumbnailService->getThumbnailUrl($updatedProduct->getThumbnailKey());
        return ProductResponseDto::fromEntity($updatedProduct, $thumbnailUrl);
    }

    public function deleteProduct(Product $product): void
    {
        $this->logger->info('Starting product deletion', [
            'product_id' => $product->getId(),
        ]);

        $thumbnailKey = $product->getThumbnailKey();

        $this->transactionManager->executeInTransaction(function () use ($product) {
            $fileKeys = $this->fileManager->getProductFileKeys($product);

            $this->entityManager->remove($product);
            $this->entityManager->flush();

            $this->fileManager->cleanupFiles($fileKeys);

            $this->logger->info('Product deleted successfully', [
                'product_id' => $product->getId(),
            ]);
        });

        // Invalidate cache after successful deletion
        $this->cachedThumbnailService->invalidateThumbnailCache($thumbnailKey);
    }

    public function checkUpdatePermission(Product $product, User $user): void
    {
        $this->authorizationService->checkUpdatePermission($product, $user);
    }

    public function checkDeletePermission(Product $product, User $user): void
    {
        $this->authorizationService->checkDeletePermission($product, $user);
    }

    /**
     * Optional: Warmup cache for multiple products (useful for listings)
     */
    public function warmupThumbnailCache(array $products): void
    {
        $thumbnailKeys = array_map(
            fn(Product $product) => $product->getThumbnailKey(),
            $products
        );
        
        $this->cachedThumbnailService->warmupThumbnailCache($thumbnailKeys);
    }
}
