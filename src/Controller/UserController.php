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
                    'Admin' => 'ROLE_ADMIN'
                ],
                'multiple' => true,
                'expanded' => true
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => true
            ])
            ->getForm();


        $addForm->handleRequest($request);


        // ✅ handle ONLY when context == add
        if ($context === 'add' && $addForm->isSubmitted() && $addForm->isValid()) {


            $data     = $addForm->getData();
            $username = $data['username'];
            $roles    = $data['roles'];
            $password = $addForm->get('plainPassword')->getData();


            $existing = $em->getRepository(User::class)->findOneBy(['username' => $username]);


            if ($existing) {


                // ⛔ Prevent self-add override
                if ($this->getUser() && $existing->getId() === $this->getUser()->getId()) {
                    $this->addFlash(
                        'danger',
                        'Account Already Exists - You cannot update your own account using the Add User form.'
                    );
                    return $this->redirectToRoute('app_users');
                }


                // ✅ SNAPSHOT OLD
                $oldData = [
                    'username' => $existing->getUsername(),
                    'roles'    => $existing->getRoles(),
                ];


                // Update existing user
                $existing->setRoles($roles);
                $existing->setPassword($hasher->hashPassword($existing, $password));


                try {


                    $em->flush();


                    // ✅ SNAPSHOT NEW (password changed)
                    $newData = [
                        'username' => $existing->getUsername(),
                        'roles'    => $existing->getRoles(),
                        'password' => 'changed',
                    ];


                    // ✅ AUDIT — USER UPDATE (via ADD form)
                    $auditLogger->log(
                        User::class,
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


            } else {


                // ✅ AUTO-FIX USER AUTO_INCREMENT
                $connection = $em->getConnection();
                $maxId = $connection->fetchOne('SELECT MAX(id) FROM user');
                $nextId = $maxId ? ((int)$maxId + 1) : 1;
                $connection->executeStatement('ALTER TABLE user AUTO_INCREMENT = ' . $nextId);


                // Create new user
                $newUser = new User();
                $newUser->setUsername($username);
                $newUser->setRoles($roles);
                $newUser->setPassword($hasher->hashPassword($newUser, $password));


                try {


                    $em->persist($newUser);
                    $em->flush();


                    // ✅ AUDIT — USER CREATE
                    $auditLogger->log(
                        User::class,
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
        }


        // --------------------
        // EDIT USERS FORMS
        // --------------------
        $forms = [];
        $context = $request->request->get('context');


        foreach ($users as $user) {


            $form = $this->createFormBuilder($user, [
                'attr' => ['id' => 'user_' . $user->getId()],
            ])
                ->add('username')
                ->add('roles', ChoiceType::class, [
                    'choices'  => [
                        'User'  => 'ROLE_USER',
                        'Admin' => 'ROLE_ADMIN',
                    ],
                    'multiple' => true,
                    'expanded' => true,
                ])
                ->add('plainPassword', PasswordType::class, [
                    'mapped'   => false,
                    'required' => false,
                ])
                ->getForm();


            if ($context === 'edit_' . $user->getId()) {


                // ✅ SNAPSHOT OLD VALUES
                $oldData = [
                    'username' => $user->getUsername(),
                    'roles'    => $user->getRoles(),
                ];


                $form->handleRequest($request);


                if ($form->isSubmitted() && $form->isValid()) {


                    $submittedUsername = $form->get('username')->getData();


                    $dupe = $em->getRepository(User::class)
                               ->findOneBy(['username' => $submittedUsername]);


                    if ($dupe && $dupe->getId() !== $user->getId()) {
                        $this->addFlash('danger', 'Username already exists.');
                        return $this->redirectToRoute('app_users');
                    }


                    $user->setUsername($submittedUsername);
                    $user->setRoles($form->get('roles')->getData());


                    $pwd = $form->get('plainPassword')->getData();
                    $passwordChanged = false;


                    if (!empty($pwd)) {
                        $user->setPassword(
                            $hasher->hashPassword($user, $pwd)
                        );
                        $passwordChanged = true;
                    }


                    $em->flush();


                    $newData = [
                        'username' => $user->getUsername(),
                        'roles'    => $user->getRoles(),
                    ];


                    $cleanOld = [];
                    $cleanNew = [];


                    foreach ($oldData as $key => $oldValue) {
                        if ($oldValue !== $newData[$key]) {
                            $cleanOld[$key] = $oldValue;
                            $cleanNew[$key] = $newData[$key];
                        }
                    }


                    if ($passwordChanged) {
                        $cleanNew['password'] = 'changed';
                    }


                    if (!empty($cleanOld) || !empty($cleanNew)) {
                        $auditLogger->log(
                            User::class,
                            $user->getId(),
                            ActionType::UPDATE,
                            $cleanOld ?: null,
                            $cleanNew ?: null
                        );
                    }


                    if ($this->getUser() && $this->getUser()->getId() === $user->getId()) {
                        $session->invalidate();
                        $this->addFlash(
                            'info',
                            'Your account was updated. Please log in again.'
                        );
                        return $this->redirectToRoute('app_login');
                    }


                    $this->addFlash('success', 'User updated successfully.');
                    return $this->redirectToRoute('app_users');
                }
            }


            $forms[$user->getId()] = $form->createView();
        }


        return $this->render('users.html.twig', [
            'users'     => $users,
            'userForms' => $forms,
            'addForm'   => $addForm->createView()
        ]);
    }


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


        if (!$this->isCsrfTokenValid('delete-user' . $id, $request->request->get('_token'))) {
            $this->addFlash('danger', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_users');
        }


        $deletedId = $user->getId();


        $em->remove($user);
        $em->flush();


        // ✅ AUDIT — USER DELETE
        $auditLogger->log(
            User::class,
            $deletedId,
            ActionType::DELETE,
            ['deleted' => true],
            null
        );


        $this->addFlash('success', 'User deleted successfully.');


        return $this->redirectToRoute('app_users');
    }
}





