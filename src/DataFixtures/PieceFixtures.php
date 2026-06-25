<?php

namespace App\DataFixtures;

use App\Entity\Piece;
use App\Entity\PieceComposition;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Fournisseur;

class PieceFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        // On dit à Symfony : "Crée les fournisseurs d'abord !"
        return [
            FournisseurFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $fournBois = $this->getReference('fournisseur_0', Fournisseur::class);
        $fournMetal = $this->getReference('fournisseur_1', Fournisseur::class);
        $fournAccess = $this->getReference('fournisseur_2', Fournisseur::class);
        $fournChimie = $this->getReference('fournisseur_3', Fournisseur::class);

        // ====================================================================
        // 1. MATIÈRES PREMIÈRES (Bois, Acier, Peinture...)
        // ====================================================================

        $boisMdf = new Piece();
        $boisMdf->setReference('MAT-MDF-15MM');
        $boisMdf->setLibelle('Panneau MDF 15mm 274x152cm (Indoor)');
        $boisMdf->setType('MATIERE_PREMIERE');
        $boisMdf->setQuantiteStock(150);
        $boisMdf->setPrixCatalogue(45.00);
        $boisMdf->setFournisseur($fournBois);
        $manager->persist($boisMdf);

        $resineMelamine = new Piece();
        $resineMelamine->setReference('MAT-RESINE-5MM');
        $resineMelamine->setLibelle('Plaque Résine Stratifiée 5mm (Outdoor)');
        $resineMelamine->setType('MATIERE_PREMIERE');
        $resineMelamine->setQuantiteStock(100);
        $resineMelamine->setPrixCatalogue(85.00);
        $resineMelamine->setFournisseur($fournChimie);
        $manager->persist($resineMelamine);

        $tubeAcier30 = new Piece();
        $tubeAcier30->setReference('MAT-TUBE-ACIER-30');
        $tubeAcier30->setLibelle('Tube Acier carré 30x30mm (Barre 6m)');
        $tubeAcier30->setType('MATIERE_PREMIERE');
        $tubeAcier30->setQuantiteStock(300);
        $tubeAcier30->setPrixCatalogue(12.50);
        $tubeAcier30->setFournisseur($fournMetal);
        $manager->persist($tubeAcier30);

        $tubeAcier40 = new Piece();
        $tubeAcier40->setReference('MAT-TUBE-ACIER-40');
        $tubeAcier40->setLibelle('Tube Acier carré 40x40mm renfort (Barre 6m)');
        $tubeAcier40->setType('MATIERE_PREMIERE');
        $tubeAcier40->setQuantiteStock(200);
        $tubeAcier40->setPrixCatalogue(18.00);
        $tubeAcier40->setFournisseur($fournMetal);
        $manager->persist($tubeAcier40);

        $peintureBleue = new Piece();
        $peintureBleue->setReference('MAT-PEINT-BLEU');
        $peintureBleue->setLibelle('Peinture antireflet Bleue (Pot 5L)');
        $peintureBleue->setType('MATIERE_PREMIERE');
        $peintureBleue->setQuantiteStock(50);
        $peintureBleue->setPrixCatalogue(65.00);
        $manager->persist($peintureBleue);

        $peintureBlanche = new Piece();
        $peintureBlanche->setReference('MAT-PEINT-BLANC');
        $peintureBlanche->setLibelle('Peinture de traçage Blanche (Pot 2L)');
        $peintureBlanche->setType('MATIERE_PREMIERE');
        $peintureBlanche->setQuantiteStock(30);
        $peintureBlanche->setPrixCatalogue(35.00);
        $peintureBlanche->setFournisseur($fournAccess);

        $manager->persist($peintureBlanche);

        $profilAlu = new Piece();
        $profilAlu->setReference('MAT-PROFIL-ALU');
        $profilAlu->setLibelle('Profilé Aluminium de contour (Barre 3m)');
        $profilAlu->setType('MATIERE_PREMIERE');
        $profilAlu->setQuantiteStock(400);
        $profilAlu->setPrixCatalogue(9.50);
        $manager->persist($profilAlu);

        // ====================================================================
        // 2. PIÈCES ACHETÉES (Visserie, Roulettes, Accessoires bruts...)
        // ====================================================================

        $visM6 = new Piece();
        $visM6->setReference('ACH-VIS-M6x40');
        $visM6->setLibelle('Vis M6x40 Acier Inox');
        $visM6->setType('ACHETEE');
        $visM6->setQuantiteStock(10000);
        $visM6->setPrixCatalogue(0.10);
        $manager->persist($visM6);

        $ecrouM6 = new Piece();
        $ecrouM6->setReference('ACH-ECROU-M6');
        $ecrouM6->setLibelle('Écrou frein M6 Inox');
        $ecrouM6->setType('ACHETEE');
        $ecrouM6->setQuantiteStock(10000);
        $ecrouM6->setPrixCatalogue(0.08);
        $manager->persist($ecrouM6);

        $rouletteFrein = new Piece();
        $rouletteFrein->setReference('ACH-ROUL-FREIN-150');
        $rouletteFrein->setLibelle('Roulette double Ø150mm avec frein');
        $rouletteFrein->setType('ACHETEE');
        $rouletteFrein->setQuantiteStock(500);
        $rouletteFrein->setPrixCatalogue(12.50);
        $manager->persist($rouletteFrein);

        $rouletteSimple = new Piece();
        $rouletteSimple->setReference('ACH-ROUL-SIMP-150');
        $rouletteSimple->setLibelle('Roulette double Ø150mm sans frein');
        $rouletteSimple->setType('ACHETEE');
        $rouletteSimple->setQuantiteStock(500);
        $rouletteSimple->setPrixCatalogue(10.00);
        $manager->persist($rouletteSimple);

        $filetStandard = new Piece();
        $filetStandard->setReference('ACH-FILET-STD');
        $filetStandard->setLibelle('Filet Nylon standard noir');
        $filetStandard->setType('ACHETEE');
        $filetStandard->setQuantiteStock(200);
        $filetStandard->setPrixCatalogue(4.50);
        $manager->persist($filetStandard);

        $filetPro = new Piece();
        $filetPro->setReference('ACH-FILET-PRO');
        $filetPro->setLibelle('Filet Coton mailles serrées compétition');
        $filetPro->setType('ACHETEE');
        $filetPro->setQuantiteStock(100);
        $filetPro->setPrixCatalogue(14.00);
        $manager->persist($filetPro);

        $poteauFilet = new Piece();
        $poteauFilet->setReference('ACH-POTEAU-FIL');
        $poteauFilet->setLibelle('Poteau de fixation filet à vis');
        $poteauFilet->setType('ACHETEE');
        $poteauFilet->setQuantiteStock(400);
        $poteauFilet->setPrixCatalogue(6.50);
        $manager->persist($poteauFilet);

        // Accessoires bruts (Ils seront packagés pour être vendables)
        $raquetteBrute = new Piece();
        $raquetteBrute->setReference('ACH-RAQ-LOISIR');
        $raquetteBrute->setLibelle('Raquette Loisir 2 étoiles (Brute)');
        $raquetteBrute->setType('ACHETEE');
        $raquetteBrute->setQuantiteStock(800);
        $raquetteBrute->setPrixCatalogue(4.20);
        $manager->persist($raquetteBrute);

        $balleBrute = new Piece();
        $balleBrute->setReference('ACH-BALLE-ABS');
        $balleBrute->setLibelle('Balle plastique ABS 40+ (Unité)');
        $balleBrute->setType('ACHETEE');
        $balleBrute->setQuantiteStock(5000);
        $balleBrute->setPrixCatalogue(0.30);
        $manager->persist($balleBrute);

        $housseBrute = new Piece();
        $housseBrute->setReference('ACH-HOUSSE-PVC');
        $housseBrute->setLibelle('Housse de protection PVC Grise');
        $housseBrute->setType('ACHETEE');
        $housseBrute->setQuantiteStock(150);
        $housseBrute->setPrixCatalogue(12.00);
        $manager->persist($housseBrute);

        // ====================================================================
        // 3. PIÈCES INTERMÉDIAIRES (Sous-ensembles fabriqués en usine)
        // ====================================================================

        $demiPlateauIndoor = new Piece();
        $demiPlateauIndoor->setReference('INT-PLAT-IND-BLEU');
        $demiPlateauIndoor->setLibelle('Demi-plateau Indoor Bleu tracé');
        $demiPlateauIndoor->setType('INTERMEDIAIRE');
        $demiPlateauIndoor->setQuantiteStock(40);
        $manager->persist($demiPlateauIndoor);

        $demiPlateauOutdoor = new Piece();
        $demiPlateauOutdoor->setReference('INT-PLAT-OUT-BLEU');
        $demiPlateauOutdoor->setLibelle('Demi-plateau Outdoor Résine cerclé Alu');
        $demiPlateauOutdoor->setType('INTERMEDIAIRE');
        $demiPlateauOutdoor->setQuantiteStock(30);
        $manager->persist($demiPlateauOutdoor);

        $piedIndoor = new Piece();
        $piedIndoor->setReference('INT-PIED-IND');
        $piedIndoor->setLibelle('Pied droit acier 30mm assemblé');
        $piedIndoor->setType('INTERMEDIAIRE');
        $piedIndoor->setQuantiteStock(120);
        $manager->persist($piedIndoor);

        $chariotCentral = new Piece();
        $chariotCentral->setReference('INT-CHARIOT-MOB');
        $chariotCentral->setLibelle('Chariot central pliant avec roulettes');
        $chariotCentral->setType('INTERMEDIAIRE');
        $chariotCentral->setQuantiteStock(50);
        $manager->persist($chariotCentral);

        // ====================================================================
        // 4. PRODUITS LIVRABLES (Ce qui est vendu au client final)
        // ====================================================================

        $tableIndoor = new Piece();
        $tableIndoor->setReference('LIV-TABLE-IND-LOISIR');
        $tableIndoor->setLibelle('Table de Ping-Pong Indoor Loisir');
        $tableIndoor->setType('LIVRABLE');
        $tableIndoor->setQuantiteStock(15);
        $tableIndoor->setPrixVente(299.00);
        $manager->persist($tableIndoor);

        $tableOutdoor = new Piece();
        $tableOutdoor->setReference('LIV-TABLE-OUT-PRO');
        $tableOutdoor->setLibelle('Table de Ping-Pong Outdoor Pro Pliable');
        $tableOutdoor->setType('LIVRABLE');
        $tableOutdoor->setQuantiteStock(10);
        $tableOutdoor->setPrixVente(649.00);
        $manager->persist($tableOutdoor);

        $packRaquettes = new Piece();
        $packRaquettes->setReference('LIV-PACK-DUO');
        $packRaquettes->setLibelle('Set Duo : 2 Raquettes + 3 Balles');
        $packRaquettes->setType('LIVRABLE');
        $packRaquettes->setQuantiteStock(85);
        $packRaquettes->setPrixVente(19.90);
        $manager->persist($packRaquettes);

        $filetRechange = new Piece();
        $filetRechange->setReference('LIV-KIT-FILET-PRO');
        $filetRechange->setLibelle('Kit Filet Compétition + Poteaux');
        $filetRechange->setType('LIVRABLE');
        $filetRechange->setQuantiteStock(45);
        $filetRechange->setPrixVente(34.50);
        $manager->persist($filetRechange);

        $housseLivrable = new Piece();
        $housseLivrable->setReference('LIV-HOUSSE-PROTECT');
        $housseLivrable->setLibelle('Housse de protection Premium');
        $housseLivrable->setType('LIVRABLE');
        $housseLivrable->setQuantiteStock(60);
        $housseLivrable->setPrixVente(39.90);
        $manager->persist($housseLivrable);

        $manager->flush(); // Sauvegarde pour générer les IDs avant les compositions

        // ====================================================================
        // 5. COMPOSITIONS (Nomenclatures de fabrication)
        // ====================================================================

        // --- Fabrication d'un Demi-Plateau Indoor ---
        // 1 panneau bois + Peinture Bleue + Lignes blanches
        $comp1 = new PieceComposition();
        $comp1->setPieceParent($demiPlateauIndoor);
        $comp1->setPieceEnfant($boisMdf);
        $comp1->setQuantite(1);
        $manager->persist($comp1);

        $comp2 = new PieceComposition();
        $comp2->setPieceParent($demiPlateauIndoor);
        $comp2->setPieceEnfant($peintureBleue);
        $comp2->setQuantite(1); // On compte 1 unité de peinture = la dose pour 1 plateau
        $manager->persist($comp2);

        $comp3 = new PieceComposition();
        $comp3->setPieceParent($demiPlateauIndoor);
        $comp3->setPieceEnfant($peintureBlanche);
        $comp3->setQuantite(1);
        $manager->persist($comp3);

        // --- Fabrication d'un Demi-Plateau Outdoor ---
        // 1 plaque Résine + Profil alu autour + Peinture
        $comp4 = new PieceComposition();
        $comp4->setPieceParent($demiPlateauOutdoor);
        $comp4->setPieceEnfant($resineMelamine);
        $comp4->setQuantite(1);
        $manager->persist($comp4);

        $comp5 = new PieceComposition();
        $comp5->setPieceParent($demiPlateauOutdoor);
        $comp5->setPieceEnfant($profilAlu);
        $comp5->setQuantite(2); // 2 profilés pour faire le tour
        $manager->persist($comp5);

        $comp6 = new PieceComposition();
        $comp6->setPieceParent($demiPlateauOutdoor);
        $comp6->setPieceEnfant($peintureBleue);
        $comp6->setQuantite(1);
        $manager->persist($comp6);

        // --- Fabrication d'un Pied Indoor ---
        $comp7 = new PieceComposition();
        $comp7->setPieceParent($piedIndoor);
        $comp7->setPieceEnfant($tubeAcier30);
        $comp7->setQuantite(1);
        $manager->persist($comp7);

        // --- Fabrication d'un Chariot Central (Pour tables pliables) ---
        $comp8 = new PieceComposition();
        $comp8->setPieceParent($chariotCentral);
        $comp8->setPieceEnfant($tubeAcier40);
        $comp8->setQuantite(2); // Structure renforcée
        $manager->persist($comp8);

        $comp9 = new PieceComposition();
        $comp9->setPieceParent($chariotCentral);
        $comp9->setPieceEnfant($rouletteFrein);
        $comp9->setQuantite(2);
        $manager->persist($comp9);

        $comp10 = new PieceComposition();
        $comp10->setPieceParent($chariotCentral);
        $comp10->setPieceEnfant($rouletteSimple);
        $comp10->setQuantite(2);
        $manager->persist($comp10);

        $comp11 = new PieceComposition();
        $comp11->setPieceParent($chariotCentral);
        $comp11->setPieceEnfant($visM6);
        $comp11->setQuantite(16); // 4 vis par roulette
        $manager->persist($comp11);

        // --- ASSEMBLAGE FINAL : Table Indoor ---
        // 2 demi-plateaux + 4 pieds fixes + 1 kit filet
        $comp12 = new PieceComposition();
        $comp12->setPieceParent($tableIndoor);
        $comp12->setPieceEnfant($demiPlateauIndoor);
        $comp12->setQuantite(2);
        $manager->persist($comp12);

        $comp13 = new PieceComposition();
        $comp13->setPieceParent($tableIndoor);
        $comp13->setPieceEnfant($piedIndoor);
        $comp13->setQuantite(4);
        $manager->persist($comp13);

        $comp14 = new PieceComposition();
        $comp14->setPieceParent($tableIndoor);
        $comp14->setPieceEnfant($filetStandard);
        $comp14->setQuantite(1);
        $manager->persist($comp14);

        $comp15 = new PieceComposition();
        $comp15->setPieceParent($tableIndoor);
        $comp15->setPieceEnfant($poteauFilet);
        $comp15->setQuantite(2);
        $manager->persist($comp15);

        // --- ASSEMBLAGE FINAL : Table Outdoor Pliable ---
        // 2 demi-plateaux outdoor + 1 chariot central + filet pro
        $comp16 = new PieceComposition();
        $comp16->setPieceParent($tableOutdoor);
        $comp16->setPieceEnfant($demiPlateauOutdoor);
        $comp16->setQuantite(2);
        $manager->persist($comp16);

        $comp17 = new PieceComposition();
        $comp17->setPieceParent($tableOutdoor);
        $comp17->setPieceEnfant($chariotCentral);
        $comp17->setQuantite(1);
        $manager->persist($comp17);

        $comp18 = new PieceComposition();
        $comp18->setPieceParent($tableOutdoor);
        $comp18->setPieceEnfant($filetPro);
        $comp18->setQuantite(1);
        $manager->persist($comp18);

        $comp19 = new PieceComposition();
        $comp19->setPieceParent($tableOutdoor);
        $comp19->setPieceEnfant($poteauFilet);
        $comp19->setQuantite(2);
        $manager->persist($comp19);

        // --- PACKAGING ACCESSOIRES (Pour la vente) ---

        // Pack Raquettes (2 raquettes brutes + 3 balles)
        $comp20 = new PieceComposition();
        $comp20->setPieceParent($packRaquettes);
        $comp20->setPieceEnfant($raquetteBrute);
        $comp20->setQuantite(2);
        $manager->persist($comp20);

        $comp21 = new PieceComposition();
        $comp21->setPieceParent($packRaquettes);
        $comp21->setPieceEnfant($balleBrute);
        $comp21->setQuantite(3);
        $manager->persist($comp21);

        // Kit Filet Pro (1 Filet + 2 poteaux)
        $comp22 = new PieceComposition();
        $comp22->setPieceParent($filetRechange);
        $comp22->setPieceEnfant($filetPro);
        $comp22->setQuantite(1);
        $manager->persist($comp22);

        $comp23 = new PieceComposition();
        $comp23->setPieceParent($filetRechange);
        $comp23->setPieceEnfant($poteauFilet);
        $comp23->setQuantite(2);
        $manager->persist($comp23);

        // Housse vendable (1 housse brute conditionnée)
        $comp24 = new PieceComposition();
        $comp24->setPieceParent($housseLivrable);
        $comp24->setPieceEnfant($housseBrute);
        $comp24->setQuantite(1);
        $manager->persist($comp24);

        $manager->flush();
    }
}
