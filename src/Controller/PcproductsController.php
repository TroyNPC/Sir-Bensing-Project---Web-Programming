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
            // ✅ Reset AUTO_INCREMENT only if table is empty
            $count = $entityManager->getRepository(Pcproducts::class)->count([]);
            if ($count === 0) {
                $connection = $entityManager->getConnection();
                $connection->executeStatement('ALTER TABLE pcproducts AUTO_INCREMENT = 1;');
            }

            // ✅ Handle image upload
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('products_images_directory'),
                        $newFilename
                    );
                    $pcproduct->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', '❌ Failed to upload image.');
                }
            }

            $entityManager->persist($pcproduct);
            $entityManager->flush();

            $this->addFlash('success', '✅ Product added successfully!');
            return $this->redirectToRoute('app_pcproducts_index');
        }

        return $this->render('pcproducts/new.html.twig', [
            'pcproduct' => $pcproduct,
            'form' => $form->createView(),
        ]);
    }

    // ✅ Regular full-page "Show"
    #[Route('/{id}', name: 'app_pcproducts_show', methods: ['GET'])]
    public function show(Pcproducts $pcproduct): Response
    {
        return $this->render('pcproducts/show.html.twig', [
            'pcproduct' => $pcproduct,
        ]);
    }

    // ✅ NEW: Modal-friendly "Show" route (for AJAX modal loading)
    #[Route('/{id}/modal', name: 'app_pcproducts_show_modal', methods: ['GET'])]
    public function showModal(Pcproducts $pcproduct): Response
    {
        return $this->render('pcproducts/show.html.twig', [
            'pcproduct' => $pcproduct,
            'isModal' => true, // 👈 used by Twig to hide layout and buttons
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pcproducts_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pcproducts $pcproduct, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PcproductsType::class, $pcproduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ Handle new image upload only if a new file is selected
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('products_images_directory'),
                        $newFilename
                    );

                    // 🧹 Delete old image if exists
                    $oldImage = $pcproduct->getImage();
                    if ($oldImage && file_exists($this->getParameter('products_images_directory') . '/' . $oldImage)) {
                        @unlink($this->getParameter('products_images_directory') . '/' . $oldImage);
                    }

                    $pcproduct->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', '❌ Failed to upload new image.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', '✅ Product updated successfully!');
            return $this->redirectToRoute('app_pcproducts_index');
        }

        return $this->render('pcproducts/edit.html.twig', [
            'pcproduct' => $pcproduct,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_pcproducts_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, PcproductsRepository $repo, int $id): Response
    {
        $pcproduct = $repo->find($id);

        if (!$pcproduct) {
            $this->addFlash('error', '⚠️ Product not found or already deleted.');
            return $this->redirectToRoute('app_pcproducts_index');
        }

        if ($this->isCsrfTokenValid('delete' . $pcproduct->getId(), $request->getPayload()->getString('_token'))) {
            // 🧹 Delete image from filesystem if exists
            $oldImage = $pcproduct->getImage();
            if ($oldImage && file_exists($this->getParameter('products_images_directory') . '/' . $oldImage)) {
                @unlink($this->getParameter('products_images_directory') . '/' . $oldImage);
            }

            $entityManager->remove($pcproduct);
            $entityManager->flush();
            $this->addFlash('success', '🗑️ Product deleted successfully!');
        }

        return $this->redirectToRoute('app_pcproducts_index', [], Response::HTTP_SEE_OTHER);
    }
}
