<?php

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
