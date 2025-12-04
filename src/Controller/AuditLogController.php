<?php


namespace App\Controller;


use App\Entity\AuditLog;
use App\Entity\User;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;   // ✅ ADD
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AuditLogController extends AbstractController
{
    #[Route('/audit-log', name: 'app_audit_log_index')]
    public function index(
        AuditLogRepository $auditLogRepository,
        EntityManagerInterface $em
    ): Response {
        // Get all logs (latest first)
        $logs = $auditLogRepository->createQueryBuilder('a')
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();


        // Resolve changed_by user IDs → usernames
        $userIds = [];
        foreach ($logs as $log) {
            if ($log->getChangedBy()) {
                $userIds[] = $log->getChangedBy();
            }
        }
        $userIds = array_unique($userIds);


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


        // ✅ Truncate table (also resets AUTO_INCREMENT to 1 in MySQL)
        $conn->executeStatement('TRUNCATE TABLE audit_log');


        $this->addFlash('success', 'Audit history cleared.');


        return $this->redirectToRoute('app_audit_log_index');
    }
}





