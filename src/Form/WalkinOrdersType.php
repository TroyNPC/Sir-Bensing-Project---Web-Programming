<?php

namespace App\Form;

use App\Entity\Pcproducts;
use App\Entity\WalkinOrders;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WalkinOrdersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('buyerName')
            ->add('contact')
            ->add('paymentMethod')
            ->add('paymentStatus')
            ->add('quantity')
            ->add('warrantyText')
            ->add('purchaseDate', null, [
                'widget' => 'single_text'
            ])
            ->add('product', EntityType::class, [
                'class' => Pcproducts::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WalkinOrders::class,
        ]);
    }
}
