<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                // mapped => false signifie que ce champ n'est pas lié directement à la BDD
                // (car on doit le hacher avant de le sauvegarder)
                'mapped' => false,
                'required' => !$isEdit, // Obligatoire seulement à la création
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => $isEdit ? [] : [
                    new NotBlank(['message' => 'Le mot de passe est obligatoire.']),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins 8 caractères.',
                    ]),
                ],
            ])
            ->add('userRoles', EntityType::class, [
                'class' => Role::class, // L'entité Role
                'choice_label' => 'code', // Ce qu'on affiche (ex: ROLE_ADMIN)
                'multiple' => true, // On peut choisir plusieurs rôles
                'expanded' => true, // Affiche des cases à cocher (checkbox) au lieu d'un menu déroulant
                'label' => 'Rôles',
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Compte actif (autorisé à se connecter)',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false, // Option personnalisée pour savoir si on est en création ou modification
        ]);
    }
}
