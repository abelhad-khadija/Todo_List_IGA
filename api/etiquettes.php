<?php
/**
 * API REST pour les Étiquettes
 * Gestionnaire de Tâches
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Récupérer une étiquette spécifique ou toutes
            if (isset($_GET['id'])) {
                $id = (int) $_GET['id'];

                $stmt = $pdo->prepare("SELECT * FROM etiquettes WHERE id = ?");
                $stmt->execute([$id]);
                $etiquette = $stmt->fetch();

                if ($etiquette) {
                    echo json_encode(['succes' => true, 'etiquette' => $etiquette]);
                } else {
                    echo json_encode(['succes' => false, 'message' => 'Étiquette non trouvée']);
                }
            } else {
                // Liste toutes les étiquettes
                $etiquettes = $pdo->query("SELECT * FROM etiquettes ORDER BY nom")->fetchAll();
                echo json_encode(['succes' => true, 'etiquettes' => $etiquettes]);
            }
            break;

        case 'POST':
            // Créer ou modifier une étiquette
            $id = $_POST['id'] ?? null;
            $nom = trim($_POST['nom'] ?? '');
            $couleur = $_POST['couleur'] ?? '#6c757d';

            // Validation
            if (empty($nom)) {
                echo json_encode(['succes' => false, 'message' => 'Le nom est obligatoire']);
                break;
            }

            if ($id) {
                // Modification
                $sql = "UPDATE etiquettes SET nom = ?, couleur = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $couleur, $id]);

                $message = 'Étiquette modifiée avec succès';
            } else {
                // Création
                $sql = "INSERT INTO etiquettes (nom, couleur) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $couleur]);

                $id = $pdo->lastInsertId();
                $message = 'Étiquette créée avec succès';
            }

            echo json_encode(['succes' => true, 'message' => $message, 'id' => $id]);
            break;

        case 'DELETE':
            // Supprimer une étiquette
            $id = $_GET['id'] ?? null;

            if (!$id) {
                echo json_encode(['succes' => false, 'message' => 'ID requis']);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM etiquettes WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['succes' => true, 'message' => 'Étiquette supprimée avec succès']);
            } else {
                echo json_encode(['succes' => false, 'message' => 'Étiquette non trouvée']);
            }
            break;

        default:
            echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    echo json_encode(['succes' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
