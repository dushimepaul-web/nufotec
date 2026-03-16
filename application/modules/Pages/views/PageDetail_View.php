<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">CMS</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('Pages') ?>">Gestion des Pages</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($detail['titre_page'] ?? 'Détail') ?></li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="<?= base_url('Pages') ?>" class="btn btn-outline-secondary me-2">
                <i class="bx bx-arrow-back"></i> Retour
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_section">
                <i class="bx bx-plus"></i> Ajouter une section
            </button>
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

    <div class="row">
        <!-- Informations de la page -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Informations</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <?php if (!empty($detail['icone_menu'])): ?>
                            <i class="bx <?= $detail['icone_menu'] ?> text-primary" style="font-size: 3rem;"></i>
                        <?php else: ?>
                            <i class="bx bx-file text-muted" style="font-size: 3rem;"></i>
                        <?php endif; ?>
                        <h4 class="mt-2 mb-0"><?= htmlspecialchars($detail['titre_page'] ?? '') ?></h4>
                        <span class="badge bg-light text-dark border mt-2">Ordre: <?= $detail['menu_ordre'] ?? 0 ?></span>
                    </div>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Slug</span>
                            <code class="bg-light px-2 py-1 rounded"><?= htmlspecialchars($detail['slug'] ?? '') ?></code>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Template</span>
                            <span class="badge bg-secondary"><?= htmlspecialchars($detail['template_specifique'] ?? 'default') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Statut</span>
                            <?php if (!empty($detail['est_publiee']) && $detail['est_publiee'] == 1): ?>
                                <span class="badge bg-success"><i class="bx bx-check"></i> Publiée</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="bx bx-hide"></i> Brouillon</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Page parente</span>
                            <?php 
                            $parent_name = 'Racine';
                            if (!empty($detail['menu_parent_id'])) {
                                foreach ($pages_list as $p) {
                                    if ($p['id_page'] == $detail['menu_parent_id']) {
                                        $parent_name = $p['titre_page'];
                                        break;
                                    }
                                }
                            }
                            ?>
                            <span class="text-dark"><?= htmlspecialchars($parent_name) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Créée le</span>
                            <small><?= !empty($detail['date_creation']) ? date('d/m/Y H:i', strtotime($detail['date_creation'])) : '-' ?></small>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Modifiée le</span>
                            <small><?= !empty($detail['date_modification']) ? date('d/m/Y H:i', strtotime($detail['date_modification'])) : '-' ?></small>
                        </li>
                    </ul>

                    <?php if (!empty($detail['meta_description'])): ?>
                        <div class="mt-3">
                            <small class="text-muted d-block mb-1">Meta Description:</small>
                            <p class="small text-muted mb-0"><?= htmlspecialchars($detail['meta_description']) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($detail['meta_keywords'])): ?>
                        <div class="mt-3">
                            <small class="text-muted d-block mb-1">Meta Keywords:</small>
                            <p class="small text-muted mb-0"><?= htmlspecialchars($detail['meta_keywords']) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2 mt-4">
                        <a href="javascript:void(0)" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit_page_<?= $detail['id_page'] ?>">
                            <i class="bx bx-edit me-2"></i>Modifier la page
                        </a>
                        <a href="<?= base_url($detail['slug'] ?? '') ?>" target="_blank" class="btn btn-outline-primary">
                            <i class="bx bx-show me-2"></i>Voir sur le site
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0"><i class="bx bx-stats me-2"></i>Statistiques</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <h3 class="text-primary mb-0"><?= count($sections ?? []) ?></h3>
                            <small class="text-muted">Sections</small>
                        </div>
                        <div class="col-6">
                            <h3 class="text-success mb-0"><?= count(array_filter($sections ?? [], function($s) { return !empty($s['est_active']); })) ?></h3>
                            <small class="text-muted">Actives</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sections de contenu -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary"><i class="bx bx-layout me-2"></i>Sections de contenu</h5>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshSections()">
                            <i class="bx bx-refresh"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($sections)): ?>
                        <div class="accordion" id="sectionsAccordion">
                            <?php foreach ($sections as $index => $section): 
                                $type_icons = [
                                    'hero' => 'bx-image-alt',
                                    'texte' => 'bx-text',
                                    'image_texte' => 'bx-photo-album',
                                    'grille' => 'bx-grid-alt',
                                    'liste' => 'bx-list-ul',
                                    'tableau' => 'bx-table',
                                    'contact' => 'bx-envelope',
                                    'produits' => 'bx-package',
                                    'partenaires' => 'bx-group',
                                    'chiffres' => 'bx-bar-chart-alt-2',
                                    'temoignages' => 'bx-message-square-detail',
                                    'cta' => 'bx-bullseye',
                                    'liste_item' => 'bx-list-check'
                                ];
                                $icon = $type_icons[$section['type_section'] ?? ''] ?? 'bx-cube';
                                
                                $type_labels = [
                                    'hero' => 'Hero Banner',
                                    'texte' => 'Texte simple',
                                    'image_texte' => 'Image + Texte',
                                    'grille' => 'Grille',
                                    'liste' => 'Liste',
                                    'tableau' => 'Tableau',
                                    'contact' => 'Formulaire contact',
                                    'produits' => 'Produits',
                                    'partenaires' => 'Partenaires',
                                    'chiffres' => 'Chiffres clés',
                                    'temoignages' => 'Témoignages',
                                    'cta' => 'Call-to-Action',
                                    'liste_item' => 'Liste d\'items'
                                ];
                                $type_label = $type_labels[$section['type_section'] ?? ''] ?? ($section['type_section'] ?? 'Inconnu');
                            ?>
                                <div class="accordion-item border-0 mb-3 shadow-sm rounded overflow-hidden">
                                    <h2 class="accordion-header" id="heading<?= $section['id_section'] ?>">
                                        <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $section['id_section'] ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>">
                                            <div class="d-flex align-items-center w-100 me-3">
                                                <span class="badge bg-primary me-3" style="min-width: 30px;"><?= $section['ordre'] ?? 0 ?></span>
                                                <i class="bx <?= $icon ?> text-primary me-2 fs-5"></i>
                                                <div class="flex-grow-1">
                                                    <strong class="d-block"><?= htmlspecialchars($section['titre_section'] ?? 'Sans titre') ?></strong>
                                                    <small class="text-muted"><?= $type_label ?></small>
                                                </div>
                                                <?php if (!empty($section['est_active'])): ?>
                                                    <span class="badge bg-success me-2">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary me-2">Inactive</span>
                                                <?php endif; ?>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse<?= $section['id_section'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $section['id_section'] ?>" data-bs-parent="#sectionsAccordion">
                                        <div class="accordion-body bg-light">
                                            <!-- Aperçu du contenu -->
                                            <div class="row mb-3">
                                                <?php if (!empty($section['image_url'])): ?>
                                                    <div class="col-md-4 mb-3">
                                                        <img src="<?= base_url($section['image_url']) ?>" class="img-fluid rounded" alt="Image section" style="max-height: 150px; object-fit: cover;">
                                                        <?php if (!empty($section['image_droite'])): ?>
                                                            <span class="badge bg-info mt-1"><i class="bx bx-right-arrow-alt"></i> Image à droite</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-8">
                                                <?php else: ?>
                                                    <div class="col-12">
                                                <?php endif; ?>
                                                    <?php if (!empty($section['sous_titre'])): ?>
                                                        <h6 class="text-muted"><?= htmlspecialchars($section['sous_titre']) ?></h6>
                                                    <?php endif; ?>
                                                    <?php if (!empty($section['contenu_texte'])): ?>
                                                        <div class="content-preview bg-white p-3 rounded border" style="max-height: 200px; overflow-y: auto;">
                                                            <?= nl2br(htmlspecialchars(substr($section['contenu_texte'], 0, 300))) ?>
                                                            <?php if (strlen($section['contenu_texte']) > 300): ?>...<?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <?php if (!empty($section['bouton_texte'])): ?>
                                                <div class="mb-3">
                                                    <span class="badge bg-primary"><i class="bx bx-link me-1"></i><?= htmlspecialchars($section['bouton_texte']) ?> → <?= htmlspecialchars($section['bouton_lien'] ?? '#') ?></span>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($section['custom_class'])): ?>
                                                <div class="mb-2">
                                                    <small class="text-muted">Classe CSS: <code><?= htmlspecialchars($section['custom_class']) ?></code></small>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($section['options_json'])): ?>
                                                <div class="mb-2">
                                                    <small class="text-muted">Options JSON configurées</small>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Actions -->
                                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="moveSectionUp(<?= $section['id_section'] ?>)">
                                                        <i class="bx bx-up-arrow-alt"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="moveSectionDown(<?= $section['id_section'] ?>)">
                                                        <i class="bx bx-down-arrow-alt"></i>
                                                    </button>
                                                </div>
                                                <div class="btn-group">
                                                    <a href="<?= base_url('Sections/Edit/'.$section['id_section']) ?>" class="btn btn-sm btn-warning">
                                                        <i class="bx bx-edit me-1"></i>Modifier
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete_section_<?= $section['id_section'] ?>">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Delete Section -->
                                <div class="modal fade" id="delete_section_<?= $section['id_section'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Supprimer la section</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4 text-center">
                                                <i class="bx bx-error-circle text-danger" style="font-size: 3rem;"></i>
                                                <h5 class="mt-3">Confirmer la suppression</h5>
                                                <p class="text-muted">Supprimer la section <strong><?= htmlspecialchars($section['titre_section'] ?? '') ?></strong> ?</p>
                                            </div>
                                            <form action="<?= base_url('Sections/Delete') ?>" method="POST">
                                                <input type="hidden" name="id_section" value="<?= $section['id_section'] ?>">
                                                <input type="hidden" name="id_page" value="<?= $detail['id_page'] ?>">
                                                <div class="modal-footer bg-light justify-content-center">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-danger">Supprimer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bx bx-layout text-muted" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-muted">Aucune section</h5>
                            <p class="text-muted">Cette page ne contient pas encore de sections de contenu.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_section">
                                <i class="bx bx-plus me-2"></i>Ajouter une section
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Edit Page (même contenu que dans Pages_View) -->
<div class="modal fade" id="edit_page_<?= $detail['id_page'] ?>" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la page</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Pages/Update') ?>" method="POST">
                <input type="hidden" name="id_page" value="<?= $detail['id_page'] ?>">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="titre_page" value="<?= htmlspecialchars($detail['titre_page'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ordre <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="menu_ordre" value="<?= $detail['menu_ordre'] ?? 0 ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="slug" value="<?= htmlspecialchars($detail['slug'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Parent</label>
                            <select class="form-select" name="menu_parent_id">
                                <option value="">Racine</option>
                                <?php foreach ($pages_list as $p): ?>
                                    <?php if ($p['id_page'] != $detail['id_page']): ?>
                                        <option value="<?= $p['id_page'] ?>" <?= ($detail['menu_parent_id'] ?? '') == $p['id_page'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['titre_page']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Template</label>
                            <select class="form-select" name="template_specifique">
                                <?php 
                                $templates = ['default', 'home', 'about', 'esg', 'research', 'governance', 'facility', 'products', 'tech', 'market', 'digital', 'health', 'investment', 'investor', 'brokers', 'risk', 'partners', 'services'];
                                foreach ($templates as $t): 
                                ?>
                                    <option value="<?= $t ?>" <?= ($detail['template_specifique'] ?? '') == $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Icône</label>
                            <input type="text" class="form-control" name="icone_menu" value="<?= htmlspecialchars($detail['icone_menu'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Description</label>
                            <textarea class="form-control" name="meta_description" rows="2"><?= htmlspecialchars($detail['meta_description'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Keywords</label>
                            <input type="text" class="form-control" name="meta_keywords" value="<?= htmlspecialchars($detail['meta_keywords'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="est_publiee" value="1" <?= (!empty($detail['est_publiee']) && $detail['est_publiee'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label">Page publiée</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning"><i class="bx bx-save me-2"></i>Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Section -->
<div class="modal fade" id="add_section" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle Section</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Sections/Create') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_page" value="<?= $detail['id_page'] ?>">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Titre de la section <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="titre_section" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="type_section" required>
                                <option value="">Choisir...</option>
                                <option value="hero">Hero Banner</option>
                                <option value="texte">Texte simple</option>
                                <option value="image_texte">Image + Texte</option>
                                <option value="grille">Grille</option>
                                <option value="liste">Liste</option>
                                <option value="tableau">Tableau</option>
                                <option value="contact">Formulaire contact</option>
                                <option value="produits">Produits</option>
                                <option value="partenaires">Partenaires</option>
                                <option value="chiffres">Chiffres clés</option>
                                <option value="temoignages">Témoignages</option>
                                <option value="cta">Call-to-Action</option>
                                <option value="liste_item">Liste d'items</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Sous-titre</label>
                            <input type="text" class="form-control" name="sous_titre">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Contenu texte</label>
                            <textarea class="form-control" name="contenu_texte" rows="4"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Image</label>
                            <input type="file" class="form-control" name="image_url" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Position image</label>
                            <select class="form-select" name="image_droite">
                                <option value="0">Gauche</option>
                                <option value="1">Droite</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Texte du bouton</label>
                            <input type="text" class="form-control" name="bouton_texte">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lien du bouton</label>
                            <input type="text" class="form-control" name="bouton_lien">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ordre d'affichage</label>
                            <input type="number" class="form-control" name="ordre" value="<?= count($sections ?? []) + 1 ?>" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Classe CSS personnalisée</label>
                            <input type="text" class="form-control" name="custom_class">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="est_active" value="1" checked>
                                <label class="form-check-label">Section active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-save me-2"></i>Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function refreshSections() {
    location.reload();
}

function moveSectionUp(id) {
    // Implémenter la logique AJAX pour monter la section
    console.log('Move up section:', id);
}

function moveSectionDown(id) {
    // Implémenter la logique AJAX pour descendre la section
    console.log('Move down section:', id);
}

// Auto-hide alerts
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>