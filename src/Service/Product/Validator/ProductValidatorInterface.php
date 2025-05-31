<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Product\Validator;

use App\Dto\ProductCreateDto;
use App\Entity\Product;
use App\Entity\Category;

interface ProductValidatorInterface
{
    public function validateDto(ProductCreateDto $productDto): void;
    public function validateProduct(Product $product): void;
    public function validateCategory(?Category $category, int $categoryId): void;
}
