<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionnaire de Tâches - Todo List</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="bi bi-kanban me-2 fs-4"></i>
                <span class="fw-bold">TaskFlow</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="bi bi-list-task me-1"></i> Liste
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kanban.php' ? 'active' : ''; ?>" href="kanban.php">
                            <i class="bi bi-kanban me-1"></i> Kanban
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'projets.php' ? 'active' : ''; ?>" href="projets.php">
                            <i class="bi bi-folder me-1"></i> Projets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'statistiques.php' ? 'active' : ''; ?>" href="statistiques.php">
                            <i class="bi bi-bar-chart me-1"></i> Statistiques
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <!-- Bouton Rappels Email -->
                    <button class="btn btn-outline-light btn-sm me-2" id="btnRappels" onclick="envoyerRappels()" title="Envoyer les rappels par email">
                        <i class="bi bi-envelope me-1"></i> Rappels
                    </button>

                    <!-- Bouton Mode Sombre -->
                    <button class="btn btn-outline-light btn-sm me-2" id="btnModeSombre" title="Changer le thème">
                        <i class="bi bi-moon-fill" id="iconeTheme"></i>
                    </button>

                    <!-- Bouton Nouvelle Tâche -->
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalTache">
                        <i class="bi bi-plus-lg me-1"></i> Nouvelle Tâche
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Conteneur principal -->
    <main class="container py-4">
