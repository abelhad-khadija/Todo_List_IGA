    </main>

    <!-- Modal Ajouter/Modifier Tâche -->
    <div class="modal fade" id="modalTache" tabindex="-1" aria-labelledby="modalTacheLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTacheLabel">
                        <i class="bi bi-plus-circle me-2"></i>Nouvelle Tâche
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form id="formTache" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="tacheId">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="titre" class="form-label">Titre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="titre" name="titre" required placeholder="Entrez le titre de la tâche">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="projet_id" class="form-label">Projet</label>
                                <select class="form-select" id="projet_id" name="projet_id">
                                    <option value="">Aucun projet</option>
                                    <?php
                                    if (isset($pdo)) {
                                        $stmtProjets = $pdo->query("SELECT id, nom, couleur FROM projets ORDER BY nom");
                                        while ($projet = $stmtProjets->fetch()) {
                                            echo '<option value="' . $projet['id'] . '" data-couleur="' . $projet['couleur'] . '">' . htmlspecialchars($projet['nom']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Décrivez la tâche en détail..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="priorite" class="form-label">Priorité</label>
                                <select class="form-select" id="priorite" name="priorite">
                                    <option value="basse">🟢 Basse</option>
                                    <option value="normale" selected>🟡 Normale</option>
                                    <option value="haute">🔴 Haute</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select" id="statut" name="statut">
                                    <option value="a_faire">À faire</option>
                                    <option value="en_cours">En cours</option>
                                    <option value="termine">Terminé</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="date_echeance" class="form-label">Date d'échéance</label>
                                <input type="date" class="form-control" id="date_echeance" name="date_echeance">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Étiquettes</label>
                            <div class="d-flex flex-wrap gap-2" id="etiquettesContainer">
                                <?php
                                if (isset($pdo)) {
                                    $stmtEtiquettes = $pdo->query("SELECT id, nom, couleur FROM etiquettes ORDER BY nom");
                                    while ($etiquette = $stmtEtiquettes->fetch()) {
                                        echo '<div class="form-check">';
                                        echo '<input class="form-check-input" type="checkbox" name="etiquettes[]" value="' . $etiquette['id'] . '" id="etiq_' . $etiquette['id'] . '">';
                                        echo '<label class="form-check-label badge" style="background-color: ' . $etiquette['couleur'] . '" for="etiq_' . $etiquette['id'] . '">' . htmlspecialchars($etiquette['nom']) . '</label>';
                                        echo '</div>';
                                    }
                                }
                                ?>
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

    <!-- Modal Confirmation Suppression -->
    <div class="modal fade" id="modalSupprimer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirmer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cet élément ?</p>
                    <p class="text-muted small">Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btnConfirmerSupprimer">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toastNotification" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-info-circle me-2" id="toastIcon"></i>
                <strong class="me-auto" id="toastTitre">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SortableJS pour le drag & drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <!-- Chart.js pour les statistiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- JavaScript personnalisé -->
    <script src="js/app.js"></script>
</body>
</html>
