<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
// Liste des icônes Boxicons courantes pour les services
$icons_list = [
    'gear' => 'Paramètres / Configuration',
    'microscope' => 'Laboratoire / Recherche',
    'flask' => 'R&D / Chimie',
    'chat' => 'Consulting / Communication',
    'people' => 'Groupe / Communauté',
    'truck' => 'Transport / Logistique',
    'leaf' => 'Nature / Agriculture',
    'heart' => 'Santé / Bien-être',
    'briefcase' => 'Business / Professionnel',
    'chart' => 'Statistiques / Analyse',
    'world' => 'International / Global',
    'shield' => 'Sécurité / Protection',
    'wrench' => 'Outils / Maintenance',
    'book' => 'Formation / Documentation',
    'phone' => 'Contact / Support',
    'envelope' => 'Email / Message',
    'map' => 'Localisation / Géographie',
    'calendar' => 'Planification / Événements',
    'star' => 'Qualité / Excellence',
    'award' => 'Certification / Prix'
];
?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Services</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Services</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_service">
                <i class="bx bx-plus"></i> Nouveau Service
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
                <h5 class="mb-0 text-primary"><i class="bx bx-briefcase me-2"></i>Liste des Services</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="servicesTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">Ordre</th>
                            <th width="8%">Visuel</th>
                            <th width="20%">Titre</th>
                            <th width="30%">Description</th>
                            <th width="12%">Lien</th>
                            <th width="10%">Page</th>
                            <th width="8%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($services)): foreach ($services as $value): 
                        // Récupérer le nom de la page associée
                        $page_titre = '-';
                        if (!empty($value['id_page_associee']) && !empty($pages)) {
                            foreach ($pages as $page) {
                                if ($page['id_page'] == $value['id_page_associee']) {
                                    $page_titre = $page['titre_page'];
                                    break;
                                }
                            }
                        }
                        
                        $icon_label = $icons_list[$value['icone'] ?? ''] ?? 'Icône personnalisée';
                    ?>
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border"><?= $value['ordre'] ?? 0 ?></span>
                            </td>

                            <td class="text-center">
                                <?php if (!empty($value['image_url'])): ?>
                                    <img src="<?= base_url($value['image_url']) ?>" alt="Image" class="rounded" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.style.display='none'">
                                <?php elseif (!empty($value['icone'])): ?>
                                    <div class="bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center mx-auto" style="width: 50px; height: 50px;">
                                        <i class="bx bx-<?= htmlspecialchars($value['icone']) ?> text-primary fs-3"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width: 50px; height: 50px;">
                                        <i class="bx bx-briefcase text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['titre']) ?></strong>
                                    <?php if (!empty($value['icone'])): ?>
                                        <small class="text-muted"><i class="bx bx-<?= htmlspecialchars($value['icone']) ?> me-1"></i><?= $icon_label ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <div class="text-muted small" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                    <?= !empty($value['description']) ? htmlspecialchars($value['description']) : '<em class="text-muted">Aucune description</em>' ?>
                                </div>
                            </td>

                            <td class="text-center">
                                <?php if (!empty($value['lien'])): ?>
                                    <a href="<?= htmlspecialchars($value['lien']) ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Ouvrir le lien">
                                        <i class="bx bx-link-external"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <?php if ($page_titre != '-'): ?>
                                    <small class="text-info"><?= htmlspecialchars(strlen($page_titre) > 15 ? substr($page_titre, 0, 15).'...' : $page_titre) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_service'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_service'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_service'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id_service'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-briefcase me-2"></i>Détails du Service</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 text-center mb-3 border-end">
                                                <?php if (!empty($value['image_url'])): ?>
                                                    <img src="<?= base_url($value['image_url']) ?>" alt="Image" class="img-fluid rounded mb-2" style="max-height: 150px; object-fit: cover;" onerror="this.src='<?= base_url('attachments/services/default-service.png') ?>'">
                                                <?php elseif (!empty($value['icone'])): ?>
                                                    <div class="bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 120px; height: 120px;">
                                                        <i class="bx bx-<?= htmlspecialchars($value['icone']) ?> text-primary" style="font-size: 4rem;"></i>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 120px; height: 120px;">
                                                        <i class="bx bx-briefcase text-muted" style="font-size: 3rem;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <span class="badge bg-light text-dark border">
                                                    <i class="bx bx-sort me-1"></i>Ordre: <?= $value['ordre'] ?? 0 ?>
                                                </span>
                                            </div>
                                            <div class="col-md-8">
                                                <h4 class="mb-3"><?= htmlspecialchars($value['titre']) ?></h4>
                                                
                                                <?php if (!empty($value['description'])): ?>
                                                    <div class="card border-0 bg-light mb-3">
                                                        <div class="card-body">
                                                            <h6 class="text-muted mb-2"><i class="bx bx-file-text me-2"></i>Description</h6>
                                                            <p class="mb-0"><?= nl2br(htmlspecialchars($value['description'])) ?></p>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="row g-2">
                                                    <?php if (!empty($value['lien'])): ?>
                                                    <div class="col-12">
                                                        <label class="text-muted small">Lien externe</label>
                                                        <p class="mb-0">
                                                            <a href="<?= htmlspecialchars($value['lien']) ?>" target="_blank" class="text-decoration-none">
                                                                <i class="bx bx-link-external me-1"></i><?= htmlspecialchars($value['lien']) ?>
                                                            </a>
                                                        </p>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($value['icone'])): ?>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Icône</label>
                                                        <p class="mb-0">
                                                            <i class="bx bx-<?= htmlspecialchars($value['icone']) ?> me-1"></i>
                                                            <?= $icon_label ?>
                                                        </p>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($page_titre != '-'): ?>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Page associée</label>
                                                        <p class="mb-0 text-primary"><?= htmlspecialchars($page_titre) ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="col-6">
                                                        <label class="text-muted small">ID Service</label>
                                                        <p class="mb-0 font-monospace small text-muted">#<?= $value['id_service'] ?></p>
                                                    </div>
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
                        <div class="modal fade" id="update_<?= $value['id_service'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le Service</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Services/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $value['id_service'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Titre du service <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($value['titre']) ?>" required>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Description</label>
                                                            <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Visuel & Icône</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Image actuelle</label>
                                                            <?php if (!empty($value['image_url'])): ?>
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <img src="<?= base_url($value['image_url']) ?>" class="rounded" style="height: 50px; width: 50px; object-fit: cover;" onerror="this.style.display='none'">
                                                                    <span class="ms-2 text-muted small"><?= basename($value['image_url']) ?></span>
                                                                </div>
                                                            <?php else: ?>
                                                                <p class="text-muted small">Aucune image</p>
                                                            <?php endif; ?>
                                                            <label class="form-label fw-bold">Nouvelle image</label>
                                                            <input type="file" class="form-control" name="image" accept="image/*">
                                                            <small class="text-muted">JPG, PNG, SVG, WEBP (max 2Mo). Laissez vide pour conserver l'actuelle.</small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Icône (Boxicons)</label>
                                                            <select class="form-select" name="icone">
                                                                <option value="">Aucune icône</option>
                                                                <?php foreach ($icons_list as $icon => $label): ?>
                                                                    <option value="<?= $icon ?>" <?= ($value['icone'] ?? '') == $icon ? 'selected' : '' ?>>
                                                                        <?= $label ?> (bx-<?= $icon ?>)
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <small class="text-muted">Affichée si pas d'image</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-link me-2"></i>Liens & Organisation</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Lien externe</label>
                                                            <input type="url" class="form-control" name="lien" value="<?= htmlspecialchars($value['lien'] ?? '') ?>" placeholder="https://...">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Ordre d'affichage</label>
                                                            <input type="number" class="form-control" name="ordre" value="<?= $value['ordre'] ?? 0 ?>" min="0">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Page associée</label>
                                                            <select class="form-select" name="id_page_associee">
                                                                <option value="">Aucune page</option>
                                                                <?php foreach ($pages as $page): ?>
                                                                    <option value="<?= $page['id_page'] ?>" <?= ($value['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($page['titre_page']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-warning"><i class="bx bx-save me-1"></i>Enregistrer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $value['id_service'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        </div>
                                        <h5 class="mb-2">Êtes-vous sûr ?</h5>
                                        <p class="text-muted mb-0">
                                            Vous allez supprimer le service:<br>
                                            <strong><?= htmlspecialchars($value['titre']) ?></strong>
                                        </p>
                                        <p class="text-danger small mt-2">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Services/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_service'] ?>">
                                        <div class="modal-footer bg-light justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bx bx-trash me-1"></i>Supprimer définitivement
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bx bx-briefcase" style="font-size: 3rem;"></i>
                                    <p class="mt-2 mb-0">Aucun service enregistré</p>
                                    <small>Cliquez sur "Nouveau Service" pour ajouter</small>
                                </div>
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

<!-- MODAL CREATE -->
<div class="modal fade" id="create_service" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouveau Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Services/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Titre du service <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="titre" placeholder="Ex: Contract Manufacturing" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea class="form-control" name="description" rows="3" placeholder="Description du service..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Visuel & Icône</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Image</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                    <small class="text-muted">JPG, PNG, SVG, WEBP (max 2Mo)</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Icône (Boxicons)</label>
                                    <select class="form-select" name="icone">
                                        <option value="">Aucune icône</option>
                                        <?php foreach ($icons_list as $icon => $label): ?>
                                            <option value="<?= $icon ?>"><?= $label ?> (bx-<?= $icon ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Affichée si pas d'image</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-link me-2"></i>Liens & Organisation</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Lien externe</label>
                                    <input type="url" class="form-control" name="lien" placeholder="https://...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Ordre d'affichage</label>
                                    <input type="number" class="form-control" name="ordre" value="0" min="0">
                                    <small class="text-muted">0 = premier</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Page associée</label>
                                    <select class="form-select" name="id_page_associee">
                                        <option value="">Aucune page</option>
                                        <?php foreach ($pages as $page): ?>
                                            <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-save me-1"></i>Créer le service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#servicesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'asc']], // Sort by ordre
        pageLength: 25,
        responsive: true
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>