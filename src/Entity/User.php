<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 200)]
    private ?string $fullname = null;

    #[ORM\Column(length: 200)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address = null;

    /**
     * @var Collection<int, Servicebooking>
     */
    #[ORM\OneToMany(targetEntity: Servicebooking::class, mappedBy: 'customername')]
    private Collection $servicebookings;

    public function __construct()
    {
        $this->servicebookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, Servicebooking>
     */
    public function getServicebookings(): Collection
    {
        return $this->servicebookings;
    }

    public function addServicebooking(Servicebooking $servicebooking): static
    {
        if (!$this->servicebookings->contains($servicebooking)) {
            $this->servicebookings->add($servicebooking);
            $servicebooking->setCustomername($this);
        }

        return $this;
    }

    public function removeServicebooking(Servicebooking $servicebooking): static
    {
        if ($this->servicebookings->removeElement($servicebooking)) {
            // set the owning side to null (unless already changed)
            if ($servicebooking->getCustomername() === $this) {
                $servicebooking->setCustomername(null);
            }
        }

        return $this;
    }
}
