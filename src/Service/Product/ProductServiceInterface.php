<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Product;

use App\Dto\ProductCreateDto;
use App\Entity\Product;
use App\Entity\User;

interface ProductServiceInterface
{
    /**
     * Retrieves a paginated list of products.
     *
     * @param int $page The page number to retrieve
     * @param int $limit The number of products per page
     * @return array An array of Product entities
     */
    public function getProducts(int $page = 1, int $limit = 20): array;

    /**
     * Retrieves the total count of products.
     *
     * @return int The total number of products
     */
    public function getProductsCount(): int;

    /**
     * Retrieves a product by its ID.
     *
     * @param int $id The ID of the product
     * @return Product|null The product entity or null if not found
     */
    public function getProductById(int $id): ?Product;

    /**
     * Creates a new product for the given seller.
     *
     * @param ProductCreateDto $productDto The product data transfer object
     * @param User $seller The seller creating the product
     * @return Product The created product entity
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException When validation fails
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException When category is not found
     */
    public function createProduct(ProductCreateDto $productDto, User $seller): Product;

    /**
     * Updates an existing product with new data.
     *
     * @param Product $product The product entity to update
     * @param ProductCreateDto $productDto The updated product data
     * @return Product The updated product entity
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException When validation fails
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException When category is not found
     */
    public function updateProduct(Product $product, ProductCreateDto $productDto): Product;

    /**
     * Deletes a product and cleans up associated files.
     *
     * @param Product $product The product entity to delete
     * @return void
     * @throws \Exception When deletion fails
     */
    public function deleteProduct(Product $product): void;

    /**
     * Checks if the user has permission to update the product.
     *
     * @param Product $product The product entity to check
     * @param User $user The user attempting to update the product
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException When permission is denied
     */
    public function checkUpdatePermission(Product $product, User $user): void;

    /**
     * Checks if the user has permission to delete the product.
     *
     * @param Product $product The product entity to check
     * @param User $user The user attempting to delete the product
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException When permission is denied
     */
    public function checkDeletePermission(Product $product, User $user): void;
}