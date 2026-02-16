<?php
/**
 * Bootstrap - Chargement des variables d'environnement
 * Gestionnaire de Tâches - TaskFlow
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
