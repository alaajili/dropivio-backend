<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class ProductCreateDto
{
    #[Assert\NotBlank(message: 'Title is required')]
    #[Assert\Length(max: 50, maxMessage: 'Title cannot be longer than 50 characters')]
    public ?string $title = null;

    #[Assert\NotBlank(message: 'Short description is required')]
    #[Assert\Length(max: 150, maxMessage: 'Short description cannot be longer than 150 characters')]
    public ?string $shortDescription = null;

    #[Assert\NotBlank(message: 'Description is required')]
    public ?string $description = null;

    public ?string $about = null;

    #[Assert\NotBlank(message: 'Price is required')]
    #[Assert\Positive(message: 'Price must be positive')]
    #[Assert\Type(type: 'float', message: 'Price must be a valid number')]
    public ?float $price = null;

    #[Assert\NotBlank(message: 'Category is required')]
    #[Assert\Type(type: 'integer', message: 'Category ID must be an integer')]
    public ?int $categoryId = null;

    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Please upload a valid image file (JPEG, PNG, or WebP)'
    )]
    public ?UploadedFile $thumbnailFile = null;

    #[Assert\File(
        maxSize: '100M',
        mimeTypesMessage: 'Please upload a valid file'
    )]
    public ?UploadedFile $productFile = null;

    public function __construct(
        ?string $title = null,
        ?string $shortDescription = null,
        ?string $description = null,
        ?string $about = null,
        ?float $price = null,
        ?int $categoryId = null,
        ?UploadedFile $thumbnailFile = null,
        ?UploadedFile $productFile = null
    ) {
        $this->title = $title;
        $this->shortDescription = $shortDescription;
        $this->description = $description;
        $this->about = $about;
        $this->price = $price;
        $this->categoryId = $categoryId;
        $this->thumbnailFile = $thumbnailFile;
        $this->productFile = $productFile;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'] ?? null,
            $data['shortDescription'] ?? null,
            $data['description'] ?? null,
            $data['about'] ?? null,
            isset($data['price']) ? (float) $data['price'] : null,
            isset($data['categoryId']) ? (int) $data['categoryId'] : null,
            $data['thumbnailFile'] ?? null,
            $data['productFile'] ?? null
        );
    }
}
