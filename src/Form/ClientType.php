<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('raisonSociale', TextType::class, [
                'label' => 'Raison Sociale (Entreprise)',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Tech Industries SAS'],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom du contact',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Dupont'],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom du contact',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Jean'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: contact@entreprise.com'],
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: 06 12 34 56 78'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
