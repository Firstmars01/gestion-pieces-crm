<?php

namespace App\Form;

use App\Entity\Piece;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PieceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, [
                'label' => 'Référence unique',
                'attr' => ['placeholder' => 'Ex: TAB-PING-001'],
            ])
            ->add('libelle', TextType::class, [
                'label' => 'Libellé de la pièce',
                'attr' => ['placeholder' => 'Ex: Table de Ping-Pong'],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de pièce',
                'choices' => [
                    'Livrable au client' => 'LIVRABLE',
                    'Pièce intermédiaire (Fabriquée)' => 'INTERMEDIAIRE',
                    'Matière première' => 'MATIERE_PREMIERE',
                    'Pièce achetée' => 'ACHETEE',
                ],
                'help' => 'Attention : le type définit si la pièce est commercialisable ou non.',
            ])
            ->add('prixVente', MoneyType::class, [
                'label' => 'Prix unitaire de vente',
                'required' => false,
                'currency' => 'EUR',
                'help' => 'Obligatoire uniquement pour les pièces livrables.',
                'attr' => ['placeholder' => 'Ex: 10.99'],
            ])
            ->add('prixCatalogue', MoneyType::class, [
                'label' => 'Prix catalogue (Achat)',
                'required' => false,
                'currency' => 'EUR',
                'help' => 'Pour les pièces achetées ou matières premières.',
                'attr' => ['placeholder' => 'Ex: 10.99'],
            ])
            ->add('quantiteStock', IntegerType::class, [
                'label' => 'Quantité en stock',
                'attr' => ['placeholder' => 'Ex: 10'],
            ])
            ->add('composants', CollectionType::class, [
                'entry_type' => PieceCompositionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Piece::class,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}
