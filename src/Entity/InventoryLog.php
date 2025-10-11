<?php

namespace App\Entity;

use App\Repository\InventoryLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryLogRepository::class)]
class InventoryLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inventorylogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pcproducts $productname = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $actionType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductname(): ?Pcproducts
    {
        return $this->productname;
    }

    public function setProductname(?Pcproducts $productname): static
    {
        $this->productname = $productname;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getActionType(): ?string
    {
        return $this->actionType;
    }

    public function setActionType(string $actionType): static
    {
        $this->actionType = $actionType;

        return $this;
    }
}
