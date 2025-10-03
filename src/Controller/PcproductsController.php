<?php

namespace App\Controller;

use App\Entity\Pcproducts;
use App\Form\PcproductsType;
use App\Repository\PcproductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/pcproducts')]
final class PcproductsController extends AbstractController
{
    #[Route(name: 'app_pcproducts_index', methods: ['GET'])]
    public function index(PcproductsRepository $pcproductsRepository): Response
    {
        return $this->render('pcproducts/index.html.twig', [
            'pcproducts' => $pcproductsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_pcproducts_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $pcproduct = new Pcproducts();
        $form = $this->createForm(PcproductsType::class, $pcproduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Handle image upload
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('products_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image.');
                }

                $pcproduct->setImage($newFilename);
            }

            $entityManager->persist($pcproduct);
            $entityManager->flush();

            return $this->redirectToRoute('app_pcproducts_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pcproducts/new.html.twig', [
            'pcproduct' => $pcproduct,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_pcproducts_show', methods: ['GET'])]
    public function show(Pcproducts $pcproduct): Response
    {
        return $this->render('pcproducts/show.html.twig', [
            'pcproduct' => $pcproduct,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pcproducts_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pcproducts $pcproduct, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PcproductsType::class, $pcproduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Handle image upload on edit
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('products_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image.');
                }

                $pcproduct->setImage($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_pcproducts_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pcproducts/edit.html.twig', [
            'pcproduct' => $pcproduct,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_pcproducts_delete', methods: ['POST'])]
    public function delete(Request $request, Pcproducts $pcproduct, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pcproduct->getId(), $request->request->get('_token'))) {
            $entityManager->remove($pcproduct);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pcproducts_index', [], Response::HTTP_SEE_OTHER);
    }
}
