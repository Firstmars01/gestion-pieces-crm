<?php

namespace App\DataFixtures;

use App\Entity\Gamme;
use App\Entity\Realisation;
use App\Entity\RealisationPoste;
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

        // Crée 40 réalisations sur différentes gammes
        for ($i = 1; $i <= 40; ++$i) {
            $gamme = $gammes[($i - 1) % count($gammes)];

            $realisation = new Realisation();
            $realisation->setGamme($gamme);

            $realisation->setGammeLibelleArchive($gamme->getLibelle());
            $realisation->setPieceReferenceArchive($gamme->getPiece() ? $gamme->getPiece()->getReference() : 'Non définie');

            $manager->persist($realisation);

            foreach ($gamme->getGammeOperations() as $gammeOp) {
                $etapeReelle = new RealisationPoste();
                $etapeReelle->setRealisation($realisation);
                $etapeReelle->setOperation($gammeOp->getOperation());
                $etapeReelle->setOrdre($gammeOp->getOrdre());

                if ($gammeOp->getOperation()) {
                    $etapeReelle->setTempsPrevu($gammeOp->getOperation()->getTempsPrevu());
                    $etapeReelle->setOperationLibelleArchive($gammeOp->getOperation()->getLibelle());
                }

                $manager->persist($etapeReelle);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GammeOperationFixtures::class,
        ];
    }
}
