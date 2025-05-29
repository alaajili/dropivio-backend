<?php

namespace App\Service\Product\Builder;

use App\Dto\ProductCreateDto;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Category;

class ProductBuilder implements ProductBuilderInterface
{
    public function build(ProductCreateDto $dto, User $seller, Category $category, array $fileUrls): Product
    {
        $product = new Product();
        return $this->setProductData($product, $dto, $seller, $category, $fileUrls);
    }

    public function updateProduct(Product $product, ProductCreateDto $dto, Category $category, array $fileUrls): Product
    {
        return $this->setProductData($product, $dto, null, $category, $fileUrls);
    }

    private function setProductData(Product $product, ProductCreateDto $dto, ?User $seller, Category $category, array $fileUrls): Product
    {
        $product->setTitle($dto->title)
               ->setShortDescription($dto->shortDescription)
               ->setDescription($dto->description)
               ->setAbout($dto->about)
               ->setPrice($dto->price)
               ->setCategory($category);

        if ($seller) {
            $product->setSeller($seller);
        }

        if (isset($fileUrls['thumbnail'])) {
            $product->setThumbnailUrl($fileUrls['thumbnail']);
        }

        if (isset($fileUrls['product'])) {
            $product->setFileUrl($fileUrls['product']);
        }

        return $product;
    }
}
