<?php

namespace App\Controller;

use App\Entity\Servicebooking;
use App\Form\ServicebookingType;
use App\Repository\ServicebookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/servicebooking')]
final class ServicebookingController extends AbstractController
{
    #[Route(name: 'app_servicebooking_index', methods: ['GET'])]
    public function index(ServicebookingRepository $servicebookingRepository): Response
    {
        return $this->render('servicebooking/index.html.twig', [
            'servicebookings' => $servicebookingRepository->findAll(),
        ]);
    }

#[Route('/new', name: 'app_servicebooking_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $servicebooking = new Servicebooking();
    $form = $this->createForm(ServicebookingType::class, $servicebooking);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($servicebooking);
        $entityManager->flush();

        // Add a flash message to notify success
        $this->addFlash('success', 'Your service booking has been successfully submitted!');

        // Redirect back to the same page or to another page
        return $this->redirectToRoute('app_servicebooking_new');
    }

    return $this->render('servicebooking/new.html.twig', [
        'servicebooking' => $servicebooking,
        'form' => $form,
    ]);
}


    #[Route('/{id}', name: 'app_servicebooking_show', methods: ['GET'])]
    public function show(Servicebooking $servicebooking): Response
    {
        return $this->render('servicebooking/show.html.twig', [
            'servicebooking' => $servicebooking,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_servicebooking_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Servicebooking $servicebooking, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServicebookingType::class, $servicebooking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_servicebooking_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('servicebooking/edit.html.twig', [
            'servicebooking' => $servicebooking,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_servicebooking_delete', methods: ['POST'])]
    public function delete(Request $request, Servicebooking $servicebooking, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$servicebooking->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($servicebooking);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_servicebooking_index', [], Response::HTTP_SEE_OTHER);
    }
}
