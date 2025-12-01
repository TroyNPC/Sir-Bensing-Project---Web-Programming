<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class AuditLogger
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function log(string $entityName, int $entityId, string $actionType, $oldData = null, $newData = null)
    {
        $conn = $this->em->getConnection();
        $user = $this->security->getUser();

        $conn->insert('audit_log', [
            'entity_name' => $entityName,
            'entity_id' => $entityId,
            'action_type' => $actionType,
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null,
            'changed_by' => $user ? $user->getId() : null,
        ]);
    }
}
