<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Création des Rôles en base de données
        $roleAdmin = new Role();
        $roleAdmin->setCode('ROLE_ADMIN');
        $manager->persist($roleAdmin);

        $roleAtelier = new Role();
        $roleAtelier->setCode('ROLE_ATELIER');
        $manager->persist($roleAtelier);

        $roleCommercial = new Role();
        $roleCommercial->setCode('ROLE_COMMERCIAL');
        $manager->persist($roleCommercial);

        $roleComptable = new Role();
        $roleComptable->setCode('ROLE_COMPTABLE');
        $manager->persist($roleComptable);

        // Mot de passe par défaut pour tous les utilisateurs de test
        $defaultPassword = 'password123';

        // 2. Création de l'Administrateur
        $admin = new User();
        $admin->setEmail('admin@crm.com');
        $admin->setNom('Dupont');
        $admin->setPrenom('Jean');
        $admin->setActif(true);
        $admin->addUserRole($roleAdmin);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, $defaultPassword));
        $manager->persist($admin);

        // 3. Création de l'Utilisateur Atelier
        $atelier = new User();
        $atelier->setEmail('atelier@crm.com');
        $atelier->setNom('Martin');
        $atelier->setPrenom('Paul');
        $atelier->setActif(true);
        $atelier->addUserRole($roleAtelier);
        $atelier->setPassword($this->passwordHasher->hashPassword($atelier, $defaultPassword));
        $manager->persist($atelier);

        // 4. Création de l'Utilisateur Commercial
        $commercial = new User();
        $commercial->setEmail('commercial@crm.com');
        $commercial->setNom('Dubois');
        $commercial->setPrenom('Sophie');
        $commercial->setActif(true);
        $commercial->addUserRole($roleCommercial);
        $commercial->setPassword($this->passwordHasher->hashPassword($commercial, $defaultPassword));
        $manager->persist($commercial);

        // 5. Création de l'Utilisateur Comptable
        $comptable = new User();
        $comptable->setEmail('compta@crm.com');
        $comptable->setNom('Bernard');
        $comptable->setPrenom('Lucie');
        $comptable->setActif(true);
        $comptable->addUserRole($roleComptable);
        $comptable->setPassword($this->passwordHasher->hashPassword($comptable, $defaultPassword));
        $manager->persist($comptable);

        // 6. On envoie le tout en base de données
        $manager->flush();
    }
}
