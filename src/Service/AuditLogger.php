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

    public function log(
        string $entityName,
        int $entityId,
        ActionType $type,
        ?array $oldData,
        ?array $newData
    ): void
    {
        $log = new AuditLog();

        $log->setEntityName($entityName);
        $log->setEntityId($entityId);
        $log->setActionType($type);

        $log->setOldData($oldData);
        $log->setNewData($newData);

        $user = $this->security->getUser();
        $log->setChangedBy($user?->getId());
        $log->setChangedAt(new \DateTimeImmutable());

        $this->em->getConnection()
            ->executeStatement('ALTER TABLE audit_log AUTO_INCREMENT = 1');
        $this->em->persist($log);
        $this->em->flush();
    }
}



