<?php
/**
 * Page d'accueil - Liste des tâches
 * Gestionnaire de Tâches
 */

require_once 'config/database.php';

// Récupérer les projets pour le filtre
$projets = $pdo->query("SELECT * FROM projets ORDER BY nom")->fetchAll();

// Récupérer toutes les tâches avec leurs projets et étiquettes
$sql = "SELECT t.*, p.nom as projet_nom, p.couleur as projet_couleur,
        GROUP_CONCAT(DISTINCT e.id) as etiquette_ids,
        GROUP_CONCAT(DISTINCT CONCAT(e.nom, ':', e.couleur) SEPARATOR '|') as etiquettes_info
        FROM taches t
        LEFT JOIN projets p ON t.projet_id = p.id
        LEFT JOIN taches_etiquettes te ON t.id = te.tache_id
        LEFT JOIN etiquettes e ON te.etiquette_id = e.id
        GROUP BY t.id
        ORDER BY t.ordre ASC, t.date_creation DESC";

$taches = $pdo->query($sql)->fetchAll();

// Statistiques rapides
$stats = [
    'total' => count($taches),
    'a_faire' => count(array_filter($taches, fn($t) => $t['statut'] === 'a_faire')),
    'en_cours' => count(array_filter($taches, fn($t) => $t['statut'] === 'en_cours')),
    'termine' => count(array_filter($taches, fn($t) => $t['statut'] === 'termine'))
];

require_once 'includes/header.php';
?>

<!-- En-tête de page -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-list-task me-2 text-primary"></i>Mes Tâches
        </h1>
        <p class="text-muted mb-0">
            <?php echo $stats['total']; ?> tâche(s) au total
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="kanban.php" class="btn btn-outline-primary">
            <i class="bi bi-kanban me-1"></i> Vue Kanban
        </a>
    </div>
</div>

