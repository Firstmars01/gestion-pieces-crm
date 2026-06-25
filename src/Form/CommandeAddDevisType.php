<?php

namespace App\Form;

use App\Entity\Devis;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeAddDevisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $commande = $options['commande'];
        $client = $commande->getClient();

        // On récupère les ID des devis déjà liés pour ne pas les proposer à nouveau
        $linkedDevisIds = [];
        foreach ($commande->getDevisList() as $d) {
            $linkedDevisIds[] = $d->getId();
        }

        $builder->add('devis', EntityType::class, [
            'class' => Devis::class,
            'query_builder' => function (EntityRepository $er) use ($client, $linkedDevisIds) {
                $qb = $er->createQueryBuilder('d')
                    ->where('d.client = :client')
                    ->setParameter('client', $client);

                if (!empty($linkedDevisIds)) {
                    $qb->andWhere('d.id NOT IN (:linked)')
                        ->setParameter('linked', $linkedDevisIds);
                }

                // Règle métier : le devis doit être valide
                $qb->andWhere('d.dateLimite IS NULL OR d.dateLimite >= :now')
                    ->setParameter('now', new \DateTime());

                return $qb;
            },
            'choice_label' => function(Devis $devis) {
                $nom = $devis->getNom() ? $devis->getNom() : 'Devis #' . $devis->getId();
                return $nom . ' (Créé le ' . $devis->getDateDevis()->format('d/m/Y') . ')';
            },
            'mapped' => false,
            'label' => 'Sélectionner un devis supplémentaire',
            'placeholder' => 'Choisissez un devis de ce client...',
            'attr' => ['class' => 'select-searchable']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['commande' => null]);
    }
}
