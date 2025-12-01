<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class UserController extends AbstractController
{
    #[Route('/users', name: 'app_users')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $users = $em->getRepository(User::class)->findAll();

        // Generate forms for each user
        $forms = [];
        foreach ($users as $user) {
            $form = $this->createFormBuilder($user)
                ->add('username')
                ->add('roles', ChoiceType::class, [
        'choices' => [
            'User' => 'ROLE_USER',
            'Admin' => 'ROLE_ADMIN',
        ],
        'multiple' => true,
        'expanded' => true, // checkboxes
    ])
                ->add('password')
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->flush();
                $this->addFlash('success', 'User updated successfully.');
                return $this->redirectToRoute('app_users');
            }

            $forms[$user->getId()] = $form->createView();
        }

        return $this->render('/users.html.twig', [
            'users' => $users,
            'userForms' => $forms,
        ]);
    }


    #[Route('/users/{id}/delete', name: 'app_users_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em, Request $request): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash('danger', 'User not found.');
            return $this->redirectToRoute('app_users');
        }

        // Prevent self-delete
        if ($this->getUser()->getId() === $user->getId()) {
            $this->addFlash('danger', 'You cannot delete your own account.');
            return $this->redirectToRoute('app_users');
        }

        if (!$this->isCsrfTokenValid('delete-user'.$id, $request->request->get('_token'))) {
            $this->addFlash('danger', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_users');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'User deleted successfully.');
        return $this->redirectToRoute('admin_users');
    }
}
