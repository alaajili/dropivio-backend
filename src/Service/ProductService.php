<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Dto\ProductCreateDto;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Service\FileUpload\FileUploadServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

class ProductService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileUploadServiceInterface $fileUploadService,
        private readonly CategoryRepository $categoryRepository,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger
    ) {
    }

    public function createProduct(ProductCreateDto $productDto, User $seller): Product
    {
        $this->logger->info('Starting product creation', [
            'seller_id' => $seller->getId(),
            'title' => $productDto->title,
        ]);

        // Validate the DTO
        $violations = $this->validator->validate($productDto);
        if (count($violations) > 0) {
            $errorMessages = [];
            foreach ($violations as $violation) {
                $errorMessages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new BadRequestHttpException('Validation failed: ' . implode(', ', $errorMessages));
        }

        try {
            $this->entityManager->beginTransaction();

            // Find category
            $category = $this->categoryRepository->find($productDto->categoryId);
            if (!$category) {
                throw new NotFoundHttpException(sprintf('Category with ID %d not found', $productDto->categoryId));
            }

            // Upload files
            $thumbnailUrl = null;
            $fileUrl = null;

            if ($productDto->thumbnailFile) {
                $thumbnailUrl = $this->fileUploadService->upload(
                    $productDto->thumbnailFile, 
                    'products/thumbnails'
                );
            }

            if ($productDto->productFile) {
                $fileUrl = $this->fileUploadService->upload(
                    $productDto->productFile, 
                    'products/files'
                );
            }

            // Create product entity
            $product = new Product();
            $product->setTitle($productDto->title)
                   ->setShortDescription($productDto->shortDescription)
                   ->setDescription($productDto->description)
                   ->setAbout($productDto->about)
                   ->setPrice($productDto->price)
                   ->setThumbnailUrl($thumbnailUrl)
                   ->setFileUrl($fileUrl)
                   ->setCategory($category)
                   ->setSeller($seller);

            // Validate the product entity
            $violations = $this->validator->validate($product);
            if (count($violations) > 0) {
                throw new BadRequestHttpException('Product validation failed');
            }

            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->logger->info('Product created successfully', [
                'product_id' => $product->getId(),
                'seller_id' => $seller->getId(),
            ]);

            return $product;

        } catch (\Exception $e) {
            $this->entityManager->rollback();

            // Clean up uploaded files on error
            if (isset($thumbnailUrl)) {
                $this->cleanupFile($thumbnailUrl);
            }
            if (isset($fileUrl)) {
                $this->cleanupFile($fileUrl);
            }

            $this->logger->error('Failed to create product', [
                'error' => $e->getMessage(),
                'seller_id' => $seller->getId(),
            ]);

            throw $e;
        }
    }

    public function updateProduct(Product $product, ProductCreateDto $productDto): Product
    {
        $this->logger->info('Starting product update', [
            'product_id' => $product->getId(),
            'title' => $productDto->title,
        ]);

        // Store old file URLs for cleanup
        $oldThumbnailUrl = $product->getThumbnailUrl();
        $oldFileUrl = $product->getFileUrl();

        try {
            $this->entityManager->beginTransaction();

            // Find category if changed
            if ($productDto->categoryId !== $product->getCategory()?->getId()) {
                $category = $this->categoryRepository->find($productDto->categoryId);
                if (!$category) {
                    throw new NotFoundHttpException(sprintf('Category with ID %d not found', $productDto->categoryId));
                }
                $product->setCategory($category);
            }

            // Update basic fields
            $product->setTitle($productDto->title)
                   ->setShortDescription($productDto->shortDescription)
                   ->setDescription($productDto->description)
                   ->setAbout($productDto->about)
                   ->setPrice($productDto->price);

            // Handle file uploads
            if ($productDto->thumbnailFile) {
                $newThumbnailUrl = $this->fileUploadService->upload(
                    $productDto->thumbnailFile, 
                    'products/thumbnails'
                );
                $product->setThumbnailUrl($newThumbnailUrl);
            }

            if ($productDto->productFile) {
                $newFileUrl = $this->fileUploadService->upload(
                    $productDto->productFile, 
                    'products/files'
                );
                $product->setFileUrl($newFileUrl);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            // Clean up old files after successful update
            if ($productDto->thumbnailFile && $oldThumbnailUrl) {
                $this->cleanupFile($oldThumbnailUrl);
            }
            if ($productDto->productFile && $oldFileUrl) {
                $this->cleanupFile($oldFileUrl);
            }

            $this->logger->info('Product updated successfully', [
                'product_id' => $product->getId(),
            ]);

            return $product;

        } catch (\Exception $e) {
            $this->entityManager->rollback();

            $this->logger->error('Failed to update product', [
                'error' => $e->getMessage(),
                'product_id' => $product->getId(),
            ]);

            throw $e;
        }
    }

    public function deleteProduct(Product $product): void
    {
        $this->logger->info('Starting product deletion', [
            'product_id' => $product->getId(),
        ]);

        try {
            $this->entityManager->beginTransaction();

            // Store file URLs for cleanup
            $thumbnailUrl = $product->getThumbnailUrl();
            $fileUrl = $product->getFileUrl();

            $this->entityManager->remove($product);
            $this->entityManager->flush();
            $this->entityManager->commit();

            // Clean up files after successful deletion
            if ($thumbnailUrl) {
                $this->cleanupFile($thumbnailUrl);
            }
            if ($fileUrl) {
                $this->cleanupFile($fileUrl);
            }

            $this->logger->info('Product deleted successfully', [
                'product_id' => $product->getId(),
            ]);

        } catch (\Exception $e) {
            $this->entityManager->rollback();

            $this->logger->error('Failed to delete product', [
                'error' => $e->getMessage(),
                'product_id' => $product->getId(),
            ]);

            throw $e;
        }
    }

    private function cleanupFile(?string $fileUrl): void
    {
        if (!$fileUrl) {
            return;
        }

        try {
            // Extract the key from the URL if it's a full URL
            if ($this->fileUploadService instanceof \App\Service\FileUpload\BackblazeUploadService) {
                $key = $this->fileUploadService->extractKeyFromUrl($fileUrl);
                if ($key) {
                    $this->fileUploadService->delete($key);
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning('Failed to cleanup file', [
                'file_url' => $fileUrl,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
