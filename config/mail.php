<?php
/**
 * Configuration email - Rappels de tâches
 * Gestionnaire de Tâches - TaskFlow
 *
 * Les paramètres sont chargés depuis le fichier .env à la racine du projet.
 * Pour Gmail : activez "Mots de passe d'application" dans votre compte Google.
 */

require_once __DIR__ . '/bootstrap.php';

// Paramètres SMTP
define('MAIL_HOST', $_ENV['MAIL_HOST']);
define('MAIL_PORT', (int) $_ENV['MAIL_PORT']);
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME']);
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD']);
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION']);

// Destinataire des rappels
define('MAIL_DESTINATAIRE', $_ENV['MAIL_DESTINATAIRE']);
define('MAIL_NOM_DESTINATAIRE', $_ENV['MAIL_NOM_DESTINATAIRE']);

// Expéditeur
define('MAIL_FROM', $_ENV['MAIL_FROM']);
define('MAIL_FROM_NOM', $_ENV['MAIL_FROM_NOM']);
?>
