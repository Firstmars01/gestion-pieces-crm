<?php

namespace App\Form;

use App\Entity\Gamme;
use App\Entity\Piece;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GammeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // On récupère l'entité Gamme qui est en train d'être traitée par le formulaire (Création ou Édition)
        $gammeActuelle = $options['data'] ?? null;

        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Nom de la gamme',
                'attr' => ['placeholder' => 'Ex: Gamme assemblage standard'],
            ])
            ->add('piece', EntityType::class, [
                'class' => Piece::class,
                // On filtre les pièces disponibles avec le query_builder
                'query_builder' => function (EntityRepository $er) use ($gammeActuelle) {
                    $qb = $er->createQueryBuilder('p');

                    // 1. On prépare une sous-requête qui va chercher TOUTES les pièces déjà liées à une gamme
                    $subQuery = $er->createQueryBuilder('p2')
                        ->select('IDENTITY(g.piece)') // On cible la clé étrangère piece_id
                        ->from('App\Entity\Gamme', 'g')
                        ->where('g.piece IS NOT NULL');

                    // 2. Logique selon qu'on est en Création ou en Modification
                    if (!$gammeActuelle || !$gammeActuelle->getId()) {
                        // CRÉATION : On exclut toutes les pièces de la sous-requête
                        $qb->where($qb->expr()->notIn('p.id', $subQuery->getDQL()));
                    } else {
                        // MODIFICATION : On exclut les pièces utilisées SAUF celle de la gamme actuelle
                        $subQuery->andWhere('g.id != :gammeId');
                        $qb->where($qb->expr()->notIn('p.id', $subQuery->getDQL()))
                            ->setParameter('gammeId', $gammeActuelle->getId());
                    }

                    // On trie par ordre alphabétique des références pour que ce soit propre
                    $qb->orderBy('p.reference', 'ASC');

                    return $qb;
                },
                'choice_label' => function (Piece $piece) {
                    return $piece->getReference().' - '.$piece->getLibelle();
                },
                'label' => 'Pièce fabriquée',
                'placeholder' => 'Sélectionnez une pièce...',
                // Maintien de Tom Select
                'attr' => ['class' => 'select-searchable'],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getNom().' '.$user->getPrenom();
                },
                'label' => 'Responsable de la gamme',
                'placeholder' => 'Sélectionnez un responsable...',
                // Maintien de Tom Select
                'attr' => ['class' => 'select-searchable'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gamme::class,
        ]);
    }
}
