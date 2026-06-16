<?php

namespace App\DataFixtures;

use App\Entity\Operation;
use App\Entity\PosteMachine;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OperationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $posteMachines = $manager->getRepository(PosteMachine::class)->findAll();

        if (count($posteMachines) === 0) {
            // Pas de PosteMachine présentes : on ne crée rien
            return;
        }

        // Libellés d'opérations types
        $operationTypes = [
            'Usinage',
            'Découpe',
            'Soudage',
            'Peinture',
            'Assemblage',
            'Polissage',
            'Perçage',
            'Gravure',
            'Trempage',
            'Contrôle qualité',
        ];

        // Crée 30 opérations distribuées sur les PosteMachines disponibles
        for ($i = 1; $i <= 30; $i++) {
            $num = str_pad((string)$i, 2, '0', STR_PAD_LEFT);

            // Choisir une PosteMachine disponible
            $posteMachine = $posteMachines[($i - 1) % count($posteMachines)];

            // Choisir un type d'opération
            $opType = $operationTypes[($i - 1) % count($operationTypes)];

            $operation = new Operation();
            $operation->setLibelle("$opType $num");
            $operation->setTempsPrevu(15 + (($i * 7) % 100)); // Temps entre 15 et 115 minutes
            $operation->setPosteMachine($posteMachine);

            $manager->persist($operation);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PosteTravailFixtures::class,
        ];
    }
}

