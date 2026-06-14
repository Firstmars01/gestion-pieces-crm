<?php

namespace App\DataFixtures;

use App\Entity\Piece;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PieceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. Une Matière Première (Prix catalogue)
        $acier = new Piece();
        $acier->setReference('MAT-ACIER-001');
        $acier->setLibelle('Plaque d\'acier 5mm 1x2m');
        $acier->setType('MATIERE_PREMIERE');
        $acier->setQuantiteStock(45);
        $acier->setPrixCatalogue(85.50);
        $manager->persist($acier);

        // 2. Une Pièce Achetée (Prix catalogue)
        $vis = new Piece();
        $vis->setReference('ACH-VIS-M8');
        $vis->setLibelle('Vis M8x20 Tête Hexagonale');
        $vis->setType('ACHETEE');
        $vis->setQuantiteStock(2500);
        $vis->setPrixCatalogue(0.12);
        $manager->persist($vis);

        // 3. Un Produit Livrable (Prix de vente)
        $table = new Piece();
        $table->setReference('LIV-TAB-IND');
        $table->setLibelle('Table Industrielle Pieds Métal');
        $table->setType('LIVRABLE');
        $table->setQuantiteStock(12);
        $table->setPrixVente(450.00);
        $manager->persist($table);

        // 4. Une Pièce Intermédiaire (Sous-ensemble)
        $piedMetal = new Piece();
        $piedMetal->setReference('INT-PIED-01');
        $piedMetal->setLibelle('Pied métallique soudé noir');
        $piedMetal->setType('INTERMEDIAIRE');
        $piedMetal->setQuantiteStock(24);
        // Les pièces intermédiaires n'ont généralement ni prix de vente ni prix catalogue direct
        $manager->persist($piedMetal);

        // On envoie en base
        $manager->flush();
    }
}
