<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\AuditLogger;
use App\Enum\ActionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher,
        AuditLogger $auditLogger
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in to view your profile.');
        }

        // Simple change-password form
        $form = $this->createFormBuilder()
            ->add('plainPassword', PasswordType::class, [
                'label'    => 'New Password',
                'mapped'   => false,
                'required' => true,
                'attr'     => [
                    'class'       => 'form-control form-control-lg',
                    'placeholder' => 'Enter your new password',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('plainPassword')->getData();

            // ✅ Change password, keep user logged in
            $user->setPassword(
                $hasher->hashPassword($user, $newPassword)
            );
            $entityManager->flush();

            // ✅ Audit log (targetId = this user)
            $auditLogger->log(
                $user->getId(),
                ActionType::UPDATE,
                null,
                [
                    'username' => $user->getUsername(),
                    'password' => 'changed',
                ]
            );

            // ✅ Flash message for success
            $this->addFlash('success', '✅ Password has been changed!');

            // ✅ Stay logged in – just go back to profile page
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}



