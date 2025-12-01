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

    #[ORM\Column(length: 255)]
    private ?string $entity_name = null;

    #[ORM\Column]
    private ?int $entity_id = null;

    #[ORM\Column(enumType: ActionType::class)]
    private ?ActionType $action_type = null;

    #[ORM\Column(nullable: true)]
    private ?array $old_data = null;

    #[ORM\Column(nullable: true)]
    private ?array $new_data = null;

    #[ORM\Column(nullable: true)]
    private ?int $changed_by = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $changed_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntityName(): ?string
    {
        return $this->entity_name;
    }

    public function setEntityName(string $entity_name): static
    {
        $this->entity_name = $entity_name;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entity_id;
    }

    public function setEntityId(int $entity_id): static
    {
        $this->entity_id = $entity_id;

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

    public function getChangedBy(): ?int
    {
        return $this->changed_by;
    }

    public function setChangedBy(?int $changed_by): static
    {
        $this->changed_by = $changed_by;

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
