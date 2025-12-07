<?php

namespace App\Service;

use App\Entity\AuditLog;
use App\Enum\ActionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AuditLogger
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    /**
     * $targetId = the ID of the record affected (ex: user ID, product ID, etc.)
     * For LOGIN / LOGOUT you can pass null.
     */
    public function log(
        ?int $targetId,
        ActionType $type,
        ?array $oldData = null,
        ?array $newData = null
    ): void {

        // ✅ Keep AUTO_INCREMENT synced safely
        $conn = $this->em->getConnection();

        $maxId = $conn->fetchOne('SELECT MAX(id) FROM audit_log');
        $nextId = $maxId ? ((int) $maxId + 1) : 1;

        $conn->executeStatement(
            'ALTER TABLE audit_log AUTO_INCREMENT = ' . $nextId
        );

        // Create audit record
        $log = new AuditLog();

        // Target Data ID
        $log->setTargetId($targetId);

        // Action Type
        $log->setActionType($type);

        // Old/New data snapshots
        $log->setOldData($oldData);
        $log->setNewData($newData);

        // Actor = authenticated user
        $user = $this->security->getUser();
        $log->setUserId($user?->getId());

        // Timestamp
        $log->setChangedAt(new \DateTimeImmutable());

        // Persist
        $this->em->persist($log);
        $this->em->flush();
    }
}



