<?php

namespace App\Form;

use App\Entity\CommandeAchat;
use App\Entity\Fournisseur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeAchatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fournisseur', EntityType::class, [
                'class' => Fournisseur::class,
                'choice_label' => 'raisonSociale',
                'label' => 'Sélectionnez un fournisseur',
                'placeholder' => 'Choisir un fournisseur...',
                'attr' => ['class' => 'select-searchable'],
            ])
            ->add('dateCommande', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de la commande',
            ])
            ->add('datePrevue', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de livraison prévue',
            ])
            ->add('dateReelle', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date de livraison réelle (Si livrée)',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommandeAchat::class,
        ]);
    }
}
