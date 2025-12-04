<?php

namespace App\EventListener;

use App\Entity\AuditLog;
use App\Entity\Pcproducts;
use App\Enum\ActionType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

class AuditLogListener
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em  = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        // CREATE
        foreach ($uow->getScheduledEntityInsertions() as $entity) {

            if (!$entity instanceof Pcproducts) {
                continue;
            }

            $meta = $em->getClassMetadata(Pcproducts::class);

            $new = [];
            foreach ($meta->getFieldNames() as $f) {
                $new[$f] = $meta->getFieldValue($entity, $f);
            }

            $this->log($em, $entity, ActionType::CREATE, null, $new);
        }

        // UPDATE
        foreach ($uow->getScheduledEntityUpdates() as $entity) {

            if (!$entity instanceof Pcproducts) {
                continue;
            }

            $changeSet = $uow->getEntityChangeSet($entity);

            $old = [];
            $new = [];

            foreach ($changeSet as $field => [$o, $n]) {
                $old[$field] = $o;
                $new[$field] = $n;
            }

            $this->log($em, $entity, ActionType::UPDATE, $old, $new);
        }

        // DELETE
        foreach ($uow->getScheduledEntityDeletions() as $entity) {

            if (!$entity instanceof Pcproducts) {
                continue;
            }

            $meta = $em->getClassMetadata(Pcproducts::class);

            $old = [];
            foreach ($meta->getFieldNames() as $f) {
                $old[$f] = $meta->getFieldValue($entity, $f);
            }

            $this->log($em, $entity, ActionType::DELETE, $old, null);
        }
    }

    private function log(
        EntityManagerInterface $em,
        object $entity,
        ActionType $type,
        ?array $old,
        ?array $new
    ): void {

        $meta = $em->getClassMetadata($entity::class);
        $id   = $meta->getFieldValue(
            $entity,
            $meta->getSingleIdentifierFieldName()
        );

        $log = new AuditLog();
        $log->setEntityName($meta->getName());
        $log->setEntityId((int)$id);
        $log->setActionType($type);
        $log->setOldData($old);
        $log->setNewData($new);

        $user = $this->security->getUser();
        $log->setChangedBy($user?->getId());
        $log->setChangedAt(new \DateTimeImmutable());

        $em->persist($log);

        $cm = $em->getClassMetadata(AuditLog::class);
        $em->getUnitOfWork()->computeChangeSet($cm, $log);
    }
}



