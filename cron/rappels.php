<?php
/**
 * Script de rappels par email pour les tâches en retard
 * Gestionnaire de Tâches - TaskFlow
 *
 * Usage :
 *   - CLI (cron) : php cron/rappels.php
 *   - Via API    : inclus par api/rappels.php
 *
 * Cron (quotidien à 8h) :
 *   0 8 * * * php /chemin/vers/cron/rappels.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Envoie un email de rappel pour les tâches en retard
 * @param bool $forcer Ignorer la vérification du dernier envoi
 * @return array Résultat de l'opération
 */
function envoyerRappels($forcer = false) {
    global $pdo;

    // Vérifier si un rappel a déjà été envoyé aujourd'hui
    if (!$forcer) {
        $stmtLog = $pdo->prepare(
            "SELECT COUNT(*) FROM rappels_log WHERE DATE(date_envoi) = CURDATE() AND statut = 'envoye'"
        );
        $stmtLog->execute();
        if ($stmtLog->fetchColumn() > 0) {
            return [
                'succes' => false,
                'message' => 'Un rappel a déjà été envoyé aujourd\'hui.',
                'nb_taches' => 0
            ];
        }
    }

    // Récupérer les tâches en retard (non terminées avec date d'échéance dépassée)
    $sql = "SELECT t.*, p.nom as projet_nom, p.couleur as projet_couleur,
            DATEDIFF(CURDATE(), t.date_echeance) as jours_retard
            FROM taches t
            LEFT JOIN projets p ON t.projet_id = p.id
            WHERE t.date_echeance < CURDATE()
            AND t.statut != 'termine'
            ORDER BY t.date_echeance ASC";

    $tachesEnRetard = $pdo->query($sql)->fetchAll();

    if (empty($tachesEnRetard)) {
        return [
            'succes' => true,
            'message' => 'Aucune tâche en retard. Aucun email envoyé.',
            'nb_taches' => 0
        ];
    }

    // Construire l'email HTML
    $nbTaches = count($tachesEnRetard);
    $sujet = "TaskFlow - Rappel : {$nbTaches} tâche(s) en retard";
    $corpsHtml = construireEmailHtml($tachesEnRetard);

    // Envoyer l'email
    try {
        $mail = new PHPMailer(true);

        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        // Destinataires
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NOM);
        $mail->addAddress(MAIL_DESTINATAIRE, MAIL_NOM_DESTINATAIRE);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = $sujet;
        $mail->Body    = $corpsHtml;
        $mail->AltBody = "Vous avez {$nbTaches} tâche(s) en retard dans TaskFlow.";

        $mail->send();

        // Logger le succès
        $stmtLog = $pdo->prepare(
            "INSERT INTO rappels_log (nb_taches, email_destinataire, statut) VALUES (?, ?, 'envoye')"
        );
        $stmtLog->execute([$nbTaches, MAIL_DESTINATAIRE]);

        return [
            'succes' => true,
            'message' => "Rappel envoyé avec succès pour {$nbTaches} tâche(s) en retard.",
            'nb_taches' => $nbTaches
        ];

    } catch (Exception $e) {
        // Logger l'échec
        $stmtLog = $pdo->prepare(
            "INSERT INTO rappels_log (nb_taches, email_destinataire, statut) VALUES (?, ?, 'echec')"
        );
        $stmtLog->execute([$nbTaches, MAIL_DESTINATAIRE]);

        return [
            'succes' => false,
            'message' => "Erreur lors de l'envoi : " . $mail->ErrorInfo,
            'nb_taches' => $nbTaches
        ];
    }
}

/**
 * Construit le contenu HTML de l'email de rappel
 */
function construireEmailHtml($taches) {
    $nbTaches = count($taches);
    $date = date('d/m/Y');

    $prioriteLabels = ['basse' => 'Basse', 'normale' => 'Normale', 'haute' => 'Haute'];
    $prioriteCouleurs = ['basse' => '#28a745', 'normale' => '#ffc107', 'haute' => '#dc3545'];
    $statutLabels = ['a_faire' => 'À faire', 'en_cours' => 'En cours'];

    $lignesTaches = '';
    foreach ($taches as $tache) {
        $projet = $tache['projet_nom'] ? htmlspecialchars($tache['projet_nom']) : '<em>Aucun</em>';
        $priorite = $prioriteLabels[$tache['priorite']];
        $prioriteCouleur = $prioriteCouleurs[$tache['priorite']];
        $statut = $statutLabels[$tache['statut']] ?? $tache['statut'];
        $dateEcheance = (new DateTime($tache['date_echeance']))->format('d/m/Y');
        $joursRetard = $tache['jours_retard'];

        $lignesTaches .= "
        <tr>
            <td style='padding: 12px; border-bottom: 1px solid #eee;'>
                <strong>" . htmlspecialchars($tache['titre']) . "</strong>
            </td>
            <td style='padding: 12px; border-bottom: 1px solid #eee;'>{$projet}</td>
            <td style='padding: 12px; border-bottom: 1px solid #eee;'>
                <span style='background-color: {$prioriteCouleur}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px;'>{$priorite}</span>
            </td>
            <td style='padding: 12px; border-bottom: 1px solid #eee;'>{$statut}</td>
            <td style='padding: 12px; border-bottom: 1px solid #eee;'>{$dateEcheance}</td>
            <td style='padding: 12px; border-bottom: 1px solid #eee;'>
                {$joursRetard} jour(s)
            </td>
        </tr>";
    }

    return "
    <!DOCTYPE html>
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='font-family: Inter, Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 20px;'>
        <div style='max-width: 700px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>

            <!-- En-tête -->
            <div style='background: linear-gradient(135deg, #0d6efd, #0b5ed7); padding: 30px; text-align: center;'>
                <h1 style='color: white; margin: 0; font-size: 24px;'>TaskFlow - Rappel</h1>
                <p style='color: rgba(255,255,255,0.85); margin: 8px 0 0;'>{$date}</p>
            </div>

            <!-- Contenu -->
            <div style='padding: 30px;'>
                <div style='background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; margin-bottom: 25px;'>
                    <strong style='color: #856404;'>Attention !</strong>
                    <span style='color: #856404;'>Vous avez <strong>{$nbTaches} tâche(s)</strong> en retard.</span>
                </div>

                <table style='width: 100%; border-collapse: collapse;'>
                    <thead>
                        <tr style='background-color: #f8f9fa;'>
                            <th style='padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;'>Tâche</th>
                            <th style='padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;'>Projet</th>
                            <th style='padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;'>Priorité</th>
                            <th style='padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;'>Statut</th>
                            <th style='padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;'>Échéance</th>
                            <th style='padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;'>Retard</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$lignesTaches}
                    </tbody>
                </table>
            </div>

            <!-- Pied -->
            <div style='background-color: #f8f9fa; padding: 20px; text-align: center; color: #6c757d; font-size: 13px;'>
                <p style='margin: 0;'>Cet email a été envoyé automatiquement par <strong>TaskFlow</strong>.</p>
                <p style='margin: 5px 0 0;'>Connectez-vous à votre application pour gérer vos tâches.</p>
            </div>
        </div>
    </body>
    </html>";
}

// Exécution directe en CLI
if (php_sapi_name() === 'cli') {
    $resultat = envoyerRappels();
    echo $resultat['message'] . PHP_EOL;
    exit($resultat['succes'] ? 0 : 1);
}
?>
