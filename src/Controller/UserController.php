<?php


namespace App\Controller;


use App\Entity\User;
use App\Service\AuditLogger;
use App\Enum\ActionType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/* ✅ ADDED */
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;


class UserController extends AbstractController
{
    #[Route('/users', name: 'app_users')]
    public function index(
        EntityManagerInterface $em,
        Request $request,
        UserPasswordHasherInterface $hasher,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        AuditLogger $auditLogger
    ): Response {


        $context = $request->request->get('context');
        $users = $em->getRepository(User::class)->findAll();


        // --------------------
        // ADD USER FORM
        // --------------------
        $addForm = $this->createFormBuilder(null, [
            'attr' => ['id' => 'add_user_form']
        ])
            ->add('username')
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'User'  => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                    'Staff' => 'ROLE_STAFF'
                ],
                'multiple' => true,
                'expanded' => true
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped'  => false,
                'required'=> true
            ])
            ->getForm();


        $addForm->handleRequest($request);


        if ($context === 'add' && $addForm->isSubmitted() && $addForm->isValid()) {


            $data     = $addForm->getData();
            $username = $data['username'];
            $roles    = $data['roles'];
            $password = $addForm->get('plainPassword')->getData();


            $existing = $em->getRepository(User::class)
                           ->findOneBy(['username' => $username]);


            if ($existing) {


                if ($this->getUser() && $existing->getId() === $this->getUser()->getId()) {
                    $this->addFlash('danger',
                        'Account Already Exists - You cannot update your own account using the Add User form.'
                    );
                    return $this->redirectToRoute('app_users');
                }


                $oldData = [
                    'username' => $existing->getUsername(),
                    'roles'    => $existing->getRoles(),
                ];


                $existing->setRoles($roles);
                $existing->setPassword($hasher->hashPassword($existing, $password));


                try {
                    $em->flush();


                    $newData = [
                        'username' => $existing->getUsername(),
                        'roles'    => $existing->getRoles(),
                        'password' => 'changed',
                    ];


                    $auditLogger->log(
                        $existing->getId(),
                        ActionType::UPDATE,
                        $oldData,
                        $newData
                    );


                    $this->addFlash('success', 'User exists — updated successfully.');


                } catch (UniqueConstraintViolationException $e) {
                    $this->addFlash('danger', 'Username already exists.');
                }


                return $this->redirectToRoute('app_users');
            }


            $connection = $em->getConnection();
            $maxId  = $connection->fetchOne('SELECT MAX(id) FROM user');
            $nextId = $maxId ? ((int)$maxId + 1) : 1;
            $connection->executeStatement('ALTER TABLE user AUTO_INCREMENT = ' . $nextId);


            $newUser = new User();
            $newUser->setUsername($username);
            $newUser->setRoles($roles);
            $newUser->setPassword(
                $hasher->hashPassword($newUser, $password)
            );


            try {
                $em->persist($newUser);
                $em->flush();


                $auditLogger->log(
                    $newUser->getId(),
                    ActionType::CREATE,
                    null,
                    [
                        'username' => $newUser->getUsername(),
                        'roles'    => $newUser->getRoles(),
                        'password' => 'added',
                    ]
                );


                $this->addFlash('success', 'User added successfully.');


            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', 'Username already exists.');
            }


            return $this->redirectToRoute('app_users');
        }


  // --------------------
// EDIT USERS FORMS
// --------------------
$forms = [];


