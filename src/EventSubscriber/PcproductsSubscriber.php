<?php

namespace App\EventSubscriber;

use App\Entity\Pcproducts;
use App\Entity\InventoryLog;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;

class PcproductsSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    /**
     * Create or update inventory log automatically when a product changes
     */
    private function syncInventoryLog(Pcproducts $entity, ?int $stock = null): void
    {
        // Try to find an existing log for this product
        $existingLog = $this->em->getRepository(InventoryLog::class)->findOneBy([
            'productname' => $entity
        ]);

        $stock = $stock ?? (method_exists($entity, 'getStock') ? $entity->getStock() : 1);
        $entity->setIsavailable($stock > 0);

        if ($existingLog) {
            // Update existing log
            $existingLog->setStock($stock);
            $existingLog->setImage($entity->getImage());
            $existingLog->setCreatedAt(new \DateTimeImmutable());
        } else {
            // Create a new log
            $inventoryLog = new InventoryLog();
            $inventoryLog->setProductname($entity);
            $inventoryLog->setStock($stock);
            $inventoryLog->setImage($entity->getImage());
            $inventoryLog->setCreatedAt(new \DateTimeImmutable());
            $this->em->persist($inventoryLog);
        }

        $this->em->flush();
    }

    /**
     * Reset AUTO_INCREMENT utility
     */
    private function resetAutoIncrement(string $tableName): void
    {
        $connection = $this->em->getConnection();
        $maxId = $connection->fetchOne("SELECT MAX(id) FROM {$tableName}");
        $nextId = $maxId ? $maxId + 1 : 1;
        $connection->executeStatement("ALTER TABLE {$tableName} AUTO_INCREMENT = {$nextId}");
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Pcproducts) {
            $this->syncInventoryLog($entity);
        }
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Pcproducts) {
            $this->syncInventoryLog($entity);
        }
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Pcproducts) {
            // Optional: log the deletion as a 0-stock entry
            $this->syncInventoryLog($entity, 0);

            // Reset auto increments after removal
            $this->resetAutoIncrement('pcproducts');
            $this->resetAutoIncrement('inventory_log');
        }
    }
}
