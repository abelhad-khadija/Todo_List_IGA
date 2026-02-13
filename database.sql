-- =====================================================
-- Gestionnaire de Tâches - Base de données
-- =====================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS todo_list CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE todo_list;

-- =====================================================
-- Table des projets
-- =====================================================
CREATE TABLE IF NOT EXISTS projets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    couleur VARCHAR(7) DEFAULT '#007bff',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- Table des tâches
-- =====================================================
CREATE TABLE IF NOT EXISTS taches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    projet_id INT,
    titre VARCHAR(300) NOT NULL,
    description TEXT,
    priorite ENUM('basse', 'normale', 'haute') DEFAULT 'normale',
    statut ENUM('a_faire', 'en_cours', 'termine') DEFAULT 'a_faire',
    date_echeance DATE,
    ordre INT DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- Table des étiquettes
-- =====================================================
CREATE TABLE IF NOT EXISTS etiquettes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    couleur VARCHAR(7) DEFAULT '#6c757d'
) ENGINE=InnoDB;

-- =====================================================
-- Table de liaison tâches-étiquettes
-- =====================================================
CREATE TABLE IF NOT EXISTS taches_etiquettes (
    tache_id INT,
    etiquette_id INT,
    PRIMARY KEY (tache_id, etiquette_id),
    FOREIGN KEY (tache_id) REFERENCES taches(id) ON DELETE CASCADE,
    FOREIGN KEY (etiquette_id) REFERENCES etiquettes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- Données de test
-- =====================================================

-- Insertion des projets
INSERT INTO projets (nom, description, couleur) VALUES
('Site Web E-commerce', 'Développement du nouveau site de vente en ligne', '#007bff'),
('Application Mobile', 'Application iOS et Android pour les clients', '#28a745'),
('Refonte UI/UX', 'Modernisation de l\'interface utilisateur', '#dc3545'),
('Documentation', 'Rédaction de la documentation technique', '#ffc107');

-- Insertion des étiquettes
INSERT INTO etiquettes (nom, couleur) VALUES
('Urgent', '#dc3545'),
('Bug', '#fd7e14'),
('Amélioration', '#28a745'),
('Documentation', '#17a2b8'),
('Design', '#6f42c1'),
('Backend', '#343a40'),
('Frontend', '#007bff');

-- Insertion des tâches
INSERT INTO taches (projet_id, titre, description, priorite, statut, date_echeance, ordre) VALUES
(1, 'Créer la page d\'accueil', 'Développer la page d\'accueil avec le carrousel et les produits en vedette', 'haute', 'en_cours', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 1),
(1, 'Intégrer le système de paiement', 'Intégration de Stripe pour les paiements en ligne', 'haute', 'a_faire', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 2),
(1, 'Optimiser les images', 'Compression et lazy loading des images produits', 'normale', 'a_faire', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 3),
(2, 'Maquettes Figma', 'Créer les maquettes de l\'application mobile', 'normale', 'termine', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1),
(2, 'Développer l\'écran de connexion', 'Page de login avec authentification biométrique', 'haute', 'en_cours', DATE_ADD(CURDATE(), INTERVAL 2 DAY), 2),
(2, 'Tests unitaires', 'Écrire les tests pour les composants principaux', 'basse', 'a_faire', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 3),
(3, 'Audit UX actuel', 'Analyser les points faibles de l\'interface actuelle', 'normale', 'termine', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 1),
(3, 'Nouvelle charte graphique', 'Définir les couleurs et typographies', 'haute', 'en_cours', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 2),
(3, 'Prototypes interactifs', 'Créer des prototypes cliquables', 'normale', 'a_faire', DATE_ADD(CURDATE(), INTERVAL 8 DAY), 3),
(4, 'Guide d\'installation', 'Rédiger le guide d\'installation pour les développeurs', 'basse', 'a_faire', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1),
(4, 'Documentation API', 'Documenter tous les endpoints de l\'API REST', 'normale', 'en_cours', DATE_ADD(CURDATE(), INTERVAL 6 DAY), 2);

-- Liaison tâches-étiquettes
INSERT INTO taches_etiquettes (tache_id, etiquette_id) VALUES
(1, 7), -- Page d'accueil - Frontend
(2, 6), -- Paiement - Backend
(2, 1), -- Paiement - Urgent
(3, 3), -- Images - Amélioration
(4, 5), -- Maquettes - Design
(5, 7), -- Login - Frontend
(5, 6), -- Login - Backend
(6, 4), -- Tests - Documentation
(7, 5), -- Audit - Design
(8, 5), -- Charte - Design
(8, 1), -- Charte - Urgent
(9, 5), -- Prototypes - Design
(10, 4), -- Guide - Documentation
(11, 4), -- API Doc - Documentation
(11, 6); -- API Doc - Backend
