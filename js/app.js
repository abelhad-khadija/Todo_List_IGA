/**
 * Gestionnaire de Tâches - JavaScript Principal
 * =====================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation
    initialiserTheme();
    initialiserFormulaires();
    initialiserFiltres();
    initialiserKanban();
    initialiserSuppressions();
});

/* =====================================================
   Gestion du thème (Mode Sombre)
   ===================================================== */

function initialiserTheme() {
    const btnTheme = document.getElementById('btnModeSombre');
    const iconeTheme = document.getElementById('iconeTheme');
    const html = document.documentElement;

    // Charger le thème sauvegardé
    const themeSauvegarde = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-bs-theme', themeSauvegarde);
    mettreAJourIconeTheme(themeSauvegarde);

    // Écouteur du bouton
    if (btnTheme) {
        btnTheme.addEventListener('click', function() {
            const themeActuel = html.getAttribute('data-bs-theme');
            const nouveauTheme = themeActuel === 'light' ? 'dark' : 'light';

            html.setAttribute('data-bs-theme', nouveauTheme);
            localStorage.setItem('theme', nouveauTheme);
            mettreAJourIconeTheme(nouveauTheme);
        });
    }
}

function mettreAJourIconeTheme(theme) {
    const iconeTheme = document.getElementById('iconeTheme');
    if (iconeTheme) {
        iconeTheme.className = theme === 'light' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
    }
}

/* =====================================================
   Gestion des formulaires
   ===================================================== */

function initialiserFormulaires() {
    // Formulaire des tâches
    const formTache = document.getElementById('formTache');
    if (formTache) {
        formTache.addEventListener('submit', function(e) {
            e.preventDefault();
            sauvegarderTache(new FormData(this));
        });
    }

    // Formulaire des projets
    const formProjet = document.getElementById('formProjet');
    if (formProjet) {
        formProjet.addEventListener('submit', function(e) {
            e.preventDefault();
            sauvegarderProjet(new FormData(this));
        });
    }

    // Réinitialiser le modal à la fermeture
    const modalTache = document.getElementById('modalTache');
    if (modalTache) {
        modalTache.addEventListener('hidden.bs.modal', function() {
            formTache.reset();
            document.getElementById('tacheId').value = '';
            document.getElementById('modalTacheLabel').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Nouvelle Tâche';
            // Décocher toutes les étiquettes
            document.querySelectorAll('#etiquettesContainer input[type="checkbox"]').forEach(cb => cb.checked = false);
        });
    }
}

function sauvegarderTache(formData) {
    fetch('api/taches.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.succes) {
            afficherNotification('Succès', data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalTache')).hide();
            setTimeout(() => location.reload(), 500);
        } else {
            afficherNotification('Erreur', data.message, 'danger');
        }
    })
    .catch(error => {
        afficherNotification('Erreur', 'Une erreur est survenue', 'danger');
        console.error('Erreur:', error);
    });
}

function sauvegarderProjet(formData) {
    fetch('api/projets.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.succes) {
            afficherNotification('Succès', data.message, 'success');
            const modal = document.getElementById('modalProjet');
            if (modal) {
                bootstrap.Modal.getInstance(modal).hide();
            }
            setTimeout(() => location.reload(), 500);
        } else {
            afficherNotification('Erreur', data.message, 'danger');
        }
    })
    .catch(error => {
        afficherNotification('Erreur', 'Une erreur est survenue', 'danger');
        console.error('Erreur:', error);
    });
}

/* =====================================================
   Modification des tâches
   ===================================================== */

function modifierTache(id) {
    fetch(`api/taches.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.succes) {
                const tache = data.tache;

                // Remplir le formulaire
                document.getElementById('tacheId').value = tache.id;
                document.getElementById('titre').value = tache.titre;
                document.getElementById('description').value = tache.description || '';
                document.getElementById('projet_id').value = tache.projet_id || '';
                document.getElementById('priorite').value = tache.priorite;
                document.getElementById('statut').value = tache.statut;
                document.getElementById('date_echeance').value = tache.date_echeance || '';

                // Cocher les étiquettes
                document.querySelectorAll('#etiquettesContainer input[type="checkbox"]').forEach(cb => {
                    cb.checked = tache.etiquettes && tache.etiquettes.includes(parseInt(cb.value));
                });

                // Modifier le titre du modal
                document.getElementById('modalTacheLabel').innerHTML = '<i class="bi bi-pencil me-2"></i>Modifier la Tâche';

                // Ouvrir le modal
                new bootstrap.Modal(document.getElementById('modalTache')).show();
            }
        })
        .catch(error => console.error('Erreur:', error));
}

function modifierProjet(id) {
    fetch(`api/projets.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.succes) {
                const projet = data.projet;

                document.getElementById('projetId').value = projet.id;
                document.getElementById('projetNom').value = projet.nom;
                document.getElementById('projetDescription').value = projet.description || '';
                document.getElementById('projetCouleur').value = projet.couleur;

                document.getElementById('modalProjetLabel').innerHTML = '<i class="bi bi-pencil me-2"></i>Modifier le Projet';

                new bootstrap.Modal(document.getElementById('modalProjet')).show();
            }
        })
        .catch(error => console.error('Erreur:', error));
}

/* =====================================================
   Suppression des éléments
   ===================================================== */

let elementASupprimer = null;
let typeElementASupprimer = null;

function initialiserSuppressions() {
    const btnConfirmer = document.getElementById('btnConfirmerSupprimer');
    if (btnConfirmer) {
        btnConfirmer.addEventListener('click', function() {
            if (elementASupprimer && typeElementASupprimer) {
                effectuerSuppression(elementASupprimer, typeElementASupprimer);
            }
        });
    }
}

