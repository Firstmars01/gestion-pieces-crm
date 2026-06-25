<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Devis;
use App\Entity\DevisLigne;
use App\Entity\Piece;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class DevisFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            ClientFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // 1. Récupération des clients existants
        $clients = $manager->getRepository(Client::class)->findAll();
        if (empty($clients)) {
            throw new \LogicException('Aucun client trouvé dans la base.');
        }

        // 2. Création d'un User s'il n'y en a pas
        $users = $manager->getRepository(User::class)->findAll();
        if (empty($users)) {
            $user = new User();
            $user->setEmail('commercial@crm.fr');
            // $user->setPassword('password');
            $manager->persist($user);
            $users[] = $user;
        }

        // 3. Création de 20 Pièces
        $pieces = $manager->getRepository(Piece::class)->findAll();
        if (empty($pieces)) {
            for ($i = 0; $i < 20; ++$i) {
                $piece = new Piece();
                $piece->setReference('REF-'.$faker->unique()->randomNumber(5, true));
                $piece->setLibelle(ucfirst($faker->words(3, true)));

                // NOUVEAUTÉ : 80% des pièces ont un prix, 20% n'en ont pas (ou 0€)
                if ($faker->boolean(80)) {
                    $piece->setPrixVente($faker->randomFloat(2, 5, 500)); // <-- ICI
                } else {
                    $piece->setPrixVente(0); // <-- ICI
                }

                $manager->persist($piece);
                $pieces[] = $piece;
            }
        }

        // On crée un tableau qui ne contient QUE les pièces avec un prix > 0
        $piecesVendables = array_filter($pieces, function ($p) {
            return $p->getPrixVente() > 0;
        });

        if (empty($piecesVendables)) {
            throw new \LogicException('Aucune pièce vendable générée.');
        }

        // 4. Création de 30 Devis
        for ($i = 0; $i < 30; ++$i) {
            $devis = new Devis();
            $devis->setClient($faker->randomElement($clients));
            $devis->setUser($faker->randomElement($users));
            $devis->setNom('Projet '.ucfirst($faker->words(2, true)));

            $dateCreation = $faker->dateTimeBetween('-6 months', '-1 days');
            $devis->setDateDevis($dateCreation);

            // 30% de devis expirés
            $isExpired = $faker->boolean(30);

            if ($isExpired) {
                $dateLimite = clone $dateCreation;
                $dateLimite->modify('+15 days');
                if ($dateLimite > new \DateTime()) {
                    $dateLimite = new \DateTime('-1 day');
                }
            } else {
                $dateLimite = new \DateTime();
                $dateLimite->modify('+'.$faker->numberBetween(1, 6).' months');
            }
            $devis->setDateLimite($dateLimite);

            $nbLignes = $faker->numberBetween(1, 4);
            $premiereLigne = null;

            for ($j = 0; $j < $nbLignes; ++$j) {
                $ligne = new DevisLigne();

                // NOUVEAUTÉ : On pioche UNIQUEMENT dans les pièces vendables
                $pieceChoisie = $faker->randomElement($piecesVendables);
                $ligne->setPiece($pieceChoisie);

                $ligne->setQuantite($faker->numberBetween(1, 50) * 10);

                // NOUVEAUTÉ : On copie le vrai prix de la pièce
                $ligne->setPrix($pieceChoisie->getPrixVente());

                $devis->addDevisLigne($ligne);
                $manager->persist($ligne);

                if (0 === $j) {
                    $premiereLigne = $ligne;
                }
            }

            // CRÉATION DE DOUBLONS PARFAITS
            if (0 === $i % 5 && $premiereLigne) {
                $doublon = new DevisLigne();
                $doublon->setPiece($premiereLigne->getPiece());
                $doublon->setQuantite($premiereLigne->getQuantite());
                $doublon->setPrix($premiereLigne->getPrix());

                $devis->addDevisLigne($doublon);
                $manager->persist($doublon);
            }

            $manager->persist($devis);
        }

        $manager->flush();
    }
}
