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
        $stocks = $stocksRepository->findAll();
        $stockForms = [];


        foreach ($stocks as $stock) {
            $form = $this->createForm(StocksType::class, $stock, [
                'action' => $this->generateUrl('app_stocks_edit', ['id' => $stock->getId()]),
                'method' => 'POST',
            ]);
            $stockForms[$stock->getId()] = $form->createView();
        }


        return $this->render('stocks/index.html.twig', [
            'stocks' => $stocks,
            'stockForms' => $stockForms,
        ]);
    }




    #[Route('/new', name: 'app_stocks_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        AuditLogger $auditLogger
    ): Response
    {
        $stocks = new Stocks();
        $form = $this->createForm(StocksType::class, $stocks);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            $entityManager->persist($stocks);
            $entityManager->flush();


            // ✅ AUDIT — STOCK CREATE
            $auditLogger->log(
                Stocks::class,
                $stocks->getId(),
                ActionType::CREATE,
                null,
                [
                    'product_id' => $stocks->getProductname()?->getId(),
                    'stock' => $stocks->getStock(),
                ]
            );


            return $this->redirectToRoute(
                'app_stocks_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }


        return $this->render('stocks/new.html.twig', [
            'stocks' => $stocks,
            'form'   => $form,
        ]);
    }




    #[Route('/{id}', name: 'app_stocks_show', methods: ['GET'])]
    public function show(Stocks $stocks): Response
    {
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
    ): Response
    {
        // ✅ CAPTURE OLD STOCK BEFORE FORM BINDS NEW VALUE
        $oldStock = $stocks->getStock();


        $form = $this->createForm(StocksType::class, $stocks);
        $form->handleRequest($request);




        if ($form->isSubmitted() && $form->isValid()) {


            // Get related product
            $product = $stocks->getProductname();


            if ($product) {


                $newStock = $stocks->getStock();


                // ✅ Update Pcproducts availability based on NEW stock
                $product->setIsavailable($newStock > 0);


                $entityManager->flush();


                // ✅ ADD CHANGE DIRECTION
                $newData = [
                    'stock' => $newStock
                ];


                if ($newStock > $oldStock) {
                    $newData['change'] = 'increased';
                } elseif ($newStock < $oldStock) {
                    $newData['change'] = 'decreased';
                }


                // ✅ AUDIT LOG ONLY IF STOCK REALLY CHANGED
                if ($newStock !== $oldStock) {


                    $auditLogger->log(
                        Stocks::class,
                        $stocks->getId(),
                        ActionType::UPDATE,
                        ['stock' => $oldStock],
                        $newData
                    );
                }
            }


            return $this->redirectToRoute(
                'app_stocks_index',
                [],
                Response::HTTP_SEE_OTHER
            );
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
    ): Response
    {
        if ($this->isCsrfTokenValid('delete' . $stocks->getId(), $request->getPayload()->getString('_token'))) {


            $deletedId = $stocks->getId();


            $entityManager->remove($stocks);
            $entityManager->flush();


            // ✅ AUDIT — STOCK DELETE
            $auditLogger->log(
                Stocks::class,
                $deletedId,
                ActionType::DELETE,
                ['deleted' => true],
                null
            );
        }


        return $this->redirectToRoute(
            'app_stocks_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}





