<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Product\Validator;

use App\Dto\ProductCreateDto;
use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductValidator implements ProductValidatorInterface
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
    }

    public function validateDto(ProductCreateDto $productDto): void
    {
        $violations = $this->validator->validate($productDto);
        if (count($violations) > 0) {
            $errorMessages = [];
            foreach ($violations as $violation) {
                $errorMessages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new BadRequestHttpException('Validation failed: ' . implode(', ', $errorMessages));
        }
    }

    public function validateProduct(Product $product): void
    {
        $violations = $this->validator->validate($product);
        if (count($violations) > 0) {
            throw new BadRequestHttpException('Product validation failed');
        }
    }

    public function validateCategory(?Category $category, int $categoryId): void
    {
        if (!$category) {
            throw new NotFoundHttpException(sprintf('Category with ID %d not found', $categoryId));
        }
    }
}
