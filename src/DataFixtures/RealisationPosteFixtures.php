<?php

namespace App\DataFixtures;

use App\Entity\GammeOperation;
use App\Entity\PosteMachine;
use App\Entity\Realisation;
use App\Entity\RealisationPoste;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RealisationPosteFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $realisations = $manager->getRepository(Realisation::class)->findAll();
        $gammeOperations = $manager->getRepository(GammeOperation::class)->findAll();
        $posteMachines = $manager->getRepository(PosteMachine::class)->findAll();

        if (0 === count($realisations) || 0 === count($gammeOperations) || 0 === count($posteMachines)) {
            return;
        }

        foreach ($realisations as $index => $realisation) {
            $operationCount = 2 + ($index % 4); // 2 à 5 pointages par réalisation
            $goPool = $gammeOperations;
            $pmPool = $posteMachines;
            shuffle($goPool);
            shuffle($pmPool);

            $selectedGo = array_slice($goPool, 0, min($operationCount, count($goPool)));
            $selectedPm = array_slice($pmPool, 0, min($operationCount, count($pmPool)));

            foreach ($selectedGo as $idx => $gammeOperation) {
                $posteMachine = $selectedPm[$idx] ?? $selectedPm[0];
                $operation = $gammeOperation->getOperation();

                $pointage = new RealisationPoste();
                $pointage->setRealisation($realisation);
                $pointage->setGammeOperation($gammeOperation);
                $pointage->setPosteMachine($posteMachine);
                $pointage->setTemps($operation ? max(5, (int) round($operation->getTempsPrevu() * (0.8 + (($index + $idx) % 5) * 0.1))) : 15);
                $manager->persist($pointage);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RealisationFixtures::class,
            GammeOperationFixtures::class,
            PosteTravailFixtures::class,
        ];
    }
}

