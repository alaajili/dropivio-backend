<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model\Operation;

#[ApiResource(
    shortName: 'Product',
    operations: [
        new Delete(
            uriTemplate: '/products/{id}',
            controller: 'App\Controller\ProductController::delete',
            name: 'api_products_delete',
            output: false,
            openapi: new Operation(
                summary: 'Delete Product',
                description: 'Deletes a product. Requires authentication (ROLE_USER) and appropriate permissions.',
            )
        )
    ]
)]
class ProductDelete
{
}
