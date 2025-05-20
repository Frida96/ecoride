Projet EcoRide
Description du projet
EcoRide est une plateforme de covoiturage écologique développée dans le cadre d'un ECF (Évaluation en Cours de Formation) pour le titre professionnel Développeur Web et Web Mobile. Cette application web permet aux utilisateurs de proposer et de réserver des trajets en voiture, en mettant l'accent sur l'aspect écologique des déplacements.
Fonctionnalités implémentées
Partie front-end
US1 : Page d'accueil

Présentation de l'entreprise avec des images
Barre de recherche pour trouver un itinéraire
Bas de page avec contact et lien vers mentions légales

US2 : Menu de l'application

Navigation fluide avec retour à l'accueil
Accès aux covoiturages
Connexion
Contact

US3 : Vue des covoiturages

Interface de recherche de trajets
Affichage des trajets disponibles
Informations sur le trajet (prix, date, heure, places disponibles)
Indication si le trajet est écologique
Bouton "Détails" pour voir plus d'informations sur le trajet

US4 : Filtres des covoiturages

Filtrage par aspect écologique
Filtrage par prix (en cours)
Filtrage par durée (en cours)
Filtrage par note du conducteur (en cours)

US7 : Création de compte

Formulaire d'inscription
Connexion sécurisée
Système d'authentification avec différents rôles (Utilisateur, Employé, Admin)

US8 : Espace Utilisateur

Espace personnel utilisateur
Gestion du profil (informations personnelles, photo, biographie)
Possibilité d'ajouter et de gérer des véhicules
Indication si l'utilisateur est "chauffeur" ou "passager"

US9 : Saisir un voyage (trajet)

Interface pour proposer un nouveau trajet
Sélection du véhicule pour le trajet
Définition du prix, de la date et des horaires

Partie back-end
Base de données

Modèle de données pour les utilisateurs (User)
Modèle de données pour les trajets (Trajet)
Modèle de données pour les véhicules (Vehicule)
Relations entre les différentes entités

Sécurité

Authentification des utilisateurs
Gestion des rôles (ROLE_USER, ROLE_EMPLOYE, ROLE_ADMIN)
Accès restreint aux espaces personnels

Gestion des données

CRUD pour les trajets
CRUD pour les véhicules
Recherche et filtrage des trajets selon différents critères

Technologies utilisées

Symfony 6.x : Framework PHP principal
Doctrine ORM : Pour la gestion des entités et l'interaction avec la base de données
Twig : Moteur de templates pour les vues
Bootstrap 5 : Framework CSS pour le design responsive
FontAwesome : Pour les icônes
MySQL : Base de données relationnelle

Structure du projet
Entités principales
User

Informations personnelles (nom, prénom, email, téléphone)
Authentification (email, mot de passe, rôles)
Profil (pseudo, bio, photo)
Vérifications (identité, email, téléphone)
Relation avec les véhicules et trajets

Trajet

Informations de trajet (départ, arrivée, date, heure)
Prix et nombre de places
Aspect écologique
Relations avec le conducteur et le véhicule

Vehicule

Informations du véhicule (marque, modèle, immatriculation)
Caractéristiques (places, couleur, type d'énergie)
Aspect écologique
Relation avec le propriétaire

Contrôleurs

HomeController : Gestion de la page d'accueil
SecurityController : Gestion de l'authentification
TrajetController : Recherche et affichage des trajets
TrajetUserController : Gestion des trajets de l'utilisateur
ProfilController : Gestion du profil utilisateur
VehiculeController : Gestion des véhicules de l'utilisateur

Formulaires

RechercheTrajetType : Formulaire de recherche de trajets
TrajetType : Formulaire de création/modification de trajet
ProfilType : Formulaire de modification du profil
VehiculeType : Formulaire de création/modification de véhicule

Installation et déploiement
Prérequis

PHP 8.1 ou supérieur
Composer
MySQL ou MariaDB
Node.js et npm (optionnel, pour les assets)

Installation

Cloner le repository
bashgit clone https://github.com/votre-username/ecoride.git
cd ecoride

Installer les dépendances
bashcomposer install

Configurer la base de données dans le fichier .env
DATABASE_URL="mysql://username:password@127.0.0.1:3306/ecoride?serverVersion=8.0.32&charset=utf8mb4"

Créer la base de données et exécuter les migrations
bashphp bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

Charger les fixtures (données de test)
bashphp bin/console doctrine:fixtures:load

Démarrer le serveur de développement
bashphp bin/console server:start

Accéder à l'application
http://localhost:8000


Comptes de test

Administrateur

Email: admin@ecoride.com
Mot de passe: adminpass


Employé

Email: employe@ecoride.com
Mot de passe: employepass


Utilisateur

Email: utilisateur@exemple.com
Mot de passe: userpass



Fonctionnalités à venir

US5 : Vue détaillée d'un covoiturage (en cours)
US6 : Participation à un covoiturage
US10 : Historique des covoiturages
US11 : Démarrer et arrêter un covoiturage
US12 : Espace employé
US13 : Espace administrateur

Architecture technique
Symfony
Le projet est structuré selon les bonnes pratiques de Symfony, avec une séparation claire des responsabilités :

Controllers : Gestion des requêtes HTTP
Entities : Modèles de données
Repositories : Accès aux données
Forms : Gestion des formulaires
Templates : Vues Twig
Services : Logique métier réutilisable

Base de données
La base de données suit une architecture relationnelle avec les tables principales :

user : Stockage des utilisateurs
trajet : Stockage des trajets
vehicule : Stockage des véhicules

Sécurité
L'application utilise le composant Security de Symfony pour :

Authentification des utilisateurs
Gestion des sessions
Protection CSRF
Contrôle d'accès basé sur les rôles

Contribution
Ce projet est développé dans le cadre d'un ECF et n'est pas ouvert aux contributions externes.
Auteur
Mbunga Longo - Développeur Web et Web Mobile en formation
