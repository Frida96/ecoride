# 🌱 EcoRide - Plateforme de Covoiturage Écologique

## 📖 Description

EcoRide est une startup française dédiée au covoiturage écologique. Notre mission est de réduire l'impact environnemental des déplacements en encourageant le partage de véhicules, particulièrement les véhicules électriques.

## ✨ Fonctionnalités Actuelles

### ✅ Terminées
- **US 1** - Page d'accueil avec présentation et recherche
- **US 2** - Menu de navigation complet
- **US 7** - Système d'inscription et connexion

### 🚧 En cours de développement
- **US 3** - Vue des covoiturages
- **US 4** - Filtres de recherche
- **US 5** - Vue détaillée des trajets
- Et plus encore...

## 🛠️ Technologies

- **Backend**: Symfony 6, PHP 8
- **Base de données**: PostgreSQL
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Sécurité**: Système d'authentification Symfony

## 🚀 Installation en local

### Prérequis
- PHP 8.1+
- Composer
- PostgreSQL
- Node.js (pour les assets)

### Étapes d'installation

1. **Cloner le projet**
```bash
git clone https://github.com/Frida96/ecoride.git
cd ecoride
```

2. **Installer les dépendances**
```bash
composer install
npm install
```

3. **Configuration de la base de données**
```bash
# Copier le fichier d'environnement
cp .env .env.local

# Modifier DATABASE_URL dans .env.local avec vos credentials PostgreSQL
DATABASE_URL="postgresql://username:password@127.0.0.1:5432/ecoride?serverVersion=15&charset=utf8"
```

4. **Créer la base de données**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. **Lancer le serveur de développement**
```bash
php -S localhost:8000 -t public/
```

6. **Accéder à l'application**
Ouvrez votre navigateur sur `http://localhost:8000`

## 🎨 Design

EcoRide utilise une charte graphique entièrement dédiée à l'écologie :
- **Couleurs principales**: Verts (#22c55e, #16a34a, #dcfce7)
- **Style**: Moderne, épuré, avec animations subtiles
- **Responsive**: Compatible mobile et desktop

## 📊 Base de données

Le projet utilise les entités suivantes :
- **User**: Utilisateurs avec système de crédits
- **Vehicle**: Véhicules avec information énergétique
- **Trajet**: Covoiturages proposés
- **Participation**: Réservations de places
- **Avis**: Système d'évaluation

## 🤝 Contribution

Ce projet est développé dans le cadre d'une formation.

## 📝 Licence

Projet éducatif - Tous droits réservés

## 👨‍💻 Développeur

Développé dans le cadre du TP DWWM

---

**🌍 Ensemble pour une mobilité plus verte ! 🚗💚**
