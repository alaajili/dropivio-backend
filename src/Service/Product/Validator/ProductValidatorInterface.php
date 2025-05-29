<?php

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
