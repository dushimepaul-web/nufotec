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
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Catégories</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_category">
                <i class="bx bx-plus"></i> Nouvelle Catégorie
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
                <h5 class="mb-0 text-primary"><i class="bx bx-category me-2"></i>Liste des Catégories</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="categoriesTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="8%">Code</th>
                            <th width="10%">Image</th>
                            <th width="20%">Nom</th>
                            <th width="30%">Description Courte</th>
                            <th width="10%">Icône</th>
                            <th width="17%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($categories)): $i = 1; foreach ($categories as $value): 
                        // Image path
                        $image_path = !empty($value['image_url']) ? 'attachments/Categories/'.$value['image_url'] : 'assets/images/category-placeholder.png';
                        
                        // Icône Bootstrap
                        $icone_class = !empty($value['icone']) ? $value['icone'] : 'tag';
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            
                            <td>
                                <span class="badge bg-primary fs-6"><?= htmlspecialchars($value['code_categorie']) ?></span>
                            </td>

                            <td>
                                <img src="<?= base_url($image_path) ?>" 
                                     class="rounded border"
                                     style="width:60px; height:60px; object-fit:cover;"
                                     onerror="this.src='<?= base_url('assets/images/category-placeholder.png') ?>'"
                                     alt="<?= htmlspecialchars($value['nom_categorie']) ?>">
                            </td>

                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($value['nom_categorie']) ?></strong>
                            </td>

                            <td>
                                <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                                    <?= htmlspecialchars(substr($value['description_courte'], 0, 100)) ?>
                                    <?= strlen($value['description_courte']) > 100 ? '...' : '' ?>
                                </p>
                            </td>

                            <td class="text-center">
                                <i class="bi bi-<?= htmlspecialchars($icone_class) ?> fs-3 text-primary"></i>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($icone_class) ?></small>
                            </td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_categorie'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_categorie'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_categorie'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id_categorie'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-category me-2"></i>Détails de la Catégorie</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 text-center border-end">
                                                <img src="<?= base_url($image_path) ?>" 
                                                     class="rounded border border-3 border-primary mb-3"
                                                     style="width:150px; height:150px; object-fit:cover;"
                                                     onerror="this.src='<?= base_url('assets/images/category-placeholder.png') ?>'"
                                                     alt="<?= htmlspecialchars($value['nom_categorie']) ?>">
                                                <h4 class="mb-1"><?= htmlspecialchars($value['nom_categorie']) ?></h4>
                                                <span class="badge bg-primary fs-5">Code <?= htmlspecialchars($value['code_categorie']) ?></span>
                                                <div class="mt-3">
                                                    <i class="bi bi-<?= htmlspecialchars($icone_class) ?> fs-1 text-primary"></i>
                                                    <p class="text-muted small">Icône: <?= htmlspecialchars($icone_class) ?></p>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="mb-4">
                                                    <h6 class="text-primary border-bottom pb-2">Description Courte</h6>
                                                    <p><?= nl2br(htmlspecialchars($value['description_courte'])) ?></p>
                                                </div>
                                                
                                                <?php if (!empty($value['description_longue'])): ?>
                                                <div class="mb-4">
                                                    <h6 class="text-primary border-bottom pb-2">Description Longue</h6>
                                                    <p><?= nl2br(htmlspecialchars($value['description_longue'])) ?></p>
                                                </div>
                                                <?php endif; ?>

                                                <div class="alert alert-info">
                                                    <i class="bx bx-info-circle me-2"></i>
                                                    Cette catégorie est utilisée pour classer les produits et services de type <strong><?= htmlspecialchars($value['nom_categorie']) ?></strong>.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id_categorie'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la Catégorie</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Categories/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_categorie" value="<?= $value['id_categorie'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-2">
                                                    <label class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control text-center fw-bold" name="code_categorie" 
                                                           value="<?= htmlspecialchars($value['code_categorie']) ?>" 
                                                           maxlength="1" required style="text-transform: uppercase;">
                                                    <small class="text-muted">1 caractère</small>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Nom de la catégorie <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="nom_categorie" 
                                                           value="<?= htmlspecialchars($value['nom_categorie']) ?>" required>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">Icône (Bootstrap Icons)</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-<?= htmlspecialchars($icone_class) ?>"></i></span>
                                                        <input type="text" class="form-control" name="icone" 
                                                               value="<?= htmlspecialchars($value['icone']) ?>" 
                                                               placeholder="Ex: capsule-pill">
                                                    </div>
                                                    <small class="text-muted">Nom de l'icône Bootstrap</small>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Description Courte <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="description_courte" rows="2" required><?= htmlspecialchars($value['description_courte']) ?></textarea>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Description Longue</label>
                                                    <textarea class="form-control" name="description_longue" rows="4"><?= htmlspecialchars($value['description_longue'] ?? '') ?></textarea>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Image de la catégorie</label>
                                                    <input type="file" class="form-control" name="image_url" accept="image/*">
                                                    <?php if (!empty($value['image_url'])): ?>
                                                        <small class="text-muted">Actuelle: <?= $value['image_url'] ?></small>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="col-md-6">
                                                    <?php if (!empty($value['image_url'])): ?>
                                                        <img src="<?= base_url($image_path) ?>" 
                                                             class="rounded border mt-2"
                                                             style="width:100px; height:100px; object-fit:cover;"
                                                             alt="Aperçu">
                                                    <?php endif; ?>
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
                        <div class="modal fade" id="delete_<?= $value['id_categorie'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer <strong><?= htmlspecialchars($value['nom_categorie']) ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                        <div class="alert alert-warning">
                                            <i class="bx bx-error me-2"></i>
                                            Attention: Les produits associés à cette catégorie pourraient être affectés.
                                        </div>
                                    </div>
                                    <form action="<?= base_url('Categories/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_categorie'] ?>">
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
                            <td colspan="7" class="text-center py-5">
                                <i class="bx bx-category text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucune catégorie trouvée</p>
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

