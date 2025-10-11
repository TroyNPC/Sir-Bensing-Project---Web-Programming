<?php

namespace App\Form;

use App\Entity\InventoryLog;
use App\Entity\Pcproducts;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventoryLogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('stock')
            ->add('createdAt', null, [
                'widget' => 'single_text'
            ])
            ->add('actionType')
            ->add('productname', EntityType::class, [
                'class' => Pcproducts::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InventoryLog::class,
        ]);
    }
}
