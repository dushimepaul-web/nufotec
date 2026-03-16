<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Équipe</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion de l'Équipe</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_membre">
                <i class="bx bx-plus"></i> Nouveau Membre
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
                <h5 class="mb-0 text-primary"><i class="bx bx-group me-2"></i>Liste des Membres de l'Équipe</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="equipeTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">Ordre</th>
                            <th width="10%">Photo</th>
                            <th width="20%">Identité</th>
                            <th width="20%">Poste</th>
                            <th width="15%">Contact</th>
                            <th width="10%">Spécialité</th>
                            <th width="8%">Admin</th>
                            <th width="12%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($membres)): foreach ($membres as $value): 
                        // Photo
                        $photo_path = !empty($value['photo_url']) ? $value['photo_url'] : 'attachments/Equipe/default-avatar.png';
                        
                        // Page associée
                        $page_name = 'Global';
                        if (!empty($value['id_page_associee'])) {
                            foreach ($pages as $page) {
                                if ($page['id_page'] == $value['id_page_associee']) {
                                    $page_name = $page['titre_page'];
                                    break;
                                }
                            }
                        }
                    ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border"><?= $value['ordre_affichage'] ?? 0 ?></span></td>
                            
                            <td>
                                <img src="<?= base_url($photo_path) ?>" 
                                     class="rounded-circle border"
                                     style="width:50px; height:50px; object-fit:cover;"
                                     onerror="this.src='<?= base_url('attachments/Equipe/default-avatar.png') ?>'"
                                     alt="Photo">
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars(($value['prenom'] ?? '').' '.($value['nom'] ?? '')) ?></strong>
                                    <?php if (!empty($value['biographie'])): ?>
                                        <small class="text-muted text-truncate" style="max-width: 200px;">
                                            <?= htmlspecialchars(substr($value['biographie'], 0, 50)) ?>...
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                                    <?= htmlspecialchars($value['poste'] ?? '-') ?>
                                </span>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <?php if (!empty($value['email'])): ?>
                                        <small><i class="bx bx-envelope text-muted me-1"></i><?= htmlspecialchars($value['email']) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($value['linkedin'])): ?>
                                        <small><i class="bx bxl-linkedin text-primary me-1"></i><a href="<?= htmlspecialchars($value['linkedin']) ?>" target="_blank">LinkedIn</a></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <?php if (!empty($value['specialite'])): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                        <?= htmlspecialchars($value['specialite']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_membre'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['est_admin']) && $value['est_admin'] == 1): ?>
                                        <span class="badge bg-danger"><i class="bx bx-shield"></i> Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">Membre</span>
                                    <?php endif; ?>
                                </a>
                            </td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('Equipe/MembreDetail/'.$value['id_membre']) ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_membre'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_membre'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id_membre'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le membre</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Equipe/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_membre" value="<?= $value['id_membre'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($value['nom'] ?? '') ?>" required>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="prenom" value="<?= htmlspecialchars($value['prenom'] ?? '') ?>" required>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">Poste <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="poste" value="<?= htmlspecialchars($value['poste'] ?? '') ?>" required>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Email</label>
                                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($value['email'] ?? '') ?>">
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">LinkedIn</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bx bxl-linkedin"></i></span>
                                                        <input type="url" class="form-control" name="linkedin" value="<?= htmlspecialchars($value['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/...">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Spécialité</label>
                                                    <input type="text" class="form-control" name="specialite" value="<?= htmlspecialchars($value['specialite'] ?? '') ?>" placeholder="Ex: PhD in Clinical Pharmacology">
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold">Ordre d'affichage</label>
                                                    <input type="number" class="form-control" name="ordre_affichage" value="<?= $value['ordre_affichage'] ?? 0 ?>" min="0">
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold">Page associée</label>
                                                    <select class="form-select" name="id_page_associee">
                                                        <option value="">Global</option>
                                                        <?php foreach ($pages as $page): ?>
                                                            <option value="<?= $page['id_page'] ?>" <?= ($value['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($page['titre_page']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Biographie</label>
                                                    <textarea class="form-control" name="biographie" rows="3" placeholder="Biographie du membre..."><?= htmlspecialchars($value['biographie'] ?? '') ?></textarea>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Photo</label>
                                                    <div class="input-group mb-2">
                                                        <span class="input-group-text"><i class="bx bx-image"></i></span>
                                                        <input type="text" class="form-control" name="photo_url" value="<?= htmlspecialchars($value['photo_url'] ?? '') ?>" placeholder="/images/team/member.jpg">
                                                    </div>
                                                    <input type="file" class="form-control" name="photo" accept="image/*">
                                                    <?php if (!empty($value['photo_url'])): ?>
                                                        <small class="text-muted">Actuelle: <?= basename($value['photo_url']) ?></small>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-check form-switch mt-4">
                                                        <input type="checkbox" class="form-check-input" name="est_admin" id="est_admin_<?= $value['id_membre'] ?>" value="1" <?= (!empty($value['est_admin']) && $value['est_admin'] == 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="est_admin_<?= $value['id_membre'] ?>">Administrateur (accès privilégié)</label>
                                                    </div>
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
                        <div class="modal fade" id="delete_<?= $value['id_membre'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer <strong><?= htmlspecialchars(($value['prenom'] ?? '').' '.($value['nom'] ?? '')) ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Equipe/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_membre'] ?>">
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

                        <!-- MODAL STATUS ADMIN -->
                        <div class="modal fade" id="status_<?= $value['id_membre'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['est_admin']) && $value['est_admin'] == 1) ? 'bg-secondary' : 'bg-danger' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['est_admin']) && $value['est_admin'] == 1) ? '<i class="bx bx-user me-2"></i>Retirer admin' : '<i class="bx bx-shield me-2"></i>Promouvoir admin' ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['est_admin']) && $value['est_admin'] == 1) ? 'retirer les droits administrateur de' : 'promouvoir comme administrateur' ?></strong> <strong><?= htmlspecialchars(($value['prenom'] ?? '').' '.($value['nom'] ?? '')) ?></strong> ?</p>
                                    </div>
                                    <form action="<?= base_url('Equipe/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_membre'] ?>">
                                        <input type="hidden" name="est_admin" value="<?= (!empty($value['est_admin']) && $value['est_admin'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['est_admin']) && $value['est_admin'] == 1) ? 'btn-secondary' : 'btn-danger' ?>">
                                                <?= (!empty($value['est_admin']) && $value['est_admin'] == 1) ? 'Retirer admin' : 'Promouvoir admin' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bx bx-group text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun membre de l'équipe trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- MODAL CREATE MEMBRE -->
<div class="modal fade" id="create_membre" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouveau Membre</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Equipe/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="prenom" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Poste <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="poste" required placeholder="Ex: Chief Executive Officer">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">LinkedIn</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bxl-linkedin"></i></span>
                                <input type="url" class="form-control" name="linkedin" placeholder="https://linkedin.com/in/...">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Spécialité</label>
                            <input type="text" class="form-control" name="specialite" placeholder="Ex: PhD in Clinical Pharmacology">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Ordre d'affichage</label>
                            <input type="number" class="form-control" name="ordre_affichage" value="0" min="0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Page associée</label>
                            <select class="form-select" name="id_page_associee">
                                <option value="">Global</option>
                                <?php foreach ($pages as $page): ?>
                                    <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Biographie</label>
                            <textarea class="form-control" name="biographie" rows="3" placeholder="Biographie du membre..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Photo</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="bx bx-image"></i></span>
                                <input type="text" class="form-control" name="photo_url" placeholder="/images/team/member.jpg">
                            </div>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input type="checkbox" class="form-check-input" name="est_admin" id="create_est_admin" value="1">
                                <label class="form-check-label" for="create_est_admin">Administrateur (accès privilégié)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer le membre
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#equipeTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [1, 7] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>