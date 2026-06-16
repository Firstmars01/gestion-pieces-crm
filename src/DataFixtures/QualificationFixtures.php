<?php

namespace App\DataFixtures;

use App\Entity\Qualification;
use App\Entity\User;
use App\Entity\PosteTravail;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class QualificationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Récupérer tous les utilisateurs (on prend seulement ceux avec ROLE_ATELIER pour la logique)
        $users = $manager->getRepository(User::class)->findAll();

        // Filtrer les utilisateurs pour garder ceux qui ont du sens pour les qualifications
        // On exclut les utilisateurs admin, commercial, comptable et on garde seulement les ouvriers
        $atelierUsers = array_filter($users, function(User $user) {
            foreach ($user->getUserRoles() as $role) {
                if ($role->getCode() === 'ROLE_ATELIER') {
                    return true;
                }
            }
            return false;
        });

        // Récupérer tous les postes de travail
        $postes = $manager->getRepository(PosteTravail::class)->findAll();

        if (count($atelierUsers) === 0 || count($postes) === 0) {
            // Pas d'utilisateurs atelier ou de postes : on ne crée rien
            return;
        }

        // Créer des qualifications pour chaque utilisateur atelier
        // Chaque utilisateur aura entre 2 et 5 qualifications pour différents postes
        foreach ($atelierUsers as $i => $user) {
            // Nombre de qualifications pour cet utilisateur (2 à 5)
            $nbQualif = 2 + (($i * 13) % 4); // distribution déterministe 2..5

            // Choisir des postes uniques pour cet utilisateur
            $postesMelange = $postes;
            shuffle($postesMelange);
            $postesChosen = array_slice($postesMelange, 0, $nbQualif);

            foreach ($postesChosen as $poste) {
                // Vérifier qu'on ne crée pas de doublon
                $exists = $manager->getRepository(Qualification::class)->findOneBy([
                    'user' => $user,
                    'poste' => $poste
                ]);

                if (!$exists) {
                    $qualification = new Qualification();
                    $qualification->setUser($user);
                    $qualification->setPoste($poste);
                    $manager->persist($qualification);
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PosteTravailFixtures::class,
        ];
    }
}

