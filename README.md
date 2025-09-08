## EcoRide — Plateforme de covoiturage écologique

## Description

EcoRide est une application web de covoiturage qui favorise les déplacements responsables (accent sur les véhicules électriques). Elle permet de proposer des trajets, de rechercher/filtrer des covoiturages, de réserver une place, de laisser des avis, et de gérer un système de crédits.

## Fonctionnalités

- Recherche de trajets avec filtres (écologique, prix maximum, durée maximum, note minimale)
- Détail d’un trajet (chauffeur, véhicule, heures, prix, note moyenne…)
- Réservation/participation avec débit automatique des crédits et double validation
- Gestion de véhicules par les utilisateurs chauffeurs
- Système d’avis avec modération (employés)
- Tableaux de bord Employé et Admin (modération, statistiques)
- Authentification et rôles: `admin`, `employe`, `chauffeur`, `passager`, `passager_chauffeur`

## Stack technique

- Backend: Symfony 7.3, PHP 8.4
- ORM: Doctrine (migrations, repositories)
- Base de données: PostgreSQL
- Frontend: Twig, Bootstrap 5, Font Awesome, Stimulus
- Assets: Bootstrap via CDN; AssetMapper/Encore

## Architecture (MVC)

- `src/Entity/`: modèles métier (`User`, `Vehicle`, `Trajet`, `Participation`, `Avis`)
- `src/Repository/`: accès aux données (ex: `TrajetRepository` avec requêtes filtrées)
- `src/Controller/`: logique applicative (recherche, participation, modération, etc.)
- `templates/`: vues Twig (mise en page, pages fonctionnelles)
- `src/Form/`: formulaires (validation côté serveur)

## Installation locale

## Prérequis
- PHP 8.4
- Composer
- PostgreSQL 
- Symfony CLI

### Étapes

1) Installer les dépendances PHP
```bash
composer install
```

2) Configurer l’environnement
```bash
cp .env .env.local
# Éditer .env.local et définir DATABASE_URL,
# DATABASE_URL="postgresql://username:password@127.0.0.1:5432/ecoride?serverVersion=15&charset=utf8"
```

3) Préparer la base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

4) Démarrer le serveur
```bash
symfony server:start -d
# ou
php -S localhost:8000 -t public/
```

5) Charger des données de test
```bash
php bin/console app:create-test-data
```

6) Créer un administrateur
```bash
php bin/console app:create-admin <pseudo> <email> <mot_de_passe>
```

### Trajets et recherche
- `TrajetRepository::findTrajetsDisponibles()` applique: filtre lieu, date, statut, énergie (électrique), prix max, durée max, et disponibilité (places restantes).
- `TrajetRepository::getNoteMoyenneChauffeur()` calcule la note moyenne à partir des `Avis` validés.

### Participations et crédits
- Vérifications métier (places restantes, fonds suffisants, interdiction au chauffeur de se réserver lui-même, doublon de participation) dans `CovoiturageController::participer()`.
- Débit/crédit automatique des comptes utilisateurs selon les actions (réservation, résolution de problème côté employé, etc.).

### Avis et modération
- Les avis sont stockés et validés par les employés (`EmployeController`).
- Le front affiche les notes et commentaires validés.

## Modèle de données (entités principales)

- `User` (pseudo, email unique, rôle, crédits, préférences)
- `Vehicle` (immatriculation, marque, modèle, couleur, énergie, date 1ère immatriculation)
- `Trajet` (lieux, dates, prix, nbPlaces, statut, chauffeur, véhicule, dateCreation)
- `Participation` (passager, trajet, statut, doubleValidation, commentaireProbleme, dateSignalement)
- `Avis` (passager, chauffeur, note, commentaire, valide)

## Design & UX

- Layout moderne (dégradés, cards, hover states) dans `templates/base.html.twig`
- Accessibilité de base (labels sur les champs, feedback visuels)
- Responsive via Bootstrap 5

## Sécurité

- Authentification Symfony Security
- Hachage sécurisé des mots de passe
- Protection CSRF sur formulaires
- Gestion de rôles applicatifs

## Commandes utiles

```bash
# Vérifier le schéma Doctrine
php bin/console doctrine:schema:validate

# Vider le cache
php bin/console cache:clear

# Lancer le serveur
symfony server:start -d
```


## Licence

Projet pédagogique — tous droits réservés

## Auteur

Mbunga Longo — Projet EcoRide (TP DWWM)

---

** Ensemble pour une mobilité plus verte ! **