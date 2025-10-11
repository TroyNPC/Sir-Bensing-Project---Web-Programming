<?php

namespace App\Controller;

use App\Entity\Pcproducts;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectsController extends AbstractController
{
    #[Route('/projects', name: 'app_projects')]
    public function index(EntityManagerInterface $em): Response
    {
        $repository = $em->getRepository(Pcproducts::class);
        $products = $repository->findAll();

        shuffle($products);

        $featured = array_slice($products, 0, 4);
        $gallery = array_slice($products, 0, 2);

        return $this->render('projects/index.html.twig', [
            'featured' => $featured,
            'gallery' => $gallery,
        ]);
    }
    #[Route('/product/{id}', name: 'chosen_product')]
public function chosenProduct(EntityManagerInterface $em, int $id): Response
{
    $repository = $em->getRepository(Pcproducts::class);
    $product = $repository->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Product not found.');
    }

    return $this->render('projects/chosenproduct.html.twig', [
        'product' => $product,
    ]);

}

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('projects/contact.html.twig');
    }
    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('projects/about.html.twig');
    }
    #[Route('/products', name: 'app_products')]
    public function product(EntityManagerInterface $em): Response
    {
        $repository = $em->getRepository(Pcproducts::class);

        $products = $repository->findAll(); // ✅ Added this line

        $gpus = $repository->findBy(['category' => 'GPU']);
        $rams = $repository->findBy(['category' => 'RAM']);
        $motherboards = $repository->findBy(['category' => 'Motherboard']);
        $cases = $repository->findBy(['category' => 'Case']);
        $coolers = $repository->findBy(['category' => 'Cooling']);
        $storages = $repository->findBy(['category' => 'Storage']);
        $powerSupplies = $repository->findBy(['category' => 'Power Supply']);

        return $this->render('projects/products.html.twig', [
            'products' => $products, // ✅ Added this
            'gpus' => $gpus,
            'rams' => $rams,
            'motherboards' => $motherboards,
            'cases' => $cases,
            'coolers' => $coolers,
            'storages' => $storages,
            'powerSupplies' => $powerSupplies,
        ]);
    }
}
