<?php

namespace App\Controller;

use App\Repository\WalkinOrdersRepository;
use App\Repository\PcproductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StaffDashboardController extends AbstractController
{
    #[Route('/staff/dashboard', name: 'app_staff_dashboard')]
    public function index(
        WalkinOrdersRepository $walkinOrdersRepo,
        PcproductsRepository $productRepo
    ): Response {

        // 🔹 Logged-in staff
        $staff = $this->getUser();

        // 🔹 Total orders created by this staff
        $totalOrders = $walkinOrdersRepo->count([
            'createdBy' => $staff
        ]);

        // 🔹 Total unpaid (PENDING)
        $unpaidOrders = $walkinOrdersRepo->count([
            'createdBy'     => $staff,
            'paymentStatus' => 'unpaid'
        ]);

        // 🔹 Total paid (COMPLETED)
        $paidOrders = $walkinOrdersRepo->count([
            'createdBy'     => $staff,
            'paymentStatus' => 'paid'
        ]);

                // 🔹 Total products created by this staff
        $productCount = $productRepo->count([
            'createdBy' => $staff
        ]);

        return $this->render('staff_dashboard/index.html.twig', [
            'totalOrders'  => $totalOrders,
            'unpaidOrders' => $unpaidOrders,
            'paidOrders'   => $paidOrders,
            'productCount' => $productCount,
        ]);
    }
}
