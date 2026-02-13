<?php
/**
 * Page des Statistiques
 * Gestionnaire de Tâches
 */

require_once 'config/database.php';

// Statistiques générales
$stats = [];

// Total des tâches
$stats['total'] = $pdo->query("SELECT COUNT(*) FROM taches")->fetchColumn();

// Par statut
$statsStatut = $pdo->query("
    SELECT statut, COUNT(*) as count
    FROM taches
    GROUP BY statut
")->fetchAll(PDO::FETCH_KEY_PAIR);

$stats['a_faire'] = $statsStatut['a_faire'] ?? 0;
$stats['en_cours'] = $statsStatut['en_cours'] ?? 0;
$stats['termine'] = $statsStatut['termine'] ?? 0;

// Taux de complétion
$stats['taux_completion'] = $stats['total'] > 0
    ? round(($stats['termine'] / $stats['total']) * 100, 1)
    : 0;

// Tâches en retard
$stats['en_retard'] = $pdo->query("
    SELECT COUNT(*)
    FROM taches
    WHERE date_echeance < CURDATE() AND statut != 'termine'
")->fetchColumn();

// Par priorité
$statsPriorite = $pdo->query("
    SELECT priorite, COUNT(*) as count
    FROM taches
    WHERE statut != 'termine'
    GROUP BY priorite
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Tâches par projet
$tachesParProjet = $pdo->query("
    SELECT p.nom, p.couleur,
           COUNT(t.id) as total,
           SUM(CASE WHEN t.statut = 'termine' THEN 1 ELSE 0 END) as terminees
    FROM projets p
    LEFT JOIN taches t ON p.id = t.projet_id
    GROUP BY p.id
    ORDER BY total DESC
")->fetchAll();

// Tâches créées par semaine (4 dernières semaines)
$tachesParSemaine = $pdo->query("
    SELECT
        YEARWEEK(date_creation, 1) as semaine,
        DATE_FORMAT(MIN(date_creation), '%d/%m') as debut_semaine,
        COUNT(*) as total,
        SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END) as terminees
    FROM taches
    WHERE date_creation >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK)
    GROUP BY YEARWEEK(date_creation, 1)
    ORDER BY semaine
")->fetchAll();

// Tâches terminées ce mois
$stats['terminees_mois'] = $pdo->query("
    SELECT COUNT(*)
    FROM taches
    WHERE statut = 'termine'
    AND MONTH(date_modification) = MONTH(CURDATE())
    AND YEAR(date_modification) = YEAR(CURDATE())
")->fetchColumn();

require_once 'includes/header.php';
?>

<!-- En-tête de page -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-bar-chart me-2 text-primary"></i>Statistiques
        </h1>
        <p class="text-muted mb-0">
            Vue d'ensemble de votre productivité
        </p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Imprimer
        </button>
    </div>
</div>

<!-- Cartes statistiques -->
<div class="row g-4 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 bg-primary bg-opacity-10">
            <div class="stat-icon text-primary"><i class="bi bi-list-check"></i></div>
            <div class="stat-nombre text-primary"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Tâches</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 bg-success bg-opacity-10">
            <div class="stat-icon text-success"><i class="bi bi-check-circle"></i></div>
            <div class="stat-nombre text-success"><?php echo $stats['taux_completion']; ?>%</div>
            <div class="stat-label">Taux Complétion</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 bg-warning bg-opacity-10">
            <div class="stat-icon text-warning"><i class="bi bi-arrow-repeat"></i></div>
            <div class="stat-nombre text-warning"><?php echo $stats['en_cours']; ?></div>
            <div class="stat-label">En Cours</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 bg-danger bg-opacity-10">
            <div class="stat-icon text-danger"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-nombre text-danger"><?php echo $stats['en_retard']; ?></div>
            <div class="stat-label">En Retard</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Graphique par statut -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart me-2"></i>Répartition par Statut
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartStatut"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique par priorité -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-flag me-2"></i>Tâches par Priorité (non terminées)
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartPriorite"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique progression par semaine -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>Activité (4 dernières semaines)
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartSemaines"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Projets -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-folder me-2"></i>Progression par Projet
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($tachesParProjet)): ?>
                    <p class="text-muted text-center">Aucun projet</p>
                <?php else: ?>
                    <?php foreach ($tachesParProjet as $projet): ?>
                        <?php
                        $progression = $projet['total'] > 0
                            ? round(($projet['terminees'] / $projet['total']) * 100)
                            : 0;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="d-flex align-items-center">
                                    <span class="rounded-circle me-2" style="width: 12px; height: 12px; background-color: <?php echo $projet['couleur']; ?>; display: inline-block;"></span>
                                    <?php echo htmlspecialchars($projet['nom']); ?>
                                </span>
                                <small class="text-muted">
                                    <?php echo $projet['terminees']; ?>/<?php echo $projet['total']; ?>
                                </small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width: <?php echo $progression; ?>%; background-color: <?php echo $projet['couleur']; ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Résumé mensuel -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white">
            <div class="card-body text-center py-4">
                <h4 class="mb-3">
                    <i class="bi bi-trophy me-2"></i>Résumé du Mois
                </h4>
                <div class="row justify-content-center">
                    <div class="col-auto">
                        <div class="display-4 fw-bold"><?php echo $stats['terminees_mois']; ?></div>
                        <p class="mb-0 opacity-75">tâches terminées ce mois</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration commune
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#e9ecef' : '#212529';

    Chart.defaults.color = textColor;

    // Graphique par Statut (Doughnut)
    new Chart(document.getElementById('chartStatut'), {
        type: 'doughnut',
        data: {
            labels: ['À faire', 'En cours', 'Terminé'],
            datasets: [{
                data: [<?php echo $stats['a_faire']; ?>, <?php echo $stats['en_cours']; ?>, <?php echo $stats['termine']; ?>],
                backgroundColor: ['#6c757d', '#0d6efd', '#198754'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Graphique par Priorité (Bar horizontal)
    new Chart(document.getElementById('chartPriorite'), {
        type: 'bar',
        data: {
            labels: ['Haute', 'Normale', 'Basse'],
            datasets: [{
                label: 'Tâches',
                data: [
                    <?php echo $statsPriorite['haute'] ?? 0; ?>,
                    <?php echo $statsPriorite['normale'] ?? 0; ?>,
                    <?php echo $statsPriorite['basse'] ?? 0; ?>
                ],
                backgroundColor: ['#dc3545', '#ffc107', '#198754']
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Graphique par Semaine (Line)
    new Chart(document.getElementById('chartSemaines'), {
        type: 'line',
        data: {
            labels: [<?php
                $labels = array_map(fn($s) => "'" . $s['debut_semaine'] . "'", $tachesParSemaine);
                echo implode(', ', $labels);
            ?>],
            datasets: [{
                label: 'Créées',
                data: [<?php echo implode(', ', array_column($tachesParSemaine, 'total')); ?>],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.3
            }, {
                label: 'Terminées',
                data: [<?php echo implode(', ', array_column($tachesParSemaine, 'terminees')); ?>],
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
