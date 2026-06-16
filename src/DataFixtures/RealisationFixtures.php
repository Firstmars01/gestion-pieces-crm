<?php

namespace App\DataFixtures;

use App\Entity\Gamme;
use App\Entity\Realisation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RealisationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $gammes = $manager->getRepository(Gamme::class)->findAll();

        if (0 === count($gammes)) {
            return;
        }

        // Crée 20 réalisations sur différentes gammes
        for ($i = 1; $i <= 40; ++$i) {
            $gamme = $gammes[($i - 1) % count($gammes)];

            $realisation = new Realisation();
            $realisation->setGamme($gamme);
            $manager->persist($realisation);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GammeFixtures::class,
        ];
    }
}
