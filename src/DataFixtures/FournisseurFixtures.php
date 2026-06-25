<?php

namespace App\DataFixtures;

use App\Entity\Fournisseur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FournisseurFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $fournisseursData = [
            ['raisonSociale' => 'Bois & Dérivés', 'email' => 'contact@bois-derives.fr', 'telephone' => '0123456789'],
            ['raisonSociale' => 'Métallurgie Pro', 'email' => 'ventes@metallurgie-pro.com', 'telephone' => '0198765432'],
            ['raisonSociale' => 'PingPong Access', 'email' => 'fournisseur@pingpong-access.com', 'telephone' => '0456781234'],
            ['raisonSociale' => 'Chimie & Couleurs', 'email' => 'pro@chimie-couleurs.fr', 'telephone' => '0321654987'],
        ];

        foreach ($fournisseursData as $i => $data) {
            $fournisseur = new Fournisseur();
            $fournisseur->setRaisonSociale($data['raisonSociale']);
            $fournisseur->setEmail($data['email']);
            $fournisseur->setTelephone($data['telephone']);

            $manager->persist($fournisseur);

            // On crée une "référence" pour pouvoir récupérer ces fournisseurs dans nos autres fichiers !
            $this->addReference('fournisseur_' . $i, $fournisseur);
        }

        $manager->flush();
    }
}
