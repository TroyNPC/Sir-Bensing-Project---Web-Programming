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


/* ✅ ADDED */
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;


class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher,
        AuditLogger $auditLogger,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ): Response {


        /** @var User|null $user */
        $user = $this->getUser();


        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in to view your profile.');
        }


        // ✅ Change-password form
        $form = $this->createFormBuilder()
            ->add('plainPassword', PasswordType::class, [
                'label'    => 'New Password',
                'mapped'   => false,
                'required' => true,
                'attr'     => [
                    'class'       => 'form-control form-control-lg',
                    'placeholder' => 'Enter your new password',
                    'autocomplete'=> 'new-password',
                ],
            ])
            ->getForm();


        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            $newPassword = $form->get('plainPassword')->getData();


            // ✅ Change password
            $user->setPassword(
                $hasher->hashPassword($user, $newPassword)
            );


            $entityManager->flush();


            // ✅ KEEP USER LOGGED IN AFTER PASSWORD CHANGE
            $token = new PostAuthenticationToken(
                $user,
                'main',
                $user->getRoles()
            );


            $tokenStorage->setToken($token);
            $session->set('_security_main', serialize($token));


            // ✅ Audit log
            $auditLogger->log(
                $user->getId(),
                ActionType::UPDATE,
                null,
                [
                    'username' => $user->getUsername(),
                    'password' => 'changed',
                ]
            );


            // ✅ Success message
            $this->addFlash('success', '✅ Password has been changed!');


            // ✅ Stay on profile page
            return $this->redirectToRoute('app_profile');
        }


        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}






