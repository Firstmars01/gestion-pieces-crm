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
    // On dit à Symfony : "Exécute DevisFixtures AVANT ce fichier !"
    public function getDependencies(): array
    {
        return [
            DevisFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // On récupère TOUS les devis de la BDD (valides ET expirés)
        $tousLesDevis = $manager->getRepository(Devis::class)->findAll();

        foreach ($tousLesDevis as $devisPrincipal) {

            // Chaque devis aura obligatoirement entre 1 et 2 commandes
            $nbCommandes = $faker->numberBetween(1, 2);

            for ($i = 0; $i < $nbCommandes; $i++) {
                $commande = new Commande();
                $commande->setClient($devisPrincipal->getClient());

                // Numéro unique et date de création (quelques jours après le devis)
                $dateBase = clone $devisPrincipal->getDateDevis();
                $dateBase->modify('+' . $faker->numberBetween(0, 5) . ' days');

                $commande->setNumero('CMD-' . $dateBase->format('YmdHis') . $faker->randomNumber(3, true));
                $commande->setDateCmd($dateBase);

                // On lie le devis principal
                $commande->addDevisList($devisPrincipal);

                // TEST MULTI-DEVIS : 30% de chance de lier un DEUXIÈME devis du même client
                if ($faker->boolean(30)) {
                    foreach ($tousLesDevis as $autreDevis) {
                        if ($autreDevis->getId() !== $devisPrincipal->getId() && $autreDevis->getClient()->getId() === $devisPrincipal->getClient()->getId()) {
                            $commande->addDevisList($autreDevis);
                            break; // On a trouvé un autre devis, on arrête de chercher
                        }
                    }
                }

                // TEST STATUT : 40% de commandes Livrées (Vert) / 60% En cours (Orange/Bleu)
                $estLivree = $faker->boolean(40);

                if ($estLivree) {
                    // Date de livraison dans le passé
                    $dateFacture = clone $commande->getDateCmd();
                    $dateFacture->modify('+' . $faker->numberBetween(2, 10) . ' days');
                    if ($dateFacture > new \DateTime()) {
                        $dateFacture = new \DateTime('-1 day');
                    }
                    $commande->setDateFacture($dateFacture);
                } else {
                    // Date de livraison dans le futur (ou null)
                    if ($faker->boolean(70)) {
                        $dateFacture = new \DateTime();
                        $dateFacture->modify('+' . $faker->numberBetween(5, 30) . ' days');
                        $commande->setDateFacture($dateFacture);
                    }
                }

                // AJOUT DES LIGNES (On pioche dans TOUS les devis liés à cette commande)
                $lignesAjoutees = 0;
                foreach ($commande->getDevisList() as $d) {
                    foreach ($d->getDevisLignes() as $dLigne) {
                        // 80% de chance de commander cette pièce, ou 100% si aucune ligne n'a encore été ajoutée (évite les commandes vides)
                        if ($faker->boolean(80) || $lignesAjoutees === 0) {
                            $cLigne = new CommandeLigne();
                            $cLigne->setPiece($dLigne->getPiece());
                            $cLigne->setQuantite($dLigne->getQuantite()); // Copie exacte de la quantité
                            $cLigne->setPrixUnitaire($dLigne->getPrix());

                            $commande->addCommandeLigne($cLigne);
                            $manager->persist($cLigne);
                            $lignesAjoutees++;
                        }
                    }
                }

                $manager->persist($commande);
            }
        }

        $manager->flush();
    }
}
