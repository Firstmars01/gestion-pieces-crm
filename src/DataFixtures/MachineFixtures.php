<?php

namespace App\DataFixtures;

use App\Entity\Machine;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MachineFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Crée 40 machines avec des libellés simples
        for ($i = 1; $i <= 40; ++$i) {
            $num = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $machine = new Machine();
            $machine->setLibelle("Machine $num");
            $manager->persist($machine);
        }

        $manager->flush();
    }
}
