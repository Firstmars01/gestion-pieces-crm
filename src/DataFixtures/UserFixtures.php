<?php

namespace App\DataFixtures;

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
        $admin = new User();
        $admin->setEmail('admin@crm.com');
        $admin->setNom('Dupont');
        $admin->setPrenom('Jean');
        $admin->setActif(true);

        // On lui donne le rôle ADMIN
        $admin->setRoles(['ROLE_ADMIN']);

        // On hache son mot de passe (ici: "admin123")
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'admin123'
        );
        $admin->setPassword($hashedPassword);

        // On demande à Doctrine de préparer l'insertion
        $manager->persist($admin);

        // On envoie le tout en base de données
        $manager->flush();
    }
}
