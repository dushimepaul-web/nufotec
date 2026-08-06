<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Administration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Médecins</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_medecin">
                <i class="bx bx-plus"></i> Nouveau Médecin
            </a>
        </div>
    </div>

    <!-- Messages flash -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-check-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-error-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-primary"><i class="bx bx-plus-medical me-2"></i>Liste des Médecins</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="medecinsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="8%">Photo</th>
                            <th width="18%">Médecin</th>
                            <th width="12%">Spécialité</th>
                            <th width="12%">Horaires</th>
                            <th width="10%">Honoraires</th>
                            <th width="8%">Dispo</th>
                            <th width="8%">Note</th>
                            <th width="8%">KYC</th>
                            <th width="11%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($medecins)): $i = 1; foreach ($medecins as $value): 
                        
                        $est_dispo = isset($value['est_disponible']) && $value['est_disponible'] == 1;
                        $dispo_badge = $est_dispo ? 
                            '<span class="badge bg-success"><i class="bx bx-check-circle"></i></span>' : 
                            '<span class="badge bg-danger"><i class="bx bx-block"></i></span>';
                        
                        $est_verifie = isset($value['est_verifie']) && $value['est_verifie'] == 1;
                        $kyc_badge = $est_verifie ? 
                            '<span class="badge bg-success"><i class="bx bx-shield"></i></span>' : 
                            '<span class="badge bg-warning text-dark"><i class="bx bx-time"></i></span>';
                        
                        $photo_path = !empty($value['photo']) ? 'attachments/Users/'.$value['photo'] : 'assets/frontend/img/default-avatar.jpg';
                        
                        $note = $value['note_moyenne'] ?? 0;
                        $nb_avis = $value['nombre_avis'] ?? 0;
                        
                        $honoraires = !empty($value['honoraires_consultation']) ? number_format($value['honoraires_consultation'], 2).' $' : '-';
                        
                        // Formatage compact des horaires groupés par jour
                        $horaires_display = '';
                        if (!empty($value['horaires_groupes'])) {
                            $jours_count = count($value['horaires_groupes']);
                            $horaires_display = '<span class="badge bg-info text-white" title="Voir détails">' . $jours_count . ' jour(s)</span>';
                        } else {
                            $horaires_display = '<span class="badge bg-secondary">Aucun</span>';
                        }
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            
                            <td>
                                <img src="<?= base_url($photo_path) ?>" 
                                     class="rounded-circle border"
                                     style="width:45px; height:45px; object-fit:cover;"
                                     onerror="this.src='<?= base_url('assets/frontend/img/default-avatar.jpg') ?>'"
                                     alt="Photo">
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark">Dr. <?= htmlspecialchars(($value['prenom'] ?? '').' '.($value['nom'] ?? '')) ?></strong>
                                    <small class="text-muted"><?= $value['annees_experience'] ?? 0 ?> ans d'exp.</small>
                                </div>
                            </td>

                            <td><?= htmlspecialchars($value['specialite'] ?? 'Non spécifiée') ?></td>

                            <td><?= $horaires_display ?></td>

                            <td class="text-center"><strong class="text-primary"><?= $honoraires ?></strong></td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id'] ?>" title="Changer disponibilité">
                                    <?= $dispo_badge ?>
                                </a>
                            </td>

                            <td class="text-center">
                                <?php if ($note > 0): ?>
                                    <div class="text-warning">
                                        <?php for($s = 1; $s <= 5; $s++): ?>
                                            <i class="bx <?= $s <= round($note) ? 'bxs-star' : 'bx-star' ?>"></i>
                                        <?php endfor; ?>
                                        <small class="text-muted d-block">(<?= $nb_avis ?>)</small>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#kyc_<?= $value['id'] ?>" title="Changer vérification KYC">
                                    <?= $kyc_badge ?>
                                </a>
                            </td>

                            <td>
                                <div class="btn-group">
                                    <a href="<?= base_url('Users/Medecins/Detail/'.$value['id']) ?>" class="btn btn-sm btn-info" title="Détails complets">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    <button class="btn btn-sm btn-warning btn-open-update" data-id="<?= $value['id'] ?>" title="Modifier">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id'] ?>" title="Supprimer">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="bx bx-plus-medical text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun médecin trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>

                <?php if (!empty($medecins)): foreach ($medecins as $value):
                    $est_dispo = isset($value['est_disponible']) && $value['est_disponible'] == 1;
                    $est_verifie = isset($value['est_verifie']) && $value['est_verifie'] == 1;
                ?>

                        <!-- MODAL STATUS -->
                        <div class="modal fade" id="status_<?= $value['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= $est_dispo ? 'bg-danger' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <i class="bx <?= $est_dispo ? 'bx-block' : 'bx-check-circle' ?> me-2"></i>
                                            <?= $est_dispo ? 'Rendre indisponible' : 'Rendre disponible' ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('Users/Medecins/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="est_disponible" value="<?= $value['est_disponible'] ?? 0 ?>">
                                        <div class="modal-body text-center p-4">
                                            <p class="mb-0">
                                                Voulez-vous vraiment <strong><?= $est_dispo ? 'rendre indisponible' : 'rendre disponible' ?></strong> 
                                                le Dr. <strong><?= htmlspecialchars(($value['prenom'] ?? '').' '.($value['nom'] ?? '')) ?></strong> ?
                                            </p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= $est_dispo ? 'btn-danger' : 'btn-success' ?>">
                                                Confirmer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL KYC -->
                        <div class="modal fade" id="kyc_<?= $value['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= $est_verifie ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <i class="bx <?= $est_verifie ? 'bx-x-circle' : 'bx-check-shield' ?> me-2"></i>
                                            <?= $est_verifie ? 'Révoquer KYC' : 'Vérifier KYC' ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('Users/Medecins/VerifierKYC') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="est_verifie" value="<?= $value['est_verifie'] ?? 0 ?>">
                                        <div class="modal-body text-center p-4">
                                            <p class="mb-0">
                                                Voulez-vous vraiment <strong><?= $est_verifie ? 'révoquer la vérification' : 'vérifier' ?></strong> 
                                                le Dr. <strong><?= htmlspecialchars(($value['prenom'] ?? '').' '.($value['nom'] ?? '')) ?></strong> ?
                                            </p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= $est_verifie ? 'btn-warning' : 'btn-success' ?>">
                                                Confirmer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $value['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Supprimer</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center p-4">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 3rem;"></i>
                                        <p class="mt-3">Êtes-vous sûr de vouloir supprimer le Dr. <strong><?= htmlspecialchars(($value['prenom'] ?? '').' '.($value['nom'] ?? '')) ?></strong> ?</p>
                                        <p class="text-muted small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Users/Medecins/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">Supprimer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; endif; ?>

            </div>
        </div>
    </div>

</div>

<!-- ==================== MODAL UPDATE UNIQUE (chargée dynamiquement) ==================== -->
<div class="modal fade" id="modalUpdateMedecin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow" id="modalUpdateContent">
            <!-- Contenu chargé via AJAX -->
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier Médecin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-2">Chargement des données...</p>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODALE DE CRÉATION MÉDECIN ==================== -->
<div class="modal fade" id="create_medecin" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            
            <!-- En-tête de la modale -->
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bx bx-plus-medical me-2"></i>
                    Nouveau Médecin
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <!-- Formulaire de création -->
            <form action="<?= base_url('Users/Medecins/Create') ?>" 
                  method="POST" 
                  id="formCreateMedecin" 
                  class="form-medecin">
                
                <div class="modal-body p-4">
                    <div class="row g-4">
                        
                        <!-- Colonne gauche : Informations générales -->
                        <div class="col-md-12">
                            <div class="section-title mb-3">
                                <h6 class="text-primary">
                                    <i class="bx bx-user me-2"></i>
                                    Informations générales
                                </h6>
                                <hr class="mt-1">
                            </div>
                            
                            <!-- Utilisateur -->
                            <div class="mb-3">
                                <label for="user_id" class="form-label fw-bold">
                                    Utilisateur <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="" selected disabled>Sélectionner un utilisateur...</option>
                                    <?php if (!empty($users_disponibles)): ?>
                                        <?php foreach ($users_disponibles as $user): ?>
                                            <option value="<?= $user['id'] ?>">
                                                <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom'] . ' (' . $user['email'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>Aucun utilisateur disponible</option>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text text-muted">
                                    <i class="bx bx-info-circle"></i>
                                    Seuls les utilisateurs actifs non encore médecins sont affichés
                                </div>
                            </div>

                            <!-- Spécialité -->
                            <div class="mb-3">
                                <label for="specialite" class="form-label fw-bold">
                                    Spécialité <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" 
                                       id="specialite" name="specialite" 
                                       placeholder="Ex: Médecine Interne et Phytothérapie" 
                                       required>
                            </div>

                            <!-- Numéro de licence -->
                            <div class="mb-3">
                                <label for="numero_licence" class="form-label fw-bold">
                                    Numéro de licence <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" 
                                       id="numero_licence" name="numero_licence" 
                                       placeholder="Ex: ONB-12345-BI" 
                                       required>
                            </div>

                            <!-- Expérience et Honoraires -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="annees_experience" class="form-label fw-bold">
                                        Expérience (années)
                                    </label>
                                    <input type="number" class="form-control" 
                                           id="annees_experience" name="annees_experience" 
                                           min="0" value="0" 
                                           placeholder="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="honoraires_consultation" class="form-label fw-bold">
                                        Honoraires ($) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.01" class="form-control" 
                                           id="honoraires_consultation" name="honoraires_consultation" 
                                           placeholder="0.00" 
                                           required>
                                </div>
                            </div>

                            <!-- Langues parlées -->
                            <div class="mb-3">
                                <label for="langues_parlees" class="form-label fw-bold">
                                    Langues parlées
                                </label>
                                <input type="text" class="form-control" 
                                       id="langues_parlees" name="langues_parlees" 
                                       placeholder="Français, Kirundi, Anglais">
                                <div class="form-text text-muted">
                                    <i class="bx bx-info-circle"></i>
                                    Séparez les langues par des virgules
                                </div>
                            </div>

                            <!-- Diplômes -->
                            <div class="mb-3">
                                <label for="diplomes" class="form-label fw-bold">
                                    Diplômes
                                </label>
                                <textarea class="form-control" id="diplomes" name="diplomes" 
                                          rows="3" placeholder="Doctorat en Médecine..."></textarea>
                            </div>
                        </div>

                        <!-- Colonne droite : Espace pour d'autres informations si nécessaire -->
                        <div class="col-md-6">
                            <!-- Vous pouvez ajouter d'autres champs ici si besoin -->
                        </div>
                    </div>
                </div>

                <!-- Pied de la modale -->
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-1"></i>
                        Créer le médecin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== TEMPLATE POUR NOUVELLE LIGNE D'HORAIRE ==================== -->
<template id="template-horaire-row">
    <div class="horaire-row d-flex align-items-center gap-2 mb-2 p-2 bg-white rounded border">
        <select class="form-select form-select-sm" name="horaires[INDEX][jour]" style="width: 110px;" required>
            <option value="">Jour...</option>
            <?php foreach ($jours_semaine as $jour): ?>
                <option value="<?= $jour ?>"><?= ucfirst($jour) ?></option>
            <?php endforeach; ?>
        </select>
        
        <div class="d-flex align-items-center gap-1 flex-grow-1">
            <input type="time" class="form-control form-control-sm" name="horaires[INDEX][debut]" value="08:00" required>
            <span class="text-muted small">à</span>
            <input type="time" class="form-control form-control-sm" name="horaires[INDEX][fin]" value="12:00" required>
        </div>
        
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-horaire" title="Supprimer ce créneau">
            <i class="bx bx-trash"></i>
        </button>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // DataTable
    setTimeout(function() {
        if (document.getElementById('medecinsTable')) {
            $('#medecinsTable').DataTable({
                language: { 
                    url: '<?= base_url("assets/backend/plugins/datatable/js/fr-FR.json") ?>',
                    emptyTable: "Aucun médecin disponible",
                    zeroRecords: "Aucun médecin trouvé"
                },
                order: [[0, 'desc']],
                pageLength: 25,
                responsive: true,
                columnDefs: [{ orderable: false, targets: [1, 9] }]
            });
        }
    }, 100);
    
    // Auto-hide alerts
    setTimeout(function() { 
        $('.alert').fadeOut('slow'); 
    }, 5000);
    
    // Template pour nouvelle ligne
    var template = document.getElementById('template-horaire-row');
    
    // ==================== FONCTION: Mettre à jour le compteur ====================
    function updateCounter(containerId, counterId) {
        var container = document.getElementById(containerId);
        var counter = document.getElementById(counterId);
        if (container && counter) {
            var count = container.querySelectorAll('.horaire-row').length;
            counter.textContent = count + ' créneau' + (count > 1 ? 'x' : '');
        }
    }
    
    // ==================== FONCTION: Ajouter une ligne ====================
    function addHoraireRow(containerId, counterId) {
        var container = document.getElementById(containerId);
        if (!container || !template) return;
        
        var rows = container.querySelectorAll('.horaire-row');
        var index = rows.length;
        
        var clone = template.content.cloneNode(true);
        var newRow = clone.querySelector('.horaire-row');
        
        // Remplacer INDEX dans les names
        newRow.querySelectorAll('select, input').forEach(function(input) {
            var name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace('INDEX', index));
            }
        });
        
        container.appendChild(newRow);
        
        if (counterId) {
            updateCounter(containerId, counterId);
        }
        
        // Animation
        newRow.style.opacity = '0';
        newRow.style.transform = 'translateY(-10px)';
        setTimeout(function() {
            newRow.style.transition = 'all 0.3s ease';
            newRow.style.opacity = '1';
            newRow.style.transform = 'translateY(0)';
        }, 10);
    }
    
    // ==================== FONCTION: Supprimer une ligne ====================
    function removeHoraireRow(button, containerId, counterId) {
        var row = button.closest('.horaire-row');
        var container = row.parentElement;
        
        var allRows = container.querySelectorAll('.horaire-row');
        if (allRows.length <= 1) {
            alert('Vous devez garder au moins un créneau horaire');
            return;
        }
        
        row.style.transition = 'all 0.3s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateX(20px)';
        
        setTimeout(function() {
            row.remove();
            reindexHoraires(container);
            if (counterId) {
                updateCounter(containerId, counterId);
            }
        }, 300);
    }
    
    // ==================== FONCTION: Réindexer les names ====================
    function reindexHoraires(container) {
        container.querySelectorAll('.horaire-row').forEach(function(row, idx) {
            row.querySelectorAll('select, input').forEach(function(input) {
                var name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/horaires\[\d+\]/, 'horaires[' + idx + ']'));
                }
            });
        });
    }
    
    // ==================== ÉVÉNEMENT: Bouton Ajouter (CREATE) ====================
    var btnAddCreate = document.getElementById('btn-add-horaire-create');
    if (btnAddCreate) {
        btnAddCreate.addEventListener('click', function(e) {
            e.preventDefault();
            addHoraireRow('horaires-container-create', 'count-horaires-create');
        });
    }
    
    // ==================== DÉLÉGATION: Supprimer et Ajouter dans modales ====================
    document.addEventListener('click', function(e) {
        // Bouton supprimer (CREATE)
        if (e.target.closest('#horaires-container-create .btn-remove-horaire')) {
            e.preventDefault();
            removeHoraireRow(e.target.closest('.btn-remove-horaire'), 'horaires-container-create', 'count-horaires-create');
        }
        
        // Bouton supprimer (UPDATE - dynamique)
        if (e.target.closest('#horaires-container-update .btn-remove-horaire')) {
            e.preventDefault();
            removeHoraireRow(e.target.closest('.btn-remove-horaire'), 'horaires-container-update', 'count-horaires-update');
        }
        
        // Bouton ajouter dans modale UPDATE (dynamique)
        if (e.target.closest('#btn-add-horaire-update')) {
            e.preventDefault();
            addHoraireRow('horaires-container-update', 'count-horaires-update');
        }
    });
    
    // ==================== MODALE UPDATE: Chargement AJAX ====================
    $(document).on('click', '.btn-open-update', function() {
        var medecinId = $(this).data('id');
        var modalEl = document.getElementById('modalUpdateMedecin');
        var modal = new bootstrap.Modal(modalEl);
        
        // Reset contenu
        $('#modalUpdateContent').html(`
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier Médecin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-5 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-3 text-muted">Chargement des données...</p>
            </div>
        `);
        
        modal.show();
        
        // Charger le contenu
        $.ajax({
            url: '<?= base_url('Users/Medecins/EditForm/') ?>' + medecinId,
            type: 'GET',
            success: function(response) {
                $('#modalUpdateContent').html(response);
                
                // Initialiser les compteurs après chargement
                updateCounter('horaires-container-update', 'count-horaires-update');
            },
            error: function(xhr, status, error) {
                $('#modalUpdateContent').html(`
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bx bx-error me-2"></i>Erreur</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-5 text-center">
                        <i class="bx bx-error-circle text-danger" style="font-size: 3rem;"></i>
                        <p class="mt-3">Impossible de charger les données</p>
                        <small class="text-muted">${error}</small>
                    </div>
                `);
            }
        });
    });
    
    // ==================== VALIDATION FORMULAIRES ====================
    $(document).on('submit', '.form-medecin', function(e) {
        var container = $(this).find('.horaires-dynamic-container')[0];
        if (!container) return true;
        
        var errors = [];
        var validRows = 0;
        var creneaux = []; // Pour détecter les doublons
        
        container.querySelectorAll('.horaire-row').forEach(function(row, idx) {
            var jourSelect = row.querySelector('select[name*="[jour]"]');
            var debutInput = row.querySelector('input[name*="[debut]"]');
            var finInput = row.querySelector('input[name*="[fin]"]');
            
            var jour = jourSelect ? jourSelect.value : '';
            var debut = debutInput ? debutInput.value : '';
            var fin = finInput ? finInput.value : '';
            
            if (!jour) {
                errors.push('Ligne ' + (idx + 1) + ': Veuillez sélectionner un jour');
                return;
            }
            
            if (!debut || !fin) {
                errors.push('Ligne ' + (idx + 1) + ': Heures manquantes');
                return;
            }
            
            if (fin <= debut) {
                errors.push('Ligne ' + (idx + 1) + ': L\'heure de fin doit être après le début');
                return;
            }
            
            // Vérifier doublon (même jour, chevauchement)
            var key = jour + '_' + debut + '_' + fin;
            if (creneaux.includes(key)) {
                errors.push('Ligne ' + (idx + 1) + ': Créneau dupliqué détecté');
                return;
            }
            creneaux.push(key);
            
            validRows++;
        });
        
        if (errors.length > 0) {
            alert('Erreurs dans les horaires:\n\n' + errors.join('\n'));
            e.preventDefault();
            return false;
        }
        
        if (validRows === 0) {
            if (!confirm('Aucun horaire valide n\'a été saisi. Voulez-vous continuer sans horaire ?')) {
                e.preventDefault();
                return false;
            }
        }
        
        return true;
    });
    
    // ==================== RESET FORM CREATE ON CLOSE ====================
    $('#create_medecin').on('hidden.bs.modal', function() {
        var form = document.getElementById('formCreateMedecin');
        if (form) {
            form.reset();
            // Remettre une seule ligne d'horaire
            var container = document.getElementById('horaires-container-create');
            container.innerHTML = container.querySelector('.horaire-row').outerHTML;
            updateCounter('horaires-container-create', 'count-horaires-create');
        }
    });
    
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>