function supprimerTache(id) {
    elementASupprimer = id;
    typeElementASupprimer = 'tache';
    new bootstrap.Modal(document.getElementById('modalSupprimer')).show();
}

function supprimerProjet(id) {
    elementASupprimer = id;
    typeElementASupprimer = 'projet';
    new bootstrap.Modal(document.getElementById('modalSupprimer')).show();
}

function effectuerSuppression(id, type) {
    const url = type === 'tache' ? 'api/taches.php' : 'api/projets.php';

    fetch(`${url}?id=${id}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('modalSupprimer')).hide();

        if (data.succes) {
            afficherNotification('Succès', data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            afficherNotification('Erreur', data.message, 'danger');
        }
    })
    .catch(error => {
        afficherNotification('Erreur', 'Une erreur est survenue', 'danger');
        console.error('Erreur:', error);
    });
}

/* =====================================================
   Filtres dynamiques
   ===================================================== */

function initialiserFiltres() {
    const filtres = document.querySelectorAll('.filtre-select');

    filtres.forEach(filtre => {
        filtre.addEventListener('change', appliquerFiltres);
    });

    // Recherche en temps réel
    const rechercheInput = document.getElementById('recherche');
    if (rechercheInput) {
        rechercheInput.addEventListener('input', debounce(appliquerFiltres, 300));
    }
}

function appliquerFiltres() {
    const projet = document.getElementById('filtreProjet')?.value || '';
    const priorite = document.getElementById('filtrePriorite')?.value || '';
    const statut = document.getElementById('filtreStatut')?.value || '';
    const recherche = document.getElementById('recherche')?.value.toLowerCase() || '';

    const taches = document.querySelectorAll('.tache-item');

    taches.forEach(tache => {
        const tacheProjet = tache.dataset.projet || '';
        const tachePriorite = tache.dataset.priorite || '';
        const tacheStatut = tache.dataset.statut || '';
        const tacheTitre = tache.querySelector('.tache-titre')?.textContent.toLowerCase() || '';

        const matchProjet = !projet || tacheProjet === projet;
        const matchPriorite = !priorite || tachePriorite === priorite;
        const matchStatut = !statut || tacheStatut === statut;
        const matchRecherche = !recherche || tacheTitre.includes(recherche);

        if (matchProjet && matchPriorite && matchStatut && matchRecherche) {
            tache.style.display = '';
            tache.classList.add('fade-in');
        } else {
            tache.style.display = 'none';
        }
    });

    // Mettre à jour les compteurs Kanban si présents
    mettreAJourCompteursKanban();
}

/* =====================================================
   Vue Kanban - Drag & Drop
   ===================================================== */

function initialiserKanban() {
    const colonnes = document.querySelectorAll('.kanban-liste');

    colonnes.forEach(colonne => {
        new Sortable(colonne, {
            group: 'taches',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'dragging',
            onEnd: function(evt) {
                const tacheId = evt.item.dataset.id;
                const nouveauStatut = evt.to.closest('.kanban-colonne').dataset.statut;
                const nouvelOrdre = Array.from(evt.to.children).indexOf(evt.item);

                mettreAJourStatutTache(tacheId, nouveauStatut, nouvelOrdre);
                mettreAJourCompteursKanban();
            }
        });
    });
}

function mettreAJourStatutTache(id, statut, ordre) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('statut', statut);
    formData.append('ordre', ordre);
    formData.append('action', 'updateStatut');

    fetch('api/taches.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.succes) {
            afficherNotification('Succès', 'Tâche mise à jour', 'success');
        }
    })
    .catch(error => console.error('Erreur:', error));
}

function mettreAJourCompteursKanban() {
    const colonnes = document.querySelectorAll('.kanban-colonne');

    colonnes.forEach(colonne => {
        const liste = colonne.querySelector('.kanban-liste');
        const compteur = colonne.querySelector('.kanban-count');
        const tachesVisibles = liste.querySelectorAll('.tache-item:not([style*="display: none"])');

        if (compteur) {
            compteur.textContent = tachesVisibles.length;
        }
    });
}

/* =====================================================
   Notifications Toast
   ===================================================== */

function afficherNotification(titre, message, type = 'info') {
    const toast = document.getElementById('toastNotification');
    const toastTitre = document.getElementById('toastTitre');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');

    if (!toast) return;

    toastTitre.textContent = titre;
    toastMessage.textContent = message;

    // Icônes selon le type
    const icones = {
        success: 'bi-check-circle-fill text-success',
        danger: 'bi-exclamation-circle-fill text-danger',
        warning: 'bi-exclamation-triangle-fill text-warning',
        info: 'bi-info-circle-fill text-info'
    };

    toastIcon.className = `bi ${icones[type] || icones.info} me-2`;

    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

/* =====================================================
   Utilitaires
   ===================================================== */

// Debounce pour la recherche
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Formater une date
function formaterDate(dateStr) {
    if (!dateStr) return '';

    const date = new Date(dateStr);
    const options = { day: 'numeric', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('fr-FR', options);
}

// Vérifier si une date est passée
function estEnRetard(dateStr) {
    if (!dateStr) return false;

    const date = new Date(dateStr);
    const aujourdhui = new Date();
    aujourdhui.setHours(0, 0, 0, 0);

    return date < aujourdhui;
}

// Vérifier si une date est proche (dans les 3 jours)
function estProche(dateStr) {
    if (!dateStr) return false;

    const date = new Date(dateStr);
    const aujourdhui = new Date();
    const dansTroisJours = new Date();
    dansTroisJours.setDate(dansTroisJours.getDate() + 3);

    return date >= aujourdhui && date <= dansTroisJours;
}
