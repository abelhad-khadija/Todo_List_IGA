<?php
/**
 * Vue Kanban
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

// Grouper les tâches par statut
$tachesParStatut = [
    'a_faire' => [],
    'en_cours' => [],
    'termine' => []
];

foreach ($taches as $tache) {
    $tachesParStatut[$tache['statut']][] = $tache;
}

require_once 'includes/header.php';
?>

<!-- En-tête de page -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-kanban me-2 text-primary"></i>Tableau Kanban
        </h1>
        <p class="text-muted mb-0">
            Glissez-déposez les tâches pour changer leur statut
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-outline-primary">
            <i class="bi bi-list-task me-1"></i> Vue Liste
        </a>
    </div>
</div>

<!-- Filtres -->
<div class="filtres-container">
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="filtre-label">Rechercher</label>
            <input type="text" class="form-control" id="recherche" placeholder="Titre de la tâche...">
        </div>
        <div class="col-md-4">
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
        <div class="col-md-4">
            <label class="filtre-label">Priorité</label>
            <select class="form-select filtre-select" id="filtrePriorite">
                <option value="">Toutes</option>
                <option value="haute">Haute</option>
                <option value="normale">Normale</option>
                <option value="basse">Basse</option>
            </select>
        </div>
    </div>
</div>

<!-- Colonnes Kanban -->
<div class="kanban-container">
    <!-- Colonne: À faire -->
    <div class="kanban-colonne" data-statut="a_faire">
        <div class="kanban-header">
            <div class="kanban-titre">
                <i class="bi bi-inbox text-secondary"></i>
                À faire
            </div>
            <span class="kanban-count"><?php echo count($tachesParStatut['a_faire']); ?></span>
        </div>
        <div class="kanban-liste" id="listeAFaire">
            <?php foreach ($tachesParStatut['a_faire'] as $tache): ?>
                <?php echo renderTacheKanban($tache); ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Colonne: En cours -->
    <div class="kanban-colonne" data-statut="en_cours">
        <div class="kanban-header">
            <div class="kanban-titre">
                <i class="bi bi-arrow-repeat text-primary"></i>
                En cours
            </div>
            <span class="kanban-count"><?php echo count($tachesParStatut['en_cours']); ?></span>
        </div>
        <div class="kanban-liste" id="listeEnCours">
            <?php foreach ($tachesParStatut['en_cours'] as $tache): ?>
                <?php echo renderTacheKanban($tache); ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Colonne: Terminé -->
    <div class="kanban-colonne" data-statut="termine">
        <div class="kanban-header">
            <div class="kanban-titre">
                <i class="bi bi-check-circle text-success"></i>
                Terminé
            </div>
            <span class="kanban-count"><?php echo count($tachesParStatut['termine']); ?></span>
        </div>
        <div class="kanban-liste" id="listeTermine">
            <?php foreach ($tachesParStatut['termine'] as $tache): ?>
                <?php echo renderTacheKanban($tache); ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
/**
 * Fonction pour générer le HTML d'une tâche Kanban
 */
function renderTacheKanban($tache) {
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

    $html = '<div class="tache-item"
                 data-id="' . $tache['id'] . '"
                 data-projet="' . $tache['projet_id'] . '"
                 data-priorite="' . $tache['priorite'] . '"
                 data-statut="' . $tache['statut'] . '">';

    // Titre
    $html .= '<div class="tache-titre">' . htmlspecialchars($tache['titre']) . '</div>';

    // Description
    if ($tache['description']) {
        $html .= '<div class="tache-description">' . htmlspecialchars($tache['description']) . '</div>';
    }

    $html .= '<div class="tache-meta">';

    // Projet
    if ($tache['projet_nom']) {
        $html .= '<span class="projet-badge" style="background-color: ' . $tache['projet_couleur'] . '">'
               . htmlspecialchars($tache['projet_nom']) . '</span>';
    }

    // Priorité
    $prioriteLabels = ['basse' => 'Basse', 'normale' => 'Normale', 'haute' => 'Haute'];
    $html .= '<span class="priorite-badge priorite-' . $tache['priorite'] . '">'
           . $prioriteLabels[$tache['priorite']] . '</span>';

    // Date d'échéance
    if ($tache['date_echeance']) {
        $classeDate = $enRetard ? 'en-retard' : ($proche ? 'proche' : '');
        $html .= '<span class="date-echeance ' . $classeDate . '">';
        $html .= '<i class="bi bi-calendar me-1"></i>';
        $html .= (new DateTime($tache['date_echeance']))->format('d/m');
        if ($enRetard) {
            $html .= ' <i class="bi bi-exclamation-circle text-danger"></i>';
        }
        $html .= '</span>';
    }

    $html .= '</div>';

    // Étiquettes
    if ($tache['etiquettes_info']) {
        $html .= '<div class="d-flex flex-wrap gap-1 mt-2">';
        $etiquettes = explode('|', $tache['etiquettes_info']);
        foreach ($etiquettes as $etiq) {
            if ($etiq) {
                list($nom, $couleur) = explode(':', $etiq);
                $html .= '<span class="etiquette" style="background-color: ' . $couleur . '">'
                       . htmlspecialchars($nom) . '</span>';
            }
        }
        $html .= '</div>';
    }

    // Actions
    $html .= '<div class="d-flex justify-content-end gap-1 mt-2">';
    $html .= '<button class="btn btn-sm btn-outline-primary btn-action" onclick="modifierTache(' . $tache['id'] . ')" title="Modifier">';
    $html .= '<i class="bi bi-pencil"></i></button>';
    $html .= '<button class="btn btn-sm btn-outline-danger btn-action" onclick="supprimerTache(' . $tache['id'] . ')" title="Supprimer">';
    $html .= '<i class="bi bi-trash"></i></button>';
    $html .= '</div>';

    $html .= '</div>';

    return $html;
}
?>

<?php require_once 'includes/footer.php'; ?>
