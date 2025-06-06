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
use ApiPlatform\Metadata\Get;
use App\Dto\Product\ProductResponseDto;
use ApiPlatform\OpenApi\Model\Operation;

#[ApiResource(
    shortName: 'Product',
    operations: [
        new Get(
            uriTemplate: '/products/{id}',
            controller: 'App\Controller\ProductController::show',
            name: 'api_products_show',
            output: ProductResponseDto::class,
            openapi: new Operation(
                summary: 'Get Product Details',
                description: 'Retrieves detailed information about a specific product by its ID.',
            )
        )
    ]
)]
class ProductShow
{
}
