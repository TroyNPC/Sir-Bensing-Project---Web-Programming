<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;

        $builder
            ->add('fullname', TextType::class, [
                'label' => 'Full Name',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Full name cannot be blank.']),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z\s]+$/',
                        'message' => 'Full name can only contain letters and spaces.',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Email cannot be blank.']),
                    new Assert\Email(['message' => 'Please enter a valid email address.']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'label' => 'Password',  
                'required' => !$isEdit,           // required on create, optional on edit
                'constraints' => !$isEdit ? [      // only validate NotBlank on create
                    new Assert\NotBlank(['message' => 'Password cannot be blank.']),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/',
                        'message' => 'Password must be at least 6 characters long and include letters and numbers.',
                    ]),
                ] : [
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/',
                        'message' => 'Password must be at least 6 characters long and include letters and numbers.',
                    ]),
                ],
                'attr' => [
                    'placeholder' => $isEdit ? 'Leave blank to keep current password' : 'Enter password',
                ],
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Phone Number',
                'required' => false,
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^\+?\d{7,15}$/',
                        'message' => 'Enter a valid phone number (7-15 digits, optional + at start).',
                    ]),
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Address',
                'required' => false,
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z0-9\s,.-]*$/',
                        'message' => 'Address can only contain letters, numbers, spaces, commas, dots, and dashes.',
                    ]),
                ],
            ]);

        // Roles field only for admins
        if (!empty($options['is_admin']) && $options['is_admin'] === true) {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'Role',
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'Customer' => 'ROLE_CUSTOMER',
                ],
                'expanded' => true,
                 'mapped' => true,      // maps to $user->roles
                'multiple' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_admin' => false,
            'is_edit' => false, // default is new form
        ]);
    }
}
