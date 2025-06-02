<?php

namespace App\Dto\Product;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class ProductUpdateDto
{
    #[Assert\Length(max: 50, maxMessage: 'Title cannot be longer than 50 characters')]
    public ?string $title = null;

    #[Assert\Length(max: 150, maxMessage: 'Short description cannot be longer than 150 characters')]
    public ?string $shortDescription = null;

    public ?string $description = null;
    public ?string $about = null;

    #[Assert\Positive(message: 'Price must be positive')]
    #[Assert\Type(type: 'float', message: 'Price must be a valid number')]
    public ?float $price = null;

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
}
