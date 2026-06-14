<?php

namespace App\Form;

use App\Entity\Piece;
use App\Entity\PieceComposition;
use App\Repository\PieceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PieceCompositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pieceEnfant', EntityType::class, [
                'class' => Piece::class,
                // Règle métier : On ne peut composer qu'avec des matières 1ères, pièces achetées ou intermédiaires
                'query_builder' => function (PieceRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.type IN (:types)')
                        ->setParameter('types', ['MATIERE_PREMIERE', 'ACHETEE', 'INTERMEDIAIRE'])
                        ->orderBy('p.libelle', 'ASC');
                },
                'choice_label' => function (Piece $piece) {
                    return $piece->getReference().' - '.$piece->getLibelle();
                },
                'label' => false,
                'attr' => ['class' => 'form-select form-select-sm'],
            ])
            ->add('quantite', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'min' => 1,
                    'placeholder' => 'Quantité',
                    'class' => 'form-control form-control-sm',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PieceComposition::class,
        ]);
    }
}
