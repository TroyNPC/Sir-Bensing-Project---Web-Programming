<?php

namespace App\Controller;

use App\Entity\Pcproducts;
use App\Entity\Stocks;
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

            // ✅ Always make it available at creation
            $pcproduct->setIsavailable(true);

            // ✅ Adjust AUTO_INCREMENT based on the highest existing ID
            $connection = $entityManager->getConnection();
            $maxIdResult = $connection->fetchOne('SELECT MAX(id) FROM pcproducts');
            $nextId = ((int)$maxIdResult) + 1;
            $connection->executeStatement('ALTER TABLE pcproducts AUTO_INCREMENT = ' . $nextId);

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

            // ✅ Persist product
            $entityManager->persist($pcproduct);
            $entityManager->flush();

            // ✅ Automatically create a new Stock record for this product
            $stock = new Stocks();
            $stock->setProductname($pcproduct);
            $stock->setStock(1); // Default stock 1
            $stock->setImage($pcproduct->getImage());
            $stock->setCreatedAt(new \DateTimeImmutable());
             $stock->setUpdatedAt(new \DateTimeImmutable());

            $maxStockId = $connection->fetchOne('SELECT MAX(id) FROM stocks');
            $nextStockId = ((int)$maxStockId) + 1;
            $connection->executeStatement('ALTER TABLE stocks AUTO_INCREMENT = ' . $nextStockId);


            // ✅ Persist stock
            $entityManager->persist($stock);
            $entityManager->flush();

            // ✅ Flash success message
            $this->addFlash('success', '✅ Product added successfully and stock record created!');

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

    // ✅ Modal-friendly "Show" route (for AJAX modal loading)
    #[Route('/{id}/modal', name: 'app_pcproducts_show_modal', methods: ['GET'])]
    public function showModal(Pcproducts $pcproduct): Response
    {
        return $this->render('pcproducts/show.html.twig', [
            'pcproduct' => $pcproduct,
            'isModal' => true,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pcproducts_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pcproducts $pcproduct, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PcproductsType::class, $pcproduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ✅ Handle availability change (true/false)
            $isAvailable = $form->get('isavailable')->getData();
            $pcproduct->setIsavailable($isAvailable);

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

            // ✅ Save updated product (including isavailable)
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

            // 🗑️ Remove product
            $entityManager->remove($pcproduct);
            $entityManager->flush();

            // 🧮 Adjust AUTO_INCREMENT based on the highest ID after deletion
            $maxId = $entityManager->getConnection()
                ->fetchOne('SELECT MAX(id) FROM pcproducts');
            $nextId = $maxId ? $maxId + 1 : 1;
            $entityManager->getConnection()
                ->executeStatement('ALTER TABLE pcproducts AUTO_INCREMENT = ' . $nextId);

            $this->addFlash('success', '🗑️ Product deleted successfully!');
        }

        return $this->redirectToRoute('app_pcproducts_index', [], Response::HTTP_SEE_OTHER);
    }
}
