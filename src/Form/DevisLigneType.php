<?php

namespace App\Form;

use App\Entity\DevisLigne;
use App\Entity\Piece;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevisLigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('piece', EntityType::class, [
                'class' => Piece::class,
                // 1. LE FILTRE : On ne récupère QUE les pièces de type LIVRABLE
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.type = :type')
                        ->setParameter('type', 'LIVRABLE')
                        ->orderBy('p.reference', 'ASC');
                },
                'choice_label' => function (Piece $piece) {
                    return $piece->getReference().' - '.$piece->getLibelle();
                },
                // On cache le prix dans les options du select
                'choice_attr' => function (?Piece $piece) {
                    return $piece ? ['data-prix' => $piece->getPrixVente() ?? ''] : [];
                },
                'placeholder' => 'Sélectionnez une pièce...',
                'label' => 'Sélectionner une pièce',
                // 2. LE SCRIPT ANTI-AJAX : On met le script directement dans l'événement "onchange" du HTML
                'attr' => [
                    'class' => 'select-searchable', // Ajout de la classe de recherche
                    'onchange' => "document.querySelector('.prix-input-auto').value = this.options[this.selectedIndex].getAttribute('data-prix') || '';",
                ],
                'required' => true,
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité demandée',
                'attr' => ['min' => 1],
                'required' => true,
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix Unitaire (HT)',
                'currency' => 'EUR',
                'attr' => ['class' => 'prix-input-auto'],
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => DevisLigne::class]);
    }
}
