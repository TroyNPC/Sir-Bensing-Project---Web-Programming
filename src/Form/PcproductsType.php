<?php

namespace App\Form;

use App\Entity\Pcproducts;
use App\Entity\InventoryLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PcproductsType extends AbstractType
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Pcproducts|null $product */
        $product = $options['data'] ?? null;

        $disabledAvailability = false;

        if ($product && $product->getId()) {
            // Get the latest InventoryLog for this product
            $latestLog = $this->em->getRepository(InventoryLog::class)
                ->findOneBy(
                    ['productname' => $product],
                    ['createdAt' => 'DESC']
                );

            if ($latestLog && $latestLog->getStock() <= 0) {
                $disabledAvailability = true;
            }
        }

        $builder
            ->add('image', FileType::class, [
                'label' => 'Product Image (JPG, PNG, GIF)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '50M',
                        'mimeTypes' => ['image/jpeg','image/png','image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPG, PNG, GIF)',
                    ])
                ],
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'GPU'=>'GPU', 'RAM'=>'RAM','Motherboard'=>'Motherboard',
                    'Case'=>'Case','Storage'=>'Storage','Power Supply'=>'Power Supply','Cooling'=>'Cooling'
                ],
                'placeholder'=>'Select a category',
            ])
            ->add('brand', ChoiceType::class, [
                'choices' => [
                    'Asus'=>'Asus','MSI'=>'MSI','Gigabyte'=>'Gigabyte',
                    'Easy PC'=>'Easy PC','Corsair'=>'Corsair','Cooler Master'=>'Cooler Master','EVGA'=>'EVGA'
                ],
                'placeholder'=>'Select a brand',
            ])
            ->add('name')
            ->add('price')
            ->add('description')
            ->add('isavailable', null, [
                'disabled' => $disabledAvailability,
            ])
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