<!-- Statistiques rapides -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 bg-secondary bg-opacity-10">
            <div class="stat-icon text-secondary"><i class="bi bi-inbox"></i></div>
            <div class="stat-nombre"><?php echo $stats['a_faire']; ?></div>
            <div class="stat-label">À faire</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 bg-primary bg-opacity-10">
            <div class="stat-icon text-primary"><i class="bi bi-arrow-repeat"></i></div>
            <div class="stat-nombre"><?php echo $stats['en_cours']; ?></div>
            <div class="stat-label">En cours</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 bg-success bg-opacity-10">
            <div class="stat-icon text-success"><i class="bi bi-check-circle"></i></div>
            <div class="stat-nombre"><?php echo $stats['termine']; ?></div>
            <div class="stat-label">Terminées</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 bg-info bg-opacity-10">
            <div class="stat-icon text-info"><i class="bi bi-folder"></i></div>
            <div class="stat-nombre"><?php echo count($projets); ?></div>
            <div class="stat-label">Projets</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filtres-container">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="filtre-label">Rechercher</label>
            <input type="text" class="form-control" id="recherche" placeholder="Titre de la tâche...">
        </div>
        <div class="col-md-3">
            <label class="filtre-label">Projet</label>
            <select class="form-select filtre-select" id="filtreProjet">
                <option value="">Tous les projets</option>
                <?php foreach ($projets as $projet): ?>
                    <option value="<?php echo $projet['id']; ?>">
                        <?php echo htmlspecialchars($projet['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="filtre-label">Priorité</label>
            <select class="form-select filtre-select" id="filtrePriorite">
                <option value="">Toutes</option>
                <option value="haute">Haute</option>
                <option value="normale">Normale</option>
                <option value="basse">Basse</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="filtre-label">Statut</label>
            <select class="form-select filtre-select" id="filtreStatut">
                <option value="">Tous</option>
                <option value="a_faire">À faire</option>
                <option value="en_cours">En cours</option>
                <option value="termine">Terminé</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
            </button>
        </div>
    </div>
</div>

<!-- Liste des tâches -->
<div class="taches-liste" id="listeTaches">
    <?php if (empty($taches)): ?>
        <div class="empty-state">
            <i class="bi bi-clipboard-check"></i>
            <p>Aucune tâche pour le moment</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTache">
                <i class="bi bi-plus-lg me-1"></i> Créer ma première tâche
            </button>
        </div>
    <?php else: ?>
        <?php foreach ($taches as $tache): ?>
            <?php
            // Calculer si en retard
            $enRetard = false;
            $proche = false;
            if ($tache['date_echeance'] && $tache['statut'] !== 'termine') {
                $dateEcheance = new DateTime($tache['date_echeance']);
                $aujourdhui = new DateTime('today');
                $diff = $aujourdhui->diff($dateEcheance);
                $enRetard = $dateEcheance < $aujourdhui;
                $proche = !$enRetard && $diff->days <= 3;
            }
            ?>
            <div class="tache-item fade-in"
                 data-id="<?php echo $tache['id']; ?>"
                 data-projet="<?php echo $tache['projet_id']; ?>"
                 data-priorite="<?php echo $tache['priorite']; ?>"
                 data-statut="<?php echo $tache['statut']; ?>">

                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="tache-titre">
                            <?php if ($tache['statut'] === 'termine'): ?>
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span class="text-decoration-line-through text-muted">
                                    <?php echo htmlspecialchars($tache['titre']); ?>
                                </span>
                            <?php else: ?>
                                <?php echo htmlspecialchars($tache['titre']); ?>
                            <?php endif; ?>
                        </div>

                        <?php if ($tache['description']): ?>
                            <div class="tache-description">
                                <?php echo htmlspecialchars($tache['description']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="tache-meta">
                            <!-- Projet -->
                            <?php if ($tache['projet_nom']): ?>
                                <span class="projet-badge" style="background-color: <?php echo $tache['projet_couleur']; ?>">
                                    <?php echo htmlspecialchars($tache['projet_nom']); ?>
                                </span>
                            <?php endif; ?>

                            <!-- Priorité -->
                            <span class="priorite-badge priorite-<?php echo $tache['priorite']; ?>">
                                <?php
                                $prioriteLabels = ['basse' => 'Basse', 'normale' => 'Normale', 'haute' => 'Haute'];
                                echo $prioriteLabels[$tache['priorite']];
                                ?>
                            </span>

                            <!-- Statut -->
                            <span class="statut-badge statut-<?php echo $tache['statut']; ?>">
                                <?php
                                $statutLabels = ['a_faire' => 'À faire', 'en_cours' => 'En cours', 'termine' => 'Terminé'];
                                echo $statutLabels[$tache['statut']];
                                ?>
                            </span>

                            <!-- Date d'échéance -->
                            <?php if ($tache['date_echeance']): ?>
                                <span class="date-echeance <?php echo $enRetard ? 'en-retard' : ($proche ? 'proche' : ''); ?>">
                                    <i class="bi bi-calendar me-1"></i>
                                    <?php echo (new DateTime($tache['date_echeance']))->format('d/m/Y'); ?>
                                    <?php if ($enRetard): ?>
                                        <span class="badge bg-danger ms-1">En retard</span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>

                            <!-- Étiquettes -->
                            <?php if ($tache['etiquettes_info']): ?>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php
                                    $etiquettes = explode('|', $tache['etiquettes_info']);
                                    foreach ($etiquettes as $etiq):
                                        if ($etiq):
                                            list($nom, $couleur) = explode(':', $etiq);
                                    ?>
                                        <span class="etiquette" style="background-color: <?php echo $couleur; ?>">
                                            <?php echo htmlspecialchars($nom); ?>
                                        </span>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-primary btn-action" onclick="modifierTache(<?php echo $tache['id']; ?>)" title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-action" onclick="supprimerTache(<?php echo $tache['id']; ?>)" title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
