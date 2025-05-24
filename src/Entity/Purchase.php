<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\CreatePurchaseController;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('ROLE_ADMIN') or object.getBuyer() == user or object.getProduct().getSeller() == user",
            normalizationContext: ['groups' => ['purchase:read']]
        ),
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['purchase:read']]
        ),
        new Post(
            uriTemplate: '/purchases',
            controller: CreatePurchaseController::class,
            denormalizationContext: ['groups' => ['purchase:create']],
            security: "is_granted('ROLE_USER')"
        )
    ],
)]
class Purchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['purchase:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['purchase:read'])]
    private ?User $buyer = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['purchase:read', 'purchase:create'])]
    private ?Product $product = null;

    #[ORM\Column]
    #[Groups(['purchase:read'])]
    private ?float $amount = null;

    #[ORM\Column(length: 255)]
    #[Groups(['purchase:read'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Groups(['purchase:read'])]
    private ?\DateTimeImmutable $purchasedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentIntentId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['purchase:read'])]
    private ?string $downloadToken = null;

    public function __construct()
    {
        $this->purchasedAt = new \DateTimeImmutable();
        $this->status = 'pending';
        $this->downloadToken = bin2hex(random_bytes(32));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    public function setBuyer(?User $buyer): static
    {
        $this->buyer = $buyer;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        $this->amount = $product ? $product->getPrice() : null;
        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getPurchasedAt(): ?\DateTimeImmutable
    {
        return $this->purchasedAt;
    }

    public function setPurchasedAt(\DateTimeImmutable $purchasedAt): static
    {
        $this->purchasedAt = $purchasedAt;
        return $this;
    }

    public function getPaymentIntentId(): ?string
    {
        return $this->paymentIntentId;
    }

    public function setPaymentIntentId(?string $paymentIntentId): static
    {
        $this->paymentIntentId = $paymentIntentId;
        return $this;
    }

    public function getDownloadToken(): ?string
    {
        return $this->downloadToken;
    }

    public function setDownloadToken(string $downloadToken): static
    {
        $this->downloadToken = $downloadToken;
        return $this;
    }
}
