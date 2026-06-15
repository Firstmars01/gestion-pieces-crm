# CRM Gestion des Pièces Détachées

Projet de synthèse M1 – ESIMED 2026

## Présentation

Cette application web permet la gestion complète des pièces détachées, de la fabrication, des devis, des commandes, des achats et de la comptabilité d'une entreprise spécialisée dans la fabrication et la vente de tables de ping-pong.

Le projet a été développé en PHP avec le framework Symfony et utilise PostgreSQL comme système de gestion de base de données.

---

## Objectifs

L'application permet de :

- Centraliser la gestion des pièces détachées
- Gérer les compositions des pièces fabriquées
- Suivre les opérations de fabrication
- Gérer les devis clients
- Générer automatiquement des commandes à partir des devis
- Gérer les achats fournisseurs
- Produire des factures PDF
- Exporter des données comptables au format CSV
- Administrer les utilisateurs et leurs droits d'accès

---

## Technologies utilisées

### Backend

- PHP >= 8.2 (projet teste avec PHP 8.3)
- Symfony 7.4
- Doctrine ORM + Doctrine Migrations
- Symfony Security (form login + roles)
- KnpPaginatorBundle (pagination des listes)

### Base de données

- PostgreSQL 16

### Frontend

- Twig
- Bootstrap 5.3 (via CDN)
- Bootstrap Icons (via CDN)
- Tom Select (via CDN)
- JavaScript vanilla

### Qualite / tests

- PHPUnit
- PHPStan
- PHP CS Fixer

## Fonctionnalites disponibles

### Authentification

- page de connexion `/login`
- deconnexion `/logout`
- controle d'acces par roles

### Atelier

### Outils

- Git
- GitHub
- GitHub Actions
- Composer

---

## Fonctionnalités

### Module Atelier

#### Gestion des pièces

- Création de pièces
- Modification de pièces
- Suppression de pièces
- Classification des pièces :
    - Matières premières
    - Pièces achetées
    - Pièces intermédiaires
    - Pièces commercialisables

#### Composition des pièces

- Gestion des nomenclatures
- Définition des quantités nécessaires
- Visualisation des composants

#### Fabrication

- Gestion des gammes de fabrication
- Gestion des opérations
- Gestion des postes de travail
- Gestion des machines
- Gestion des ouvriers
- Historique des fabrications

---

### Module Commercial

#### Clients

- Gestion des clients
- Consultation de l'historique

#### Devis

- Création de devis
- Gestion des lignes de devis
- Validation de la durée de validité
- Conservation des prix historiques

#### Commandes

- Génération depuis un ou plusieurs devis
- Suivi des commandes
- Historique des ventes

---

### Module Achats

#### Fournisseurs

- Gestion des fournisseurs

#### Commandes fournisseurs

- Création des commandes d'achat
- Suivi des livraisons
- Gestion des prix d'achat

---

### Module Comptabilité

#### Facturation

- Génération de factures PDF
- Archivage des factures

#### Exports

- Export CSV des factures
- Export CSV des achats à payer

---

### Administration

#### Gestion des utilisateurs

- Création
- Modification
- Désactivation

#### Gestion des droits

- ROLE_ADMIN
- ROLE_ATELIER
- ROLE_COMMERCIAL
- ROLE_COMPTABILITE

---

## Installation

### Prérequis

- PHP >= 8.3
- Composer
- PostgreSQL
- Symfony CLI (optionnel mais pratique)

### Cloner le projet

```bash
git clone https://github.com/votre-compte/gestion-pieces-crm.git
cd gestion-pieces-crm
```

### Installer les dépendances

```bash
composer install
```

### Configuration de l'environnement

Créer le fichier `.env.local` :

```env
DATABASE_URL="postgresql://postgres:postgres@127.0.0.1:5432/crm_local?serverVersion=16&charset=utf8"
```

### Création de la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
```

### Exécution des migrations

```bash
symfony server:start
```

Application disponible sur `http://localhost:8000`.

## Installation (Docker - PostgreSQL)

Le fichier `docker-compose.yml` fournit un service PostgreSQL 16 expose sur le port `5432`.

```bash
docker compose up -d database
```

## Comptes de demonstration (fixtures)

Mot de passe par defaut : `password123`

- `admin@crm.com` (ROLE_ADMIN)
- `atelier@crm.com` (ROLE_ATELIER)
- `commercial@crm.com` (ROLE_COMMERCIAL)
- `compta@crm.com` (ROLE_COMPTABLE)

## Commandes utiles

```bash
php bin/console doctrine:migrations:status
php bin/console doctrine:fixtures:load --no-interaction
php bin/phpunit
vendor/bin/phpstan analyse
vendor/bin/php-cs-fixer fix --dry-run --diff
```

## Architecture actuelle (src)

```text
src/
├── Controller/
│   ├── AtelierController.php
│   ├── HomeController.php
│   ├── SecurityController.php
│   └── UserAdministrationController.php
├── Entity/
│   ├── Gamme.php
│   ├── Piece.php
│   ├── PieceComposition.php
│   ├── Role.php
│   └── User.php
├── Form/
├── Repository/
└── DataFixtures/
```

---

## Gestion des branches

| Branche | Description |
|----------|------------|
| main | Version stable |

---

## Déploiement

Le projet est prévu pour être déployé sur :

- Ubuntu Linux
- Apache ou Nginx
- PostgreSQL

Le déploiement est automatisé via GitHub Actions.

---

## Planning

### Livraison 1

Module Atelier :

- Gestion des pièces
- Composition des pièces
- Fabrication

### Livraison 2

Module Commercial :

- Devis
- Commandes
- Achats
- Comptabilité
- Administration

---

## Auteur

Développeur : Clément MARIE

---

## Licence

Projet pédagogique réalisé dans le cadre de la formation ESIMED.
