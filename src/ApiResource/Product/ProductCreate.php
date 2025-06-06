<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\ApiResource\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\Product\ProductCreateDto;
use App\Dto\Product\ProductResponseDto;
use ApiPlatform\OpenApi\Model\Operation;

#[ApiResource(
    shortName: 'Product',
    operations: [
        new Post(
            uriTemplate: '/products',
            controller: 'App\Controller\ProductController::create',
            name: 'api_products_create',
            input: ProductCreateDto::class,
            output: ProductResponseDto::class,
            openapi: new Operation(
                summary: 'Create New Product',
                description: 'Creates a new product. Requires authentication (ROLE_USER). You can upload thumbnail and product files as multipart/form-data.',
            )
        )
    ]
)]
class ProductCreate extends ProductCreateDto
{
}
