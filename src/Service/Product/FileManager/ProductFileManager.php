<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Product\FileManager;

use App\Dto\ProductCreateDto;
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

    public function uploadFiles(ProductCreateDto $dto): array
    {
        $fileUrls = [];

        if ($dto->thumbnailFile) {
            $fileUrls['thumbnail'] = $this->fileUploadService->upload(
                $dto->thumbnailFile, 
                'products/thumbnails'
            );
        }

        if ($dto->productFile) {
            $fileUrls['product'] = $this->fileUploadService->upload(
                $dto->productFile, 
                'products/files'
            );
        }

        return $fileUrls;
    }

    public function getProductFileUrls(Product $product): array
    {
        return [
            'thumbnail' => $product->getThumbnailUrl(),
            'product' => $product->getFileUrl()
        ];
    }

    public function cleanupFiles(array $fileUrls): void
    {
        foreach ($fileUrls as $fileUrl) {
            $this->cleanupFile($fileUrl);
        }
    }

    public function cleanupOldFiles(array $oldFileUrls, array $newFileUrls, ProductCreateDto $dto): void
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
