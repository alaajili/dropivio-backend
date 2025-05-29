<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Factory;

use App\Dto\ProductCreateDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProductDtoFactory implements ProductDtoFactoryInterface
{
    public function createFromRequest(Request $request): ProductCreateDto
    {
        $title = $request->request->get('title');
        $shortDescription = $request->request->get('shortDescription');
        $description = $request->request->get('description');
        $about = $request->request->get('about');
        $price = $request->request->get('price');
        $categoryId = $request->request->get('categoryId');

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
}
