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
                'query_builder' => function (EntityRepository $er) use ($gammeActuelle) {
                    $qb = $er->createQueryBuilder('p');

                    // 1. Règle métier : Seulement les pièces fabricables
                    $qb->where('p.type IN (:types_autorises)')
                        ->setParameter('types_autorises', ['INTERMEDIAIRE', 'LIVRABLE']);

                    // 2. L'ARME NUCLÉAIRE : On demande une liste d'IDs pure à la BDD
                    $em = $er->getEntityManager();
                    $query = $em->createQuery('SELECT IDENTITY(g.piece) AS piece_id FROM App\Entity\Gamme g WHERE g.piece IS NOT NULL');
                    $result = $query->getArrayResult();

                    // On transforme le résultat en un simple tableau d'IDs (ex: [1, 4, 12])
                    $piecesOccupees = array_map(function($row) { return $row['piece_id']; }, $result);

                    // 3. Gestion de la modification : on "libère" la pièce de la gamme qu'on modifie
                    if ($gammeActuelle && $gammeActuelle->getId() && $gammeActuelle->getPiece()) {
                        $piecesOccupees = array_filter($piecesOccupees, function($id) use ($gammeActuelle) {
                            return $id !== $gammeActuelle->getPiece()->getId();
                        });
                    }

                    // 4. On applique l'exclusion stricte si des pièces sont occupées
                    if (!empty($piecesOccupees)) {
                        $qb->andWhere('p.id NOT IN (:occupees)')
                            ->setParameter('occupees', array_values($piecesOccupees));
                    }

                    // On trie par ordre alphabétique
                    $qb->orderBy('p.reference', 'ASC');

                    return $qb;
                },
                'choice_label' => function (Piece $piece) {
                    return $piece->getReference().' - '.$piece->getLibelle();
                },
                'label' => 'Pièce fabriquée',
                'placeholder' => 'Sélectionnez une pièce...',
                'required' => true,
                'attr' => ['class' => 'select-searchable'],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getNom().' '.$user->getPrenom();
                },
                'label' => 'Responsable de la gamme',
                'placeholder' => 'Sélectionnez un responsable...',
                'required' => true,
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
