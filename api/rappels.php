<?php
/**
 * API REST pour les Rappels par email
 * Gestionnaire de Tâches - TaskFlow
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../cron/rappels.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            $forcer = isset($_POST['forcer']) && $_POST['forcer'] === '1';
            $resultat = envoyerRappels($forcer);
            echo json_encode($resultat);
            break;

        case 'GET':
            // Récupérer le nombre de tâches en retard et le dernier envoi
            $stmtRetard = $pdo->query(
                "SELECT COUNT(*) FROM taches WHERE date_echeance < CURDATE() AND statut != 'termine'"
            );
            $nbEnRetard = $stmtRetard->fetchColumn();

            $stmtDernier = $pdo->query(
                "SELECT date_envoi, nb_taches, statut FROM rappels_log ORDER BY date_envoi DESC LIMIT 1"
            );
            $dernierRappel = $stmtDernier->fetch();

            echo json_encode([
                'succes' => true,
                'nb_taches_en_retard' => (int) $nbEnRetard,
                'dernier_rappel' => $dernierRappel ?: null
            ]);
            break;

        default:
            echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    echo json_encode(['succes' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
