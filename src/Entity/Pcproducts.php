<?php

namespace App\Entity;

use App\Repository\PcproductsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PcproductsRepository::class)]
class Pcproducts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    // Category field (dropdown values)
    #[ORM\Column(length: 255)]
    private ?string $category = null;

    // Brand field (dropdown values)
    #[ORM\Column(length: 255)]
    private ?string $brand = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdat = null;

    #[ORM\Column]
    private ?\DateTime $updatedat = null;

    #[ORM\Column]
    private ?bool $isavailable = null;

    // Image field
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    // ----------------- GETTERS & SETTERS -----------------
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCreatedat(): ?\DateTimeImmutable
    {
        return $this->createdat;
    }

    public function setCreatedat(\DateTimeImmutable $createdat): static
    {
        $this->createdat = $createdat;
        return $this;
    }

    public function getUpdatedat(): ?\DateTime
    {
        return $this->updatedat;
    }

    public function setUpdatedat(\DateTime $updatedat): static
    {
        $this->updatedat = $updatedat;
        return $this;
    }

    public function isavailable(): ?bool
    {
        return $this->isavailable;
    }

    public function setIsavailable(bool $isavailable): static
    {
        $this->isavailable = $isavailable;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }
}
