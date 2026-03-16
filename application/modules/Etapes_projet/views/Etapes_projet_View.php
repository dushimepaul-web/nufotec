<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Projet</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Étapes du Projet</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_etape">
                <i class="bx bx-plus"></i> Nouvelle Étape
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

    <!-- Vue Timeline -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-primary"><i class="bx bx-timeline me-2"></i>Timeline du Projet Vision 2026-2031</h5>
                <div class="btn-group">
                    <button class="btn btn-outline-primary btn-sm active" onclick="showView('timeline')"><i class="bx bx-timeline"></i> Timeline</button>
                    <button class="btn btn-outline-primary btn-sm" onclick="showView('table')"><i class="bx bx-table"></i> Tableau</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Vue Timeline -->
            <div id="timeline-view">
                <div class="timeline">
                    <?php 
                    $phase_colors = [
                        'Pre-Seed' => 'bg-info',
                        'Phase I' => 'bg-primary',
                        'Phase II' => 'bg-warning',
                        'Phase II-III' => 'bg-orange',
                        'Phase III' => 'bg-success'
                    ];
                    
                    $status_badges = [
                        'a_venir' => ['label' => 'À venir', 'class' => 'bg-secondary'],
                        'en_cours' => ['label' => 'En cours', 'class' => 'bg-primary'],
                        'termine' => ['label' => 'Terminé', 'class' => 'bg-success'],
                        'retarde' => ['label' => 'Retardé', 'class' => 'bg-danger']
                    ];

                    if (!empty($etapes)): 
                        $current_phase = '';
                        foreach ($etapes as $etape): 
                            $phase_color = $phase_colors[$etape['phase'] ?? ''] ?? 'bg-secondary';
                            $status = $status_badges[$etape['statut'] ?? 'a_venir'];
                            
                            // Afficher le séparateur de phase si changement
                            if ($etape['phase'] != $current_phase):
                                $current_phase = $etape['phase'];
                    ?>
                        <div class="timeline-phase text-center my-4">
                            <span class="badge <?= $phase_color ?> fs-6 px-4 py-2"><?= htmlspecialchars($current_phase) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="timeline-item <?= ($etape['statut'] == 'termine') ? 'completed' : '' ?> <?= ($etape['statut'] == 'en_cours') ? 'active' : '' ?>">
                        <div class="timeline-marker <?= $phase_color ?>"></div>
                        <div class="timeline-content">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($etape['titre'] ?? '') ?></h6>
                                            <?php if (!empty($etape['description'])): ?>
                                                <p class="text-muted small mb-2"><?= htmlspecialchars(substr($etape['description'], 0, 100)) ?>...</p>
                                            <?php endif; ?>
                                            <div class="d-flex gap-2 align-items-center">
                                                <span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span>
                                                <small class="text-muted">
                                                    <i class="bx bx-calendar me-1"></i>
                                                    <?= !empty($etape['date_debut']) ? date('d/m/Y', strtotime($etape['date_debut'])) : '-' ?>
                                                    <?php if (!empty($etape['date_fin_prevue'])): ?>
                                                        → <?= date('d/m/Y', strtotime($etape['date_fin_prevue'])) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="progress mb-2" style="width: 100px; height: 8px;">
                                                <div class="progress-bar <?= $phase_color ?>" role="progressbar" style="width: <?= $etape['pourcentage_avancement'] ?? 0 ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $etape['pourcentage_avancement'] ?? 0 ?>%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Vue Tableau (cachée par défaut) -->
            <div id="table-view" style="display: none;">
                <div class="table-responsive">
                    <table id="etapesTable" class="table table-hover align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Phase</th>
                                <th>Titre</th>
                                <th>Dates</th>
                                <th>Progression</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($etapes)): foreach ($etapes as $value): 
                            $phase_color = $phase_colors[$value['phase'] ?? ''] ?? 'bg-secondary';
                            $status = $status_badges[$value['statut'] ?? 'a_venir'];
                        ?>
                            <tr>
                                <td><span class="badge <?= $phase_color ?>"><?= htmlspecialchars($value['phase'] ?? '-') ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars($value['titre'] ?? '') ?></strong>
                                    <?php if (!empty($value['description'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars(substr($value['description'], 0, 50)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small>
                                        <i class="bx bx-play-circle text-success"></i> <?= !empty($value['date_debut']) ? date('d/m/Y', strtotime($value['date_debut'])) : '-' ?>
                                        <br>
                                        <i class="bx bx-stop-circle text-danger"></i> <?= !empty($value['date_fin_prevue']) ? date('d/m/Y', strtotime($value['date_fin_prevue'])) : '-' ?>
                                        <?php if (!empty($value['date_fin_reelle'])): ?>
                                            <br><i class="bx bx-check-circle text-success"></i> <?= date('d/m/Y', strtotime($value['date_fin_reelle'])) ?>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar <?= $phase_color ?>" role="progressbar" style="width: <?= $value['pourcentage_avancement'] ?? 0 ?>%">
                                            <?= $value['pourcentage_avancement'] ?? 0 ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_etape'] ?>" class="text-decoration-none">
                                        <span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span>
                                    </a>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('Etapes_projet/EtapeDetail/'.$value['id_etape']) ?>">
                                                    <i class="bx bx-show text-info me-2"></i>Voir détails
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#progress_<?= $value['id_etape'] ?>">
                                                    <i class="bx bx-chart text-success me-2"></i>Mettre à jour progression
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_etape'] ?>">
                                                    <i class="bx bx-edit text-warning me-2"></i>Modifier
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_etape'] ?>">
                                                    <i class="bx bx-trash me-2"></i>Supprimer
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <!-- MODAL UPDATE PROGRESS -->
                            <div class="modal fade" id="progress_<?= $value['id_etape'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title"><i class="bx bx-chart me-2"></i>Mise à jour progression</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('Etapes_projet/UpdateProgress') ?>" method="POST">
                                            <input type="hidden" name="id" value="<?= $value['id_etape'] ?>">
                                            <div class="modal-body p-4">
                                                <label class="form-label fw-bold">Pourcentage d'avancement</label>
                                                <input type="range" class="form-range" name="pourcentage_avancement" min="0" max="100" value="<?= $value['pourcentage_avancement'] ?? 0 ?>" oninput="document.getElementById('progress-value-<?= $value['id_etape'] ?>').innerText = this.value + '%'">
                                                <div class="text-center mt-2">
                                                    <span class="badge bg-primary fs-5" id="progress-value-<?= $value['id_etape'] ?>"><?= $value['pourcentage_avancement'] ?? 0 ?>%</span>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-success">Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- MODAL STATUS -->
                            <div class="modal fade" id="status_<?= $value['id_etape'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title"><i class="bx bx-refresh me-2"></i>Changer le statut</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <p>Statut actuel: <span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span></p>
                                            <p>Cliquez pour passer au statut suivant.</p>
                                        </div>
                                        <form action="<?= base_url('Etapes_projet/ChangeStatus') ?>" method="POST">
                                            <input type="hidden" name="id" value="<?= $value['id_etape'] ?>">
                                            <input type="hidden" name="statut" value="<?= $value['statut'] ?? 'a_venir' ?>">
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-primary">Changer le statut</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- MODAL UPDATE -->
                            <div class="modal fade" id="update_<?= $value['id_etape'] ?>" data-bs-backdrop="static" tabindex="-1">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-warning text-dark">
                                            <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier l'étape</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <form action="<?= base_url('Etapes_projet/Update') ?>" method="POST">
                                            <input type="hidden" name="id_etape" value="<?= $value['id_etape'] ?>">
                                            
                                            <div class="modal-body p-4">
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($value['titre'] ?? '') ?>" required>
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label fw-bold">Description</label>
                                                        <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Date de début <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" name="date_debut" value="<?= $value['date_debut'] ?? '' ?>" required>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Date fin prévue</label>
                                                        <input type="date" class="form-control" name="date_fin_prevue" value="<?= $value['date_fin_prevue'] ?? '' ?>">
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Date fin réelle</label>
                                                        <input type="date" class="form-control" name="date_fin_reelle" value="<?= $value['date_fin_reelle'] ?? '' ?>">
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Statut</label>
                                                        <select class="form-select" name="statut">
                                                            <option value="a_venir" <?= ($value['statut'] ?? '') == 'a_venir' ? 'selected' : '' ?>>À venir</option>
                                                            <option value="en_cours" <?= ($value['statut'] ?? '') == 'en_cours' ? 'selected' : '' ?>>En cours</option>
                                                            <option value="termine" <?= ($value['statut'] ?? '') == 'termine' ? 'selected' : '' ?>>Terminé</option>
                                                            <option value="retarde" <?= ($value['statut'] ?? '') == 'retarde' ? 'selected' : '' ?>>Retardé</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Progression (%)</label>
                                                        <input type="number" class="form-control" name="pourcentage_avancement" value="<?= $value['pourcentage_avancement'] ?? 0 ?>" min="0" max="100">
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Phase</label>
                                                        <select class="form-select" name="phase">
                                                            <option value="Pre-Seed" <?= ($value['phase'] ?? '') == 'Pre-Seed' ? 'selected' : '' ?>>Pre-Seed</option>
                                                            <option value="Phase I" <?= ($value['phase'] ?? '') == 'Phase I' ? 'selected' : '' ?>>Phase I</option>
                                                            <option value="Phase II" <?= ($value['phase'] ?? '') == 'Phase II' ? 'selected' : '' ?>>Phase II</option>
                                                            <option value="Phase II-III" <?= ($value['phase'] ?? '') == 'Phase II-III' ? 'selected' : '' ?>>Phase II-III</option>
                                                            <option value="Phase III" <?= ($value['phase'] ?? '') == 'Phase III' ? 'selected' : '' ?>>Phase III</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Page associée</label>
                                                        <select class="form-select" name="id_page_associee">
                                                            <option value="">Aucune</option>
                                                            <?php foreach ($pages as $page): ?>
                                                                <option value="<?= $page['id_page'] ?>" <?= ($value['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($page['titre_page']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="bx bx-save me-2"></i>Enregistrer les modifications
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- MODAL DELETE -->
                            <div class="modal fade" id="delete_<?= $value['id_etape'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4 text-center">
                                            <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                            <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                            <p class="text-muted">Vous êtes sur le point de supprimer l'étape <strong><?= htmlspecialchars($value['titre'] ?? '') ?></strong>.</p>
                                            <p class="text-danger small">Cette action est irréversible.</p>
                                        </div>
                                        <form action="<?= base_url('Etapes_projet/Delete') ?>" method="POST">
                                            <input type="hidden" name="id" value="<?= $value['id_etape'] ?>">
                                            <div class="modal-footer bg-light justify-content-center">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="bx bx-trash me-2"></i>Supprimer définitivement
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bx bx-timeline text-muted" style="font-size: 4rem;"></i>
                                    <p class="mt-3 text-muted">Aucune étape du projet trouvée</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>


<!-- MODAL CREATE ETAPE -->
<div class="modal fade" id="create_etape" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle Étape</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Etapes_projet/Create') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="titre" required placeholder="Ex: Company Incorporation">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Description de l'étape..."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Date de début <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="date_debut" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Date fin prévue</label>
                            <input type="date" class="form-control" name="date_fin_prevue">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Date fin réelle</label>
                            <input type="date" class="form-control" name="date_fin_reelle">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Statut</label>
                            <select class="form-select" name="statut">
                                <option value="a_venir" selected>À venir</option>
                                <option value="en_cours">En cours</option>
                                <option value="termine">Terminé</option>
                                <option value="retarde">Retardé</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Progression (%)</label>
                            <input type="number" class="form-control" name="pourcentage_avancement" value="0" min="0" max="100">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Phase</label>
                            <select class="form-select" name="phase">
                                <option value="Pre-Seed">Pre-Seed</option>
                                <option value="Phase I">Phase I</option>
                                <option value="Phase II">Phase II</option>
                                <option value="Phase II-III">Phase II-III</option>
                                <option value="Phase III">Phase III</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Page associée</label>
                            <select class="form-select" name="id_page_associee">
                                <option value="">Aucune</option>
                                <?php foreach ($pages as $page): ?>
                                    <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer l'étape
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#etapesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Toggle entre vue timeline et tableau
function showView(view) {
    if (view === 'timeline') {
        document.getElementById('timeline-view').style.display = 'block';
        document.getElementById('table-view').style.display = 'none';
    } else {
        document.getElementById('timeline-view').style.display = 'none';
        document.getElementById('table-view').style.display = 'block';
    }
    
    // Mettre à jour les boutons actifs
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}
</script>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}
.timeline:before {
    content: '';
    position: absolute;
    left: 30px;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #e9ecef;
}
.timeline-item {
    position: relative;
    padding-left: 70px;
    margin-bottom: 20px;
}
.timeline-marker {
    position: absolute;
    left: 20px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 4px solid #fff;
    box-shadow: 0 0 0 4px #e9ecef;
}
.timeline-item.active .timeline-marker {
    box-shadow: 0 0 0 4px #0d6efd;
    animation: pulse 2s infinite;
}
.timeline-item.completed .timeline-marker {
    box-shadow: 0 0 0 4px #198754;
}
.timeline-content {
    background: #fff;
    border-radius: 8px;
}
.timeline-phase {
    position: relative;
    z-index: 1;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(13, 110, 253, 0.2); }
    100% { box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.4); }
}
</style>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>