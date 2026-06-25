<?php

namespace App\Form;

use App\Entity\Piece;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class FournisseurAddPieceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('piece', EntityType::class, [
                'class' => Piece::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.type IN (:types)')
                        ->setParameter('types', ['Matière première', 'Achetée', 'ACHETEE', 'Achete'])
                        ->orderBy('p.reference', 'ASC');
                },
                'choice_label' => function (Piece $piece) {
                    return sprintf('%s - %s', $piece->getReference(), $piece->getLibelle());
                },
                // On met le prix en attribut de l'option
                'choice_attr' => function (Piece $piece) {
                    return ['data-prix' => $piece->getPrixCatalogue()];
                },
                'label' => 'Sélectionnez la pièce à associer',
                'placeholder' => 'Choisir une pièce...',
                'attr' => [
                    'class' => 'select-searchable',
                    'onchange' => "document.querySelector('.prix-input-auto').value = this.options[this.selectedIndex].getAttribute('data-prix') || '';",
                ],
                'mapped' => false,
            ])
            ->add('prixAchat', NumberType::class, [
                'label' => 'Prix d\'achat unitaire (€)',
                'scale' => 2,
                'html5' => true,
                'required' => false,
                'attr' => [
                    'step' => '0.01',
                    'min' => '0',
                    'class' => 'prix-input-auto',
                ],
                'mapped' => false,
                'help' => 'Ce montant mettra à jour le prix d\'achat de référence de cette pièce.'
            ])
        ;
    }
}
