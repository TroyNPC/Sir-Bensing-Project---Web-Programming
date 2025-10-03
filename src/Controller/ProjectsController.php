<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectsController extends AbstractController
{
    #[Route('/projects', name: 'app_projects')]
    public function index(): Response
    {
        return $this->render('projects/index.html.twig', [
            'controller_name' => 'ProjectsController',
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('projects/contact.html.twig', [
            'controller_name' => 'ProjectsController',
        ]);
    }
    #[Route('/products', name: 'app_products')]
    public function product(): Response
    {
        return $this->render('projects/products.html.twig', [
            'controller_name' => 'ProjectsController',
        ]);
    }
}
