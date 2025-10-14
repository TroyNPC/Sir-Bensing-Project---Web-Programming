<?php

namespace App\Form;

use App\Entity\Stocks;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class StocksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Stocks|null $log */
        $log = $options['data'] ?? null;

        $builder
            ->add('stock', IntegerType::class, [
                'attr' => [
                    'min' => 0,
                    'step' => 1,
                ],
                'required' => true,
                'empty_data' => '0', // ✅ ensures zero (0) is accepted and not treated as empty
            ]);
        // Image is not included, as it cannot be changed
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\Stocks::class,
            // Ensure CSRF protection is explicit and uses a stable token id
            'csrf_protection'   => true,
            'csrf_field_name'   => '_token',
            // Use a stable token id that will be the same across all forms of this type
            'csrf_token_id'     => 'stocks_item',
        ]);
    }

}
