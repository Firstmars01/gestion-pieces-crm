<?php

namespace App\Form;

use App\Entity\Piece;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FournisseurPiecePrixCatalogueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('prixCatalogue', MoneyType::class, [
            'label' => 'Prix catalogue (Achat)',
            'required' => false,
            'currency' => 'EUR',
            'help' => 'Modifiez uniquement le prix catalogue de cette pièce.',
            'attr' => [
                'placeholder' => 'Ex: 10.99',
                'step' => '0.01',
                'min' => '0',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Piece::class,
        ]);
    }
}
