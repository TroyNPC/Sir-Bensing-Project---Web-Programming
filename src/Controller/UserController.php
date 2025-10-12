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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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

 #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $user = new User();
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Set default role
        $user->setRoles('ROLE_CUSTOMER');

        // Store password (plain text for now; hash in production)
        $user->setPassword($user->getPassword());

        // Reset AUTO_INCREMENT if table empty
        $count = $entityManager->getRepository(User::class)->count([]);
        if ($count === 0) {
            $entityManager->getConnection()->exec('ALTER TABLE user AUTO_INCREMENT = 1');
        }

        $entityManager->persist($user);
        $entityManager->flush();

        // Add flash success message
        $this->addFlash('success', '✅ Registration successful! You can now log in.');

        // Redirect to login page
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
    // 🟡 Store the current password before handling the form
    $currentPassword = $user->getPassword();

    // 🟢 Create form with edit flag (password optional)
    $form = $this->createForm(UserType::class, $user, [
        'is_admin' => true,
        'is_edit' => true,
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // ✅ Handle roles (same logic as addAdmin)
        $roles = $form->get('roles')->getData() ?: 'ROLE_CUSTOMER';
        $user->setRoles($roles);

        // ✅ Handle password
        $newPassword = $form->get('password')->getData();
        if (!empty($newPassword)) {
            $user->setPassword($newPassword);
        } else {
            // Keep old password if no new one provided
            $user->setPassword($currentPassword);
        }

        $entityManager->flush();

        $this->addFlash('success', 'User updated successfully.');
        return $this->redirectToRoute('app_user_index');
    }

    return $this->render('user/edit.html.twig', [
        'user' => $user,
        'form' => $form->createView(),
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
