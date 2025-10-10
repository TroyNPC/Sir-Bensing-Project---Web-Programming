<?php

namespace App\Form;

use App\Entity\Pcproducts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class PcproductsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // IMAGE FIELD
            ->add('image', FileType::class, [
                'label' => 'Product Image (JPG, PNG, GIF)',
                'mapped' => false, // not directly mapped to entity
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '50M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPG, PNG, GIF)',
                    ])
                ],
            ])
            // CATEGORY DROPDOWN
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'GPU' => 'GPU',
                    'RAM' => 'RAM',
                    'Motherboard' => 'Motherboard',
                    'Case' => 'Case',
                    'Storage' => 'Storage',
                    'Power Supply' => 'Power Supply',
                    'Cooling' => 'Cooling',
                ],
                'placeholder' => 'Select a category',
            ])
            // BRAND DROPDOWN
            ->add('brand', ChoiceType::class, [
                'choices' => [
                    'Asus' => 'Asus',
                    'MSI' => 'MSI',
                    'Gigabyte' => 'Gigabyte',
                    'Easy PC' => 'Easy PC',
                    'Corsair' => 'Corsair',
                    'Cooler Master' => 'Cooler Master',
                    'EVGA' => 'EVGA',
                ],
                'placeholder' => 'Select a brand',
            ])
            ->add('name')
            ->add('price')
            ->add('description')
            ->add('isavailable')
            ->add('createdat', null, [
                'disabled' => true,
                'data' => new \DateTimeImmutable(),
                'widget' => 'single_text',
            ])
            ->add('updatedat', null, [
                'widget' => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pcproducts::class,
        ]);
    }
}
