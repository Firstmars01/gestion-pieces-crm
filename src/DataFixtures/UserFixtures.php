<?php

namespace App\DataFixtures;

use App\Entity\Role; // <-- Ne pas oublier d'importer l'entité Role
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    // On injecte le service de hachage de Symfony pour sécuriser le mot de passe
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Création des Rôles en base de données
        $roleAdmin = new Role();
        $roleAdmin->setCode('ROLE_ADMIN');
        $manager->persist($roleAdmin);

        // (Optionnel) Création des autres rôles prévus pour votre application
        $roleAtelier = new Role();
        $roleAtelier->setCode('ROLE_ATELIER');
        $manager->persist($roleAtelier);

        $roleCommercial = new Role();
        $roleCommercial->setCode('ROLE_COMMERCIAL');
        $manager->persist($roleCommercial);

        $roleComptable = new Role();
        $roleComptable->setCode('ROLE_COMPTABLE');
        $manager->persist($roleComptable);

        // 2. Création de l'Utilisateur
        $admin = new User();
        $admin->setEmail('admin@crm.com');
        $admin->setNom('Dupont');
        $admin->setPrenom('Jean');
        $admin->setActif(true);

        // 3. On lui attribue le rôle avec la nouvelle méthode métier (on passe l'objet Role)
        $admin->addUserRole($roleAdmin);

        // On hache son mot de passe (ici: "admin123")
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'admin123'
        );
        $admin->setPassword($hashedPassword);

        // On demande à Doctrine de préparer l'insertion
        $manager->persist($admin);

        // 4. On envoie le tout en base de données
        $manager->flush();
    }
}
