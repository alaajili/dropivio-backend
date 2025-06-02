<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Factory;

use App\Dto\Product\ProductCreateDto;
use App\Dto\Product\ProductUpdateDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProductDtoFactory implements ProductDtoFactoryInterface
{
    // for debugging purposes, you can inject a logger if needed
    public function __construct(private readonly \Psr\Log\LoggerInterface $logger)
    {
    }

    public function createFromRequest(Request $request): ProductCreateDto
    {
        $data = $request->request->all();
        
        $title = $data['title'] ?? null;
        $shortDescription = $data['shortDescription'] ?? null;
        $description = $data['description'] ?? null;
        $about = $data['about'] ?? null;
        $price = $data['price'] ?? null;
        $categoryId = $data['categoryId'] ?? null;

        // Validation required for creation
        if (empty($title)) {
            throw new BadRequestHttpException('Title is required');
        }
        if (empty($shortDescription)) {
            throw new BadRequestHttpException('Short description is required');
        }
        if (empty($description)) {
            throw new BadRequestHttpException('Description is required');
        }
        if (empty($price) || !is_numeric($price)) {
            throw new BadRequestHttpException('Valid price is required');
        }
        if (empty($categoryId) || !is_numeric($categoryId)) {
            throw new BadRequestHttpException('Valid category ID is required');
        }

        $thumbnailFile = $request->files->get('thumbnailFile');
        $productFile = $request->files->get('productFile');

        return new ProductCreateDto(
            title: $title,
            shortDescription: $shortDescription,
            description: $description,
            about: $about,
            price: (float) $price,
            categoryId: (int) $categoryId,
            thumbnailFile: $thumbnailFile,
            productFile: $productFile
        );
    }

    public function createUpdateFromRequest(Request $request): ProductUpdateDto
    {
        $data = $request->request->all();

        $title = $data['title'] ?? null;
        $shortDescription = $data['shortDescription'] ?? null;
        $description = $data['description'] ?? null;
        $about = $data['about'] ?? null;
        $price = isset($data['price']) && is_numeric($data['price']) ? (float) $data['price'] : null;
        $categoryId = isset($data['categoryId']) && is_numeric($data['categoryId']) ? (int) $data['categoryId'] : null;

        $thumbnailFile = $request->files->get('thumbnailFile');
        $productFile = $request->files->get('productFile');

        return new ProductUpdateDto(
            title: $title,
            shortDescription: $shortDescription,
            description: $description,
            about: $about,
            price: $price,
            categoryId: $categoryId,
            thumbnailFile: $thumbnailFile,
            productFile: $productFile
        );
    }
}
