<?php

namespace App\Controller;

use App\Entity\Stocks;
use App\Form\StocksType;
use App\Repository\StocksRepository;
use App\Service\AuditLogger;
use App\Enum\ActionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stocks')]
final class StocksController extends AbstractController
{
    #[Route(name: 'app_stocks_index', methods: ['GET'])]
    public function index(StocksRepository $stocksRepository): Response
    {
        $user = $this->getUser();

        // Admin sees all stocks, staff sees only their own created products
        if ($this->isGranted('ROLE_ADMIN')) {
            $stocks = $stocksRepository->findAll();
        } else {
            $stocks = $stocksRepository->createQueryBuilder('s')
                ->join('s.productname', 'p')
                ->andWhere('p.createdBy = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();
        }

        $stockForms = [];
        foreach ($stocks as $stock) {
            $form = $this->createForm(StocksType::class, $stock, [
                'action' => $this->generateUrl('app_stocks_edit', ['id' => $stock->getId()]),
                'method' => 'POST',
            ]);
            $stockForms[$stock->getId()] = $form->createView();
        }

        return $this->render('stocks/index.html.twig', [
            'stocks'     => $stocks,
            'stockForms' => $stockForms,
        ]);
    }

    #[Route('/new', name: 'app_stocks_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        AuditLogger $auditLogger
    ): Response {
        $stocks = new Stocks();
        $form = $this->createForm(StocksType::class, $stocks);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($stocks);
            $entityManager->flush();

            // ✅ AUDIT — STOCK CREATE
            $auditLogger->log(
                $stocks->getId(),          // targetId
                ActionType::CREATE,
                null,
                [
                    'product_id' => $stocks->getProductname()?->getId(),
                    'product_name' => $product?->getName(), 
                    'stock'      => $stocks->getStock(),
                ]
            );

            return $this->redirectToRoute('app_stocks_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stocks/new.html.twig', [
            'stocks' => $stocks,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stocks_show', methods: ['GET'])]
    public function show(Stocks $stocks): Response
    {
        $user = $this->getUser();

        if (
            !$this->isGranted('ROLE_ADMIN')
            && $stocks->getProductname()->getCreatedBy() !== $user
        ) {
            throw $this->createAccessDeniedException('You cannot view this stock.');
        }

        return $this->render('stocks/show.html.twig', [
            'stocks' => $stocks,
        ]);
    }

#[Route('/{id}/edit', name: 'app_stocks_edit', methods: ['POST'])]
public function edit(
    Request $request,
    Stocks $stocks,
    EntityManagerInterface $entityManager,
    AuditLogger $auditLogger
): Response {
    $user = $this->getUser();


    if (
        !$this->isGranted('ROLE_ADMIN')
        && $stocks->getProductname()->getCreatedBy() !== $user
    ) {
        throw $this->createAccessDeniedException('You cannot edit this stock.');
    }


    $product  = $stocks->getProductname();
    $oldStock = $stocks->getStock();


    $form = $this->createForm(StocksType::class, $stocks);
    $form->handleRequest($request);


    if ($form->isSubmitted() && $form->isValid()) {
        if ($product) {
            $newStock = $stocks->getStock();


            // ✅ Update Pcproducts availability based on NEW stock
            $product->setIsavailable($newStock > 0);


            $entityManager->flush();


            // build old/new for audit
            $oldData = [
                'product_id'   => $product->getId(),
                'product_name' => $product->getName(),
                'stock'        => $oldStock,
            ];


            $newData = [
                'product_id'   => $product->getId(),
                'product_name' => $product->getName(),
                'stock'        => $newStock,
            ];


            if ($newStock > $oldStock) {
                $newData['change'] = 'increased';
            } elseif ($newStock < $oldStock) {
                $newData['change'] = 'decreased';
            }


            if ($newStock !== $oldStock) {
                $auditLogger->log(
                    $stocks->getId(),          // targetId
                    ActionType::UPDATE,
                    $oldData,
                    $newData
                );
            }
        }


        return $this->redirectToRoute('app_stocks_index', [], Response::HTTP_SEE_OTHER);
    }


    return $this->render('stocks/edit.html.twig', [
        'stocks' => $stocks,
        'form'   => $form,
    ]);
}

#[Route('/{id}', name: 'app_stocks_delete', methods: ['POST'])]
public function delete(
    Request $request,
    Stocks $stocks,
    EntityManagerInterface $entityManager,
    AuditLogger $auditLogger
): Response {
    $user = $this->getUser();


    if (
        !$this->isGranted('ROLE_ADMIN')
        && $stocks->getProductname()->getCreatedBy() !== $user
    ) {
        throw $this->createAccessDeniedException('You cannot delete this stock.');
    }


    if ($this->isCsrfTokenValid('delete' . $stocks->getId(), $request->getPayload()->getString('_token'))) {
        $deletedId = $stocks->getId();
        $product   = $stocks->getProductname();


        $oldData = [
            'product_id'   => $product?->getId(),
            'product_name' => $product?->getName(),
            'deleted'      => true,
        ];


        $entityManager->remove($stocks);
        $entityManager->flush();


        $auditLogger->log(
            $deletedId,                 // targetId
            ActionType::DELETE,
            $oldData,
            null
        );
    }


    return $this->redirectToRoute('app_stocks_index', [], Response::HTTP_SEE_OTHER);
}






}



