<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Response;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\JsonResponse;

interface JsonResponseBuilderInterface
{
    public function buildProductResponse(?Product $product, int $statusCode = 200): JsonResponse;
    public function buildPaginatedResponse(array $products, int $page, int $limit, int $total): JsonResponse;
}
