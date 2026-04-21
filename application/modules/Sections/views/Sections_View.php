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
                        <li class="breadcrumb-item"><a href="<?= base_url('Pages') ?>">Pages</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Gestion des Sections</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a class="btn btn-primary" href="<?= base_url('Sections/add') ?>">
                        <i class="bx bx-plus"></i> Nouvelle Section
                    </a>
                    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= base_url('Sections/add') ?>"><i class="bx bx-plus-circle me-2"></i>Créer une section</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('Pages') ?>"><i class="bx bx-file me-2"></i>Gérer les pages</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="location.reload()"><i class="bx bx-refresh me-2"></i>Rafraîchir</a></li>
                    </ul>
                </div>
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

        <!-- Filtres rapides -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="<?= base_url('Sections') ?>" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Filtrer par page</label>
                        <select name="page_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Toutes les pages</option>
                            <?php foreach ($pages as $page): ?>
                                <option value="<?= $page['id_page'] ?>" <?= ($this->input->get('page_filter') == $page['id_page']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($page['titre_page']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Type de section</label>
                        <select name="type_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tous les types</option>
                            <?php 
                            $types = ['hero', 'texte', 'image_texte', 'html', 'grille', 'grille_card', 'grille_inline', 'liste', 'liste_card', 'liste_inline', 'liste_item', 'tableau', 'timeline'];
                            foreach ($types as $type): 
                            ?>
                                <option value="<?= $type ?>" <?= ($this->input->get('type_filter') == $type) ? 'selected' : '' ?>>
                                    <?= ucfirst(str_replace('_', ' ', $type)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Langue</label>
                        <select name="lang_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="fr" <?= ($this->input->get('lang_filter') == 'fr') ? 'selected' : '' ?>>🇫🇷 Français</option>
                            <option value="en" <?= ($this->input->get('lang_filter') == 'en') ? 'selected' : '' ?>>🇬🇧 English</option>
                            <option value="sw" <?= ($this->input->get('lang_filter') == 'sw') ? 'selected' : '' ?>>🇹🇿 Kiswahili</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Statut</label>
                        <select name="status_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tous</option>
                            <option value="1" <?= ($this->input->get('status_filter') === '1') ? 'selected' : '' ?>>Actives</option>
                            <option value="0" <?= ($this->input->get('status_filter') === '0') ? 'selected' : '' ?>>Inactives</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="<?= base_url('Sections') ?>" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bx bx-reset"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0 text-primary"><i class="bx bx-layout me-2"></i>Liste des Sections de Contenu</h5>
                    <span class="badge bg-secondary ms-2"><?= count($sections) ?> section(s)</span>
                </div>
                <div class="d-flex gap-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="toggleInactive" <?= ($this->input->get('show_inactive') == '1') ? 'checked' : '' ?> onchange="toggleInactiveSections()">
                        <label class="form-check-label small text-muted" for="toggleInactive">Afficher inactives</label>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tableSections" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="text-center">#</th>
                                <th width="70">Ordre</th>
                                <th width="12%">Page</th>
                                <th width="10%">Type</th>
                                <th width="25%">Contenu (<?= ucfirst($this->input->get('lang_filter') ?: 'fr') ?>)</th>
                                <th width="10%">Traductions</th>
                                <th width="50" class="text-center">Img</th>
                                <th width="50" class="text-center">CTA</th>
                                <th width="60" class="text-center">Statut</th>
                                <th width="110" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php if (!empty($sections)): 
        $counter = 1;
        $current_lang = $this->input->get('lang_filter') ?: 'fr';
        
        foreach ($sections as $value): 
            // Ignorer les sections supprimées (soft delete)
            if (!empty($value['deleted_at']) && $this->input->get('show_deleted') != '1') continue;
            
            // Récupérer le nom et le slug de la page
            $page_name = 'Inconnue';
            $page_slug = '';
            foreach ($pages as $page) {
                if ($page['id_page'] == $value['id_page']) {
                    $page_name = $page['titre_page'];
                    $page_slug = $page['slug_page'] ?? '';
                    break;
                }
            }
            
            // Récupérer le contenu selon la langue sélectionnée
            $titre = $value['titre_section_' . $current_lang] ?? $value['titre_section_fr'] ?? '(Sans titre)';
            $sous_titre = $value['sous_titre_' . $current_lang] ?? '';
            $contenu = $value['contenu_texte_' . $current_lang] ?? '';
            $bouton_texte = $value['bouton_texte_' . $current_lang] ?? '';
            
            // Vérifier les traductions disponibles
            $has_fr = !empty($value['titre_section_fr']) || !empty($value['sous_titre_fr']) || !empty($value['contenu_texte_fr']);
            $has_en = !empty($value['titre_section_en']) || !empty($value['sous_titre_en']) || !empty($value['contenu_texte_en']);
            $has_sw = !empty($value['titre_section_sw']) || !empty($value['sous_titre_sw']) || !empty($value['contenu_texte_sw']);
            
            // Configuration des badges par type de section
            $type_config = [
                'hero' => ['class' => 'bg-primary', 'icon' => 'bx-star', 'label' => 'Hero'],
                'texte' => ['class' => 'bg-secondary', 'icon' => 'bx-text', 'label' => 'Texte'],
                'image_texte' => ['class' => 'bg-info', 'icon' => 'bx-image-alt', 'label' => 'Image+Texte'],
                'html' => ['class' => 'bg-dark', 'icon' => 'bx-code', 'label' => 'HTML'],
                'grille' => ['class' => 'bg-success', 'icon' => 'bx-grid-alt', 'label' => 'Grille'],
                'grille_card' => ['class' => 'bg-success', 'icon' => 'bx-grid', 'label' => 'Grille Card'],
                'grille_inline' => ['class' => 'bg-success', 'icon' => 'bx-grid-small', 'label' => 'Grille Inline'],
                'liste' => ['class' => 'bg-warning text-dark', 'icon' => 'bx-list-ul', 'label' => 'Liste'],
                'liste_card' => ['class' => 'bg-warning text-dark', 'icon' => 'bx-list-check', 'label' => 'Liste Card'],
                'liste_inline' => ['class' => 'bg-warning text-dark', 'icon' => 'bx-list-minus', 'label' => 'Liste Inline'],
                'liste_item' => ['class' => 'bg-warning text-dark', 'icon' => 'bx-list-ol', 'label' => 'Liste Item'],
                'tableau' => ['class' => 'bg-dark', 'icon' => 'bx-table', 'label' => 'Tableau'],
                'timeline' => ['class' => 'bg-info', 'icon' => 'bx-time', 'label' => 'Timeline']
            ];
            
            $type_info = $type_config[$value['type_section']] ?? [
                'class' => 'bg-light text-dark border', 
                'icon' => 'bx-question-mark', 
                'label' => ucfirst($value['type_section'])
            ];
            
            // Vérifier la présence d'un CTA
            $has_cta = (!empty($bouton_texte) && !empty($value['bouton_lien']));
            
            // Statut actif/inactif
            $is_active = ($value['est_active'] ?? 1) == 1;
            $row_class = !$is_active ? 'table-secondary opacity-50' : '';
            if (!empty($value['deleted_at'])) $row_class = 'table-danger opacity-75';
    ?>
    <tr class="<?= $row_class ?>" data-section-id="<?= $value['id_section'] ?>">
        <!-- Numéro -->
        <td class="text-center fw-bold text-muted"><?= $counter++ ?></td>
        
        <!-- Ordre -->
        <td class="text-center">
            <div class="d-flex flex-column align-items-center gap-1">
                <form action="<?= base_url('Sections/ChangeOrdre') ?>" method="POST" class="m-0">
                    <input type="hidden" name="id" value="<?= $value['id_section'] ?>">
                    <input type="hidden" name="direction" value="up">
                    <button type="submit" class="btn btn-outline-secondary btn-xs py-0 px-2" title="Monter">
                        <i class="bx bx-chevron-up"></i>
                    </button>
                </form>
                <span class="badge bg-light text-dark border fw-bold"><?= $value['ordre'] ?? 0 ?></span>
                <form action="<?= base_url('Sections/ChangeOrdre') ?>" method="POST" class="m-0">
                    <input type="hidden" name="id" value="<?= $value['id_section'] ?>">
                    <input type="hidden" name="direction" value="down">
                    <button type="submit" class="btn btn-outline-secondary btn-xs py-0 px-2" title="Descendre">
                        <i class="bx bx-chevron-down"></i>
                    </button>
                </form>
            </div>
        </td>
        
        <!-- Page -->
        <td>
            <div class="d-flex flex-column">
                <span class="badge bg-light text-dark border mb-1" style="width: fit-content;">
                    <i class="bx bx-file me-1"></i><?= htmlspecialchars($page_name) ?>
                </span>
                <?php if ($page_slug): ?>
                    <small class="text-muted" style="font-size: 0.65rem;">/<?= htmlspecialchars($page_slug) ?></small>
                <?php endif; ?>
            </div>
        </td>
        
        <!-- Type -->
        <td>
            <span class="badge <?= $type_info['class'] ?>" style="font-size: 0.75rem;">
                <i class="bx <?= $type_info['icon'] ?> me-1"></i><?= $type_info['label'] ?>
            </span>
        </td>
        
        <!-- Titre / Sous-titre -->
        <td>
            <div class="d-flex flex-column">
                <strong class="text-dark text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($titre) ?>">
                    <?= htmlspecialchars(mb_substr($titre, 0, 50)) ?><?= mb_strlen($titre) > 50 ? '...' : '' ?>
                </strong>
                <?php if (!empty($sous_titre)): ?>
                    <small class="text-muted text-truncate" style="max-width: 250px; font-size: 0.7rem;">
                        <?= htmlspecialchars(mb_substr($sous_titre, 0, 60)) ?><?= mb_strlen($sous_titre) > 60 ? '...' : '' ?>
                    </small>
                <?php endif; ?>
                <?php if (!empty($value['custom_class'])): ?>
                    <small class="text-primary" style="font-size: 0.65rem;">
                        <i class="bx bx-code-alt"></i> .<?= htmlspecialchars($value['custom_class']) ?>
                    </small>
                <?php endif; ?>
                <?php 
                $content_length = !empty($contenu) ? strlen(strip_tags($contenu)) : 0;
                if ($content_length > 0): 
                ?>
                    <small class="text-muted" style="font-size: 0.65rem;">
                        <i class="bx bx-text"></i> <?= number_format($content_length) ?> car.
                    </small>
                <?php endif; ?>
            </div>
        </td>
        
        <!-- Traductions -->
        <td>
            <div class="d-flex gap-1 flex-wrap">
                <span class="badge <?= $has_fr ? 'bg-primary' : 'bg-light text-muted border' ?>" style="font-size: 0.7rem;" title="<?= $has_fr ? 'Français disponible' : 'Français manquant' ?>">
                    FR
                </span>
                <span class="badge <?= $has_en ? 'bg-primary' : 'bg-light text-muted border' ?>" style="font-size: 0.7rem;" title="<?= $has_en ? 'English available' : 'English missing' ?>">
                    EN
                </span>
                <span class="badge <?= $has_sw ? 'bg-primary' : 'bg-light text-muted border' ?>" style="font-size: 0.7rem;" title="<?= $has_sw ? 'Kiswahili inapatikana' : 'Kiswahili haipo' ?>">
                    SW
                </span>
            </div>
        </td>
        
        <!-- Image -->
        <td class="text-center">
            <?php if (!empty($value['image_url'])): 
                $img_path = $value['image_url'];
                if (strpos($img_path, 'http') !== 0) {
                    $img_path = base_url($img_path);
                }
            ?>
                <img src="<?= $img_path ?>" 
                     class="rounded border"
                     style="width:40px; height:40px; object-fit:cover; cursor: pointer;"
                     onclick="previewImage('<?= $img_path ?>')"
                     onerror="this.src='<?= base_url('assets/images/no-image.png') ?>'"
                     alt="Miniature"
                     title="Cliquer pour prévisualiser">
            <?php else: ?>
                <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">
                    <i class="bx bx-image"></i> Non
                </span>
            <?php endif; ?>
        </td>
        
        <!-- CTA -->
        <td class="text-center">
            <?php if ($has_cta): ?>
                <span class="badge bg-success" style="font-size: 0.7rem;" title="<?= htmlspecialchars($bouton_texte) ?>">
                    <i class="bx bx-check"></i> <?= htmlspecialchars(mb_substr($bouton_texte, 0, 8)) ?>...
                </span>
            <?php else: ?>
                <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">-</span>
            <?php endif; ?>
        </td>
        
        <!-- Statut -->
        <td class="text-center">
            <?php if (!empty($value['deleted_at'])): ?>
                <span class="badge bg-danger" style="font-size: 0.7rem;">
                    <i class="bx bx-trash"></i> Suppr.
                </span>
            <?php else: ?>
                <div class="form-check form-switch d-flex justify-content-center">
                    <input class="form-check-input toggle-status" 
                           type="checkbox" 
                           data-id="<?= $value['id_section'] ?>"
                           <?= $is_active ? 'checked' : '' ?> 
                           style="cursor: pointer;">
                </div>
            <?php endif; ?>
        </td>
        
        <!-- Actions -->
        <td>
            <div class="btn-group btn-group-sm" role="group">
                <!-- Détail -->
                <a href="<?= base_url('Sections/SectionDetail/'.$value['id_section']) ?>" class="btn btn-info" title="Voir le détail">
                    <i class="bx bx-show"></i>
                </a>
                <!-- Modifier -->
                <a href="<?= base_url('Sections/edit/'.$value['id_section']) ?>" class="btn btn-warning" title="Modifier">
                    <i class="bx bx-edit"></i>
                </a>
                <!-- Traductions -->
                <button type="button" class="btn btn-secondary" title="Traductions" data-bs-toggle="modal" data-bs-target="#translationsModal<?= $value['id_section'] ?>">
                    <i class="bx bx-flag"></i>
                </button>
                <!-- Supprimer -->
                <?php if (empty($value['deleted_at'])): ?>
                    <button type="button" class="btn btn-danger" title="Supprimer" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $value['id_section'] ?>">
                        <i class="bx bx-trash"></i>
                    </button>
                <?php else: ?>
                    <a href="<?= base_url('Sections/restore/'.$value['id_section']) ?>" class="btn btn-success" title="Restaurer">
                        <i class="bx bx-undo"></i>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Modal Traductions -->
            <div class="modal fade" id="translationsModal<?= $value['id_section'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-info text-white py-2">
                            <h6 class="modal-title"><i class="bx bx-flag me-2"></i>Traductions - Section #<?= $value['id_section'] ?></h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <ul class="nav nav-tabs mb-3" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#fr-<?= $value['id_section'] ?>">🇫🇷 Français</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#en-<?= $value['id_section'] ?>">🇬🇧 English</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sw-<?= $value['id_section'] ?>">🇹🇿 Kiswahili</button>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <!-- FR -->
                                <div class="tab-pane fade show active" id="fr-<?= $value['id_section'] ?>">
                                    <div class="mb-3">
                                        <label class="fw-bold small">Titre</label>
                                        <p class="border rounded p-2 bg-light"><?= nl2br(htmlspecialchars($value['titre_section_fr'] ?? '(Non défini)')) ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold small">Sous-titre</label>
                                        <p class="border rounded p-2 bg-light"><?= nl2br(htmlspecialchars($value['sous_titre_fr'] ?? '(Non défini)')) ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold small">Contenu</label>
                                        <div class="border rounded p-2 bg-light"><?= $value['contenu_texte_fr'] ?? '(Non défini)' ?></div>
                                    </div>
                                    <?php if (!empty($value['bouton_texte_fr'])): ?>
                                    <div class="mb-3">
                                        <label class="fw-bold small">Bouton</label>
                                        <p class="border rounded p-2 bg-light"><?= htmlspecialchars($value['bouton_texte_fr']) ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <!-- EN -->
                                <div class="tab-pane fade" id="en-<?= $value['id_section'] ?>">
                                    <div class="mb-3">
                                        <label class="fw-bold small">Title</label>
                                        <p class="border rounded p-2 bg-light"><?= nl2br(htmlspecialchars($value['titre_section_en'] ?? '(Not defined)')) ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold small">Subtitle</label>
                                        <p class="border rounded p-2 bg-light"><?= nl2br(htmlspecialchars($value['sous_titre_en'] ?? '(Not defined)')) ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold small">Content</label>
                                        <div class="border rounded p-2 bg-light"><?= $value['contenu_texte_en'] ?? '(Not defined)' ?></div>
                                    </div>
                                    <?php if (!empty($value['bouton_texte_en'])): ?>
                                    <div class="mb-3">
                                        <label class="fw-bold small">Button</label>
                                        <p class="border rounded p-2 bg-light"><?= htmlspecialchars($value['bouton_texte_en']) ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <!-- SW -->
                                <div class="tab-pane fade" id="sw-<?= $value['id_section'] ?>">
                                    <div class="mb-3">
                                        <label class="fw-bold small">Kichwa</label>
                                        <p class="border rounded p-2 bg-light"><?= nl2br(htmlspecialchars($value['titre_section_sw'] ?? '(Haijafafanuliwa)')) ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold small">Kichwa kidogo</label>
                                        <p class="border rounded p-2 bg-light"><?= nl2br(htmlspecialchars($value['sous_titre_sw'] ?? '(Haijafafanuliwa)')) ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold small">Maudhui</label>
                                        <div class="border rounded p-2 bg-light"><?= $value['contenu_texte_sw'] ?? '(Haijafafanuliwa)' ?></div>
                                    </div>
                                    <?php if (!empty($value['bouton_texte_sw'])): ?>
                                    <div class="mb-3">
                                        <label class="fw-bold small">Kitufe</label>
                                        <p class="border rounded p-2 bg-light"><?= htmlspecialchars($value['bouton_texte_sw']) ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer py-2">
                            <a href="<?= base_url('Sections/edit/'.$value['id_section']) ?>" class="btn btn-sm btn-primary">
                                <i class="bx bx-edit"></i> Modifier toutes les langues
                            </a>
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Suppression -->
            <div class="modal fade" id="deleteModal<?= $value['id_section'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-danger text-white py-2">
                            <h6 class="modal-title"><i class="bx bx-trash me-2"></i>Supprimer ?</h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center py-3">
                            <p class="mb-0 small">Section : <strong><?= htmlspecialchars($titre) ?></strong></p>
                        </div>
                        <form action="<?= base_url('Sections/Delete') ?>" method="POST">
                            <input type="hidden" name="id" value="<?= $value['id_section'] ?>">
                            <div class="modal-footer justify-content-center py-2">
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-sm btn-danger">Confirmer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <?php 
        endforeach; 
    else: 
    ?>
    <tr>
        <td colspan="10" class="text-center py-5">
            <div class="d-flex flex-column align-items-center text-muted">
                <i class="bx bx-layout" style="font-size: 3rem;"></i>
                <p class="mt-2 mb-0">Aucune section trouvée</p>
                <a href="<?= base_url('Sections/add') ?>" class="btn btn-sm btn-primary mt-3">
                    <i class="bx bx-plus"></i> Créer une section
                </a>
            </div>
        </td>
    </tr>
    <?php endif; ?>
</tbody>
                    </table>
                </div>
            </div>
            <?php if (!empty($sections)): ?>
            <div class="card-footer bg-white py-2">
                <small class="text-muted">
                    <i class="bx bx-info-circle me-1"></i>
                    <i class="bx bx-flag text-info"></i> Cliquez sur l'icône drapeau pour voir toutes les traductions
                </small>
            </div>
            <?php endif; ?>
        </div>

    </div>


<!-- Modal Prévisualisation Image -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Prévisualisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" class="img-fluid rounded" style="max-height: 70vh;" alt="Preview">
            </div>
        </div>
    </div>
</div>

<script>
// Prévisualisation d'image
function previewImage(src) {
    document.getElementById('previewImage').src = src;
    new bootstrap.Modal(document.getElementById('imagePreviewModal')).show();
}

// Toggle affichage sections inactives
function toggleInactiveSections() {
    const checkbox = document.getElementById('toggleInactive');
    const url = new URL(window.location.href);
    if (checkbox.checked) {
        url.searchParams.set('show_inactive', '1');
    } else {
        url.searchParams.delete('show_inactive');
    }
    window.location.href = url.toString();
}

// Toggle status via AJAX
document.querySelectorAll('.toggle-status').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const sectionId = this.dataset.id;
        const isChecked = this.checked;
        
        fetch('<?= base_url("Sections/ToggleStatus") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id_section=' + sectionId
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // Revenir à l'état précédent si erreur
                this.checked = !isChecked;
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            this.checked = !isChecked;
            alert('Erreur lors de la communication avec le serveur');
        });
    });
});

// Initialisation DataTable si disponible
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#tableSections').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            pageLength: 25,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [0, 5, 6, 7, 8, 9] }
            ]
        });
    }
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>