<?php

namespace App\DataFixtures;

use App\Entity\Gamme;
use App\Entity\Piece;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GammeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $admin = $manager->getRepository(User::class)->findOneBy(['email' => 'admin@crm.com']);
        $atelier = $manager->getRepository(User::class)->findOneBy(['email' => 'atelier@crm.com']);

        // On récupère TOUTES les pièces générées par ton super fichier PieceFixtures
        $pieces = $manager->getRepository(Piece::class)->findAll();

        $compteur = 0;

        foreach ($pieces as $piece) {
            // RÈGLE MÉTIER : On ne crée une gamme que pour les pièces fabricables
            if (in_array($piece->getType(), ['LIVRABLE', 'INTERMEDIAIRE'])) {

                $gamme = new Gamme();
                // On crée un nom de gamme basé sur le nom de la pièce (ex: "Gamme Table Industrielle")
                $gamme->setLibelle('Gamme ' . $piece->getLibelle());
                $gamme->setPiece($piece);

                // On alterne le propriétaire entre admin et atelier
                $gamme->setUser($compteur % 2 === 0 ? $admin : $atelier);

                $manager->persist($gamme);
                $compteur++;
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PieceFixtures::class, // OBLIGATOIRE : Dit à Symfony de charger PieceFixtures AVANT GammeFixtures
        ];
    }
}
