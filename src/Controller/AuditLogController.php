<?php

namespace App\Controller;

use App\Entity\AuditLog;
use App\Entity\User;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuditLogController extends AbstractController
{
    #[Route('/audit-log', name: 'app_audit_log_index')]
    public function index(
        AuditLogRepository $auditLogRepository,
        EntityManagerInterface $em
    ): Response {
        // 🗒️ Get all logs (oldest first so IDs are in order)
        $logs = $auditLogRepository->createQueryBuilder('a')
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();

        // 🧩 Collect unique user IDs from audit_log.user_id
        $userIds = [];
        foreach ($logs as $log) {
            /** @var AuditLog $log */
            if ($log->getUserId()) {
                $userIds[] = $log->getUserId();
            }
        }
        $userIds = array_unique($userIds);

        // 🧭 Resolve user_id → User entity so Twig can display username + roles
        $usersById = [];
        if (!empty($userIds)) {
            $userRepo = $em->getRepository(User::class);
            foreach ($userIds as $uid) {
                $user = $userRepo->find($uid);
                if ($user) {
                    $usersById[$uid] = $user;
                }
            }
        }

        return $this->render('audit_log/index.html.twig', [
            'logs'      => $logs,
            'usersById' => $usersById,
        ]);
    }

    #[Route('/audit-log/clear', name: 'app_audit_log_clear', methods: ['POST'])]
    public function clear(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid('clear_audit_log', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_audit_log_index');
        }

        $conn = $em->getConnection();

        // 🧹 Clear all logs + reset AUTO_INCREMENT back to 1
        $conn->executeStatement('TRUNCATE TABLE audit_log');

        $this->addFlash('success', 'Audit history cleared.');

        return $this->redirectToRoute('app_audit_log_index');
    }
}



