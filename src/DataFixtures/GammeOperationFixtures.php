<?php

namespace App\DataFixtures;

use App\Entity\Gamme;
use App\Entity\GammeOperation;
use App\Entity\Operation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GammeOperationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $gammes = $manager->getRepository(Gamme::class)->findAll();
        $operations = $manager->getRepository(Operation::class)->findAll();

        if (0 === count($gammes) || 0 === count($operations)) {
            return;
        }

        foreach ($gammes as $index => $gamme) {
            $operationCount = 3 + ($index % 3); // 3 à 5 opérations par gamme
            $pool = $operations;
            shuffle($pool);
            $selectedOperations = array_slice($pool, 0, min($operationCount, count($pool)));

            $ordre = 1;
            foreach ($selectedOperations as $operation) {
                $gammeOperation = new GammeOperation();
                $gammeOperation->setGamme($gamme);
                $gammeOperation->setOperation($operation);
                $gammeOperation->setOrdre($ordre++);
                $manager->persist($gammeOperation);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GammeFixtures::class,
            OperationFixtures::class,
        ];
    }
}

