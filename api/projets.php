<?php
/**
 * API REST pour les Projets
 * Gestionnaire de Tâches
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Récupérer un projet spécifique
            if (isset($_GET['id'])) {
                $id = (int) $_GET['id'];

                $stmt = $pdo->prepare("SELECT * FROM projets WHERE id = ?");
                $stmt->execute([$id]);
                $projet = $stmt->fetch();

                if ($projet) {
                    echo json_encode(['succes' => true, 'projet' => $projet]);
                } else {
                    echo json_encode(['succes' => false, 'message' => 'Projet non trouvé']);
                }
            } else {
                // Liste tous les projets
                $projets = $pdo->query("SELECT * FROM projets ORDER BY nom")->fetchAll();
                echo json_encode(['succes' => true, 'projets' => $projets]);
            }
            break;

        case 'POST':
            // Créer ou modifier un projet
            $id = $_POST['id'] ?? null;
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $couleur = $_POST['couleur'] ?? '#007bff';

            // Validation
            if (empty($nom)) {
                echo json_encode(['succes' => false, 'message' => 'Le nom est obligatoire']);
                break;
            }

            if ($id) {
                // Modification
                $sql = "UPDATE projets SET nom = ?, description = ?, couleur = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $description, $couleur, $id]);

                $message = 'Projet modifié avec succès';
            } else {
                // Création
                $sql = "INSERT INTO projets (nom, description, couleur) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $description, $couleur]);

                $id = $pdo->lastInsertId();
                $message = 'Projet créé avec succès';
            }

            echo json_encode(['succes' => true, 'message' => $message, 'id' => $id]);
            break;

        case 'DELETE':
            // Supprimer un projet
            $id = $_GET['id'] ?? null;

            if (!$id) {
                echo json_encode(['succes' => false, 'message' => 'ID requis']);
                break;
            }

            // Vérifier s'il y a des tâches associées
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM taches WHERE projet_id = ?");
            $stmtCheck->execute([$id]);
            $nbTaches = $stmtCheck->fetchColumn();

            if ($nbTaches > 0) {
                // Supprimer aussi les tâches associées (ON DELETE CASCADE devrait s'en charger)
            }

            $stmt = $pdo->prepare("DELETE FROM projets WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['succes' => true, 'message' => 'Projet supprimé avec succès']);
            } else {
                echo json_encode(['succes' => false, 'message' => 'Projet non trouvé']);
            }
            break;

        default:
            echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    echo json_encode(['succes' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
