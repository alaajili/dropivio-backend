<?php

namespace App\Service\Product\FileManager;

use App\Dto\ProductCreateDto;
use App\Entity\Product;

interface ProductFileManagerInterface
{
    public function uploadFiles(ProductCreateDto $dto): array;
    public function getProductFileUrls(Product $product): array;
    public function cleanupFiles(array $fileUrls): void;
    public function cleanupOldFiles(array $oldFileUrls, array $newFileUrls, ProductCreateDto $dto): void;
}
