<?php


namespace App\Entity;


use App\Enum\ActionType;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    // This is your "Target Data" ID (e.g., the user ID, product ID, etc.)
    #[ORM\Column(name: 'target_id', nullable: true)]
    private ?int $targetId = null;


    #[ORM\Column(enumType: ActionType::class)]
    private ?ActionType $action_type = null;


    #[ORM\Column(nullable: true)]
    private ?array $old_data = null;


    #[ORM\Column(nullable: true)]
    private ?array $new_data = null;


    // changed_by → user_id
    #[ORM\Column(name: 'user_id', nullable: true)]
    private ?int $userId = null;


    #[ORM\Column]
    private ?\DateTimeImmutable $changed_at = null;


    // -------------------
    // Getters / Setters
    // -------------------


    public function getId(): ?int
    {
        return $this->id;
    }


    // Target / entity that was affected
    public function getTargetId(): ?int
    {
        return $this->targetId;
    }


    public function setTargetId(?int $targetId): static
    {
        $this->targetId = $targetId;


        return $this;
    }


    public function getActionType(): ?ActionType
    {
        return $this->action_type;
    }


    public function setActionType(ActionType $action_type): static
    {
        $this->action_type = $action_type;


        return $this;
    }


    public function getOldData(): ?array
    {
        return $this->old_data;
    }


    public function setOldData(?array $old_data): static
    {
        $this->old_data = $old_data;


        return $this;
    }


    public function getNewData(): ?array
    {
        return $this->new_data;
        
    }


    public function setNewData(?array $new_data): static
    {
        $this->new_data = $new_data;


        return $this;
    }


    public function getUserId(): ?int
    {
        return $this->userId;
    }


    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;


        return $this;
    }


    public function getChangedAt(): ?\DateTimeImmutable
    {
        return $this->changed_at;
    }


    public function setChangedAt(\DateTimeImmutable $changed_at): static
    {
        $this->changed_at = $changed_at;


        return $this;
    }
}





