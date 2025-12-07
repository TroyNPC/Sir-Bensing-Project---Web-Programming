<?php


namespace App\Controller;


use App\Entity\Servicebooking;
use App\Form\ServicebookingType;
use App\Repository\ServicebookingRepository;
use App\Service\AuditLogger;
use App\Enum\ActionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/servicebooking')]
final class ServicebookingController extends AbstractController
{
    #[Route(name: 'app_servicebooking_index', methods: ['GET', 'POST'])]
    public function index(
        ServicebookingRepository $servicebookingRepository,
        \App\Repository\UserRepository $userRepository
    ): Response {
        $user = $this->getUser();


        // Admin sees all bookings, staff sees only theirs
        if ($this->isGranted('ROLE_ADMIN')) {
            $bookings = $servicebookingRepository->findAll();
        } else {
            $bookings = $servicebookingRepository->findBy(['staff' => $user]);
        }


        $allUsers = $userRepository->findAll();
        $staffs = array_filter($allUsers, fn($u) => in_array('ROLE_STAFF', $u->getRoles()));


        return $this->render('servicebooking/index.html.twig', [
            'servicebookings' => $bookings,
            'staffs'          => $staffs,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_servicebooking_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Servicebooking $servicebooking,
        EntityManagerInterface $entityManager,
        AuditLogger $auditLogger
    ): Response {
        // Only admin or assigned staff can edit
        $user = $this->getUser();
        if (
            !$this->isGranted('ROLE_ADMIN')
            && $servicebooking->getStaff() !== $user
        ) {
            throw $this->createAccessDeniedException('You cannot edit this booking.');
        }


        $form = $this->createForm(ServicebookingType::class, $servicebooking);
        $form->handleRequest($request);


        // ✅ OLD DATA
        $oldData = [
            'customer_name'    => $servicebooking->getCustomerName(),
            'service_type'     => $servicebooking->getServiceType(),
            'adviser_category' => $servicebooking->getAdvisercategory(),
            'preferred_date'   => $servicebooking->getPreferredDate()?->format('Y-m-d H:i:s'),
            'notes'            => $servicebooking->getNotes(),
            'contact_number'   => $servicebooking->getContactNumber(),
            'email_address'    => $servicebooking->getEmailAddress(),
            'status'           => $servicebooking->getStatus(),
        ];


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();


            // ✅ NEW DATA
            $newData = [
                'customer_name'    => $servicebooking->getCustomerName(),
                'service_type'     => $servicebooking->getServiceType(),
                'adviser_category' => $servicebooking->getAdvisercategory(),
                'preferred_date'   => $servicebooking->getPreferredDate()?->format('Y-m-d H:i:s'),
                'notes'            => $servicebooking->getNotes(),
                'contact_number'   => $servicebooking->getContactNumber(),
                'email_address'    => $servicebooking->getEmailAddress(),
                'status'           => $servicebooking->getStatus(),
            ];


            $changedOld = [];
            $changedNew = [];
            foreach ($oldData as $key => $oldValue) {
                if ($oldValue !== $newData[$key]) {
                    $changedOld[$key] = $oldValue;
                    $changedNew[$key] = $newData[$key];
                }
            }


            if (!empty($changedOld)) {
                $auditLogger->log(
                    $servicebooking->getId(),
                    ActionType::UPDATE,
                    $changedOld,
                    $changedNew
                );
            }

            $this->addFlash('success', 'Service booking edited successfully.');
            return $this->redirectToRoute('app_servicebooking_index');
        }


        return $this->render('servicebooking/edit.html.twig', [
            'servicebooking' => $servicebooking,
            'form'           => $form->createView(),
        ]);
    }


    #[Route('/new', name: 'app_servicebooking_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        AuditLogger $auditLogger,
        \App\Repository\UserRepository $userRepository
    ): Response {
        $servicebooking = new Servicebooking();


        $form = $this->createForm(ServicebookingType::class, $servicebooking);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            // ✅ RESET AUTO_INCREMENT IF TABLE EMPTY
            $connection = $entityManager->getConnection();
            $count = $connection->fetchOne('SELECT COUNT(*) FROM servicebooking');


            if ((int)$count === 0) {
                $connection->executeStatement('ALTER TABLE servicebooking AUTO_INCREMENT = 1');
            }


            $entityManager->persist($servicebooking);
            $entityManager->flush();


            // ✅ AUDIT — CREATE
            $auditLogger->log(
                $servicebooking->getId(),
                ActionType::CREATE,
                null,
                [
                    'customer_name'    => $servicebooking->getCustomerName(),
                    'service_type'     => $servicebooking->getServiceType(),
                    'adviser_category' => $servicebooking->getAdvisercategory(),
                    'preferred_date'   => $servicebooking->getPreferredDate()?->format('Y-m-d H:i:s'),
                    'notes'            => $servicebooking->getNotes(),
                    'contact_number'   => $servicebooking->getContactNumber(),
                    'email_address'    => $servicebooking->getEmailAddress(),
                    'status'           => $servicebooking->getStatus(),
                ]
            );

            $this->addFlash('success', 'ServiceBooking request has been added!.');

            return $this->redirectToRoute('app_servicebooking_new');
        }


        // Safe way: fetch all users and filter in PHP
        $allUsers = $userRepository->findAll();
        $staffs = array_filter($allUsers, fn($user) => in_array('ROLE_STAFF', $user->getRoles()));


        return $this->render('servicebooking/new.html.twig', [
            'servicebooking' => $servicebooking,
            'form'           => $form->createView(),
            'staffs'         => $staffs,
        ]);
    }


    #[Route('/{id}', name: 'app_servicebooking_show', methods: ['GET'])]
    public function show(Servicebooking $servicebooking): Response
    {
        return $this->render('servicebooking/show.html.twig', [
            'servicebooking' => $servicebooking,
        ]);
    }


    #[Route('/{id}', name: 'app_servicebooking_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Servicebooking $servicebooking,
        EntityManagerInterface $entityManager,
        AuditLogger $auditLogger
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $servicebooking->getId(), $request->getPayload()->getString('_token'))) {


            // ✅ Snapshot before delete
            $deletedId   = $servicebooking->getId();
            $deletedData = [
                'customer_name'    => $servicebooking->getCustomerName(),
                'service_type'     => $servicebooking->getServiceType(),
                'adviser_category' => $servicebooking->getAdvisercategory(),
                'preferred_date'   => $servicebooking->getPreferredDate()?->format('Y-m-d H:i:s'),
                'notes'            => $servicebooking->getNotes(),
                'contact_number'   => $servicebooking->getContactNumber(),
                'email_address'    => $servicebooking->getEmailAddress(),
                'status'           => $servicebooking->getStatus(),
                'deleted'          => true,
            ];


            $entityManager->remove($servicebooking);
            $entityManager->flush();


            // ✅ AUDIT — DELETE
            $auditLogger->log(
                $deletedId,
                ActionType::DELETE,
                $deletedData,
                null
            );
        }

        $this->addFlash('success', 'Service booking deleted successfully.');
        return $this->redirectToRoute('app_servicebooking_index');
    }
}






