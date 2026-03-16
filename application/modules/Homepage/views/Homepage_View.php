<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">CMS</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Pages</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_page">
                <i class="bx bx-plus"></i> Nouvelle Page
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
                <h5 class="mb-0 text-primary"><i class="bx bx-file me-2"></i>Liste des Pages</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">Ordre</th>
                            <th width="20%">Titre</th>
                            <th width="15%">Slug</th>
                            <th width="12%">Template</th>
                            <th width="10%">Parent</th>
                            <th width="8%">Statut</th>
                            <th width="10%">Modifié le</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($pages)): foreach ($pages as $value): 
                        // Récupérer le parent si existe
                        $parent_name = '-';
                        if (!empty($value['menu_parent_id'])) {
                            foreach ($pages_list as $p) {
                                if ($p['id_page'] == $value['menu_parent_id']) {
                                    $parent_name = $p['titre_page'];
                                    break;
                                }
                            }
                        }
                        
                        // Badge template
                        $template_badges = [
                            'default' => '<span class="badge bg-secondary">Default</span>',
                            'home' => '<span class="badge bg-primary"><i class="bx bx-home"></i> Home</span>',
                            'about' => '<span class="badge bg-info"><i class="bx bx-info-circle"></i> About</span>',
                            'esg' => '<span class="badge bg-success"><i class="bx bx-leaf"></i> ESG</span>',
                            'research' => '<span class="badge bg-warning text-dark"><i class="bx bx-flask"></i> Research</span>',
                            'governance' => '<span class="badge bg-dark"><i class="bx bx-building"></i> Governance</span>',
                            'facility' => '<span class="badge bg-secondary"><i class="bx bx-factory"></i> Facility</span>',
                            'products' => '<span class="badge bg-primary"><i class="bx bx-package"></i> Products</span>',
                            'tech' => '<span class="badge bg-info"><i class="bx bx-cog"></i> Tech</span>',
                            'market' => '<span class="badge bg-success"><i class="bx bx-trending-up"></i> Market</span>',
                            'digital' => '<span class="badge bg-warning text-dark"><i class="bx bx-digital"></i> Digital</span>',
                            'health' => '<span class="badge bg-danger"><i class="bx bx-health"></i> Health</span>',
                            'investment' => '<span class="badge bg-success"><i class="bx bx-money"></i> Investment</span>',
                            'investor' => '<span class="badge bg-primary"><i class="bx bx-user-check"></i> Investor</span>',
                            'brokers' => '<span class="badge bg-dark"><i class="bx bx-transfer"></i> Brokers</span>',
                            'risk' => '<span class="badge bg-danger"><i class="bx bx-shield-x"></i> Risk</span>',
                            'partners' => '<span class="badge bg-info"><i class="bx bx-group"></i> Partners</span>',
                            'services' => '<span class="badge bg-secondary"><i class="bx bx-wrench"></i> Services</span>'
                        ];
                        
                        $template_badge = $template_badges[$value['template_specifique'] ?? 'default'] ?? '<span class="badge bg-light text-dark">Default</span>';
                    ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border"><?= $value['menu_ordre'] ?? 0 ?></span></td>
                            
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($value['icone_menu'])): ?>
                                        <i class="bx <?= $value['icone_menu'] ?> text-primary me-2 fs-5"></i>
                                    <?php else: ?>
                                        <i class="bx bx-file text-muted me-2 fs-5"></i>
                                    <?php endif; ?>
                                    <div>
                                        <strong class="text-dark"><?= htmlspecialchars($value['titre_page'] ?? '') ?></strong>
                                        <?php if (!empty($value['meta_description'])): ?>
                                            <small class="text-muted d-block text-truncate" style="max-width: 200px;">
                                                <?= htmlspecialchars(substr($value['meta_description'], 0, 50)) ?>...
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <code class="bg-light px-2 py-1 rounded"><?= htmlspecialchars($value['slug'] ?? '') ?></code>
                            </td>

                            <td><?= $template_badge ?></td>

                            <td>
                                <?php if ($parent_name != '-'): ?>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bx bx-link me-1"></i><?= htmlspecialchars($parent_name) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Racine</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_page'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['est_publiee']) && $value['est_publiee'] == 1): ?>
                                        <span class="badge bg-success"><i class="bx bx-check"></i> Publiée</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="bx bx-hide"></i> Brouillon</span>
                                    <?php endif; ?>
                                </a>
                            </td>

                            <td><?= !empty($value['date_modification']) ? date('d/m/Y H:i', strtotime($value['date_modification'])) : '-' ?></td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('Homepage/PageDetail/'.$value['id_page'].'_'.urlencode($value['slug'])) ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_page'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_page'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id_page'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la page</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Homepage/Update') ?>" method="POST">
                                        <input type="hidden" name="id_page" value="<?= $value['id_page'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <label class="form-label fw-bold">Titre de la page <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="titre_page" value="<?= htmlspecialchars($value['titre_page'] ?? '') ?>" required onkeyup="generateSlugUpdate(this.value, <?= $value['id_page'] ?>)">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">Ordre menu <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" name="menu_ordre" value="<?= $value['menu_ordre'] ?? 0 ?>" required min="0">
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Slug <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="slug" id="slug_update_<?= $value['id_page'] ?>" value="<?= htmlspecialchars($value['slug'] ?? '') ?>" required>
                                                    <small class="text-muted">URL de la page</small>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Page parente</label>
                                                    <select class="form-select" name="menu_parent_id">
                                                        <option value="">Aucune (Racine)</option>
                                                        <?php foreach ($pages_list as $p): ?>
                                                            <?php if ($p['id_page'] != $value['id_page']): ?>
                                                                <option value="<?= $p['id_page'] ?>" <?= ($value['menu_parent_id'] ?? '') == $p['id_page'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($p['titre_page']) ?>
                                                                </option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Template spécifique</label>
                                                    <select class="form-select" name="template_specifique">
                                                        <option value="default" <?= ($value['template_specifique'] ?? '') == 'default' ? 'selected' : '' ?>>Default</option>
                                                        <option value="home" <?= ($value['template_specifique'] ?? '') == 'home' ? 'selected' : '' ?>>Home</option>
                                                        <option value="about" <?= ($value['template_specifique'] ?? '') == 'about' ? 'selected' : '' ?>>About Us</option>
                                                        <option value="esg" <?= ($value['template_specifique'] ?? '') == 'esg' ? 'selected' : '' ?>>ESG & Sustainability</option>
                                                        <option value="research" <?= ($value['template_specifique'] ?? '') == 'research' ? 'selected' : '' ?>>Research & Innovation</option>
                                                        <option value="governance" <?= ($value['template_specifique'] ?? '') == 'governance' ? 'selected' : '' ?>>Corporate Governance</option>
                                                        <option value="facility" <?= ($value['template_specifique'] ?? '') == 'facility' ? 'selected' : '' ?>>Facility</option>
                                                        <option value="products" <?= ($value['template_specifique'] ?? '') == 'products' ? 'selected' : '' ?>>Products</option>
                                                        <option value="tech" <?= ($value['template_specifique'] ?? '') == 'tech' ? 'selected' : '' ?>>Technology</option>
                                                        <option value="market" <?= ($value['template_specifique'] ?? '') == 'market' ? 'selected' : '' ?>>Market</option>
                                                        <option value="digital" <?= ($value['template_specifique'] ?? '') == 'digital' ? 'selected' : '' ?>>Digital Growth</option>
                                                        <option value="health" <?= ($value['template_specifique'] ?? '') == 'health' ? 'selected' : '' ?>>Digital Health</option>
                                                        <option value="investment" <?= ($value['template_specifique'] ?? '') == 'investment' ? 'selected' : '' ?>>Investment</option>
                                                        <option value="investor" <?= ($value['template_specifique'] ?? '') == 'investor' ? 'selected' : '' ?>>Investor Commitment</option>
                                                        <option value="brokers" <?= ($value['template_specifique'] ?? '') == 'brokers' ? 'selected' : '' ?>>Brokers</option>
                                                        <option value="risk" <?= ($value['template_specifique'] ?? '') == 'risk' ? 'selected' : '' ?>>Risk Analysis</option>
                                                        <option value="partners" <?= ($value['template_specifique'] ?? '') == 'partners' ? 'selected' : '' ?>>Partners</option>
                                                        <option value="services" <?= ($value['template_specifique'] ?? '') == 'services' ? 'selected' : '' ?>>Services</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Icône menu (classe Bootstrap)</label>
                                                    <input type="text" class="form-control" name="icone_menu" value="<?= htmlspecialchars($value['icone_menu'] ?? '') ?>" placeholder="Ex: bx-home">
                                                    <small class="text-muted"><a href="https://boxicons.com/" target="_blank">Voir les icônes Boxicons</a></small>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Meta Description</label>
                                                    <textarea class="form-control" name="meta_description" rows="2"><?= htmlspecialchars($value['meta_description'] ?? '') ?></textarea>
                                                    <small class="text-muted">Description pour le référencement SEO</small>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Meta Keywords</label>
                                                    <input type="text" class="form-control" name="meta_keywords" value="<?= htmlspecialchars($value['meta_keywords'] ?? '') ?>" placeholder="Mots-clés séparés par des virgules">
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" name="est_publiee" id="est_publiee_<?= $value['id_page'] ?>" value="1" <?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="est_publiee_<?= $value['id_page'] ?>">Page publiée (visible sur le site)</label>
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
                        <div class="modal fade" id="delete_<?= $value['id_page'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer la page <strong><?= htmlspecialchars($value['titre_page'] ?? '') ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible (suppression logique).</p>
                                    </div>
                                    <form action="<?= base_url('Homepage/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_page'] ?>">
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

                        <!-- MODAL STATUS -->
                        <div class="modal fade" id="status_<?= $value['id_page'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? '<i class="bx bx-hide me-2"></i>Dépublier' : '<i class="bx bx-show me-2"></i>Publier' ?> la page
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? 'dépublier' : 'publier' ?></strong> la page <strong><?= htmlspecialchars($value['titre_page'] ?? '') ?></strong> ?</p>
                                    </div>
                                    <form action="<?= base_url('Homepage/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_page'] ?>">
                                        <input type="hidden" name="est_publiee" value="<?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? 'btn-warning' : 'btn-success' ?>">
                                                <?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? 'Dépublier' : 'Publier' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bx bx-file-blank text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucune page trouvée</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- MODAL CREATE PAGE -->
<div class="modal fade" id="create_page" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle Page</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Homepage/Create') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Titre de la page <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="titre_page" required onkeyup="generateSlug(this.value)">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ordre menu <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="menu_ordre" value="0" required min="0">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="slug" id="create_slug" required>
                            <small class="text-muted">URL de la page (générée automatiquement)</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Page parente</label>
                            <select class="form-select" name="menu_parent_id">
                                <option value="">Aucune (Racine)</option>
                                <?php foreach ($pages_list as $p): ?>
                                    <option value="<?= $p['id_page'] ?>"><?= htmlspecialchars($p['titre_page']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Template spécifique</label>
                            <select class="form-select" name="template_specifique">
                                <option value="default" selected>Default</option>
                                <option value="home">Home</option>
                                <option value="about">About Us</option>
                                <option value="esg">ESG & Sustainability</option>
                                <option value="research">Research & Innovation</option>
                                <option value="governance">Corporate Governance</option>
                                <option value="facility">Facility</option>
                                <option value="products">Products</option>
                                <option value="tech">Technology</option>
                                <option value="market">Market</option>
                                <option value="digital">Digital Growth</option>
                                <option value="health">Digital Health</option>
                                <option value="investment">Investment</option>
                                <option value="investor">Investor Commitment</option>
                                <option value="brokers">Brokers</option>
                                <option value="risk">Risk Analysis</option>
                                <option value="partners">Partners</option>
                                <option value="services">Services</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Icône menu (classe Bootstrap)</label>
                            <input type="text" class="form-control" name="icone_menu" placeholder="Ex: bx-home">
                            <small class="text-muted"><a href="https://boxicons.com/" target="_blank">Voir les icônes Boxicons</a></small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Description</label>
                            <textarea class="form-control" name="meta_description" rows="2" placeholder="Description pour le référencement SEO"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Keywords</label>
                            <input type="text" class="form-control" name="meta_keywords" placeholder="Mots-clés séparés par des virgules">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer la page
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#pagesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [7] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Génération automatique du slug
function generateSlug(titre) {
    const slug = titre.toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .substring(0, 100);
    document.getElementById('create_slug').value = slug;
}

function generateSlugUpdate(titre, id) {
    const slug = titre.toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .substring(0, 100);
    document.getElementById('slug_update_' + id).value = slug;
}
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>