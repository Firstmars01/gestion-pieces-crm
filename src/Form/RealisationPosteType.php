<?php

namespace App\Form;

use App\Entity\PosteMachine;
use App\Entity\RealisationPoste;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RealisationPosteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('temps', IntegerType::class, [
                'label' => 'Temps réel passé (en minutes)',
                'attr' => ['min' => 1]
            ])
            ->add('posteMachine', EntityType::class, [
                'class' => PosteMachine::class,
                'choice_label' => function (PosteMachine $pm) {
                    return $pm->getMachine()->getLibelle() . ' (sur ' . $pm->getPoste()->getLibelle() . ')';
                },
                'label' => 'Machine et Poste réellement utilisés',
                'placeholder' => 'Sélectionnez obligatoirement une machine...',
                'required' => true,
                'attr' => ['class' => 'select-searchable']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RealisationPoste::class,
        ]);
    }
}
