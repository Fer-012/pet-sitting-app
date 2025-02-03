<?php

namespace App\Form;

use App\Entity\Pet;
use App\Entity\Reservation;
use App\Entity\Services;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateTime', null, [
                'widget' => 'single_text',
            ])
            ->add('duration')
            ->add('pet', ChoiceType::class, [
                'choices' => $options['pets'], // Pass the list of pets
                'choice_label' => function ($pet) {
                    return $pet->getName(); // Assuming Pet entity has a `getName` method
                },
                'choice_value' => 'id', // Use the ID of the pet
                'placeholder' => 'Select a pet',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'pets' => [], // Default value for pets
        ]);
        $resolver->setAllowedTypes('pets', 'array');
    }
}
