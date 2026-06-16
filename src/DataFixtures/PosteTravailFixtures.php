<?php

namespace App\DataFixtures;

use App\Entity\Machine;
use App\Entity\PosteMachine;
use App\Entity\PosteTravail;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PosteTravailFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $machines = $manager->getRepository(Machine::class)->findAll();

        if (0 === count($machines)) {
            // Pas de machines présentes : on ne crée que les postes vides
            for ($i = 1; $i <= 25; ++$i) {
                $num = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                $poste = new PosteTravail();
                $poste->setLibelle("Poste $num");
                $manager->persist($poste);
            }
            $manager->flush();

            return;
        }

        // Crée 25 postes et les relie à plusieurs machines (1 à 4 machines par poste)
        for ($i = 1; $i <= 25; ++$i) {
            $num = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $poste = new PosteTravail();
            $poste->setLibelle("Poste $num");
            $manager->persist($poste);

            // Choisir entre 1 et 4 machines différentes pour ce poste
            $maxMachines = min(4, count($machines));
            $nb = 1 + (($i - 1) % $maxMachines); // distribution déterministe 1..maxMachines

            // copie et mélange pour choisir des machines uniques
            $pool = $machines;
            shuffle($pool);
            $selected = array_slice($pool, 0, $nb);

            foreach ($selected as $machine) {
                $pm = new PosteMachine();
                $pm->setPoste($poste);
                $pm->setMachine($machine);
                $manager->persist($pm);
                // lien inverse géré par addPosteMachine si besoin, mais on persiste explicitement
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            MachineFixtures::class,
        ];
    }
}
