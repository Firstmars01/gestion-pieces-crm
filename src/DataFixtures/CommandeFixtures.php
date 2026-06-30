<?php

namespace App\DataFixtures;

use App\Entity\Commande;
use App\Entity\CommandeLigne;
use App\Entity\Devis;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CommandeFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            DevisFixtures::class, // On s'assure que les devis et pièces existent déjà
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // 1. Récupération des devis existants
        $devisList = $manager->getRepository(Devis::class)->findAll();

        if (empty($devisList)) {
            throw new \LogicException('Aucun devis trouvé. Exécutez DevisFixtures en premier.');
        }

        // 2. Création des commandes
        foreach ($devisList as $devis) {
            // On ne transforme en commande que ~60% des devis
            if ($faker->boolean(40)) {
                continue;
            }

            $commande = new Commande();
            $commande->setClient($devis->getClient());

            // On définit le VRAI parent
            $commande->setDevisParent($devis);

            // On utilise la méthode corrigée pour lier les deux côtés de la relation ManyToMany
            $devis->addCommande($commande);

            // Génération des dates
            $dateCmd = clone $devis->getDateDevis();
            $dateCmd->modify('+' . $faker->numberBetween(1, 10) . ' days');
            $commande->setDateCmd($dateCmd);

            // Numérotation basée sur ta logique du contrôleur
            $numero = 'CMD-' . $dateCmd->format('YmdHis') . '-P' . $devis->getId();
            $commande->setNumero($numero);

            // Statut de la commande
            $isLivree = $faker->boolean(40);
            $commande->setIsLivree($isLivree);

            if ($isLivree) {
                // Si livrée, la dateFacture correspond à la date de livraison effective
                $dateFacture = clone $dateCmd;
                $dateFacture->modify('+' . $faker->numberBetween(2, 15) . ' days');
                if ($dateFacture > new \DateTime()) {
                    $dateFacture = new \DateTime(); // Pas de livraison dans le futur
                }
                $commande->setDateFacture($dateFacture);
            } else {
                // Si en cours, dateFacture sert de "Date de livraison prévue"
                if ($faker->boolean(70)) {
                    $dateLivraisonPrevue = clone $dateCmd;
                    $dateLivraisonPrevue->modify('+' . $faker->numberBetween(5, 30) . ' days');
                    $commande->setDateFacture($dateLivraisonPrevue);
                }
            }

            // 3. Clonage des lignes du Devis vers la Commande
            foreach ($devis->getDevisLignes() as $devisLigne) {
                // Le client commande 80% des lignes proposées sur le devis
                if ($faker->boolean(80)) {
                    $commandeLigne = new CommandeLigne();
                    $commandeLigne->setPiece($devisLigne->getPiece());

                    // Parfois le client commande une quantité inférieure à ce qui était prévu
                    if ($faker->boolean(20)) {
                        $commandeLigne->setQuantite($faker->numberBetween(1, $devisLigne->getQuantite()));
                    } else {
                        $commandeLigne->setQuantite($devisLigne->getQuantite());
                    }

                    $commandeLigne->setPrixUnitaire($devisLigne->getPrix());
                    $commande->addCommandeLigne($commandeLigne);

                    $manager->persist($commandeLigne);
                }
            }

            // 4. TEST DE ROBUSTESSE : Lier un deuxième devis (pour tester notre fameux correctif !)
            if ($faker->boolean(25)) {
                // On cherche un autre devis appartenant au MÊME client
                $autresDevis = array_filter($devisList, function($d) use ($devis) {
                    return $d->getId() !== $devis->getId() && $d->getClient() === $devis->getClient();
                });

                if (!empty($autresDevis)) {
                    $devisLie = $faker->randomElement($autresDevis);

                    // On lie l'autre devis à la commande
                    $devisLie->addCommande($commande);

                    // On pique une pièce de cet autre devis pour l'ajouter à la commande
                    if ($devisLie->getDevisLignes()->count() > 0) {
                        $ligneLiee = $devisLie->getDevisLignes()->first();

                        $commandeLigneLiee = new CommandeLigne();
                        $commandeLigneLiee->setPiece($ligneLiee->getPiece());
                        $commandeLigneLiee->setQuantite($ligneLiee->getQuantite());
                        $commandeLigneLiee->setPrixUnitaire($ligneLiee->getPrix());

                        $commande->addCommandeLigne($commandeLigneLiee);
                        $manager->persist($commandeLigneLiee);
                    }
                }
            }

            // On sauvegarde la commande dans tous les cas
            $manager->persist($commande);
        }

        $manager->flush();
    }
}
