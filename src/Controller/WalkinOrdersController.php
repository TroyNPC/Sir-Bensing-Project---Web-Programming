<?php

namespace App\Controller;

use App\Entity\WalkinOrders;
use App\Form\WalkinOrdersType;
use App\Repository\WalkinOrdersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/walkin/orders')]
final class WalkinOrdersController extends AbstractController
{
    #[Route(name: 'app_walkin_orders_index', methods: ['GET'])]
    public function index(WalkinOrdersRepository $walkinOrdersRepository): Response
    {
        return $this->render('walkin_orders/index.html.twig', [
            'walkin_orders' => $walkinOrdersRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_walkin_orders_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $walkinOrder = new WalkinOrders();
        $form = $this->createForm(WalkinOrdersType::class, $walkinOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($walkinOrder);
            $entityManager->flush();

            return $this->redirectToRoute('app_walkin_orders_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('walkin_orders/new.html.twig', [
            'walkin_order' => $walkinOrder,
            'form' => $form,
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
    public function edit(Request $request, WalkinOrders $walkinOrder, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WalkinOrdersType::class, $walkinOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_walkin_orders_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('walkin_orders/edit.html.twig', [
            'walkin_order' => $walkinOrder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_walkin_orders_delete', methods: ['POST'])]
    public function delete(Request $request, WalkinOrders $walkinOrder, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$walkinOrder->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($walkinOrder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_walkin_orders_index', [], Response::HTTP_SEE_OTHER);
    }
}
