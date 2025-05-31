<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Product\Builder;

use App\Dto\ProductCreateDto;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Category;

interface ProductBuilderInterface
{
    public function build(ProductCreateDto $dto, User $seller, Category $category, array $fileUrls): Product;
    public function updateProduct(Product $product, ProductCreateDto $dto, Category $category, array $fileUrls): Product;
}
