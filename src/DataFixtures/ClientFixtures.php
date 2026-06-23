<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClientFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $dataClients = [
            // --- Entreprises / Sociétés ---
            [
                'raison_sociale' => 'Aérospatiale Occitanie SAS',
                'nom' => 'Garnier',
                'prenom' => 'Thierry',
                'email' => 't.garnier@aero-occitanie.fr',
                'telephone' => '0468811234',
            ],
            [
                'raison_sociale' => 'Meubles du Roussillon Sarl',
                'nom' => 'Martinez',
                'prenom' => 'Sophie',
                'email' => 'contact@meubles-roussillon.com',
                'telephone' => '0468529876',
            ],
            [
                'raison_sociale' => 'Construction Bois du Sud',
                'nom' => 'Fontaine',
                'prenom' => 'Lucas',
                'email' => 'l.fontaine@boisdusud.fr',
                'telephone' => '0612345678',
            ],
            [
                'raison_sociale' => 'Atelier Métal Design',
                'nom' => 'Rousseau',
                'prenom' => 'Marc',
                'email' => 'm.rousseau@metaldesign.com',
                'telephone' => '0789456123',
            ],
            // --- Particuliers ---
            [
                'raison_sociale' => null,
                'nom' => 'Durand',
                'prenom' => 'Pierre',
                'email' => 'pierre.durand@gmail.com',
                'telephone' => '0678912345',
            ],
            [
                'raison_sociale' => null,
                'nom' => 'Chavez',
                'prenom' => 'Maria',
                'email' => 'mchavez@outlook.fr',
                'telephone' => '0645127893',
            ],
            [
                'raison_sociale' => null,
                'nom' => 'Petit',
                'prenom' => 'Julien',
                'email' => 'j.petit@yahoo.fr',
                'telephone' => '0623568914',
            ],
            [
                'raison_sociale' => null,
                'nom' => 'Moreau',
                'prenom' => 'Céline',
                'email' => 'celine.moreau82@gmail.com',
                'telephone' => '0711223344',
            ],
        ];

        foreach ($dataClients as $data) {
            $client = new Client();
            $client->setRaisonSociale($data['raison_sociale']);
            $client->setNom($data['nom']);
            $client->setPrenom($data['prenom']);
            $client->setEmail($data['email']);
            $client->setTelephone($data['telephone']);

            $manager->persist($client);
        }

        $manager->flush();
    }
}
