<?php

namespace App\Form;

use App\Entity\Machine;
use App\Entity\PosteMachine;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PosteMachineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('machine', EntityType::class, [
                'class' => Machine::class,
                'choice_label' => 'libelle',
                'label' => false,
                'placeholder' => 'Sélectionner une machine...',
                'attr' => ['class' => 'select-searchable'] // Notre barre de recherche magique !
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PosteMachine::class,
        ]);
    }
}
