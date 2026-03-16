<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
// Définir les catégories et badges directement dans la vue
$categories_list = [
    'entreprise' => 'Entreprise',
    'produits' => 'Produits',
    'recherche' => 'Recherche & Innovation',
    'partenariats' => 'Partenariats',
    'esg' => 'ESG & Durabilité',
    'evenements' => 'Événements',
    'presse' => 'Presse',
    'blog' => 'Blog'
];

$categorie_badges = [
    'entreprise' => 'bg-primary',
    'produits' => 'bg-success',
    'recherche' => 'bg-info',
    'partenariats' => 'bg-warning text-dark',
    'esg' => 'bg-success',
    'evenements' => 'bg-danger',
    'presse' => 'bg-secondary',
    'blog' => 'bg-light text-dark'
];

// Fonction pour obtenir le label d'une catégorie
function get_categorie_label($categorie_key, $categories) {
    return $categories[$categorie_key] ?? ucfirst($categorie_key ?? 'Non classé');
}

// Fonction pour obtenir le badge d'une catégorie
function get_categorie_badge($categorie_key, $badges) {
    return $badges[$categorie_key] ?? 'bg-light text-dark';
}
?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Contenu</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Actualités</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_actualite">
                <i class="bx bx-plus"></i> Nouvelle Actualité
            </a>
        </div>
    </div>

    <!-- Messages flash -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-check-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-error-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-primary"><i class="bx bx-news me-2"></i>Liste des Actualités & Blog</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="actualitesTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Image</th>
                            <th width="25%">Titre</th>
                            <th width="12%">Catégorie</th>
                            <th width="10%">Auteur</th>
                            <th width="8%">Vues</th>
                            <th width="8%">En avant</th>
                            <th width="10%">Date Pub.</th>
                            <th width="12%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($actualites)): $i = 1; foreach ($actualites as $value): 
                        // Catégories - utilisation des tableaux définis localement
                        $categorie_label = get_categorie_label($value['categorie'] ?? '', $categories_list);
                        $categorie_badge = get_categorie_badge($value['categorie'] ?? '', $categorie_badges);
                        
                        // Image
                        $image_path = !empty($value['image_principale']) ? 'attachments/Actualites/'.$value['image_principale'] : 'assets/images/no-image.png';
                        
                        // Tags
                        $tags = [];
                        if (!empty($value['tags'])) {
                            $tags = is_string($value['tags']) ? json_decode($value['tags'], true) : [];
                            $tags = is_array($tags) ? $tags : [];
                        }
                        
                        // Page associée
                        $page_titre = 'Non associée';
                        if (!empty($value['id_page_associee']) && !empty($pages)) {
                            foreach ($pages as $page) {
                                if (isset($page['id_page']) && $page['id_page'] == $value['id_page_associee']) {
                                    $page_titre = $page['titre_page'] ?? 'Non associée';
                                    break;
                                }
                            }
                        }
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            
                            <td>
                                <img src="<?= base_url($image_path) ?>" 
                                     class="rounded border"
                                     style="width:80px; height:60px; object-fit:cover;"
                                     onerror="this.src='<?= base_url('assets/images/no-image.png') ?>'"
                                     alt="<?= htmlspecialchars($value['titre'] ?? '') ?>">
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['titre'] ?? '') ?></strong>
                                    <?php if (!empty($value['resume'])): ?>
                                        <small class="text-muted text-truncate" style="max-width: 300px;"><?= htmlspecialchars($value['resume']) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($tags)): ?>
                                        <div class="mt-1">
                                            <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                                                <?php if (!empty($tag)): ?>
                                                <span class="badge bg-light text-dark border me-1">#<?= htmlspecialchars(trim($tag)) ?></span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                            <?php if (count($tags) > 3): ?>
                                                <span class="badge bg-light text-dark border">+<?= count($tags) - 3 ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td><span class="badge <?= $categorie_badge ?>"><?= htmlspecialchars($categorie_label) ?></span></td>

                            <td><?= htmlspecialchars($value['auteur'] ?? '-') ?></td>

                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    <i class="bx bx-show me-1"></i><?= number_format($value['vues'] ?? 0) ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_actualite'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['est_en_avant']) && $value['est_en_avant'] == 1): ?>
                                        <span class="badge bg-warning text-dark"><i class="bx bx-star me-1"></i>À la une</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">Standard</span>
                                    <?php endif; ?>
                                </a>
                            </td>

                            <td><?= !empty($value['date_publication']) ? date('d/m/Y', strtotime($value['date_publication'])) : '-' ?></td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_actualite'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_actualite'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_actualite'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id_actualite'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-news me-2"></i>Détails de l'actualité</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 text-center border-end">
                                                <img src="<?= base_url($image_path) ?>" 
                                                     class="rounded border border-3 border-primary mb-3"
                                                     style="width:100%; max-height:250px; object-fit:cover;"
                                                     onerror="this.src='<?= base_url('assets/images/no-image.png') ?>'"
                                                     alt="<?= htmlspecialchars($value['titre'] ?? '') ?>">
                                                
                                                <?php if (!empty($value['est_en_avant']) && $value['est_en_avant'] == 1): ?>
                                                    <span class="badge bg-warning text-dark mb-2"><i class="bx bx-star me-1"></i>À la une</span>
                                                <?php endif; ?>
                                                
                                                <h5 class="mb-1"><?= htmlspecialchars($value['titre'] ?? '') ?></h5>
                                                <p class="text-muted mb-2"><?= htmlspecialchars($categorie_label) ?></p>
                                                
                                                <div class="mt-3">
                                                    <span class="badge bg-light text-dark border me-2">
                                                        <i class="bx bx-show me-1"></i><?= number_format($value['vues'] ?? 0) ?> vues
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="text-muted small">Titre complet</label>
                                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($value['titre'] ?? '-') ?></p>
                                                    </div>
                                                    
                                                    <div class="col-12">
                                                        <label class="text-muted small">Slug</label>
                                                        <p class="mb-0 font-monospace text-muted"><?= htmlspecialchars($value['slug'] ?? '-') ?></p>
                                                    </div>
                                                    
                                                    <?php if (!empty($value['resume'])): ?>
                                                    <div class="col-12">
                                                        <label class="text-muted small">Résumé</label>
                                                        <p class="mb-0"><?= nl2br(htmlspecialchars($value['resume'])) ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="col-12">
                                                        <label class="text-muted small">Contenu</label>
                                                        <div class="p-3 bg-light rounded border" style="max-height: 200px; overflow-y: auto;">
                                                            <?= $value['contenu'] ?? '-' ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-6">
                                                        <label class="text-muted small">Catégorie</label>
                                                        <p class="mb-0"><span class="badge <?= $categorie_badge ?>"><?= htmlspecialchars($categorie_label) ?></span></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Auteur</label>
                                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($value['auteur'] ?? '-') ?></p>
                                                    </div>
                                                    
                                                    <?php if (!empty($tags)): ?>
                                                    <div class="col-12">
                                                        <label class="text-muted small">Tags</label>
                                                        <div>
                                                            <?php foreach ($tags as $tag): ?>
                                                                <?php if (!empty($tag)): ?>
                                                                <span class="badge bg-light text-dark border me-1">#<?= htmlspecialchars(trim($tag)) ?></span>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="col-6">
                                                        <label class="text-muted small">Date de publication</label>
                                                        <p class="mb-0 fw-bold"><?= !empty($value['date_publication']) ? date('d/m/Y H:i', strtotime($value['date_publication'])) : '-' ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Page associée</label>
                                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($page_titre) ?></p>
                                                    </div>
                                                    
                                                    <div class="col-6">
                                                        <label class="text-muted small">Créé le</label>
                                                        <p class="mb-0 fw-bold"><?= !empty($value['created_at']) ? date('d/m/Y H:i', strtotime($value['created_at'])) : '-' ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Modifié le</label>
                                                        <p class="mb-0 fw-bold"><?= !empty($value['updated_at']) ? date('d/m/Y H:i', strtotime($value['updated_at'])) : '-' ?></p>
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
                        <div class="modal fade" id="update_<?= $value['id_actualite'] ?>" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier l'actualité</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <form action="<?= base_url('Actualites/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $value['id_actualite'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <!-- Section Informations -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations générales</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-8">
                                                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($value['titre'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Slug <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="slug" value="<?= htmlspecialchars($value['slug'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Catégorie</label>
                                                            <select class="form-select" name="categorie">
                                                                <option value="">Sélectionner...</option>
                                                                <?php foreach ($categories_list as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= ($value['categorie'] ?? '') == $key ? 'selected' : '' ?>><?= $label ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Auteur</label>
                                                            <input type="text" class="form-control" name="auteur" value="<?= htmlspecialchars($value['auteur'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Résumé</label>
                                                            <textarea class="form-control" name="resume" rows="2"><?= htmlspecialchars($value['resume'] ?? '') ?></textarea>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Contenu <span class="text-danger">*</span></label>
                                                            <textarea class="form-control editor" name="contenu" rows="6" required><?= htmlspecialchars($value['contenu'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Publication -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-calendar me-2"></i>Publication & Métadonnées</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Date de publication</label>
                                                            <input type="datetime-local" class="form-control" name="date_publication" value="<?= !empty($value['date_publication']) ? date('Y-m-d\TH:i', strtotime($value['date_publication'])) : date('Y-m-d\TH:i') ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Tags (séparés par des virgules)</label>
                                                            <input type="text" class="form-control" name="tags" value="<?= !empty($tags) ? htmlspecialchars(implode(', ', $tags)) : '' ?>" placeholder="tag1, tag2, tag3">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Page associée</label>
                                                            <select class="form-select" name="id_page_associee">
                                                                <option value="">Aucune</option>
                                                                <?php foreach ($pages as $page): ?>
                                                                    <?php if (!empty($page['id_page'])): ?>
                                                                    <option value="<?= $page['id_page'] ?>" <?= ($value['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($page['titre_page'] ?? '') ?>
                                                                    </option>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Image -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Image principale</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-8">
                                                            <label class="form-label fw-bold">Nouvelle image (laisser vide pour conserver l'actuelle)</label>
                                                            <input type="file" class="form-control" name="image_principale" accept="image/*">
                                                            <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WEBP (max 2MB)</small>
                                                            <?php if (!empty($value['image_principale'])): ?>
                                                                <div class="mt-2">
                                                                    <small class="text-muted">Image actuelle:</small><br>
                                                                    <img src="<?= base_url('attachments/Actualites/'.$value['image_principale']) ?>" style="max-width: 150px; max-height: 100px;" class="rounded border">
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Options</label>
                                                            <div class="form-check form-switch mt-2">
                                                                <input type="checkbox" class="form-check-input" name="est_en_avant" id="est_en_avant_<?= $value['id_actualite'] ?>" value="1" <?= (!empty($value['est_en_avant']) && $value['est_en_avant'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="est_en_avant_<?= $value['id_actualite'] ?>">Mettre à la une</label>
                                                            </div>
                                                        </div>
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
                        <div class="modal fade" id="delete_<?= $value['id_actualite'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer :</p>
                                        <p class="fw-bold fs-5"><?= htmlspecialchars($value['titre'] ?? '') ?></p>
                                        <?php if (!empty($value['image_principale'])): ?>
                                            <p class="text-warning small"><i class="bx bx-info-circle me-1"></i>L'image associée sera également supprimée.</p>
                                        <?php endif; ?>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Actualites/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_actualite'] ?>">
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

                        <!-- MODAL STATUS (En avant) -->
                        <div class="modal fade" id="status_<?= $value['id_actualite'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['est_en_avant']) && $value['est_en_avant'] == 1) ? 'bg-secondary' : 'bg-warning' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['est_en_avant']) && $value['est_en_avant'] == 1) ? '<i class="bx bx-star me-2"></i>Retirer de la une' : '<i class="bx bx-star me-2"></i>Mettre à la une' ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['est_en_avant']) && $value['est_en_avant'] == 1) ? 'retirer de la une' : 'mettre à la une' ?></strong> l'actualité :</p>
                                        <p class="fw-bold"><?= htmlspecialchars($value['titre'] ?? '') ?></p>
                                    </div>
                                    <form action="<?= base_url('Actualites/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_actualite'] ?>">
                                        <input type="hidden" name="est_en_avant" value="<?= (!empty($value['est_en_avant']) && $value['est_en_avant'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['est_en_avant']) && $value['est_en_avant'] == 1) ? 'btn-secondary' : 'btn-warning' ?>">
                                                <?= (!empty($value['est_en_avant']) && $value['est_en_avant'] == 1) ? 'Retirer de la une' : 'Mettre à la une' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bx bx-news text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucune actualité trouvée</p>
                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#create_actualite">
                                    <i class="bx bx-plus me-2"></i>Créer votre première actualité
                                </button>
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

<!-- MODAL CREATE ACTUALITE -->
<div class="modal fade" id="create_actualite" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle Actualité</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= base_url('Actualites/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <!-- Section Informations -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations générales</h6>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="titre" required id="create_titre">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Slug <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="slug" required id="create_slug" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Catégorie</label>
                                    <select class="form-select" name="categorie">
                                        <option value="">Sélectionner...</option>
                                        <?php foreach ($categories_list as $key => $label): ?>
                                            <option value="<?= $key ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Auteur</label>
                                    <input type="text" class="form-control" name="auteur" value="<?= $this->session->userdata('nom_utilisateur') ?? '' ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Résumé</label>
                                    <textarea class="form-control" name="resume" rows="2" placeholder="Brève description de l'actualité..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Contenu <span class="text-danger">*</span></label>
                                    <textarea class="form-control editor" name="contenu" rows="6" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Publication -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-calendar me-2"></i>Publication & Métadonnées</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Date de publication</label>
                                    <input type="datetime-local" class="form-control" name="date_publication" value="<?= date('Y-m-d\TH:i') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Tags (séparés par des virgules)</label>
                                    <input type="text" class="form-control" name="tags" placeholder="tag1, tag2, tag3">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Page associée</label>
                                    <select class="form-select" name="id_page_associee">
                                        <option value="">Aucune</option>
                                        <?php foreach ($pages as $page): ?>
                                            <?php if (!empty($page['id_page'])): ?>
                                            <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page'] ?? '') ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Image -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Image principale</h6>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Image</label>
                                    <input type="file" class="form-control" name="image_principale" accept="image/*">
                                    <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WEBP (max 2MB)</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Options</label>
                                    <div class="form-check form-switch mt-2">
                                        <input type="checkbox" class="form-check-input" name="est_en_avant" id="create_est_en_avant" value="1">
                                        <label class="form-check-label" for="create_est_en_avant">Mettre à la une</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer l'actualité
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    if ($.fn.DataTable) {
        $('#actualitesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [1, 8] }
            ]
        });
    } else {
        console.error('DataTable non chargé');
    }
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Génération automatique du slug
    $('#create_titre').on('input', function() {
        var titre = $(this).val();
        var slug = titre.toLowerCase()
            .replace(/[àáâãäå]/g, 'a')
            .replace(/[èéêë]/g, 'e')
            .replace(/[ìíîï]/g, 'i')
            .replace(/[òóôõö]/g, 'o')
            .replace(/[ùúûü]/g, 'u')
            .replace(/[ýÿ]/g, 'y')
            .replace(/[ñ]/g, 'n')
            .replace(/[ç]/g, 'c')
            .replace(/[œ]/g, 'oe')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        $('#create_slug').val(slug);
    });
    
    // Initialisation éditeur WYSIWYG si disponible
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '.editor',
            height: 300,
            plugins: 'link image code lists',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
            branding: false
        });
    }
});

// Vérification du chargement de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap JS n\'est pas chargé!');
    } else {
        console.log('Bootstrap JS chargé');
    }
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>