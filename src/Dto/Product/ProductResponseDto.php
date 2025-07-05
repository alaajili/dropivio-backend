<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Dto\Product;

use App\Entity\Product;
use Symfony\Component\Serializer\Annotation\Groups;

class ProductResponseDto
{
    #[Groups(['product:read'])]
    public int $id;
    
    #[Groups(['product:read'])]
    public string $title;
    
    #[Groups(['product:read'])]
    public string $shortDescription;
    
    #[Groups(['product:read'])]
    public string $description;
    
    #[Groups(['product:read'])]
    public ?string $about;
    
    #[Groups(['product:read'])]
    public float $price;
    
    #[Groups(['product:read'])]
    public string $thumbnailUrl;
    
    #[Groups(['product:read'])]
    public string $createdAt;
    
    #[Groups(['product:read'])]
    public ?string $updatedAt;
    
    #[Groups(['product:read'])]
    public array $category;
    
    #[Groups(['product:read'])]
    public array $seller;

    public static function fromEntity(Product $product): self
    {
        $instance = new self();
        $instance->id = $product->getId();
        $instance->title = $product->getTitle();
        $instance->shortDescription = $product->getShortDescription();
        $instance->description = $product->getDescription();
        $instance->about = $product->getAbout();
        $instance->price = $product->getPrice();
        $instance->thumbnailUrl = $product->getThumbnailUrl();
        $instance->createdAt = $product->getCreatedAt()->format(\DateTimeInterface::ATOM);
        $instance->updatedAt = $product->getUpdatedAt()?->format(\DateTimeInterface::ATOM);
        
        // Convert category to array
        $category = $product->getCategory();
        $instance->category = [
            'id' => $category->getId(),
            'name' => $category->getName(),
        ];
        
        // Convert seller to array
        $seller = $product->getSeller();
        $instance->seller = [
            'id' => $seller->getId(),
            'firstName' => $seller->getFirstName(),
            'lastName' => $seller->getLastName(),
        ];

        return $instance;
    }
}
