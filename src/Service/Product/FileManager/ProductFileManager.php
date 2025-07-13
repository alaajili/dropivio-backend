<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Product\FileManager;

use App\Dto\Product\ProductCreateDto;
use App\Dto\Product\ProductUpdateDto;
use App\Entity\Product;
use App\Service\FileUpload\FileUploadServiceInterface;
use Psr\Log\LoggerInterface;

class ProductFileManager implements ProductFileManagerInterface
{
    public function __construct(
        private readonly FileUploadServiceInterface $fileUploadService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function uploadFiles(ProductCreateDto|ProductUpdateDto $dto): array
    {
        $fileKeys = [];

        if ($dto->thumbnailFile) {
            $fileKeys['thumbnail'] = $this->fileUploadService->upload(
                $dto->thumbnailFile, 
                'products/thumbnails'
            );
        }

        if ($dto->productFile) {
            $fileKeys['product'] = $this->fileUploadService->upload(
                $dto->productFile, 
                'products/files'
            );
        }

        return $fileKeys;
    }

    public function getProductFileKeys(Product $product): array
    {
        return [
            'thumbnail' => $product->getThumbnailKey(),
            'product' => $product->getFileKey()
        ];
    }

    public function cleanupFiles(array $fileKeys): void
    {
        foreach ($fileKeys as $fileUrl) {
            $this->cleanupFile($fileUrl);
        }
    }

    public function cleanupOldFiles(array $oldFileUrls, array $newFileUrls, ProductCreateDto|ProductUpdateDto $dto): void
    {
        if ($dto->thumbnailFile && $oldFileUrls['thumbnail']) {
            $this->cleanupFile($oldFileUrls['thumbnail']);
        }
        if ($dto->productFile && $oldFileUrls['product']) {
            $this->cleanupFile($oldFileUrls['product']);
        }
    }

    private function cleanupFile(?string $fileUrl): void
    {
        if (!$fileUrl) {
            return;
        }

        try {
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
