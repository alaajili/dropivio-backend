<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Response;

use App\Dto\Product\ProductResponseDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

class JsonResponseBuilder implements JsonResponseBuilderInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer
    ) {
    }

    public function buildProductResponse(?ProductResponseDto $product, int $statusCode = 200): JsonResponse
    {
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        $data = json_decode($this->serializer->serialize($product, 'json', ['groups' => ['product:read']]));
        return new JsonResponse($data, $statusCode);
    }

    public function buildPaginatedResponse(array $products, int $page, int $limit, int $total): JsonResponse
    {
        $data = [
            'products' => json_decode($this->serializer->serialize($products, 'json', ['groups' => ['product:read']])),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];

        return new JsonResponse($data);
    }
}
