<?php

namespace App\Form;

use App\Entity\GammeOperation;
use App\Entity\Operation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GammeOperationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('operation', EntityType::class, [
                'class' => Operation::class,
                'choice_label' => function(Operation $op) {
                    return $op->getLibelle() . ' (' . $op->getTempsPrevu() . ' min)';
                },
                'label' => 'Opération à ajouter',
                'placeholder' => 'Sélectionnez une opération...',
                'attr' => ['class' => 'select-searchable']
            ])
            ->add('ordre', IntegerType::class, [
                'label' => 'Ordre (N° de l\'étape)',
                'attr' => ['min' => 1]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GammeOperation::class,
        ]);
    }
}
