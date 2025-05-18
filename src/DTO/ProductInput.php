<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProductInput
{
    #[Assert\NotBlank]
    public ?string $title = null;

    #[Assert\NotBlank]
    public ?string $shortDescription = null;

    #[Assert\NotBlank]
    public ?string $description = null;

    public ?string $about = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?float $price = null;

    #[Assert\NotBlank]
    public ?string $thumbnailUrl = null;

    #[Assert\NotBlank]
    public ?string $fileUrl = null;

    /**
     * Category ID (just the numeric ID, not the IRI)
     */
    #[Assert\NotBlank]
    public int $category;
}
