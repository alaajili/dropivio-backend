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
use ApiPlatform\Metadata\GetCollection;
use App\Dto\Product\ProductResponseDto;
use ApiPlatform\OpenApi\Model\Operation;

#[ApiResource(
    shortName: 'Product',
    operations: [
        new GetCollection(
            uriTemplate: '/products',
            controller: 'App\Controller\ProductController::list',
            name: 'api_products_list',
            output: ProductResponseDto::class,
            openapi: new Operation(
                summary: 'Get Products List',
                description: 'Retrieves a paginated list of products. You can specify page number and limit for pagination.',
            )
        )
    ]
)]
class ProductList
{
}
