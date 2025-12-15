<?php


namespace App\Controller;


use App\Entity\WalkinOrders;
use App\Entity\Pcproducts;
use App\Entity\Stocks;
use App\Form\WalkinOrdersType;
use App\Repository\WalkinOrdersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


// ✅ Audit logger
use App\Service\AuditLogger;
use App\Enum\ActionType;


#[Route('/walkin/orders')]
final class WalkinOrdersController extends AbstractController
{
    #[Route(name: 'app_walkin_orders_index', methods: ['GET'])]
    public function index(WalkinOrdersRepository $walkinOrdersRepository): Response
    {
        $user = $this->getUser();


        if (!$user) {
            throw $this->createAccessDeniedException('Login required.');
        }


        // ✅ ROLE FILTERING
        if ($this->isGranted('ROLE_ADMIN')) {
            // Admin sees everything
            $orders = $walkinOrdersRepository->findAll();
        }
        elseif ($this->isGranted('ROLE_STAFF')) {
            // Staff sees only what THEY created
            $orders = $walkinOrdersRepository->findBy([
                'createdBy' => $user
            ]);
        }
        else {
            throw $this->createAccessDeniedException('Access denied.');
        }


        return $this->render('walkin_orders/index.html.twig', [
            'walkin_orders' => $orders,
        ]);
    }


    #[Route('/new', name: 'app_walkin_orders_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        AuditLogger $auditLogger
    ): Response {


        $walkinOrder = new WalkinOrders();


        // ✅ Attach logged-in user as creator
        $walkinOrder->setCreatedBy($this->getUser());


        $form = $this->createForm(WalkinOrdersType::class, $walkinOrder);
        $form->handleRequest($request);


        // ✅ STOCK MAP (FOR DISPLAY)
        $stocksMap = [];
        $stockRepo = $entityManager->getRepository(Stocks::class);
        $allStocks = $stockRepo->findAll();


        foreach ($allStocks as $stock) {
            $product = $stock->getProductname();
            if ($product) {
                $stocksMap[$product->getId()] = $stock->getStock();
            }
        }


        if ($form->isSubmitted() && $form->isValid()) {


            $product  = $walkinOrder->getProduct();
            $quantity = $walkinOrder->getQuantity();


            if ($product) {


                $stockRecord = $stockRepo->findOneBy(['productname' => $product]);
                $available   = $stockRecord ? $stockRecord->getStock() : 0;


                if ($quantity > $available) {


                    $this->addFlash(
                        'danger',
                        sprintf(
                            'Not enough stock for "%s". Available: %d, requested: %d.',
                            $product->getName(),
                            $available,
                            $quantity
                        )
                    );


                    return $this->render('walkin_orders/new.html.twig', [
                        'walkin_order' => $walkinOrder,
                        'form'         => $form,
                        'stocksMap'    => $stocksMap,
                    ]);
                }


                if ($stockRecord) {
                    $stockRecord->setStock($available - $quantity);
                    $entityManager->persist($stockRecord);
                }
            }


            $entityManager->persist($walkinOrder);
            $entityManager->flush();


            // ✅ AUDIT — CREATE
            $newData = [
                'id'             => $walkinOrder->getId(),
                'buyerName'      => $walkinOrder->getBuyerName(),
                'contact'        => $walkinOrder->getContact(),
                'paymentMethod'  => $walkinOrder->getPaymentMethod(),
                'paymentStatus'  => $walkinOrder->getPaymentStatus(),
                'productId'      => $product?->getId(),
                'productName'    => $product?->getName(),
                'quantity'       => $walkinOrder->getQuantity(),
                'purchaseDate'   => $walkinOrder->getPurchaseDate()?->format('Y-m-d H:i:s'),
                'warrantyText'   => $walkinOrder->getWarrantyText(),
                'remainingStock'=> $stockRecord?->getStock(),
            ];


            $auditLogger->log(
                $walkinOrder->getId(),
                ActionType::CREATE,
                null,
                $newData
            );


            $this->addFlash('success', 'Walk-in order created successfully.');


            return $this->redirectToRoute('app_walkin_orders_index');
        }


        return $this->render('walkin_orders/new.html.twig', [
            'walkin_order' => $walkinOrder,
            'form'         => $form,
            'stocksMap'    => $stocksMap,
        ]);
    }


    #[Route('/{id}', name: 'app_walkin_orders_show', methods: ['GET'])]
    public function show(WalkinOrders $walkinOrder): Response
    {
        return $this->render('walkin_orders/show.html.twig', [
            'walkin_order' => $walkinOrder,
        ]);
    }


