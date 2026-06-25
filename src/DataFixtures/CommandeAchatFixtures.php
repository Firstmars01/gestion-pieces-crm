<?php

namespace App\DataFixtures;

use App\Entity\CommandeAchat;
use App\Entity\CmdAchatLigne;
use App\Entity\Fournisseur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CommandeAchatFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            PieceFixtures::class, // Doit s'exécuter APRES les pièces (qui s'exécutent après les fournisseurs)
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $fournisseurs = $manager->getRepository(Fournisseur::class)->findAll();

        foreach ($fournisseurs as $fournisseur) {
            // Créer entre 2 et 5 commandes par fournisseur
            $nbCommandes = $faker->numberBetween(2, 5);

            for ($i = 0; $i < $nbCommandes; $i++) {
                $commande = new CommandeAchat();
                $commande->setFournisseur($fournisseur);

                // Date de commande dans les 6 derniers mois
                $dateCmd = $faker->dateTimeBetween('-6 months', '-1 week');
                $commande->setDateCommande($dateCmd);

                // Date prévue : entre 5 et 15 jours après la commande
                $datePrevue = clone $dateCmd;
                $datePrevue->modify('+' . $faker->numberBetween(5, 15) . ' days');
                $commande->setDatePrevue($datePrevue);

                // 70% de chance que la commande soit déjà livrée (dateReelle)
                if ($faker->boolean(70)) {
                    $dateReelle = clone $datePrevue;
                    // Livraison avec un peu d'avance ou du retard
                    $dateReelle->modify($faker->numberBetween(-2, 5) . ' days');

                    // Sécurité : on ne livre pas dans le futur
                    if ($dateReelle > new \DateTime()) {
                        $dateReelle = new \DateTime();
                    }
                    $commande->setDateReelle($dateReelle);
                }

                // On récupère toutes les pièces de ce fournisseur
                $piecesDuFournisseur = $fournisseur->getPieces();

                // On commande entre 1 et 3 pièces différentes chez ce fournisseur
                if (count($piecesDuFournisseur) > 0) {
                    $nbLignes = $faker->numberBetween(1, min(3, count($piecesDuFournisseur)));
                    $piecesChoisies = $faker->randomElements($piecesDuFournisseur->toArray(), $nbLignes);

                    foreach ($piecesChoisies as $piece) {
                        $ligne = new CmdAchatLigne();
                        $ligne->setCommandeAchat($commande);
                        $ligne->setPiece($piece);
                        $ligne->setQuantite($faker->numberBetween(50, 500)); // Grosses quantités car c'est de l'usine

                        // RÈGLE MÉTIER : Le prix d'achat fluctue ! (+ ou - 10% par rapport au prix catalogue)
                        $variation = $faker->randomFloat(2, 0.9, 1.1);
                        $prixAchat = $piece->getPrixCatalogue() * $variation;
                        $ligne->setPrixAchat(round($prixAchat, 2));

                        $manager->persist($ligne);
                    }
                    $manager->persist($commande);
                }
            }
        }
        $manager->flush();
    }
}
