<?php
/**
 * API REST pour les Tâches
 * Gestionnaire de Tâches
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Récupérer une tâche spécifique
            if (isset($_GET['id'])) {
                $id = (int) $_GET['id'];

                $stmt = $pdo->prepare("SELECT * FROM taches WHERE id = ?");
                $stmt->execute([$id]);
                $tache = $stmt->fetch();

                if ($tache) {
                    // Récupérer les étiquettes de la tâche
                    $stmtEtiq = $pdo->prepare("SELECT etiquette_id FROM taches_etiquettes WHERE tache_id = ?");
                    $stmtEtiq->execute([$id]);
                    $tache['etiquettes'] = $stmtEtiq->fetchAll(PDO::FETCH_COLUMN);

                    echo json_encode(['succes' => true, 'tache' => $tache]);
                } else {
                    echo json_encode(['succes' => false, 'message' => 'Tâche non trouvée']);
                }
            }
            break;

        case 'POST':
            // Créer ou modifier une tâche
            $id = $_POST['id'] ?? null;
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $projet_id = !empty($_POST['projet_id']) ? (int) $_POST['projet_id'] : null;
            $priorite = $_POST['priorite'] ?? 'normale';
            $statut = $_POST['statut'] ?? 'a_faire';
            $date_echeance = !empty($_POST['date_echeance']) ? $_POST['date_echeance'] : null;
            $etiquettes = $_POST['etiquettes'] ?? [];
            $ordre = isset($_POST['ordre']) ? (int) $_POST['ordre'] : 0;
            $action = $_POST['action'] ?? '';

            // Mise à jour rapide du statut (drag & drop)
            if ($action === 'updateStatut' && $id) {
                $stmt = $pdo->prepare("UPDATE taches SET statut = ?, ordre = ? WHERE id = ?");
                $stmt->execute([$statut, $ordre, $id]);

                echo json_encode(['succes' => true, 'message' => 'Statut mis à jour']);
                break;
            }

            // Validation
            if (empty($titre)) {
                echo json_encode(['succes' => false, 'message' => 'Le titre est obligatoire']);
                break;
            }

            if ($id) {
                // Modification
                $sql = "UPDATE taches SET
                        titre = ?, description = ?, projet_id = ?, priorite = ?,
                        statut = ?, date_echeance = ?, ordre = ?
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$titre, $description, $projet_id, $priorite, $statut, $date_echeance, $ordre, $id]);

                $message = 'Tâche modifiée avec succès';
            } else {
                // Création
                $sql = "INSERT INTO taches (titre, description, projet_id, priorite, statut, date_echeance, ordre)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$titre, $description, $projet_id, $priorite, $statut, $date_echeance, $ordre]);

                $id = $pdo->lastInsertId();
                $message = 'Tâche créée avec succès';
            }

            // Mettre à jour les étiquettes
            $pdo->prepare("DELETE FROM taches_etiquettes WHERE tache_id = ?")->execute([$id]);

            if (!empty($etiquettes)) {
                $stmtEtiq = $pdo->prepare("INSERT INTO taches_etiquettes (tache_id, etiquette_id) VALUES (?, ?)");
                foreach ($etiquettes as $etiq_id) {
                    $stmtEtiq->execute([$id, (int) $etiq_id]);
                }
            }

            echo json_encode(['succes' => true, 'message' => $message, 'id' => $id]);
            break;

        case 'DELETE':
            // Supprimer une tâche
            $id = $_GET['id'] ?? null;

            if (!$id) {
                echo json_encode(['succes' => false, 'message' => 'ID requis']);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM taches WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['succes' => true, 'message' => 'Tâche supprimée avec succès']);
            } else {
                echo json_encode(['succes' => false, 'message' => 'Tâche non trouvée']);
            }
            break;

        default:
            echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    echo json_encode(['succes' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
