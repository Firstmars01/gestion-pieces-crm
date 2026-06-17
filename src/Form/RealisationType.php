<?php

namespace App\Form;

use App\Entity\Gamme;
use App\Entity\Realisation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RealisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gamme', EntityType::class, [
                'class' => Gamme::class,
                'choice_label' => function (Gamme $gamme) {
                    return $gamme->getLibelle() . ' (Pièce: ' . $gamme->getPiece()->getReference() . ')';
                },
                'label' => 'Gamme à fabriquer',
                'placeholder' => 'Sélectionnez une gamme...',
                'required' => true,
                'attr' => ['class' => 'select-searchable']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Realisation::class,
        ]);
    }
}
