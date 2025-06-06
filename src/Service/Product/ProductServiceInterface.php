<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Product;

use App\Dto\Product\ProductCreateDto;
use App\Dto\Product\ProductResponseDto;
use App\Dto\Product\ProductUpdateDto;
use App\Entity\Product;
use App\Entity\User;

interface ProductServiceInterface
{
    /**
     * @return ProductResponseDto[]
     */
    public function getProducts(int $page = 1, int $limit = 20): array;

    public function getProductsCount(): int;

    public function getProductById(int $id): ?ProductResponseDto;

    public function createProduct(ProductCreateDto $productDto, User $seller): ProductResponseDto;

    public function updateProduct(Product $product, ProductUpdateDto $productDto): ProductResponseDto;

    public function deleteProduct(Product $product): void;

    public function checkUpdatePermission(Product $product, User $user): void;

    public function checkDeletePermission(Product $product, User $user): void;
}
