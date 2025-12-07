<?php

namespace App\Controller;

use App\Repository\PcproductsRepository;
use App\Repository\UserRepository;
use App\Repository\ServicebookingRepository;
use App\Repository\AuditLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdmindashboardController extends AbstractController
{
    #[Route('/admindashboard', name: 'app_admindashboard')]
    public function index(
        PcproductsRepository $productRepo,
        UserRepository $userRepo,
        ServicebookingRepository $bookingRepo,
        AuditLogRepository $auditLogRepo
    ): Response {

        // Basic counts
        $productCount = $productRepo->count([]);
        $userCount    = $userRepo->count([]);
        $bookingCount = $bookingRepo->count([]);
        $auditCount   = $auditLogRepo->count([]);

        // ✅ Count admins & staff safely in PHP
        $users      = $userRepo->findAll();
        $adminCount = 0;
        $staffCount = 0;

        foreach ($users as $user) {
            $roles = $user->getRoles();

            if (in_array('ROLE_ADMIN', $roles, true)) {
                $adminCount++;
            }
            if (in_array('ROLE_STAFF', $roles, true)) {
                $staffCount++;
            }
        }

        // ✅ Total sales (sum of product prices)
        $totalSales = $productRepo->createQueryBuilder('p')
            ->select('COALESCE(SUM(p.price), 0)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        $salesGrowth = 10; // placeholder

        // ✅ Recent activities (last 5 audit logs)
        $recentLogs = $auditLogRepo->createQueryBuilder('a')
            ->orderBy('a.changed_at', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // ✅ Resolve user IDs → User objects for Twig display
        $userIds = [];
        foreach ($recentLogs as $log) {
            if ($log->getUserId()) {
                $userIds[] = $log->getUserId();
            }
        }
        $userIds = array_unique($userIds);

        $usersById = [];
        if (!empty($userIds)) {
            $usersForLogs = $userRepo->createQueryBuilder('u')
                ->where('u.id IN (:ids)')
                ->setParameter('ids', $userIds)
                ->getQuery()
                ->getResult();

            foreach ($usersForLogs as $u) {
                $usersById[$u->getId()] = $u;
            }
        }

        return $this->render('admindashboard/index.html.twig', [
            'productCount' => $productCount,
            'userCount'    => $userCount,
            'bookingCount' => $bookingCount,
            'auditCount'   => $auditCount,

            'totalSales'   => $totalSales,
            'salesGrowth'  => $salesGrowth,

            // ✅ NEW dashboard metrics
            'adminCount'   => $adminCount,
            'staffCount'   => $staffCount,

            // ✅ Recent activity feed
            'recentLogs'   => $recentLogs,
            'usersById'    => $usersById,
        ]);
    }
}


