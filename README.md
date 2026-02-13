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
- **Responsive** : Compatible mobile, tablette et desktop

## Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur (ou MariaDB 10.3+)
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

### 3. Configurer la connexion

Modifiez le fichier `config/database.php` si nécessaire :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'todo_list');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Lancer l'application

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
│   └── etiquettes.php      # CRUD étiquettes
├── config/
│   └── database.php        # Configuration BDD
├── css/
│   └── style.css           # Styles personnalisés
├── includes/
│   ├── header.php          # En-tête HTML
│   └── footer.php          # Pied de page + modals
├── js/
│   └── app.js              # JavaScript principal
├── index.php               # Page liste des tâches
├── kanban.php              # Vue Kanban
├── projets.php             # Gestion des projets
├── statistiques.php        # Statistiques
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
