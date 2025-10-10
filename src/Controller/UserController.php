<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    // Customer signup (frontend)
    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_admin' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles('ROLE_CUSTOMER'); // always CUSTOMER

            // Store password as plain text (not recommended for production)
            $user->setPassword($user->getPassword());

            // Reset AUTO_INCREMENT if table empty
            $count = $entityManager->getRepository(User::class)->count([]);
            if ($count === 0) {
                $entityManager->getConnection()->exec('ALTER TABLE user AUTO_INCREMENT = 1');
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Account created successfully! Please log in.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

        // Admin add user page
    #[Route('/addaccountadmin', name: 'app_user_add_admin', methods: ['GET', 'POST'])]
    public function addAdmin(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_admin' => true, 'is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ensure roles is a string, default to ROLE_CUSTOMER if empty
            $roles = $form->get('roles')->getData() ?: 'ROLE_CUSTOMER';
            $user->setRoles($roles);

            // Set password from form data
            $password = $form->get('password')->getData();
            if (!$password) {
                $this->addFlash('error', 'Password cannot be blank.');
                return $this->redirectToRoute('app_user_add_admin');
            }
            $user->setPassword($password);

            // Reset AUTO_INCREMENT if table empty
            $count = $entityManager->getRepository(User::class)->count([]);
            if ($count === 0) {
                $entityManager->getConnection()->exec('ALTER TABLE user AUTO_INCREMENT = 1');
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User added successfully.');
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/addaccountadmin.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

#[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
{
    // Tell the form it's an edit, so password is optional
    $form = $this->createForm(UserType::class, $user, [
        'is_admin' => true,
        'is_edit' => true, // <-- important for password optional
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Update password only if the user typed a new one
        $password = $form->get('password')->getData();
        if ($password) {
            $user->setPassword($password);
        }

        // Handle roles if present
        if ($form->has('roles')) {
            $roles = $form->get('roles')->getData(); // returns 'ROLE_ADMIN' or 'ROLE_CUSTOMER'
            if ($roles) {
                $user->setRoles($roles);
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'User updated successfully.');

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('user/edit.html.twig', [
        'user' => $user,
        'form' => $form,
    ]);
}




    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            // Reset AUTO_INCREMENT if table empty
            $count = $entityManager->getRepository(User::class)->count([]);
            if ($count === 0) {
                $entityManager->getConnection()->exec('ALTER TABLE user AUTO_INCREMENT = 1');
            }
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
