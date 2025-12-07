<?php

namespace App\Controller;

use App\Entity\Pcproducts;
use App\Entity\Stocks;
use App\Form\PcproductsType;
use App\Repository\PcproductsRepository;
use App\Service\AuditLogger;
use App\Enum\ActionType;
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
        $user = $this->getUser();

        // Admin sees all, staff sees only their own products
        if ($this->isGranted('ROLE_ADMIN')) {
            $products = $pcproductsRepository->findAll();
        } else {
            $products = $pcproductsRepository->createQueryBuilder('p')
                ->where('p.createdBy = :user')
                ->setParameter('user', $user)
                ->orderBy('p.id', 'ASC')
                ->getQuery()
                ->getResult();
        }

        return $this->render('pcproducts/index.html.twig', [
            'pcproducts' => $products,
        ]);
    }

    #[Route('/new', name: 'app_pcproducts_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        AuditLogger $auditLogger
    ): Response {
        $pcproduct = new Pcproducts();
        $pcproduct->setCreatedBy($this->getUser()); // 🔹 Set the product owner
        $form = $this->createForm(PcproductsType::class, $pcproduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ✅ Always make it available at creation
            $pcproduct->setIsavailable(true);

            // ✅ Adjust AUTO_INCREMENT based on the highest existing ID
            $connection = $entityManager->getConnection();
            $maxIdResult = $connection->fetchOne('SELECT MAX(id) FROM pcproducts');
            $nextId = ((int) $maxIdResult) + 1;
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

            // ✅ AUDIT — PRODUCT CREATE (FULL SNAPSHOT)
            $auditLogger->log(
                $pcproduct->getId(),    // targetId
                ActionType::CREATE,     // action type
                null,                   // old data
                [
                    'product_name' => $pcproduct->getName(),
                    'brand'        => $pcproduct->getBrand(),
                    'category'     => $pcproduct->getCategory(),
                    'price'        => $pcproduct->getPrice(),
                    'description'  => $pcproduct->getDescription(),
                    'image'        => $pcproduct->getImage(),
                    'available'    => $pcproduct->isavailable(),
                ]
            );

            // ✅ Automatically create a new Stock record for this product
            $stock = new Stocks();
            $stock->setProductname($pcproduct);
            $stock->setStock(1);
            $stock->setImage($pcproduct->getImage());
            $stock->setCreatedAt(new \DateTimeImmutable());
            $stock->setUpdatedAt(new \DateTimeImmutable());

            $maxStockId = $connection->fetchOne('SELECT MAX(id) FROM stocks');
            $nextStockId = ((int) $maxStockId) + 1;
            $connection->executeStatement('ALTER TABLE stocks AUTO_INCREMENT = ' . $nextStockId);

            // ✅ Persist stock
            $entityManager->persist($stock);
            $entityManager->flush();

            $this->addFlash('success', '✅ Product added successfully and stock record created!');

            return $this->redirectToRoute('app_pcproducts_index');
        }

        return $this->render('pcproducts/new.html.twig', [
            'pcproduct' => $pcproduct,
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_pcproducts_show', methods: ['GET'])]
    public function show(Pcproducts $pcproduct): Response
    {
        return $this->render('pcproducts/show.html.twig', [
            'pcproduct' => $pcproduct,
        ]);
    }

    #[Route('/{id}/modal', name: 'app_pcproducts_show_modal', methods: ['GET'])]
    public function showModal(Pcproducts $pcproduct): Response
    {
        return $this->render('pcproducts/show.html.twig', [
            'pcproduct' => $pcproduct,
            'isModal'   => true,
        ]);
    }

  #[Route('/{id}/edit', name: 'app_pcproducts_edit', methods: ['GET', 'POST'])]
public function edit(
    Request $request,
    Pcproducts $pcproduct,
    EntityManagerInterface $entityManager,
    SluggerInterface $slugger,
    AuditLogger $auditLogger
): Response {
    // 🔹 Ownership check: staff cannot edit others' products
    if (!$this->isGranted('ROLE_ADMIN') && $pcproduct->getCreatedBy() !== $this->getUser()) {
        $this->addFlash('error', '⚠️ You cannot edit products that are not yours.');
        return $this->redirectToRoute('app_pcproducts_index');
    }

    // ✅ SNAPSHOT OLD VALUES *before* handling the form
    $oldData = [
        'product_name' => $pcproduct->getName(),
        'brand'        => $pcproduct->getBrand(),
        'category'     => $pcproduct->getCategory(),
        'price'        => $pcproduct->getPrice(),
        'description'  => $pcproduct->getDescription(),
        'image'        => $pcproduct->getImage(),
        'available'    => $pcproduct->isavailable(),
    ];

    $form = $this->createForm(PcproductsType::class, $pcproduct);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $pcproduct->setUpdatedAt(new \DateTimeImmutable());

        // isavailable will already be updated by handleRequest, but this doesn’t hurt
        $isAvailable = $form->get('isavailable')->getData();
        $pcproduct->setIsavailable($isAvailable);

        // Image upload
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename     = $slugger->slug($originalFilename);
            $newFilename      = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('products_images_directory'),
                    $newFilename
                );

                $oldImage = $pcproduct->getImage();
                if ($oldImage && file_exists($this->getParameter('products_images_directory').'/'.$oldImage)) {
                    @unlink($this->getParameter('products_images_directory').'/'.$oldImage);
                }

                $pcproduct->setImage($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', '❌ Failed to upload new image.');
            }
        }

        // ✅ Save updated product
        $entityManager->flush();

        // ✅ SNAPSHOT NEW VALUES
        $newData = [
            'product_name' => $pcproduct->getName(),
            'brand'        => $pcproduct->getBrand(),
            'category'     => $pcproduct->getCategory(),
            'price'        => $pcproduct->getPrice(),
            'description'  => $pcproduct->getDescription(),
            'image'        => $pcproduct->getImage(),
            'available'    => $pcproduct->isavailable(),
        ];

        // ✅ FILTER UNCHANGED FIELDS
        $cleanOld = [];
        $cleanNew = [];

        foreach ($oldData as $key => $oldValue) {
            if ($oldValue !== $newData[$key]) {
                $cleanOld[$key] = $oldValue;
                $cleanNew[$key] = $newData[$key];
            }
        }

        // Only log if something actually changed
        if (!empty($cleanOld) || !empty($cleanNew)) {
            $auditLogger->log(
                $pcproduct->getId(),   // targetId
                ActionType::UPDATE,    // action type
                $cleanOld ?: null,     // old data
                $cleanNew ?: null      // new data
            );
        }

        $this->addFlash('success', '✅ Product updated successfully!');
        return $this->redirectToRoute('app_pcproducts_index');
    }

    return $this->render('pcproducts/edit.html.twig', [
        'pcproduct' => $pcproduct,
        'form'      => $form->createView(),
    ]);
}





    #[Route('/{id}', name: 'app_pcproducts_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        EntityManagerInterface $entityManager,
        PcproductsRepository $repo,
        int $id,
        AuditLogger $auditLogger
    ): Response {
        $pcproduct = $repo->find($id);

        if (!$pcproduct) {
            $this->addFlash('error', '⚠️ Product not found or already deleted.');
            return $this->redirectToRoute('app_pcproducts_index');
        }

        // 🔹 Ownership check: staff cannot delete others' products
        if (!$this->isGranted('ROLE_ADMIN') && $pcproduct->getCreatedBy() !== $this->getUser()) {
            $this->addFlash('error', '⚠️ You cannot delete products that are not yours.');
            return $this->redirectToRoute('app_pcproducts_index');
        }

        if ($this->isCsrfTokenValid('delete' . $pcproduct->getId(), $request->getPayload()->getString('_token'))) {

            $oldImage = $pcproduct->getImage();
            if ($oldImage && file_exists($this->getParameter('products_images_directory') . '/' . $oldImage)) {
                @unlink($this->getParameter('products_images_directory') . '/' . $oldImage);
            }

            $deletedID = $pcproduct->getId();

            // ✅ Remove product
            $entityManager->remove($pcproduct);
            $entityManager->flush();

            // ✅ AUDIT — PRODUCT DELETE
            $auditLogger->log(
                $deletedID,            // targetId
                ActionType::DELETE,    // action type
                ['deleted' => true],   // old data
                null                   // new data
            );

            $maxId = $entityManager->getConnection()
                ->fetchOne('SELECT MAX(id) FROM pcproducts');

            $nextId = $maxId ? $maxId + 1 : 1;

            $entityManager->getConnection()
                ->executeStatement(
                    'ALTER TABLE pcproducts AUTO_INCREMENT = ' . $nextId
                );

            $this->addFlash('success', '🗑️ Product deleted successfully!');
        }

        return $this->redirectToRoute('app_pcproducts_index');
    }
}





