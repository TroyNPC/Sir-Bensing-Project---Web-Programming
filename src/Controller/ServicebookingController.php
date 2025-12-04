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
    #[Route(name: 'app_servicebooking_index', methods: ['GET'])]
    public function index(ServicebookingRepository $servicebookingRepository): Response
    {
        return $this->render('servicebooking/index.html.twig', [
            'servicebookings' => $servicebookingRepository->findAll(),
        ]);
    }


    #[Route('/new', name: 'app_servicebooking_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        AuditLogger $auditLogger
    ): Response
    {
        $servicebooking = new Servicebooking();
        $form = $this->createForm(ServicebookingType::class, $servicebooking);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            $entityManager->persist($servicebooking);
            $entityManager->flush();


            // ✅ AUDIT — SERVICE BOOKING CREATE
            $auditLogger->log(
                Servicebooking::class,
                $servicebooking->getId(),
                ActionType::CREATE,
                null,
                [
                    'customer_name'  => $servicebooking->getCustomerName(),
                    'service_type'   => $servicebooking->getServiceType(),
                    'adviser_category' => $servicebooking->getAdvisercategory(),
                    'preferred_date' => $servicebooking->getPreferredDate()?->format('Y-m-d H:i:s'),
                    'notes'           => $servicebooking->getNotes(),
                ]
            );


            return $this->redirectToRoute('app_servicebooking_index');
        }


        return $this->render('servicebooking/new.html.twig', [
            'servicebooking' => $servicebooking,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_servicebooking_show', methods: ['GET'])]
    public function show(Servicebooking $servicebooking): Response
    {
        return $this->render('servicebooking/show.html.twig', [
            'servicebooking' => $servicebooking,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_servicebooking_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Servicebooking $servicebooking,
        EntityManagerInterface $entityManager,
        AuditLogger $auditLogger
    ): Response
    {
        // ✅ SNAPSHOT OLD VALUES BEFORE FORM BINDS
        $oldData = [
            'customer_name'    => $servicebooking->getCustomerName(),
            'service_type'     => $servicebooking->getServiceType(),
            'adviser_category' => $servicebooking->getAdvisercategory(),
            'preferred_date'   => $servicebooking->getPreferredDate()?->format('Y-m-d H:i:s'),
            'notes'            => $servicebooking->getNotes(),
        ];


        $form = $this->createForm(ServicebookingType::class, $servicebooking);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            $entityManager->flush();


            // ✅ SNAPSHOT NEW VALUES AFTER UPDATE
            $newData = [
                'customer_name'    => $servicebooking->getCustomerName(),
                'service_type'     => $servicebooking->getServiceType(),
                'adviser_category' => $servicebooking->getAdvisercategory(),
                'preferred_date'   => $servicebooking->getPreferredDate()?->format('Y-m-d H:i:s'),
                'notes'            => $servicebooking->getNotes(),
            ];


            // ✅ DIFF FILTER — ONLY LOG CHANGES
            $cleanOld = [];
            $cleanNew = [];


            foreach ($oldData as $key => $oldValue) {
                if ($oldValue !== $newData[$key]) {
                    $cleanOld[$key] = $oldValue;
                    $cleanNew[$key] = $newData[$key];
                }
            }


            // ✅ AUDIT — SERVICE BOOKING UPDATE
            if (!empty($cleanOld)) {
                $auditLogger->log(
                    Servicebooking::class,
                    $servicebooking->getId(),
                    ActionType::UPDATE,
                    $cleanOld,
                    $cleanNew
                );
            }


            return $this->redirectToRoute('app_servicebooking_index');
        }


        return $this->render('servicebooking/edit.html.twig', [
            'servicebooking' => $servicebooking,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_servicebooking_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Servicebooking $servicebooking,
        EntityManagerInterface $entityManager,
        AuditLogger $auditLogger
    ): Response
    {
        if ($this->isCsrfTokenValid('delete' . $servicebooking->getId(), $request->getPayload()->getString('_token'))) {


            $deletedId = $servicebooking->getId();


            $entityManager->remove($servicebooking);
            $entityManager->flush();


            // ✅ AUDIT — SERVICE BOOKING DELETE (UNCHANGED)
            $auditLogger->log(
                Servicebooking::class,
                $deletedId,
                ActionType::DELETE,
                ['deleted' => true],
                null
            );
        }


        return $this->redirectToRoute('app_servicebooking_index');
    }
}





