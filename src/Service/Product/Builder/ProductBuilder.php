<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Product\Builder;

use App\Dto\Product\ProductCreateDto;
use App\Dto\Product\ProductUpdateDto;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Category;

class ProductBuilder implements ProductBuilderInterface
{
    public function build(ProductCreateDto $dto, User $seller, Category $category, array $fileKeys): Product
    {
        $product = new Product();
        return $this->setProductData($product, $dto, $seller, $category, $fileKeys);
    }

    public function updateProduct(Product $product, ProductUpdateDto $dto, Category $category, array $fileKeys): Product
    {
        return $this->setProductData($product, $dto, null, $category, $fileKeys);
    }

    private function setProductData(
        Product $product,
        ProductCreateDto|ProductUpdateDto $dto,
        ?User $seller,
        Category $category,
        array $fileKeys
    ): Product {
        $product->setTitle($dto->title ?? $product->getTitle())
               ->setShortDescription($dto->shortDescription ?? $product->getShortDescription())
               ->setDescription($dto->description ?? $product->getDescription())
               ->setAbout($dto->about ?? $product->getAbout())
               ->setPrice($dto->price ?? $product->getPrice())
               ->setCategory($category);

        if ($seller) {
            $product->setSeller($seller);
        }

        if (isset($fileKeys['thumbnail'])) {
            $product->setThumbnailKey($fileKeys['thumbnail']);
        }

        if (isset($fileKeys['product'])) {
            $product->setFileKey($fileKeys['product']);
        }

        return $product;
    }
}
