<?php

namespace App\Form;

use App\Entity\CmdAchatLigne;
use App\Entity\Piece;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
                // On filtre pour n'avoir que les pièces de CE fournisseur
                'query_builder' => function (EntityRepository $er) use ($fournisseur) {
                    return $er->createQueryBuilder('p')
                        ->where('p.fournisseur = :fournisseur')
                        ->setParameter('fournisseur', $fournisseur)
                        ->orderBy('p.reference', 'ASC');
                },
                // On affiche le prix catalogue dans la liste pour aider l'acheteur
                'choice_label' => function (Piece $piece) {
                    return sprintf('%s - %s (Cat: %s €)', $piece->getReference(), $piece->getLibelle(), $piece->getPrixCatalogue());
                },
                'label' => 'Sélectionnez la pièce',
                'placeholder' => 'Choisir une pièce...',
                'attr' => ['class' => 'select-searchable']
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité commandée',
                'attr' => ['min' => 1]
            ])
            ->add('prixAchat', NumberType::class, [
                'label' => 'Prix d\'achat unitaire négocié (€)',
                'scale' => 2,
                'html5' => true,
                'attr' => ['step' => '0.01', 'min' => '0']
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
