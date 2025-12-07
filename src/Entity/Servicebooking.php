<?php

namespace App\Entity;

use App\Repository\ServicebookingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;

#[ORM\Entity(repositoryClass: ServicebookingRepository::class)]
class Servicebooking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name is required!')]
    private ?string $customerName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Please select a service type.')]
    private ?string $serviceType = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Please select an adviser category.')]
    private ?string $advisercategory = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Preferred date is required.')]
    #[Assert\GreaterThan('now', message: 'Preferred date must be in the future.')]
    private ?\DateTime $preferredDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdat = null;

    // ✅ Staff relation
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $staff = null;

    // Contact number from customer
    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Please enter your contact number.')]
    #[Assert\Regex(
        pattern: '/^\d{10,11}$/',
        message: 'Please enter a valid mobile number (10–11 digits only).'
    )]
    private ?string $contactNumber = null;


    // Email address of the customer
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Please enter your email address.')]
    #[Assert\Email(
        message: 'Please enter a valid email address.'
    )]
    private ?string $emailAddress = null;


    // Status: ongoing | paused | completed
    #[ORM\Column(length: 20)]
    private ?string $status = 'ongoing';

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
        return $this->createdat;
    }

    public function setCreatedat(\DateTimeImmutable $createdat): static
    {
        $this->createdat = $createdat;
        return $this;
    }

    // ✅ Staff getter and setter
    public function getStaff(): ?User
    {
        return $this->staff;
    }

    public function setStaff(?User $staff): static
    {
        $this->staff = $staff;
        return $this;
    }
    public function getContactNumber(): ?string
    {
        return $this->contactNumber;
    }


    public function setContactNumber(string $contactNumber): self
    {
        $this->contactNumber = $contactNumber;


        return $this;
    }


    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }


    public function setEmailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;


        return $this;
    }


    /**
     * Status: 'ongoing', 'paused', or 'completed'
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }


    public function setStatus(string $status): self
    {
        // Optional guard, remove if you don’t want it strict:
        if (!in_array($status, ['ongoing', 'paused', 'completed'], true)) {
            throw new \InvalidArgumentException('Invalid status value');
        }


        $this->status = $status;


        return $this;
    }
}
