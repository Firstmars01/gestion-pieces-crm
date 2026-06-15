<?php

namespace App\DataFixtures;

use App\Entity\Piece;
use App\Entity\PieceComposition;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PieceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ===== MATIÈRES PREMIÈRES =====
        $acier = new Piece();
        $acier->setReference('MAT-ACIER-001');
        $acier->setLibelle('Plaque d\'acier 5mm 1x2m');
        $acier->setType('MATIERE_PREMIERE');
        $acier->setQuantiteStock(100);
        $acier->setPrixCatalogue(85.50);
        $manager->persist($acier);

        $aluminiumPlate = new Piece();
        $aluminiumPlate->setReference('MAT-ALU-002');
        $aluminiumPlate->setLibelle('Plaque d\'aluminium 3mm 1x2m');
        $aluminiumPlate->setType('MATIERE_PREMIERE');
        $aluminiumPlate->setQuantiteStock(75);
        $aluminiumPlate->setPrixCatalogue(120.00);
        $manager->persist($aluminiumPlate);

        $boisPine = new Piece();
        $boisPine->setReference('MAT-BOIS-003');
        $boisPine->setLibelle('Bois de pin 40x60mm');
        $boisPine->setType('MATIERE_PREMIERE');
        $boisPine->setQuantiteStock(200);
        $boisPine->setPrixCatalogue(15.75);
        $manager->persist($boisPine);

        $cuivre = new Piece();
        $cuivre->setReference('MAT-CUIVRE-004');
        $cuivre->setLibelle('Tube cuivre Ø12mm');
        $cuivre->setType('MATIERE_PREMIERE');
        $cuivre->setQuantiteStock(50);
        $cuivre->setPrixCatalogue(45.30);
        $manager->persist($cuivre);

        // ===== PIÈCES ACHETÉES =====
        $vis = new Piece();
        $vis->setReference('ACH-VIS-M8');
        $vis->setLibelle('Vis M8x20 Tête Hexagonale');
        $vis->setType('ACHETEE');
        $vis->setQuantiteStock(2500);
        $vis->setPrixCatalogue(0.12);
        $manager->persist($vis);

        $visPetite = new Piece();
        $visPetite->setReference('ACH-VIS-M4');
        $visPetite->setLibelle('Vis M4x16 Tête Hexagonale');
        $visPetite->setType('ACHETEE');
        $visPetite->setQuantiteStock(5000);
        $visPetite->setPrixCatalogue(0.05);
        $manager->persist($visPetite);

        $ecrou = new Piece();
        $ecrou->setReference('ACH-ECROU-M8');
        $ecrou->setLibelle('Écrou M8 Hexagonal');
        $ecrou->setType('ACHETEE');
        $ecrou->setQuantiteStock(2000);
        $ecrou->setPrixCatalogue(0.08);
        $manager->persist($ecrou);

        $roulement = new Piece();
        $roulement->setReference('ACH-ROUL-6203');
        $roulement->setLibelle('Roulement 6203-2RS');
        $roulement->setType('ACHETEE');
        $roulement->setQuantiteStock(150);
        $roulement->setPrixCatalogue(8.50);
        $manager->persist($roulement);

        $joint = new Piece();
        $joint->setReference('ACH-JOINT-16');
        $joint->setLibelle('Joint torique Ø16 mm');
        $joint->setType('ACHETEE');
        $joint->setQuantiteStock(500);
        $joint->setPrixCatalogue(0.65);
        $manager->persist($joint);

        // ===== PIÈCES INTERMÉDIAIRES (Sous-ensembles) =====
        $piedMetal = new Piece();
        $piedMetal->setReference('INT-PIED-01');
        $piedMetal->setLibelle('Pied métallique soudé noir');
        $piedMetal->setType('INTERMEDIAIRE');
        $piedMetal->setQuantiteStock(48);
        $manager->persist($piedMetal);

        $plateau = new Piece();
        $plateau->setReference('INT-PLATEAU-02');
        $plateau->setLibelle('Plateau de table 80x120cm');
        $plateau->setType('INTERMEDIAIRE');
        $plateau->setQuantiteStock(24);
        $manager->persist($plateau);

        $assemblageAntivol = new Piece();
        $assemblageAntivol->setReference('INT-ANTIVOL-03');
        $assemblageAntivol->setLibelle('Assemblage anti-vol pour meuble');
        $assemblageAntivol->setType('INTERMEDIAIRE');
        $assemblageAntivol->setQuantiteStock(100);
        $manager->persist($assemblageAntivol);

        $roueBroyeur = new Piece();
        $roueBroyeur->setReference('INT-ROUE-04');
        $roueBroyeur->setLibelle('Roue complète pour broyeur');
        $roueBroyeur->setType('INTERMEDIAIRE');
        $roueBroyeur->setQuantiteStock(12);
        $manager->persist($roueBroyeur);

        // ===== PRODUITS LIVRABLES =====
        $table = new Piece();
        $table->setReference('LIV-TAB-IND-001');
        $table->setLibelle('Table Industrielle Pieds Métal');
        $table->setType('LIVRABLE');
        $table->setQuantiteStock(12);
        $table->setPrixVente(450.00);
        $manager->persist($table);

        $bureau = new Piece();
        $bureau->setReference('LIV-BUREAU-002');
        $bureau->setLibelle('Bureau ergonomique 120x60');
        $bureau->setType('LIVRABLE');
        $bureau->setQuantiteStock(8);
        $bureau->setPrixVente(320.00);
        $manager->persist($bureau);

        $etagere = new Piece();
        $etagere->setReference('LIV-ETAGERE-003');
        $etagere->setLibelle('Étagère murale 3 niveaux');
        $etagere->setType('LIVRABLE');
        $etagere->setQuantiteStock(20);
        $etagere->setPrixVente(145.00);
        $manager->persist($etagere);

        $chaise = new Piece();
        $chaise->setReference('LIV-CHAISE-004');
        $chaise->setLibelle('Chaise de bureau pivotante');
        $chaise->setType('LIVRABLE');
        $chaise->setQuantiteStock(35);
        $chaise->setPrixVente(185.00);
        $manager->persist($chaise);

        $armoire = new Piece();
        $armoire->setReference('LIV-ARMOIRE-005');
        $armoire->setLibelle('Armoire métallique 2 portes');
        $armoire->setType('LIVRABLE');
        $armoire->setQuantiteStock(5);
        $armoire->setPrixVente(520.00);
        $manager->persist($armoire);

        $broyeur = new Piece();
        $broyeur->setReference('LIV-BROYEUR-006');
        $broyeur->setLibelle('Broyeur industriel 3kW');
        $broyeur->setType('LIVRABLE');
        $broyeur->setQuantiteStock(3);
        $broyeur->setPrixVente(1200.00);
        $manager->persist($broyeur);

        // ===== 21 PIÈCES SUPPLÉMENTAIRES POUR TOTAL DE 40 =====
        // Matières premières additionnelles
        $acierInox = new Piece();
        $acierInox->setReference('MAT-ACIER-INOX-005');
        $acierInox->setLibelle('Acier inoxydable 304 2mm');
        $acierInox->setType('MATIERE_PREMIERE');
        $acierInox->setQuantiteStock(60);
        $acierInox->setPrixCatalogue(145.00);
        $manager->persist($acierInox);

        $verreTempe = new Piece();
        $verreTempe->setReference('MAT-VERRE-006');
        $verreTempe->setLibelle('Verre trempé 8mm 1.5x1.5m');
        $verreTempe->setType('MATIERE_PREMIERE');
        $verreTempe->setQuantiteStock(30);
        $verreTempe->setPrixCatalogue(250.00);
        $manager->persist($verreTempe);

        $peinture = new Piece();
        $peinture->setReference('MAT-PEINTURE-007');
        $peinture->setLibelle('Peinture industrielle noire 10L');
        $peinture->setType('MATIERE_PREMIERE');
        $peinture->setQuantiteStock(25);
        $peinture->setPrixCatalogue(85.00);
        $manager->persist($peinture);

        // Pièces achetées
        $rondelle = new Piece();
        $rondelle->setReference('ACH-RONDEL-M8');
        $rondelle->setLibelle('Rondelle plate M8');
        $rondelle->setType('ACHETEE');
        $rondelle->setQuantiteStock(3000);
        $rondelle->setPrixCatalogue(0.03);
        $manager->persist($rondelle);

        $ressort = new Piece();
        $ressort->setReference('ACH-RESSORT-01');
        $ressort->setLibelle('Ressort hélicoïdal compression');
        $ressort->setType('ACHETEE');
        $ressort->setQuantiteStock(400);
        $ressort->setPrixCatalogue(2.50);
        $manager->persist($ressort);

        $courroie = new Piece();
        $courroie->setReference('ACH-COURROIE-02');
        $courroie->setLibelle('Courroie de transmission SPB');
        $courroie->setType('ACHETEE');
        $courroie->setQuantiteStock(80);
        $courroie->setPrixCatalogue(18.90);
        $manager->persist($courroie);

        $moteur = new Piece();
        $moteur->setReference('ACH-MOTEUR-1KW');
        $moteur->setLibelle('Moteur électrique 1kW 230V');
        $moteur->setType('ACHETEE');
        $moteur->setQuantiteStock(15);
        $moteur->setPrixCatalogue(135.00);
        $manager->persist($moteur);

        // Pièces intermédiaires
        $chassis = new Piece();
        $chassis->setReference('INT-CHASSIS-05');
        $chassis->setLibelle('Châssis aluminium 600x400mm');
        $chassis->setType('INTERMEDIAIRE');
        $chassis->setQuantiteStock(32);
        $manager->persist($chassis);

        $porte = new Piece();
        $porte->setReference('INT-PORTE-06');
        $porte->setLibelle('Porte vitrée avec serrure');
        $porte->setType('INTERMEDIAIRE');
        $porte->setQuantiteStock(18);
        $manager->persist($porte);

        $mecanisme = new Piece();
        $mecanisme->setReference('INT-MECANISME-07');
        $mecanisme->setLibelle('Mécanisme coulissant sur rail');
        $mecanisme->setType('INTERMEDIAIRE');
        $mecanisme->setQuantiteStock(40);
        $manager->persist($mecanisme);

        // Produits livrables
        $bibliotheque = new Piece();
        $bibliotheque->setReference('LIV-BIBLIOTHEQUE-008');
        $bibliotheque->setLibelle('Bibliothèque 5 niveaux bois');
        $bibliotheque->setType('LIVRABLE');
        $bibliotheque->setQuantiteStock(6);
        $bibliotheque->setPrixVente(350.00);
        $manager->persist($bibliotheque);

        $commode = new Piece();
        $commode->setReference('LIV-COMMODE-009');
        $commode->setLibelle('Commode 4 tiroirs');
        $commode->setType('LIVRABLE');
        $commode->setQuantiteStock(10);
        $commode->setPrixVente(280.00);
        $manager->persist($commode);

        $lit = new Piece();
        $lit->setReference('LIV-LIT-010');
        $lit->setLibelle('Lit cadre 140x190 bois massif');
        $lit->setType('LIVRABLE');
        $lit->setQuantiteStock(4);
        $lit->setPrixVente(650.00);
        $manager->persist($lit);

        $tableOuest = new Piece();
        $tableOuest->setReference('LIV-TABLE-OUEST-011');
        $tableOuest->setLibelle('Table basse 80x45cm');
        $tableOuest->setType('LIVRABLE');
        $tableOuest->setQuantiteStock(15);
        $tableOuest->setPrixVente(199.00);
        $manager->persist($tableOuest);

        $miroir = new Piece();
        $miroir->setReference('LIV-MIROIR-012');
        $miroir->setLibelle('Miroir mural 60x80cm');
        $miroir->setType('LIVRABLE');
        $miroir->setQuantiteStock(25);
        $miroir->setPrixVente(125.00);
        $manager->persist($miroir);

        $tabouret = new Piece();
        $tabouret->setReference('LIV-TABOURET-013');
        $tabouret->setLibelle('Tabouret de bar réglable');
        $tabouret->setType('LIVRABLE');
        $tabouret->setQuantiteStock(42);
        $tabouret->setPrixVente(89.00);
        $manager->persist($tabouret);

        $applique = new Piece();
        $applique->setReference('LIV-APPLIQUE-014');
        $applique->setLibelle('Applique murale LED 15W');
        $applique->setType('LIVRABLE');
        $applique->setQuantiteStock(60);
        $applique->setPrixVente(48.00);
        $manager->persist($applique);

        $etagereVerre = new Piece();
        $etagereVerre->setReference('LIV-ETAGERE-VERRE-015');
        $etagereVerre->setLibelle('Étagère verre et acier 2 niveaux');
        $etagereVerre->setType('LIVRABLE');
        $etagereVerre->setQuantiteStock(11);
        $etagereVerre->setPrixVente(165.00);
        $manager->persist($etagereVerre);

        $cabinetMedical = new Piece();
        $cabinetMedical->setReference('LIV-CABINET-007');
        $cabinetMedical->setLibelle('Cabinet médical complet');
        $cabinetMedical->setType('LIVRABLE');
        $cabinetMedical->setQuantiteStock(2);
        $cabinetMedical->setPrixVente(2500.00);
        $manager->persist($cabinetMedical);

        // Deux pièces supplémentaires pour atteindre 40
        $canape = new Piece();
        $canape->setReference('LIV-CANAPE-016');
        $canape->setLibelle('Canapé 3 places convertible');
        $canape->setType('LIVRABLE');
        $canape->setQuantiteStock(8);
        $canape->setPrixVente(899.00);
        $manager->persist($canape);

        $verrouil = new Piece();
        $verrouil->setReference('ACH-VERROU-03');
        $verrouil->setLibelle('Verrou de sécurité 3 points');
        $verrouil->setType('ACHETEE');
        $verrouil->setQuantiteStock(200);
        $verrouil->setPrixCatalogue(12.50);
        $manager->persist($verrouil);

        // Flush pour obtenir les IDs
        $manager->flush();

        // ===== COMPOSITIONS (Relations pieceParent -> pieceEnfant) =====

        // Table composée de : plateau + 4 pieds
        $comp1 = new PieceComposition();
        $comp1->setPieceParent($table);
        $comp1->setPieceEnfant($plateau);
        $comp1->setQuantite(1);
        $manager->persist($comp1);

        $comp2 = new PieceComposition();
        $comp2->setPieceParent($table);
        $comp2->setPieceEnfant($piedMetal);
        $comp2->setQuantite(4);
        $manager->persist($comp2);

        $comp3 = new PieceComposition();
        $comp3->setPieceParent($table);
        $comp3->setPieceEnfant($vis);
        $comp3->setQuantite(16);
        $manager->persist($comp3);

        // Bureau
        $comp4 = new PieceComposition();
        $comp4->setPieceParent($bureau);
        $comp4->setPieceEnfant($plateau);
        $comp4->setQuantite(1);
        $manager->persist($comp4);

        $comp5 = new PieceComposition();
        $comp5->setPieceParent($bureau);
        $comp5->setPieceEnfant($piedMetal);
        $comp5->setQuantite(2);
        $manager->persist($comp5);

        $comp6 = new PieceComposition();
        $comp6->setPieceParent($bureau);
        $comp6->setPieceEnfant($vis);
        $comp6->setQuantite(12);
        $manager->persist($comp6);

        // Plateau composé de bois et acier
        $comp7 = new PieceComposition();
        $comp7->setPieceParent($plateau);
        $comp7->setPieceEnfant($boisPine);
        $comp7->setQuantite(2);
        $manager->persist($comp7);

        $comp8 = new PieceComposition();
        $comp8->setPieceParent($plateau);
        $comp8->setPieceEnfant($acier);
        $comp8->setQuantite(1);
        $manager->persist($comp8);

        // Pied métal composé d'acier et de boulons
        $comp9 = new PieceComposition();
        $comp9->setPieceParent($piedMetal);
        $comp9->setPieceEnfant($acier);
        $comp9->setQuantite(1);
        $manager->persist($comp9);

        $comp10 = new PieceComposition();
        $comp10->setPieceParent($piedMetal);
        $comp10->setPieceEnfant($vis);
        $comp10->setQuantite(8);
        $manager->persist($comp10);

        // Armoire
        $comp11 = new PieceComposition();
        $comp11->setPieceParent($armoire);
        $comp11->setPieceEnfant($acier);
        $comp11->setQuantite(3);
        $manager->persist($comp11);

        $comp12 = new PieceComposition();
        $comp12->setPieceParent($armoire);
        $comp12->setPieceEnfant($assemblageAntivol);
        $comp12->setQuantite(1);
        $manager->persist($comp12);

        // Broyeur (assemblage complexe)
        $comp13 = new PieceComposition();
        $comp13->setPieceParent($broyeur);
        $comp13->setPieceEnfant($roueBroyeur);
        $comp13->setQuantite(2);
        $manager->persist($comp13);

        $comp14 = new PieceComposition();
        $comp14->setPieceParent($broyeur);
        $comp14->setPieceEnfant($roulement);
        $comp14->setQuantite(4);
        $manager->persist($comp14);

        $comp15 = new PieceComposition();
        $comp15->setPieceParent($broyeur);
        $comp15->setPieceEnfant($joint);
        $comp15->setQuantite(6);
        $manager->persist($comp15);

        // Roue broyeur composée de pièces basiques
        $comp16 = new PieceComposition();
        $comp16->setPieceParent($roueBroyeur);
        $comp16->setPieceEnfant($acier);
        $comp16->setQuantite(2);
        $manager->persist($comp16);

        $comp17 = new PieceComposition();
        $comp17->setPieceParent($roueBroyeur);
        $comp17->setPieceEnfant($vis);
        $comp17->setQuantite(20);
        $manager->persist($comp17);

        // Chaise (simple composition)
        $comp18 = new PieceComposition();
        $comp18->setPieceParent($chaise);
        $comp18->setPieceEnfant($roulement);
        $comp18->setQuantite(5);
        $manager->persist($comp18);

        // ===== COMPOSITIONS DES 21 NOUVELLES PIÈCES =====

        // Bibliothèque composée de bois et acier
        $comp19 = new PieceComposition();
        $comp19->setPieceParent($bibliotheque);
        $comp19->setPieceEnfant($boisPine);
        $comp19->setQuantite(6);
        $manager->persist($comp19);

        $comp20 = new PieceComposition();
        $comp20->setPieceParent($bibliotheque);
        $comp20->setPieceEnfant($vis);
        $comp20->setQuantite(24);
        $manager->persist($comp20);

        // Commode
        $comp21 = new PieceComposition();
        $comp21->setPieceParent($commode);
        $comp21->setPieceEnfant($boisPine);
        $comp21->setQuantite(8);
        $manager->persist($comp21);

        $comp22 = new PieceComposition();
        $comp22->setPieceParent($commode);
        $comp22->setPieceEnfant($vis);
        $comp22->setQuantite(32);
        $manager->persist($comp22);

        // Lit
        $comp23 = new PieceComposition();
        $comp23->setPieceParent($lit);
        $comp23->setPieceEnfant($boisPine);
        $comp23->setQuantite(12);
        $manager->persist($comp23);

        $comp24 = new PieceComposition();
        $comp24->setPieceParent($lit);
        $comp24->setPieceEnfant($acier);
        $comp24->setQuantite(2);
        $manager->persist($comp24);

        $comp25 = new PieceComposition();
        $comp25->setPieceParent($lit);
        $comp25->setPieceEnfant($vis);
        $comp25->setQuantite(48);
        $manager->persist($comp25);

        // Table basse
        $comp26 = new PieceComposition();
        $comp26->setPieceParent($tableOuest);
        $comp26->setPieceEnfant($boisPine);
        $comp26->setQuantite(3);
        $manager->persist($comp26);

        $comp27 = new PieceComposition();
        $comp27->setPieceParent($tableOuest);
        $comp27->setPieceEnfant($verreTempe);
        $comp27->setQuantite(1);
        $manager->persist($comp27);

        // Miroir
        $comp28 = new PieceComposition();
        $comp28->setPieceParent($miroir);
        $comp28->setPieceEnfant($verreTempe);
        $comp28->setQuantite(1);
        $manager->persist($comp28);

        $comp29 = new PieceComposition();
        $comp29->setPieceParent($miroir);
        $comp29->setPieceEnfant($acierInox);
        $comp29->setQuantite(1);
        $manager->persist($comp29);

        // Tabouret
        $comp30 = new PieceComposition();
        $comp30->setPieceParent($tabouret);
        $comp30->setPieceEnfant($acier);
        $comp30->setQuantite(1);
        $manager->persist($comp30);

        $comp31 = new PieceComposition();
        $comp31->setPieceParent($tabouret);
        $comp31->setPieceEnfant($boisPine);
        $comp31->setQuantite(2);
        $manager->persist($comp31);

        $comp32 = new PieceComposition();
        $comp32->setPieceParent($tabouret);
        $comp32->setPieceEnfant($ressort);
        $comp32->setQuantite(1);
        $manager->persist($comp32);

        // Applique murale
        $comp33 = new PieceComposition();
        $comp33->setPieceParent($applique);
        $comp33->setPieceEnfant($acierInox);
        $comp33->setQuantite(1);
        $manager->persist($comp33);

        $comp34 = new PieceComposition();
        $comp34->setPieceParent($applique);
        $comp34->setPieceEnfant($visPetite);
        $comp34->setQuantite(4);
        $manager->persist($comp34);

        // Étagère verre
        $comp35 = new PieceComposition();
        $comp35->setPieceParent($etagereVerre);
        $comp35->setPieceEnfant($verreTempe);
        $comp35->setQuantite(2);
        $manager->persist($comp35);

        $comp36 = new PieceComposition();
        $comp36->setPieceParent($etagereVerre);
        $comp36->setPieceEnfant($acierInox);
        $comp36->setQuantite(2);
        $manager->persist($comp36);

        $comp37 = new PieceComposition();
        $comp37->setPieceParent($etagereVerre);
        $comp37->setPieceEnfant($vis);
        $comp37->setQuantite(8);
        $manager->persist($comp37);

        // Cabinet médical complexe
        $comp38 = new PieceComposition();
        $comp38->setPieceParent($cabinetMedical);
        $comp38->setPieceEnfant($mecanisme);
        $comp38->setQuantite(2);
        $manager->persist($comp38);

        $comp39 = new PieceComposition();
        $comp39->setPieceParent($cabinetMedical);
        $comp39->setPieceEnfant($porte);
        $comp39->setQuantite(3);
        $manager->persist($comp39);

        $comp40 = new PieceComposition();
        $comp40->setPieceParent($cabinetMedical);
        $comp40->setPieceEnfant($acierInox);
        $comp40->setQuantite(4);
        $manager->persist($comp40);

        $comp41 = new PieceComposition();
        $comp41->setPieceParent($cabinetMedical);
        $comp41->setPieceEnfant($vis);
        $comp41->setQuantite(50);
        $manager->persist($comp41);

        // Châssis
        $comp42 = new PieceComposition();
        $comp42->setPieceParent($chassis);
        $comp42->setPieceEnfant($aluminiumPlate);
        $comp42->setQuantite(1);
        $manager->persist($comp42);

        $comp43 = new PieceComposition();
        $comp43->setPieceParent($chassis);
        $comp43->setPieceEnfant($vis);
        $comp43->setQuantite(12);
        $manager->persist($comp43);

        // Porte vitrée
        $comp44 = new PieceComposition();
        $comp44->setPieceParent($porte);
        $comp44->setPieceEnfant($verreTempe);
        $comp44->setQuantite(1);
        $manager->persist($comp44);

        $comp45 = new PieceComposition();
        $comp45->setPieceParent($porte);
        $comp45->setPieceEnfant($acierInox);
        $comp45->setQuantite(1);
        $manager->persist($comp45);

        // Mécanisme coulissant
        $comp46 = new PieceComposition();
        $comp46->setPieceParent($mecanisme);
        $comp46->setPieceEnfant($acier);
        $comp46->setQuantite(2);
        $manager->persist($comp46);

        $comp47 = new PieceComposition();
        $comp47->setPieceParent($mecanisme);
        $comp47->setPieceEnfant($roulement);
        $comp47->setQuantite(4);
        $manager->persist($comp47);

        $comp48 = new PieceComposition();
        $comp48->setPieceParent($mecanisme);
        $comp48->setPieceEnfant($vis);
        $comp48->setQuantite(16);
        $manager->persist($comp48);

        // Canapé convertible
        $comp49 = new PieceComposition();
        $comp49->setPieceParent($canape);
        $comp49->setPieceEnfant($boisPine);
        $comp49->setQuantite(10);
        $manager->persist($comp49);

        $comp50 = new PieceComposition();
        $comp50->setPieceParent($canape);
        $comp50->setPieceEnfant($ressort);
        $comp50->setQuantite(8);
        $manager->persist($comp50);

        $comp51 = new PieceComposition();
        $comp51->setPieceParent($canape);
        $comp51->setPieceEnfant($vis);
        $comp51->setQuantite(40);
        $manager->persist($comp51);

        $comp52 = new PieceComposition();
        $comp52->setPieceParent($canape);
        $comp52->setPieceEnfant($mecanisme);
        $comp52->setQuantite(2);
        $manager->persist($comp52);

        // Porte avec verrou
        $comp53 = new PieceComposition();
        $comp53->setPieceParent($porte);
        $comp53->setPieceEnfant($verrouil);
        $comp53->setQuantite(1);
        $manager->persist($comp53);

        // On envoie tout en base
        $manager->flush();
    }
}
