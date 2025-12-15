<?php


namespace App\Entity;


use App\Repository\WalkinOrdersRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
// 🔹 NEW: import User so we can relate orders to a user
use App\Entity\User;


#[ORM\Entity(repositoryClass: WalkinOrdersRepository::class)]
#[ORM\HasLifecycleCallbacks]
class WalkinOrders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    // 🔹 NEW: who created this walk-in order
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $createdBy = null;


    // Name of buyer
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Buyer name is required.')]
    private ?string $buyerName = null;


    // Contact (simple PH mobile style; you can loosen this later)
    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Contact number is required.')]
    #[Assert\Regex(
        pattern: '/^(09|\+639)\d{9}$/',
        message: 'Please enter a valid PH mobile number (e.g. 09XXXXXXXXX or +639XXXXXXXXX).'
    )]
    private ?string $contact = null;


    // Payment method: cash / credit card / gcash / etc.
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Payment method is required.')]
    #[Assert\Choice(
        choices: ['cash', 'credit_card', 'debit_card', 'gcash', 'bank_transfer'],
        message: 'Please select a valid payment method.'
    )]
    private ?string $paymentMethod = null;


    // Amount paid status: Paid / Not paid yet
    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Payment status is required.')]
    #[Assert\Choice(
        choices: ['unpaid', 'paid'],
        message: 'Payment status must be either "unpaid" or "paid".'
    )]
    private ?string $paymentStatus = 'unpaid';


    // Product name: connected to Pcproducts
    #[ORM\ManyToOne(targetEntity: Pcproducts::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull(message: 'Please select a product.')]
    private ?Pcproducts $product = null;


    // Amount purchased
    #[ORM\Column]
    #[Assert\NotNull(message: 'Amount purchased is required.')]
    #[Assert\Positive(message: 'Amount purchased must be at least 1.')]
    private ?int $quantity = null;


    // Printable warranty text (we’ll auto-fill a default NexusCore warranty)
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $warrantyText = null;


    // Date of purchase (complete datetime)
    #[ORM\Column]
    private ?\DateTimeImmutable $purchaseDate = null;


    // --------- LIFECYCLE HOOKS ---------
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if ($this->purchaseDate === null) {
            $this->purchaseDate = new \DateTimeImmutable();
        }


        // Auto-generate a simple NexusCore warranty if not filled
        if ($this->warrantyText === null) {
            $this->warrantyText =
                "NexusCore Warranty\n\n".
                "Thank you for your purchase at NexusCore.\n".
                "This product is covered by a limited warranty from the date of purchase.\n".
                "Please keep this document and your receipt for any service or claim.\n\n".
                "Date of Purchase: ".$this->purchaseDate?->format('Y-m-d H:i:s');
        }
    }


    // --------- GETTERS & SETTERS ---------


    public function getId(): ?int
    {
        return $this->id;
    }


    // 🔹 NEW: createdBy getter/setter
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }


    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }


    public function getBuyerName(): ?string
    {
        return $this->buyerName;
    }


    public function setBuyerName(string $buyerName): self
    {
        $this->buyerName = $buyerName;
        return $this;
    }


    public function getContact(): ?string
    {
        return $this->contact;
    }


    public function setContact(string $contact): self
    {
        $this->contact = $contact;
        return $this;
    }


    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }


    public function setPaymentMethod(string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }


    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }


    public function setPaymentStatus(string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }


    public function getProduct(): ?Pcproducts
    {
        return $this->product;
    }


    public function setProduct(?Pcproducts $product): self
    {
        $this->product = $product;
        return $this;
    }


    public function getQuantity(): ?int
    {
        return $this->quantity;
    }


    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }


    public function getWarrantyText(): ?string
    {
        return $this->warrantyText;
    }


    public function setWarrantyText(?string $warrantyText): self
    {
        $this->warrantyText = $warrantyText;
        return $this;
    }


    public function getPurchaseDate(): ?\DateTimeImmutable
    {
        return $this->purchaseDate;
    }


    public function setPurchaseDate(\DateTimeImmutable $purchaseDate): self
    {
        $this->purchaseDate = $purchaseDate;
        return $this;
    }
}






