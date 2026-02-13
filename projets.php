<?php
/**
 * Gestion des Projets
 * Gestionnaire de Tâches
 */

require_once 'config/database.php';

// Récupérer les projets avec le nombre de tâches
$sql = "SELECT p.*,
        COUNT(t.id) as total_taches,
        SUM(CASE WHEN t.statut = 'termine' THEN 1 ELSE 0 END) as taches_terminees,
        SUM(CASE WHEN t.statut = 'en_cours' THEN 1 ELSE 0 END) as taches_en_cours,
        SUM(CASE WHEN t.statut = 'a_faire' THEN 1 ELSE 0 END) as taches_a_faire
        FROM projets p
        LEFT JOIN taches t ON p.id = t.projet_id
        GROUP BY p.id
        ORDER BY p.date_creation DESC";

$projets = $pdo->query($sql)->fetchAll();

// Récupérer les étiquettes
$etiquettes = $pdo->query("SELECT * FROM etiquettes ORDER BY nom")->fetchAll();

require_once 'includes/header.php';
?>

<!-- En-tête de page -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-folder me-2 text-primary"></i>Mes Projets
        </h1>
        <p class="text-muted mb-0">
            <?php echo count($projets); ?> projet(s) au total
        </p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProjet">
            <i class="bi bi-plus-lg me-1"></i> Nouveau Projet
        </button>
    </div>
</div>

<!-- Liste des projets -->
<div class="row g-4">
    <?php if (empty($projets)): ?>
        <div class="col-12">
            <div class="empty-state">
                <i class="bi bi-folder-plus"></i>
                <p>Aucun projet pour le moment</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProjet">
                    <i class="bi bi-plus-lg me-1"></i> Créer mon premier projet
                </button>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($projets as $projet): ?>
            <?php
            $progression = $projet['total_taches'] > 0
                ? round(($projet['taches_terminees'] / $projet['total_taches']) * 100)
                : 0;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card projet-card h-100" style="--projet-couleur: <?php echo $projet['couleur']; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="projet-couleur-preview" style="background-color: <?php echo $projet['couleur']; ?>"></div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="modifierProjet(<?php echo $projet['id']; ?>)">
                                            <i class="bi bi-pencil me-2"></i>Modifier
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" onclick="supprimerProjet(<?php echo $projet['id']; ?>)">
                                            <i class="bi bi-trash me-2"></i>Supprimer
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <h5 class="card-title"><?php echo htmlspecialchars($projet['nom']); ?></h5>

                        <?php if ($projet['description']): ?>
                            <p class="card-text text-muted small text-truncate-2">
                                <?php echo htmlspecialchars($projet['description']); ?>
                            </p>
                        <?php endif; ?>

                        <!-- Statistiques du projet -->
                        <div class="d-flex gap-3 mb-3 text-center">
                            <div class="flex-fill">
                                <div class="h5 mb-0 text-secondary"><?php echo $projet['taches_a_faire']; ?></div>
                                <small class="text-muted">À faire</small>
                            </div>
                            <div class="flex-fill">
                                <div class="h5 mb-0 text-primary"><?php echo $projet['taches_en_cours']; ?></div>
                                <small class="text-muted">En cours</small>
                            </div>
                            <div class="flex-fill">
                                <div class="h5 mb-0 text-success"><?php echo $projet['taches_terminees']; ?></div>
                                <small class="text-muted">Terminées</small>
                            </div>
                        </div>

                        <!-- Barre de progression -->
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: <?php echo $progression; ?>%"
                                 aria-valuenow="<?php echo $progression; ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small class="text-muted"><?php echo $progression; ?>% terminé</small>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            Créé le <?php echo (new DateTime($projet['date_creation']))->format('d/m/Y'); ?>
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Section Étiquettes -->
<div class="mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4">
            <i class="bi bi-tags me-2 text-primary"></i>Étiquettes
        </h2>
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEtiquette">
            <i class="bi bi-plus-lg me-1"></i> Nouvelle Étiquette
        </button>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($etiquettes as $etiquette): ?>
            <span class="badge fs-6 py-2 px-3" style="background-color: <?php echo $etiquette['couleur']; ?>">
                <?php echo htmlspecialchars($etiquette['nom']); ?>
                <button class="btn-close btn-close-white ms-2" style="font-size: 0.6em;"
                        onclick="supprimerEtiquette(<?php echo $etiquette['id']; ?>)"></button>
            </span>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Ajouter/Modifier Projet -->
<div class="modal fade" id="modalProjet" tabindex="-1" aria-labelledby="modalProjetLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalProjetLabel">
                    <i class="bi bi-folder-plus me-2"></i>Nouveau Projet
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form id="formProjet" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="projetId">

                    <div class="mb-3">
                        <label for="projetNom" class="form-label">Nom du projet <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="projetNom" name="nom" required placeholder="Ex: Site Web E-commerce">
                    </div>

                    <div class="mb-3">
                        <label for="projetDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="projetDescription" name="description" rows="3" placeholder="Décrivez le projet..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="projetCouleur" class="form-label">Couleur</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" class="form-control form-control-color" id="projetCouleur" name="couleur" value="#007bff">
                            <span class="text-muted small">Choisissez une couleur pour identifier le projet</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajouter Étiquette -->
<div class="modal fade" id="modalEtiquette" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-tag me-2"></i>Nouvelle Étiquette</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEtiquette" method="POST" action="api/etiquettes.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="etiquetteNom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="etiquetteNom" name="nom" required placeholder="Ex: Urgent">
                    </div>
                    <div class="mb-3">
                        <label for="etiquetteCouleur" class="form-label">Couleur</label>
                        <input type="color" class="form-control form-control-color w-100" id="etiquetteCouleur" name="couleur" value="#6c757d">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Gestion du formulaire d'étiquette
document.getElementById('formEtiquette')?.addEventListener('submit', function(e) {
    e.preventDefault();

    fetch('api/etiquettes.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        if (data.succes) {
            afficherNotification('Succès', data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalEtiquette')).hide();
            setTimeout(() => location.reload(), 500);
        } else {
            afficherNotification('Erreur', data.message, 'danger');
        }
    });
});

function supprimerEtiquette(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette étiquette ?')) {
        fetch(`api/etiquettes.php?id=${id}`, { method: 'DELETE' })
            .then(response => response.json())
            .then(data => {
                if (data.succes) {
                    afficherNotification('Succès', data.message, 'success');
                    setTimeout(() => location.reload(), 500);
                }
            });
    }
}

// Réinitialiser le modal projet à la fermeture
document.getElementById('modalProjet')?.addEventListener('hidden.bs.modal', function() {
    document.getElementById('formProjet').reset();
    document.getElementById('projetId').value = '';
    document.getElementById('modalProjetLabel').innerHTML = '<i class="bi bi-folder-plus me-2"></i>Nouveau Projet';
});
</script>

<?php require_once 'includes/footer.php'; ?>
