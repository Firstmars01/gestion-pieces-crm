<?php

namespace App\Form;

use App\Entity\CommandeLigne;
use App\Entity\DevisLigne;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeLigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $commande = $options['commande'];

        // 1. Compter combien de fois chaque paire [Pièce + Quantité] a DÉJÀ été commandée
        $commandeesCount = [];
        foreach ($commande->getDevisList() as $devis) {
            foreach ($devis->getCommandes() as $cmd) {
                foreach ($cmd->getCommandeLignes() as $cl) {
                    $key = $cl->getPiece()->getId().'_'.$cl->getQuantite();
                    if (!isset($commandeesCount[$key])) {
                        $commandeesCount[$key] = 0;
                    }
                    ++$commandeesCount[$key];
                }
            }
        }

        // 2. Parcourir les lignes de devis pour voir lesquelles sont encore disponibles
        $lignesDispo = [];
        foreach ($commande->getDevisList() as $devis) {
            foreach ($devis->getDevisLignes() as $dl) {
                $key = $dl->getPiece()->getId().'_'.$dl->getQuantite();

                // Si cette paire a déjà été commandée, on "consomme" une commande et on ignore la ligne
                if (isset($commandeesCount[$key]) && $commandeesCount[$key] > 0) {
                    --$commandeesCount[$key];
                } else {
                    // Sinon, la ligne est libre pour être ajoutée !
                    $lignesDispo[] = $dl;
                }
            }
        }

        $builder->add('devisLigne', EntityType::class, [
            'class' => DevisLigne::class,
            'choices' => $lignesDispo,
            'choice_label' => function (DevisLigne $dl) {
                $nomDevis = $dl->getDevis()->getNom() ? $dl->getDevis()->getNom() : 'Devis #'.$dl->getDevis()->getId();

                return '['.$nomDevis.'] '.$dl->getPiece()->getReference().' - '.$dl->getPiece()->getLibelle().' (Quantité figée : '.$dl->getQuantite().')';
            },
            'mapped' => false,
            'label' => 'Sélectionner la ligne complète à commander',
            'placeholder' => empty($lignesDispo) ? 'Toutes les lignes ont déjà été commandées' : 'Sélectionnez une pièce...',
            'attr' => ['class' => 'select-searchable piece-commande-select'],
            'disabled' => empty($lignesDispo),
        ]);
        // REMARQUE : On a totalement supprimé le champ "quantite" ici !
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CommandeLigne::class, 'commande' => null]);
    }
}
