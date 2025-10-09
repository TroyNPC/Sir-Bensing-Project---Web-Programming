<?php

namespace App\Controller;

use App\Repository\PcproductsRepository;
use App\Repository\UserRepository;
use App\Repository\ServicebookingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdmindashboardController extends AbstractController
{
    #[Route('/admindashboard', name: 'app_admindashboard')]
    public function index(
        PcproductsRepository $productRepo,
        UserRepository $userRepo,
        ServicebookingRepository $bookingRepo
    ): Response {
        // Counts
        $productCount = $productRepo->count([]);
        $userCount = $userRepo->count([]);
        $bookingCount = $bookingRepo->count([]);

        // Optional — calculate total sales (sum of product prices)
        $totalSales = $productRepo->createQueryBuilder('p')
            ->select('SUM(p.price)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // You can later calculate growth % dynamically if you track createdAt
        $salesGrowth = 10; // placeholder

        return $this->render('admindashboard/index.html.twig', [
            'productCount' => $productCount,
            'userCount' => $userCount,
            'bookingCount' => $bookingCount,
            'totalSales' => $totalSales,
            'salesGrowth' => $salesGrowth,
        ]);
    }
}
