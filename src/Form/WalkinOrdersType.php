<?php


namespace App\Form;


use App\Entity\Pcproducts;
use App\Entity\WalkinOrders;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class WalkinOrdersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Buyer name
            ->add('buyerName', TextType::class, [
                'label'    => 'Buyer Name',
                'required' => true,
            ])


            // Contact
            ->add('contact', TextType::class, [
                'label'    => 'Contact Number',
                'required' => true,
                'attr'     => [
                    'placeholder' => '09XXXXXXXXX or +639XXXXXXXXX',
                ],
            ])


            // Payment method
            ->add('paymentMethod', ChoiceType::class, [
                'label'       => 'Payment Method',
                'required'    => true,
                'placeholder' => 'Select payment method',
                'choices'     => [
                    'Cash'          => 'cash',
                    'Credit Card'   => 'credit_card',
                    'Debit Card'    => 'debit_card',
                    'GCash'         => 'gcash',
                    'Bank Transfer' => 'bank_transfer',
                ],
            ])


            // Payment status
            ->add('paymentStatus', ChoiceType::class, [
                'label'       => 'Payment Status',
                'required'    => true,
                'choices'     => [
                    'Unpaid' => 'unpaid',
                    'Paid'   => 'paid',
                ],
            ])


            // Quantity
            ->add('quantity', IntegerType::class, [
                'label'    => 'Quantity',
                'required' => true,
                'attr'     => [
                    'min' => 1,
                ],
            ])


            // Warranty text (optional, entity also auto-fills on PrePersist if null)
            ->add('warrantyText', TextareaType::class, [
                'label'    => 'Warranty Text',
                'required' => false,
                'attr'     => [
                    'rows' => 4,
                ],
            ])


            // Purchase date (optional; entity will set if null on create)
            ->add('purchaseDate', DateTimeType::class, [
                'label'    => 'Purchase Date',
                'widget'   => 'single_text',
                'required' => false,
            ])


            // Product (Pcproducts)
            ->add('product', EntityType::class, [
                'class'         => Pcproducts::class,
                'label'         => 'Product',
                'choice_label'  => 'name', // show product name instead of ID
                'placeholder'   => 'Select product',
                'required'      => true,
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






