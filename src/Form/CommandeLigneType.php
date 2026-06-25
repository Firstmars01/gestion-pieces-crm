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
        $devis = $options['devis'];

        // On ne propose que les lignes du devis qui n'ont pas encore été commandées
        $lignesDispo = [];
        foreach ($devis->getDevisLignes() as $dl) {
            $dejaCommande = false;
            foreach ($devis->getCommandes() as $cmd) {
                foreach ($cmd->getCommandeLignes() as $cl) {
                    if ($cl->getPiece()->getId() === $dl->getPiece()->getId()) {
                        $dejaCommande = true;
                        break;
                    }
                }
            }
            if (!$dejaCommande) {
                $lignesDispo[] = $dl;
            }
        }

        $builder
            ->add('devisLigne', EntityType::class, [
                'class' => DevisLigne::class,
                'choices' => $lignesDispo,
                'choice_label' => function(DevisLigne $dl) {
                    return $dl->getPiece()->getReference() . ' - ' . $dl->getPiece()->getLibelle() . ' (Qté: ' . $dl->getQuantite() . ')';
                },
                'mapped' => false,
                'label' => 'Sélectionner la ligne du devis à commander',
                'placeholder' => empty($lignesDispo) ? 'Toutes les lignes ont déjà été commandées' : 'Sélectionnez une pièce...',
                'attr' => ['class' => 'select-searchable piece-commande-select'],
                'disabled' => empty($lignesDispo)
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
