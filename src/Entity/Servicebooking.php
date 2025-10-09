<?php

namespace App\Entity;

use App\Repository\ServicebookingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServicebookingRepository::class)]
class Servicebooking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name is required!')]
    #[Assert\Regex(
        pattern: '/^[A-Za-z\s]+$/',
        message: 'Name can only contain letters and spaces.'
    )]
    private ?string $customerName = null;

    #[ORM\Column(length: 255)]
    private ?string $serviceType = null;

    #[ORM\Column(length: 255)]
    private ?string $advisercategory = null;

    #[ORM\Column]
    private ?\DateTime $preferredDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdat = null;

    // ----------------- CONSTRUCTOR -----------------
    public function __construct()
    {
        // Automatically set createdat to current datetime
        $this->createdat = new \DateTimeImmutable();
    }

    // ----------------- GETTERS & SETTERS -----------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): static
    {
        $this->customerName = $customerName;
        return $this;
    }

    public function getServiceType(): ?string
    {
        return $this->serviceType;
    }

    public function setServiceType(string $serviceType): static
    {
        $this->serviceType = $serviceType;
        return $this;
    }

    public function getAdvisercategory(): ?string
    {
        return $this->advisercategory;
    }

    public function setAdvisercategory(string $advisercategory): static
    {
        $this->advisercategory = $advisercategory;
        return $this;
    }

    public function getPreferredDate(): ?\DateTime
    {
        return $this->preferredDate;
    }

    public function setPreferredDate(\DateTime $preferredDate): static
    {
        $this->preferredDate = $preferredDate;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedat(\DateTimeImmutable $createdat): static
    {
        $this->createdat = $createdat;
        return $this;
    }
}
