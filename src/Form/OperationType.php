<?php

namespace App\Form;

use App\Entity\Operation;
use App\Entity\PosteMachine;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Nom de l\'opération',
                'attr' => ['placeholder' => 'Ex: Perçage diamètre 10, Fraisage face avant...'],
            ])
            ->add('tempsPrevu', IntegerType::class, [
                'label' => 'Temps prévu (en minutes)',
                'attr' => [
                    'min' => 1,
                    'placeholder' => 'Ex: 15',
                ],
            ])
            ->add('posteMachine', EntityType::class, [
                'class' => PosteMachine::class,
                // On fabrique un libellé clair : "Nom de la Machine (sur Nom du Poste)"
                'choice_label' => function (PosteMachine $pm) {
                    return $pm->getMachine()->getLibelle().' (sur '.$pm->getPoste()->getLibelle().')';
                },
                'label' => 'Duo Machine / Poste requis',
                'placeholder' => 'Sélectionnez une machine affectée à un poste...',
                'required' => true,
                'attr' => ['class' => 'select-searchable'], // Toujours notre barre de recherche magique
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Operation::class,
        ]);
    }
}