#[Route('/{id}/edit', name: 'app_walkin_orders_edit', methods: ['GET', 'POST'])]
public function edit(
    Request $request,
    WalkinOrders $walkinOrder,
    EntityManagerInterface $entityManager,
    AuditLogger $auditLogger
): Response {


    // 🔹 snapshot BEFORE edit
    $originalProduct  = $walkinOrder->getProduct();
    $originalQuantity = $walkinOrder->getQuantity();




    $oldData = [
        'id'            => $walkinOrder->getId(),
        'buyerName'     => $walkinOrder->getBuyerName(),
        'contact'       => $walkinOrder->getContact(),
        'paymentMethod' => $walkinOrder->getPaymentMethod(),
        'paymentStatus' => $walkinOrder->getPaymentStatus(),
        'productId'     => $originalProduct?->getId(),
        'productName'   => $originalProduct?->getName(),
        'quantity'      => $originalQuantity,
        'purchaseDate'  => $walkinOrder->getPurchaseDate()?->format('Y-m-d H:i:s'),
        'warrantyText'  => $walkinOrder->getWarrantyText(),
    ];




    $form = $this->createForm(WalkinOrdersType::class, $walkinOrder);
    $form->handleRequest($request);




    // ✅ STOCK MAP (FOR EDIT DISPLAY)
    $stocksMap = [];
    $stockRepo = $entityManager->getRepository(Stocks::class);
    $allStocks = $stockRepo->findAll();


    foreach ($allStocks as $stock) {
        $product = $stock->getProductname();
        if ($product) {
            $stocksMap[$product->getId()] = $stock->getStock();
        }
    }




    if ($form->isSubmitted() && $form->isValid()) {


        $newProduct  = $walkinOrder->getProduct();
        $newQuantity = $walkinOrder->getQuantity();




        // 🔁 STOCK HANDLING
        // CASE 1 → SAME PRODUCT
        if ($originalProduct && $newProduct && $originalProduct->getId() === $newProduct->getId()) {


            $stockRecord = $stockRepo->findOneBy(['productname' => $newProduct]);
            $available   = $stockRecord ? $stockRecord->getStock() : 0;


            // difference from original qty
            $delta = $newQuantity - $originalQuantity;


            if ($delta > 0) {
                if ($delta > $available) {
                    $this->addFlash(
                        'danger',
                        sprintf(
                            'Not enough stock for "%s". Available extra: %d, requested extra: %d.',
                            $newProduct->getName(),
                            $available,
                            $delta
                        )
                    );


                    return $this->render('walkin_orders/edit.html.twig', [
                        'walkin_order' => $walkinOrder,
                        'form'         => $form,
                        'stocksMap'    => $stocksMap,
                    ]);
                }


                if ($stockRecord) {
                    $stockRecord->setStock($available - $delta);
                    $entityManager->persist($stockRecord);
                }
            }
            elseif ($delta < 0) {
                // returning stock
                if ($stockRecord) {
                    $stockRecord->setStock($available + abs($delta));
                    $entityManager->persist($stockRecord);
                }
            }


        }
        // CASE 2 → PRODUCT CHANGED
        else {


            // restore old product stock
            if ($originalProduct) {
                $origStock = $stockRepo->findOneBy(['productname' => $originalProduct]);
                if ($origStock) {
                    $origStock->setStock($origStock->getStock() + $originalQuantity);
                    $entityManager->persist($origStock);
                }
            }


            // deduct from new product
            if ($newProduct) {
                $newStock = $stockRepo->findOneBy(['productname' => $newProduct]);
                $available = $newStock ? $newStock->getStock() : 0;


                if ($newQuantity > $available) {


                    $this->addFlash(
                        'danger',
                        sprintf(
                            'Not enough stock for "%s". Available: %d, requested: %d.',
                            $newProduct->getName(),
                            $available,
                            $newQuantity
                        )
                    );


                    return $this->render('walkin_orders/edit.html.twig', [
                        'walkin_order' => $walkinOrder,
                        'form'         => $form,
                        'stocksMap'    => $stocksMap,
                    ]);
                }


                if ($newStock) {
                    $newStock->setStock($available - $newQuantity);
                    $entityManager->persist($newStock);
                }
            }
        }




        // ✅ SAVE CHANGES
        $entityManager->flush();




        // ✅ AUDIT UPDATE
        $finalProduct = $walkinOrder->getProduct();
        $finalStock   = $finalProduct
            ? $stockRepo->findOneBy(['productname' => $finalProduct])
            : null;




        $newData = [
            'id'             => $walkinOrder->getId(),
            'buyerName'      => $walkinOrder->getBuyerName(),
            'contact'        => $walkinOrder->getContact(),
            'paymentMethod'  => $walkinOrder->getPaymentMethod(),
            'paymentStatus'  => $walkinOrder->getPaymentStatus(),
            'productId'      => $finalProduct?->getId(),
            'productName'    => $finalProduct?->getName(),
            'quantity'       => $walkinOrder->getQuantity(),
            'purchaseDate'   => $walkinOrder->getPurchaseDate()?->format('Y-m-d H:i:s'),
            'warrantyText'   => $walkinOrder->getWarrantyText(),
            'remainingStock' => $finalStock?->getStock(),
        ];




        $auditLogger->log(
            $walkinOrder->getId(),
            ActionType::UPDATE,
            $oldData,
            $newData
        );




        $this->addFlash('success', 'Walk-in order updated successfully.');
        return $this->redirectToRoute('app_walkin_orders_index');
    }




    return $this->render('walkin_orders/edit.html.twig', [
        'walkin_order' => $walkinOrder,
        'form'         => $form,
        'stocksMap'    => $stocksMap,
    ]);
}

    #[Route('/{id}', name: 'app_walkin_orders_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        WalkinOrders $walkinOrder,
        EntityManagerInterface $entityManager,
        AuditLogger $auditLogger
    ): Response {

        if ($this->isCsrfTokenValid('delete'.$walkinOrder->getId(), $request->getPayload()->getString('_token'))) {


            $product  = $walkinOrder->getProduct();
            $quantity = $walkinOrder->getQuantity();


            $oldData = [
                'id'            => $walkinOrder->getId(),
                'buyerName'     => $walkinOrder->getBuyerName(),
                'contact'       => $walkinOrder->getContact(),
                'paymentMethod' => $walkinOrder->getPaymentMethod(),
                'paymentStatus' => $walkinOrder->getPaymentStatus(),
                'productId'     => $product?->getId(),
                'productName'   => $product?->getName(),
                'quantity'      => $quantity,
                'purchaseDate'  => $walkinOrder->getPurchaseDate()?->format('Y-m-d H:i:s'),
                'warrantyText'  => $walkinOrder->getWarrantyText(),
            ];


            if ($product) {
                $stockRepo   = $entityManager->getRepository(Stocks::class);
                $stockRecord = $stockRepo->findOneBy(['productname' => $product]);
                if ($stockRecord) {
                    $stockRecord->setStock($stockRecord->getStock() + $quantity);
                    $entityManager->persist($stockRecord);
                }
            }


            $entityManager->remove($walkinOrder);
            $entityManager->flush();


            $auditLogger->log(
                $walkinOrder->getId(),
                ActionType::DELETE,
                $oldData,
                null
            );


            $this->addFlash('success', 'Walk-in order deleted successfully.');
        }


        return $this->redirectToRoute('app_walkin_orders_index');
    }
}




