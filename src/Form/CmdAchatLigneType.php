<?php

namespace App\Form;

use App\Entity\CmdAchatLigne;
use App\Entity\Piece;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CmdAchatLigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fournisseur = $options['fournisseur'];

        $builder
            ->add('piece', EntityType::class, [
                'class' => Piece::class,
                'query_builder' => function (EntityRepository $er) use ($fournisseur) {
                    return $er->createQueryBuilder('p')
                        ->where('p.fournisseur = :fournisseur')
                        ->setParameter('fournisseur', $fournisseur)
                        ->orderBy('p.reference', 'ASC');
                },
                'choice_label' => function (Piece $piece) {
                    return sprintf('%s - %s (Cat: %s €)', $piece->getReference(), $piece->getLibelle(), $piece->getPrixCatalogue());
                },
                // --- ON REMET LE PRIX EN ATTRIBUT DE L'OPTION ---
                'choice_attr' => function (Piece $piece) {
                    return ['data-prix' => str_replace(',', '.', (string) $piece->getPrixCatalogue())];
                },
                'label' => 'Sélectionnez la pièce',
                'placeholder' => 'Choisir une pièce...',
                'attr' => ['class' => 'select-searchable']
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité commandée',
                'attr' => ['min' => 1]
            ])
            // --- ON UTILISE TextType COMME DANS LE FORMULAIRE FOURNISSEUR ---
            ->add('prixAchat', TextType::class, [
                'label' => 'Prix d\'achat unitaire négocié (€)',
                'required' => false,
                'attr' => [
                    'class' => 'prix-input-auto', // Classe utilisée pour le ciblage JS
                    'inputmode' => 'decimal',
                    'pattern' => '^\d+([.]\d{1,2})?$',
                    'placeholder' => 'Ex: 10.99',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CmdAchatLigne::class,
            'fournisseur' => null, // Option personnalisée pour passer le fournisseur depuis le contrôleur
        ]);
    }
}
