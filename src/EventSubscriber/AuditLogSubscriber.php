<?php

namespace App\EventSubscriber;

use App\Entity\AuditLog;
use App\Entity\Pcproducts;
use App\Enum\ActionType;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

class AuditLogSubscriber implements EventSubscriberInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush, // ✅ one reliable event for everything
        ];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        file_put_contents(
            _DIR_.'/hit.txt',
            'SUBSCRIBER HIT' .date('c').PHP_EOL,
            FILE_APPEND
        );
        $em  = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        /*
         |--------------------------------------------------------------------------
         | CREATE
         |--------------------------------------------------------------------------
         */
        foreach ($uow->getScheduledEntityInsertions() as $entity) {

            if ($entity instanceof AuditLog) {
                continue;
            }

            if (!$entity instanceof Pcproducts) {
                continue;
            }

            $meta = $em->getClassMetadata($entity::class);

            $newData = [];
            foreach ($meta->getFieldNames() as $field) {
                $newData[$field] = $meta->getFieldValue($entity, $field);
            }

            $this->saveLog($em, $entity, ActionType::CREATE, null, $newData);
        }

        /*
         |--------------------------------------------------------------------------
         | UPDATE
         |--------------------------------------------------------------------------
         */
        foreach ($uow->getScheduledEntityUpdates() as $entity) {

            if ($entity instanceof AuditLog) {
                continue;
            }

            if (!$entity instanceof Pcproducts) {
                continue;
            }

            $changes = $uow->getEntityChangeSet($entity);

            $oldData = [];
            $newData = [];

            foreach ($changes as $field => [$old, $new]) {
                $oldData[$field] = $old;
                $newData[$field] = $new;
            }

            $this->saveLog(
                $em,
                $entity,
                ActionType::UPDATE,
                $oldData,
                $newData
            );
        }

        /*
         |--------------------------------------------------------------------------
         | DELETE
         |--------------------------------------------------------------------------
         */
        foreach ($uow->getScheduledEntityDeletions() as $entity) {

            if ($entity instanceof AuditLog) {
                continue;
            }

            if (!$entity instanceof Pcproducts) {
                continue;
            }

            $meta = $em->getClassMetadata($entity::class);

            $oldData = [];
            foreach ($meta->getFieldNames() as $field) {
                $oldData[$field] = $meta->getFieldValue($entity, $field);
            }

            $this->saveLog(
                $em,
                $entity,
                ActionType::DELETE,
                $oldData,
                null
            );
        }
    }

    private function saveLog(
        EntityManagerInterface $em,
        object $entity,
        ActionType $type,
        ?array $old,
        ?array $new
    ): void {

        $meta = $em->getClassMetadata($entity::class);
        $idField = $meta->getSingleIdentifierFieldName();

        $audit = new AuditLog();

        $audit->setEntityName($meta->getName());
        $audit->setEntityId(
            (int)$meta->getFieldValue($entity, $idField)
        );
        $audit->setActionType($type);
        $audit->setOldData($old);
        $audit->setNewData($new);

        $user = $this->security->getUser();
        $audit->setChangedBy($user?->getId());

        $audit->setChangedAt(new \DateTimeImmutable());

        $em->persist($audit);

        $cm = $em->getClassMetadata(AuditLog::class);
        $em->getUnitOfWork()->computeChangeSet($cm, $audit);
    }
}



