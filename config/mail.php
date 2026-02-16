<?php
/**
 * Configuration email - Rappels de tâches
 * Gestionnaire de Tâches - TaskFlow
 *
 * Modifiez les paramètres ci-dessous avec vos identifiants SMTP.
 * Pour Gmail : activez "Mots de passe d'application" dans votre compte Google.
 */

// Paramètres SMTP
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'votre-email@gmail.com');
define('MAIL_PASSWORD', 'votre-mot-de-passe-application');
define('MAIL_ENCRYPTION', 'tls'); // 'tls' ou 'ssl'

// Destinataire des rappels
define('MAIL_DESTINATAIRE', 'votre-email@gmail.com');
define('MAIL_NOM_DESTINATAIRE', 'Gestionnaire TaskFlow');

// Expéditeur
define('MAIL_FROM', 'votre-email@gmail.com');
define('MAIL_FROM_NOM', 'TaskFlow - Rappels');
?>
