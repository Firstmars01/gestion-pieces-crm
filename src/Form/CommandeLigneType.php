<?php

namespace App\Form;

use App\Entity\CommandeLigne;
use App\Entity\DevisLigne;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeLigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $devis = $options['devis'];

        // 1. Calculer les pièces qui ont encore un reste à commander
        $lignesDispo = [];
        $restes = []; // Stockage du reste par ID de DevisLigne

        foreach ($devis->getDevisLignes() as $dl) {
            $dejaCommande = 0;
            // On compte combien on en a déjà commandé dans les autres commandes de ce devis
            foreach ($devis->getCommandes() as $cmd) {
                foreach ($cmd->getCommandeLignes() as $cl) {
                    if ($cl->getPiece() === $dl->getPiece()) {
                        $dejaCommande += $cl->getQuantite();
                    }
                }
            }
            $reste = $dl->getQuantite() - $dejaCommande;

            // On ne garde que s'il en reste à commander !
            if ($reste > 0) {
                $lignesDispo[] = $dl;
                $restes[$dl->getId()] = $reste;
            }
        }

        $builder
            ->add('devisLigne', EntityType::class, [
                'class' => DevisLigne::class,
                'choices' => $lignesDispo,
                'choice_label' => function(DevisLigne $dl) use ($restes) {
                    $reste = $restes[$dl->getId()] ?? 0;
                    return $dl->getPiece()->getReference() . ' - ' . $dl->getPiece()->getLibelle() . ' (Reste : ' . $reste . ')';
                },
                'choice_attr' => function(DevisLigne $dl) use ($restes) {
                    // On place le reste en attribut pour le Javascript
                    return ['data-reste' => $restes[$dl->getId()] ?? 0];
                },
                'mapped' => false,
                'label' => 'Sélectionner la pièce',
                'placeholder' => 'Sélectionnez une pièce...',
                'attr' => [
                    'class' => 'select-searchable piece-commande-select',
                    'onchange' => "updateMaxQuantite(this);"
                ]
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité à commander',
                'attr' => [
                    'min' => 1,
                    'class' => 'quantite-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommandeLigne::class,
            'devis' => null,
        ]);
    }
}
