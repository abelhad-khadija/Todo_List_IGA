# TaskFlow - Gestionnaire de Tâches

Application web de gestion de tâches de type Kanban, développée en PHP, MySQL et Bootstrap 5.

## Fonctionnalités

- **Gestion des Tâches** : Créer, modifier, supprimer des tâches
- **Vue Kanban** : Organiser les tâches avec drag & drop (À faire, En cours, Terminé)
- **Projets** : Organiser les tâches par projet avec codes couleur
- **Étiquettes** : Catégoriser les tâches avec des étiquettes colorées
- **Filtres** : Filtrer par projet, priorité, statut et recherche textuelle
- **Statistiques** : Tableaux de bord avec graphiques de productivité
- **Mode Sombre** : Interface adaptable avec thème clair/sombre
- **Rappels par Email** : Notifications automatiques pour les tâches en retard (via PHPMailer)
- **Responsive** : Compatible mobile, tablette et desktop

## Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur (ou MariaDB 10.3+)
- Composer (pour installer PHPMailer)
- Serveur web (Apache, Nginx, ou XAMPP/WAMP)

## Installation

### 1. Cloner ou télécharger le projet

```bash
git clone https://github.com/abelhad-khadija/Todo_List_IGA.git
```

Ou téléchargez et extrayez l'archive ZIP.

### 2. Configurer la base de données

1. Créez une base de données MySQL nommée `todo_list`
2. Importez le fichier `database.sql` :

```bash
mysql -u root -p todo_list < database.sql
```

Ou via phpMyAdmin : importez le fichier `database.sql`.

### 3. Installer les dépendances PHP

```bash
composer install
```

Cela installe PHPMailer (envoi d'emails) et phpdotenv (variables d'environnement).

### 4. Configurer l'environnement

Copiez le fichier `.env.example` en `.env` et remplissez vos valeurs :

```bash
cp .env.example .env
```

Puis éditez `.env` avec vos paramètres :

```env
# Base de données
DB_HOST=localhost
DB_NAME=todo_list
DB_USER=root
DB_PASS=

# SMTP (email)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_ENCRYPTION=tls

# Destinataire et expéditeur
MAIL_DESTINATAIRE=destinataire@email.com
MAIL_NOM_DESTINATAIRE=Gestionnaire TaskFlow
MAIL_FROM=votre-email@gmail.com
MAIL_FROM_NOM=TaskFlow - Rappels
```

**Pour Gmail** : vous devez générer un mot de passe d'application :
1. Allez sur https://myaccount.google.com/apppasswords
2. Sélectionnez "Autre" et nommez-le "TaskFlow"
3. Copiez le mot de passe généré dans `MAIL_PASSWORD`

> **Note** : Le fichier `.env` contient vos secrets et ne doit jamais être commité dans git. Seul `.env.example` (avec des valeurs placeholder) est versionné.

**Envoi automatique (cron)** : pour envoyer les rappels quotidiennement à 8h :

```bash
0 8 * * * php /chemin/vers/votre/projet/cron/rappels.php
```

Vous pouvez aussi envoyer les rappels manuellement via le bouton "Rappels" dans la barre de navigation.

### 5. Lancer l'application

- **Avec XAMPP/WAMP** : Placez le dossier dans `htdocs` ou `www`, puis accédez à `http://localhost/taskflow`
- **Avec PHP intégré** :

```bash
cd taskflow
php -S localhost:8000
```

Puis ouvrez `http://localhost:8000` dans votre navigateur.

## Structure du Projet

```
taskflow/
├── api/                    # API REST
│   ├── taches.php          # CRUD tâches
│   ├── projets.php         # CRUD projets
│   ├── etiquettes.php      # CRUD étiquettes
│   └── rappels.php         # Envoi des rappels par email
├── config/
│   ├── bootstrap.php       # Chargement des variables d'environnement
│   ├── database.php        # Configuration BDD
│   └── mail.php            # Configuration SMTP et email
├── cron/
│   └── rappels.php         # Script de rappels (cron/CLI)
├── css/
│   └── style.css           # Styles personnalisés
├── includes/
│   ├── header.php          # En-tête HTML
│   └── footer.php          # Pied de page + modals
├── js/
│   └── app.js              # JavaScript principal
├── vendor/                 # Dépendances Composer (PHPMailer)
├── index.php               # Page liste des tâches
├── kanban.php              # Vue Kanban
├── projets.php             # Gestion des projets
├── statistiques.php        # Statistiques
├── .env                    # Variables d'environnement (non versionné)
├── .env.example            # Modèle de configuration
├── .gitignore              # Fichiers ignorés par git
├── composer.json           # Dépendances PHP
├── database.sql            # Script SQL
└── README.md               # Documentation
```

## Technologies Utilisées

- **Frontend** :
  - HTML5 / CSS3
  - Bootstrap 5.3
  - Bootstrap Icons
  - Google Fonts (Inter)
  - SortableJS (drag & drop)
  - Chart.js (graphiques)

- **Backend** :
  - PHP 7.4+
  - PDO (accès base de données)
  - PHPMailer (envoi d'emails SMTP)

- **Base de données** :
  - MySQL / MariaDB

## Captures d'écran

### Vue Liste
La page principale affiche toutes les tâches avec filtres et statistiques rapides.

### Vue Kanban
Interface de type Kanban avec trois colonnes et drag & drop.

### Statistiques
Tableaux de bord avec graphiques interactifs.

## Auteur

Projet réalisé dans le cadre du cours de Programmation Web - S5 ISI - IGA

## Licence

Ce projet est à but éducatif.
