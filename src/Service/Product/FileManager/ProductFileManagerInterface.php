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

interface ProductFileManagerInterface
{
    public function uploadFiles(ProductCreateDto|ProductUpdateDto $dto): array;
    public function getProductFileUrls(Product $product): array;
    public function cleanupFiles(array $fileUrls): void;
    public function cleanupOldFiles(array $oldFileUrls, array $newFileUrls, ProductCreateDto|ProductUpdateDto $dto): void;
}
