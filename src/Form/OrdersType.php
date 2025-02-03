<?php

namespace App\Form;

use App\Entity\Orders;
use App\Entity\Shop;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrdersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price')
            ->add('status')
            ->add('dateOrder')
            ->add('quantity')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('shops', EntityType::class, [
                'class' => Shop::class,
                'choice_label' => 'name',  // Assuming you want to display the 'name' field of the Shop entity
                'multiple' => true,  // Allowing multiple selections for shops
                'expanded' => true,  // Use checkboxes for selection
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Orders::class,
        ]);
    }
}
