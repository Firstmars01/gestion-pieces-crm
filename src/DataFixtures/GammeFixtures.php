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
        // Récupère les utilisateurs créés par UserFixtures
        $admin = $manager->getRepository(User::class)->findOneBy(['email' => 'admin@crm.com']);
        $atelier = $manager->getRepository(User::class)->findOneBy(['email' => 'atelier@crm.com']);

        // Données pour les 10 gammes
        $gammeData = [
            [
                'libelle' => 'Gamme Standard A',
                'reference' => 'REF-GSA-001',
                'type' => 'LIVRABLE',
                'prixVente' => '150.00',
                'prixCatalogue' => '175.00',
                'quantite' => 100,
                'user' => $admin,
            ],
            [
                'libelle' => 'Gamme Premium B',
                'reference' => 'REF-GPB-002',
                'type' => 'LIVRABLE',
                'prixVente' => '250.50',
                'prixCatalogue' => '300.00',
                'quantite' => 75,
                'user' => $atelier,
            ],
            [
                'libelle' => 'Gamme Économique C',
                'reference' => 'REF-GEC-003',
                'type' => 'LIVRABLE',
                'prixVente' => '99.99',
                'prixCatalogue' => '120.00',
                'quantite' => 200,
                'user' => $admin,
            ],
            [
                'libelle' => 'Gamme Intermédiaire D',
                'reference' => 'REF-GID-004',
                'type' => 'INTERMEDIAIRE',
                'prixVente' => null,
                'prixCatalogue' => null,
                'quantite' => 50,
                'user' => $atelier,
            ],
            [
                'libelle' => 'Gamme Matière E',
                'reference' => 'REF-GME-005',
                'type' => 'MATIERE_PREMIERE',
                'prixVente' => null,
                'prixCatalogue' => '45.00',
                'quantite' => 500,
                'user' => $admin,
            ],
            [
                'libelle' => 'Gamme Achetée F',
                'reference' => 'REF-GAF-006',
                'type' => 'ACHETEE',
                'prixVente' => null,
                'prixCatalogue' => '65.00',
                'quantite' => 150,
                'user' => $atelier,
            ],
            [
                'libelle' => 'Gamme Deluxe G',
                'reference' => 'REF-GDG-007',
                'type' => 'LIVRABLE',
                'prixVente' => '450.00',
                'prixCatalogue' => '550.00',
                'quantite' => 30,
                'user' => $admin,
            ],
            [
                'libelle' => 'Gamme Classique H',
                'reference' => 'REF-GCH-008',
                'type' => 'LIVRABLE',
                'prixVente' => '180.75',
                'prixCatalogue' => '220.00',
                'quantite' => 120,
                'user' => $atelier,
            ],
            [
                'libelle' => 'Gamme Spéciale I',
                'reference' => 'REF-GSI-009',
                'type' => 'LIVRABLE',
                'prixVente' => '320.00',
                'prixCatalogue' => '380.00',
                'quantite' => 60,
                'user' => $admin,
            ],
            [
                'libelle' => 'Gamme Compacte J',
                'reference' => 'REF-GCJ-010',
                'type' => 'LIVRABLE',
                'prixVente' => '125.50',
                'prixCatalogue' => '155.00',
                'quantite' => 250,
                'user' => $atelier,
            ],
        ];

        // Création des 10 gammes avec leurs pièces
        foreach ($gammeData as $data) {
            // Créer la pièce
            $piece = new Piece();
            $piece->setReference($data['reference']);
            $piece->setLibelle($data['libelle']);
            $piece->setType($data['type']);
            $piece->setPrixVente($data['prixVente']);
            $piece->setPrixCatalogue($data['prixCatalogue']);
            $piece->setQuantiteStock($data['quantite']);
            $manager->persist($piece);

            // Créer la gamme et l'associer à la pièce et à l'utilisateur
            $gamme = new Gamme();
            $gamme->setLibelle($data['libelle']);
            $gamme->setPiece($piece);
            $gamme->setUser($data['user']);
            $manager->persist($gamme);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}

