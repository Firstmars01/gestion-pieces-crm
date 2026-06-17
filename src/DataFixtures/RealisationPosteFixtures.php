<?php

namespace App\DataFixtures;

use App\Entity\PosteMachine;
use App\Entity\RealisationPoste;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RealisationPosteFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // On récupère les étapes qui viennent d'être générées par RealisationFixtures
        $etapes = $manager->getRepository(RealisationPoste::class)->findAll();
        $posteMachines = $manager->getRepository(PosteMachine::class)->findAll();

        if (0 === count($etapes) || 0 === count($posteMachines)) {
            return;
        }

        foreach ($etapes as $etape) {
            // On simule que 60% des étapes ont déjà été "pointées" par les ouvriers
            if (rand(1, 100) <= 60) {
                $operation = $etape->getOperation();

                // On prend la machine par défaut de l'opération, sinon une au hasard
                $posteMachine = ($operation && $operation->getPosteMachine())
                    ? $operation->getPosteMachine()
                    : $posteMachines[array_rand($posteMachines)];

                $etape->setPosteMachine($posteMachine);

                // On simule un temps réel (temps prévu avec +/- 20% de variation)
                $tempsPrevu = $etape->getTempsPrevu() ?? 15;
                $variation = rand(-20, 20) / 100;
                $etape->setTemps(max(1, (int) round($tempsPrevu * (1 + $variation))));

                $manager->persist($etape);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RealisationFixtures::class,
            PosteTravailFixtures::class,
        ];
    }
}
