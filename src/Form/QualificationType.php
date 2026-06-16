<?php

namespace App\Form;

use App\Entity\PosteTravail;
use App\Entity\Qualification;
use App\Entity\User;
use App\Repository\UserRepository; // 👈 N'oublie pas d'importer le UserRepository !
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QualificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        // On utilise leftJoin pour ne pas exclure ceux qui n'ont aucun rôle en BDD
                        ->leftJoin('u.userRoles', 'r')

                        // On accepte le rôle Atelier, le rôle User, OU ceux qui n'ont pas de rôle du tout (r IS NULL)
                        ->where('r.code IN (:codes)')
                        ->orWhere('r.id IS NULL')

                        ->setParameter('codes', ['ROLE_ATELIER', 'ROLE_USER'])
                        ->orderBy('u.nom', 'ASC');
                },
                'choice_label' => function (User $user) {
                    return $user->getNom().' '.$user->getPrenom();
                },
                'label' => 'Ouvrier (Utilisateur)',
                'placeholder' => 'Sélectionnez un ouvrier...',
                'attr' => ['class' => 'select-searchable'],
            ])
            ->add('poste', EntityType::class, [
                'class' => PosteTravail::class,
                'choice_label' => 'libelle',
                'label' => 'Poste de travail autorisé',
                'placeholder' => 'Sélectionnez un poste...',
                'attr' => ['class' => 'select-searchable'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Qualification::class,
        ]);
    }
}
