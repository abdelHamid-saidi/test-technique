# DateRangePicker Demo

[![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-0.11.x-purple.svg)](https://inertiajs.com)
[![Docker](https://img.shields.io/badge/Docker-Enabled-blue.svg)](https://docker.com)

Une application de démonstration présentant un composant DateRangePicker personnalisé développé avec Laravel 10, Vue.js 3, Inertia.js et Moment.js.

## Fonctionnalités

- **DateRangePicker personnalisé** : Composant Vue.js 3 pour la sélection de plages de dates
- **Gestion d'événements** : CRUD complet pour la gestion des événements
- **Base de données** : Gestion des données et migrations Laravel

## Technologies utilisées

### Backend
- **Laravel 10.x** - Framework PHP
- **Laravel Jetstream** - Authentification et gestion des sessions
- **Laravel Sanctum** - API Authentication
- **Inertia.js** - SPA sans API
- **MySql** - Base de données

### Frontend
- **Vue.js 3** - Framework JavaScript
- **Inertia.js** - Routage côté client
- **Tailwind CSS** - Framework CSS
- **Moment.js** - Manipulation des dates
- **Feather Icons** - Icônes

### DevOps
- **Docker & Docker Compose** - Containerisation
- **phpMyAdmin** - Administration de base de données
- **Laravel Mix** - Compilation des assets

## Prérequis

- **Git** - Contrôle de version
- **Docker** - Containerisation
- **Docker Compose** - Orchestration des conteneurs

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/abdelHamid-saidi/test-technique.git
cd test-technique
```

### 2. Configuration de l'environnement

```bash
cp .env.example .env
```

### 3. Démarrer les conteneurs

```bash
docker compose up -d
```

### 4. Accéder au conteneur web

```bash
# Lister les conteneurs pour obtenir l'ID
docker container ls

# Accéder au conteneur web
docker exec -it {CONTAINER_ID} bash
```

### 5. Installation des dépendances

```bash
# Dépendances PHP
composer install

# Dépendances Node.js
npm install

# Compilation des assets
npm run dev
```

## Services disponibles

Une fois l'application démarrée, les services suivants sont accessibles :

| Service | Port | Description |
|---------|------|-------------|
| **Application Web** | 80 | Interface principale de l'application |
| **phpMyAdmin** | 8080 | Administration de la base de données |
| **MySQL** | 3306 | Base de données MySQL/MariaDB |

## Utilisation

### Connexion

Un utilisateur de test est automatiquement créé lors de l'installation :

- **Email** : `test@test.com`
- **Mot de passe** : `password`

## Structure du projet

```
├── app/
│   ├── Http/Controllers/     # Contrôleurs Laravel
│   ├── Models/              # Modèles Eloquent
│   └── Policies/            # Politiques d'autorisation
├── database/
│   ├── migrations/          # Migrations de base de données
│   ├── seeders/            # Seeders pour les données de test
│   └── factories/          # Factories pour les tests
├── resources/
│   ├── js/
│   │   ├── Components/     # Composants Vue.js
│   │   ├── Pages/         # Pages Inertia.js
│   │   └── Layouts/       # Layouts Vue.js
│   └── views/             # Vues Blade
├── routes/
│   ├── web.php            # Routes web
│   └── api.php            # Routes API
└── tests/                 # Tests automatisés
```

### Backend (PHP)
```bash
php artisan serve          # Serveur de développement
php artisan migrate        # Exécuter les migrations
php artisan db:seed        # Exécuter les seeders
php artisan key:generate   # Générer la clé d'application
```

### Frontend (Node.js)
```bash
npm run dev                   # Compilation en mode développement
npm run watch                 # Surveillance des fichiers
```

## Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](https://opensource.org/licenses/MIT) pour plus de détails.

## Autre

Développé dans le cadre d'un test technique.

---

**Note** : Cette application est une démonstration technique et ne doit pas être utilisée en production sans modifications de sécurité appropriées.
