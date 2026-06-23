<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Devis;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'nomComplet',
                'placeholder' => 'Sélectionnez un client...',
                'attr' => ['class' => 'select-searchable'] // Ajout de la classe de recherche
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom();
                },
                'label' => 'Commercial en charge',
                'placeholder' => 'Sélectionnez un commercial...',
                'attr' => ['class' => 'select-searchable'] // Ajout de la classe de recherche
            ])
            ->add('dateLimite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de limite de validité',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Devis::class]);
    }
}
