<?php

namespace App\Entity;

use App\Repository\PcproductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PcproductsRepository::class)]
#[ORM\HasLifecycleCallbacks] // ✅ keeps PrePersist working
class Pcproducts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Product name cannot be empty.")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Category cannot be empty.")]
    private ?string $category = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Brand cannot be empty.")]
    private ?string $brand = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Price cannot be empty.")]
    #[Assert\Regex(
        pattern: "/^[0-9]+(\.[0-9]{1,2})?$/",
        message: "Price must be a valid number (letters are not allowed)."
    )]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Description cannot be empty.")]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdat = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: "Date cannot be empty.")]
    private ?\DateTime $updatedat = null;

    #[ORM\Column]
    private ?bool $isavailable = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * @var Collection<int, Stocks>
     */
    #[ORM\OneToMany(targetEntity: Stocks::class, mappedBy: 'productname', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $stocks;


    public function __construct()
    {
        $this->stocks = new ArrayCollection();
    }

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

    // ----------------- AUTO DATE HANDLER -----------------
    #[ORM\PrePersist] // ✅ Automatically set only when creating
    public function onPrePersist(): void
    {
        $this->createdat = new \DateTimeImmutable();
    }

    /**
     * @return Collection<int, Stocks>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stocks $stock): static
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->setProductname($this);
        }

        return $this;
    }

    public function removeStock(Stocks $stock): static
    {
        if ($this->stocks->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getProductname() === $this) {
                $stock->setProductname(null);
            }
        }

        return $this;
    }
}
