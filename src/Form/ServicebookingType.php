<?php


namespace App\Form;


use App\Entity\Servicebooking;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class ServicebookingType extends AbstractType
{
    private TokenStorageInterface $tokenStorage;


    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('serviceType', ChoiceType::class, [
                'choices' => [
                    'Video Call' => 'Video Call',
                    'On-Site Consultation' => 'On-Site Consultation',
                    'Hardware Troubleshooting' => 'Hardware Troubleshooting',
                    'Software Setup' => 'Software Setup',
                ],
                'placeholder' => 'Select service type',
                'label' => 'Service Type',
            ])
            ->add('advisercategory', ChoiceType::class, [
                'choices' => [
                    'PC Hardware' => 'PC Hardware',
                    'PC Software' => 'PC Software',
                    'Networking' => 'Networking',
                    'Other' => 'Other',
                ],
                'placeholder' => 'Select adviser category',
                'label' => 'Adviser Category',
            ])
            ->add('preferredDate', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Preferred Date & Time',
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'label' => 'Additional Notes',
            ])
            ->add('customerName', TextType::class, [
                'label' => 'Customer Name',
                'required' => true,
            ])
            ->add('contactNumber', TextType::class, [
                'label' => 'Contact Number',
                'required' => true,
            ])
            ->add('emailAddress', EmailType::class, [
                'label' => 'Email Address',
                'required' => true,
            ])
            ->add('staff', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'label' => 'Assign Staff',
                'placeholder' => 'Select staff',
                'query_builder' => function (UserRepository $repo) {
                    return $repo->createQueryBuilder('u')
                                ->where('u.roles LIKE :role')
                                ->setParameter('role', '%"ROLE_STAFF"%')
                                ->orderBy('u.username', 'ASC');
                },
                'required' => true,
            ]);


        /* ✅ Status:
        - SHOWN ONLY on EDIT
        - Only for STAFF / ADMIN
        - Hidden on NEW for everyone */
        $token = $this->tokenStorage->getToken();
        $roles = $token ? $token->getRoleNames() : [];


        $booking = $builder->getData();   // Entity bound to the form
        $isEdit = $booking && $booking->getId() !== null;


        if ($isEdit && (in_array('ROLE_STAFF', $roles) || in_array('ROLE_ADMIN', $roles))) {
            $builder->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Ongoing'   => 'ongoing',
                    'Paused'    => 'paused',
                    'Completed' => 'completed',
                ],
                'placeholder' => 'Select status',
                'required' => true,
            ]);
        }

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Servicebooking::class,
        ]);
    }
}






