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

- PHP 8.3
- Symfony 7
- Doctrine ORM

### Base de données

- PostgreSQL

### Frontend

- Twig
- Bootstrap 5
- JavaScript

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
- Symfony CLI

### Cloner le projet

```bash
git clone https://github.com/votre-compte/crm-gestion-pieces.git
cd crm-gestion-pieces
```

### Installer les dépendances

```bash
composer install
```

### Configuration de l'environnement

Créer le fichier `.env.local` :

```env
DATABASE_URL="postgresql://postgres:password@127.0.0.1:5432/crm_pieces?serverVersion=16&charset=utf8"
```

### Création de la base de données

```bash
php bin/console doctrine:database:create
```

### Exécution des migrations

```bash
php bin/console doctrine:migrations:migrate
```

### Chargement des données de démonstration

```bash
php bin/console doctrine:fixtures:load
```

### Lancement du serveur

```bash
symfony server:start
```

Application accessible sur :

```text
http://localhost:8000
```

---

## Architecture du projet

```text
src/
│
├── Controller/
│   ├── PieceController
│   ├── DevisController
│   ├── CommandeController
│   ├── AchatController
│   └── AdminController
│
├── Entity/
│   ├── User
│   ├── Piece
│   ├── Devis
│   ├── Commande
│   ├── Facture
│   └── Fournisseur
│
├── Repository/
│
├── Form/
│
├── Security/
│
├── Service/
│   ├── PdfService
│   ├── CsvExportService
│   ├── StockService
│   └── PriceCalculatorService
│
└── EventSubscriber/
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

Projet réalisé dans le cadre du Projet de Synthèse M1 ESIMED 2026.

Développeur : Clément MARIE

---

## Licence

Projet pédagogique réalisé dans le cadre de la formation ESIMED.