foreach ($users as $user) {


    $form = $this->createFormBuilder($user, [
        'attr' => ['id' => 'user_' . $user->getId()],
    ])
        ->add('username')
        ->add('roles', ChoiceType::class, [
            'choices' => [
                'User'  => 'ROLE_USER',
                'Admin' => 'ROLE_ADMIN',
                'Staff' => 'ROLE_STAFF',
            ],
            'multiple' => true,
            'expanded' => true,
        ])
        ->add('plainPassword', PasswordType::class, [
            'mapped'   => false,
            'required' => false
        ])
        ->getForm();


    if ($context === 'edit_' . $user->getId()) {


        $originalUsername = $user->getUsername();
        $originalRoles    = $user->getRoles();


        $form->handleRequest($request);


        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('info', 'Form Submitted - Username Already Existed - No updates');
            return $this->redirectToRoute('app_users');
        }


        if ($form->isSubmitted() && $form->isValid()) {


            $newUsername = $form->get('username')->getData();
            $newRoles    = $form->get('roles')->getData();   // ✅ get roles once


            // ✅ Check duplicate username on other users
            $existing = $em->getRepository(User::class)
                           ->findOneBy(['username' => $newUsername]);


            if ($existing && $existing->getId() !== $user->getId()) {


                $user->setUsername($originalUsername);
                $user->setRoles($originalRoles);


                $this->addFlash('danger', 'Username already exists.');
                return new RedirectResponse($this->generateUrl('app_users'), 302);
            }


            // ✅ Guard: current user cannot remove their own ROLE_ADMIN
            $currentUser = $this->getUser();
            if (
                $currentUser &&
                $currentUser->getId() === $user->getId() &&
                !in_array('ROLE_ADMIN', $newRoles, true)
            ) {
                $this->addFlash('danger', 'Update Read - ROLE_ADMIN remains during logged session - Not removed.');
                return $this->redirectToRoute('app_users');
            }


            $oldData = [
                'username' => $originalUsername,
                'roles'    => $originalRoles,
            ];


            // ✅ Apply safe changes
            $user->setUsername($newUsername);
            $user->setRoles($newRoles);


            $pwd = $form->get('plainPassword')->getData();
            $passwordChanged = false;


            if (!empty($pwd)) {
                $user->setPassword($hasher->hashPassword($user, $pwd));
                $passwordChanged = true;
            }


            $em->flush();


            $newData = [
                'username' => $user->getUsername(),
                'roles'    => $user->getRoles(),
            ];


            if ($passwordChanged) {
                $newData['password'] = 'changed';
            }


            $auditLogger->log(
                $user->getId(),
                ActionType::UPDATE,
                $oldData,
                $newData
            );


            /* ✅ KEEP ADMIN LOGGED IN OR LOG OUT (your existing logic) */
            $currentUser = $this->getUser();


            if ($currentUser && $currentUser->getId() === $user->getId()) {


                if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {


                    $token = new PostAuthenticationToken(
                        $user,
                        'main',
                        $user->getRoles()
                    );


                    $tokenStorage->setToken($token);
                    $session->set('_security_main', serialize($token));


                } else {
                    $tokenStorage->setToken(null);
                    $session->invalidate();
                }
            }


            $this->addFlash('success', 'User updated successfully.');
            return new RedirectResponse($this->generateUrl('app_users'), 302);
        }
    }


    $forms[$user->getId()] = $form->createView();
}

        return $this->render('users.html.twig', [
            'users'     => $users,
            'userForms' => $forms,
            'addForm'   => $addForm->createView(),
        ]);
    }


    // --------------------
    // TOGGLE USER ENABLE/DISABLE
    // --------------------
    #[Route('/users/{id}/toggle', name: 'app_users_toggle', methods: ['POST'])]
    public function toggleEnabled(
        int $id,
        EntityManagerInterface $em,
        Request $request,
        AuditLogger $auditLogger
    ): Response {


        $user = $em->getRepository(User::class)->find($id);


        if (!$user) {
            $this->addFlash('danger', 'User not found.');
            return $this->redirectToRoute('app_users');
        }


        if ($this->getUser() && $this->getUser()->getId() === $user->getId()) {
            $this->addFlash('danger', 'You cannot disable your own account.');
            return $this->redirectToRoute('app_users');
        }


        if (!$this->isCsrfTokenValid('toggle-user'.$id, $request->request->get('_token'))) {
            $this->addFlash('danger', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_users');
        }


        $oldStatus = $user->isEnabled();
        $user->setIsEnabled(!$oldStatus);
        $em->flush();


        $auditLogger->log(
            $user->getId(),
            ActionType::UPDATE,
            ['status' => $oldStatus ? 'Enabled account' : 'Disabled account'],
            ['status' => $user->isEnabled() ? 'Enabled account' : 'Disabled account']
        );


        $this->addFlash(
            'success',
            sprintf(
                'User "%s" has been %s.',
                $user->getUsername(),
                $user->isEnabled() ? 'enabled' : 'disabled'
            )
        );


        return $this->redirectToRoute('app_users');
    }


    // --------------------
    // DELETE USER
    // --------------------
    #[Route('/users/{id}/delete', name: 'app_users_delete', methods: ['POST'])]
    public function delete(
        int $id,
        EntityManagerInterface $em,
        Request $request,
        AuditLogger $auditLogger
    ): Response {


        $user = $em->getRepository(User::class)->find($id);


        if (!$user) {
            $this->addFlash('danger', 'User not found.');
            return $this->redirectToRoute('app_users');
        }


        if ($this->getUser() && $this->getUser()->getId() === $user->getId()) {
            $this->addFlash('danger', 'You cannot delete your own account.');
            return $this->redirectToRoute('app_users');
        }


        if (
            !$this->isCsrfTokenValid(
                'delete-user' . $id,
                $request->request->get('_token')
            )
        ) {
            $this->addFlash('danger', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_users');
        }


        $deletedId       = $user->getId();
        $deletedUsername = $user->getUsername();
        $deletedRoles    = $user->getRoles();


        $em->remove($user);
        $em->flush();


        $auditLogger->log(
            $deletedId,
            ActionType::DELETE,
            [
                'username' => $deletedUsername,
                'roles'    => $deletedRoles,
                'deleted'  => true,
            ],
            null
        );


        $this->addFlash(
            'success',
            sprintf('User "%s" deleted successfully.', $deletedUsername)
        );


        return $this->redirectToRoute('app_users');
    }
}






