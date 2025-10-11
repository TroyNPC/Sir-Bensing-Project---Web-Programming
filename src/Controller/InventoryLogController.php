<?php

namespace App\Controller;

use App\Entity\InventoryLog;
use App\Form\InventoryLogType;
use App\Repository\InventoryLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inventory/log')]
final class InventoryLogController extends AbstractController
{
    #[Route(name: 'app_inventory_log_index', methods: ['GET'])]
    public function index(InventoryLogRepository $inventoryLogRepository): Response
    {
        return $this->render('inventory_log/index.html.twig', [
            'inventory_logs' => $inventoryLogRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_inventory_log_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $inventoryLog = new InventoryLog();
        $form = $this->createForm(InventoryLogType::class, $inventoryLog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($inventoryLog);
            $entityManager->flush();

            return $this->redirectToRoute('app_inventory_log_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('inventory_log/new.html.twig', [
            'inventory_log' => $inventoryLog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_inventory_log_show', methods: ['GET'])]
    public function show(InventoryLog $inventoryLog): Response
    {
        return $this->render('inventory_log/show.html.twig', [
            'inventory_log' => $inventoryLog,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_inventory_log_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, InventoryLog $inventoryLog, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InventoryLogType::class, $inventoryLog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_inventory_log_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('inventory_log/edit.html.twig', [
            'inventory_log' => $inventoryLog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_inventory_log_delete', methods: ['POST'])]
    public function delete(Request $request, InventoryLog $inventoryLog, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$inventoryLog->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($inventoryLog);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_inventory_log_index', [], Response::HTTP_SEE_OTHER);
    }
}