<!-- MODAL CREATE CATEGORY -->
<div class="modal fade" id="create_category" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle Catégorie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Categories/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-center fw-bold" name="code_categorie" 
                                   maxlength="1" required style="text-transform: uppercase;" placeholder="A">
                            <small class="text-muted">1 caractère unique</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nom de la catégorie <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nom_categorie" required 
                                   placeholder="Ex: CAMs (Traditional Medicines)">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Icône (Bootstrap Icons)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <input type="text" class="form-control" name="icone" 
                                       placeholder="Ex: capsule-pill">
                            </div>
                            <small class="text-muted"><a href="https://icons.getbootstrap.com/" target="_blank">Voir les icônes</a></small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Description Courte <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description_courte" rows="2" required
                                      placeholder="Brève description visible dans les listes..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Description Longue</label>
                            <textarea class="form-control" name="description_longue" rows="4"
                                      placeholder="Description détaillée complète..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Image de la catégorie</label>
                            <input type="file" class="form-control" name="image_url" accept="image/*">
                            <small class="text-muted">Formats: JPG, PNG, GIF, WEBP (max 2MB)</small>
                        </div>

                        <div class="col-md-6">
                            <div class="alert alert-info h-100 mb-0">
                                <h6><i class="bx bx-info-circle me-2"></i>Conseils</h6>
                                <ul class="mb-0 small">
                                    <li>Le code doit être unique (1 lettre)</li>
                                    <li>Utilisez des icônes <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a></li>
                                    <li>Image recommandée: 400x400px</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer la catégorie
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#categoriesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[1, 'asc']], // Tri par code
        pageLength: 10,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [2, 6] } // Image et Actions non triables
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Preview image avant upload
document.querySelectorAll('input[type="file"][name="image_url"]').forEach(function(input) {
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validation taille
            if (file.size > 2 * 1024 * 1024) {
                alert('L\'image ne doit pas dépasser 2MB');
                this.value = '';
                return;
            }
        }
    });
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>