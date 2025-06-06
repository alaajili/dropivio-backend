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
use ApiPlatform\Metadata\Put;
use App\Dto\Product\ProductUpdateDto;
use App\Dto\Product\ProductResponseDto;
use ApiPlatform\OpenApi\Model\Operation;

#[ApiResource(
    shortName: 'Product',
    operations: [
        new Put(
            uriTemplate: '/products/{id}',
            controller: 'App\Controller\ProductController::update',
            name: 'api_products_update',
            input: ProductUpdateDto::class,
            output: ProductResponseDto::class,
            openapi: new Operation(
                summary: 'Update Product',
                description: 'Updates an existing product. Requires authentication (ROLE_USER) and appropriate permissions. All fields are optional for updates.',
            )
        )
    ]
)]
class ProductUpdate extends ProductUpdateDto
{
}
