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
                        <li class="breadcrumb-item"><a href="<?= base_url('Sections') ?>">Sections</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Détail de la section</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="<?= base_url('Sections') ?>" class="btn btn-secondary btn-sm">
                    <i class="bx bx-arrow-back"></i> Retour
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

        <!-- Section principale -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="mb-0 text-primary">
                        <i class="bx bx-detail me-2"></i>Détail de la section #<?= $detail['id_section'] ?>
                    </h5>
                </div>
                <div class="d-flex gap-2 mt-2 mt-sm-0">
                    <!-- Sélecteur de langue rapide -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bx bx-flag"></i> 
                            <?php 
                            $lang_labels = ['fr' => 'Français', 'en' => 'English', 'sw' => 'Kiswahili'];
                            $current_lang = $this->input->get('lang') ?: 'fr';
                            echo $lang_labels[$current_lang];
                            ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item <?= $current_lang == 'fr' ? 'active' : '' ?>" href="?lang=fr">🇫🇷 Français</a></li>
                            <li><a class="dropdown-item <?= $current_lang == 'en' ? 'active' : '' ?>" href="?lang=en">🇬🇧 English</a></li>
                            <li><a class="dropdown-item <?= $current_lang == 'sw' ? 'active' : '' ?>" href="?lang=sw">🇹🇿 Kiswahili</a></li>
                        </ul>
                    </div>
                    <a href="<?= base_url('Sections/edit/'.$detail['id_section']) ?>" class="btn btn-warning btn-sm">
                        <i class="bx bx-edit"></i> Modifier
                    </a>
                </div>
            </div>
            <div class="card-body">
                
                <!-- Informations générales -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3"><i class="bx bx-info-circle"></i> Informations générales</h6>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150" class="fw-bold">ID Section :</td>
                                <td>#<?= $detail['id_section'] ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Page associée :</td>
                                <td>
                                    <?php 
                                    $page_name = '';
                                    foreach ($pages as $page) {
                                        if ($page['id_page'] == $detail['id_page']) {
                                            $page_name = $page['titre_page'];
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($page_name ?: 'Non définie');
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Type de section :</td>
                                <td>
                                    <?php 
                                    $type_labels = [
                                        'hero' => 'Hero (Bannière)',
                                        'texte' => 'Texte',
                                        'image_texte' => 'Image + Texte',
                                        'html' => 'HTML',
                                        'grille' => 'Grille',
                                        'grille_card' => 'Grille de cartes',
                                        'grille_inline' => 'Grille en ligne',
                                        'liste' => 'Liste',
                                        'liste_card' => 'Liste de cartes',
                                        'liste_inline' => 'Liste en ligne',
                                        'liste_item' => 'Item de liste',
                                        'tableau' => 'Tableau',
                                        'timeline' => 'Timeline'
                                    ];
                                    $type_badges = [
                                        'hero' => 'primary',
                                        'texte' => 'secondary',
                                        'image_texte' => 'info',
                                        'html' => 'dark',
                                        'grille' => 'success',
                                        'grille_card' => 'success',
                                        'grille_inline' => 'success',
                                        'liste' => 'warning',
                                        'liste_card' => 'warning',
                                        'liste_inline' => 'warning',
                                        'liste_item' => 'warning',
                                        'tableau' => 'dark',
                                        'timeline' => 'indigo'
                                    ];
                                    $type_label = $type_labels[$detail['type_section']] ?? $detail['type_section'];
                                    $badge_class = $type_badges[$detail['type_section']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badge_class ?>"><?= $type_label ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Ordre d'affichage :</td>
                                <td><?= $detail['ordre'] ?? 0 ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150" class="fw-bold">Statut :</td>
                                <td>
                                    <?php if (($detail['est_active'] ?? 1) == 1): ?>
                                        <span class="badge bg-success"><i class="bx bx-check-circle"></i> Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="bx bx-x-circle"></i> Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Classe CSS :</td>
                                <td><code><?= htmlspecialchars($detail['custom_class'] ?? '-') ?></code></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Créé le :</td>
                                <td><?= date('d/m/Y à H:i', strtotime($detail['created_at'] ?? 'now')) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Modifié le :</td>
                                <td><?= !empty($detail['updated_at']) ? date('d/m/Y à H:i', strtotime($detail['updated_at'])) : '-' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Image principale -->
                <?php if (!empty($detail['image_url'])): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3"><i class="bx bx-image"></i> Image principale</h6>
                        <div class="border rounded p-3 bg-light d-inline-block">
                            <img src="<?= base_url($detail['image_url']) ?>" class="img-fluid rounded" style="max-height: 200px;" alt="Image section">
                            <div class="mt-2">
                                <small class="text-muted">Position: <?= ($detail['image_droite'] ?? 0) == 1 ? 'À droite' : 'À gauche' ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Bouton CTA -->
                <?php if (!empty($detail['bouton_lien'])): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3"><i class="bx bx-link"></i> Bouton d'appel à l'action (CTA)</h6>
                        <table class="table table-borderless table-sm w-auto">
                            <tr>
                                <td class="fw-bold">Lien :</td>
                                <td><a href="<?= htmlspecialchars($detail['bouton_lien']) ?>" target="_blank"><?= htmlspecialchars($detail['bouton_lien']) ?> <i class="bx bx-link-external"></i></a></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Options JSON -->
                <?php if (!empty($detail['options_json'])): 
                    $options = json_decode($detail['options_json'], true);
                ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3"><i class="bx bx-code-alt"></i> Options JSON</h6>
                        <pre class="bg-light p-3 rounded border" style="max-height: 200px; overflow: auto;"><?= json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Statut des traductions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3"><i class="bx bx-flag"></i> Traductions disponibles</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php 
                            $has_fr = !empty($detail['titre_section_fr']) || !empty($detail['sous_titre_fr']) || !empty($detail['contenu_texte_fr']);
                            $has_en = !empty($detail['titre_section_en']) || !empty($detail['sous_titre_en']) || !empty($detail['contenu_texte_en']);
                            $has_sw = !empty($detail['titre_section_sw']) || !empty($detail['sous_titre_sw']) || !empty($detail['contenu_texte_sw']);
                            ?>
                            <div class="badge bg-<?= $has_fr ? 'success' : 'secondary' ?> p-2">
                                🇫🇷 Français <?= $has_fr ? '✓' : '✗' ?>
                            </div>
                            <div class="badge bg-<?= $has_en ? 'success' : 'secondary' ?> p-2">
                                🇬🇧 English <?= $has_en ? '✓' : '✗' ?>
                            </div>
                            <div class="badge bg-<?= $has_sw ? 'success' : 'secondary' ?> p-2">
                                🇹🇿 Kiswahili <?= $has_sw ? '✓' : '✗' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu multilingue avec onglets -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-muted mb-3"><i class="bx bx-file"></i> Contenu de la section</h6>
                        
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $current_lang == 'fr' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#french" type="button" role="tab">
                                    <i class="bx bx-flag"></i> 🇫🇷 Français
                                    <?php if (!$has_fr): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $current_lang == 'en' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#english" type="button" role="tab">
                                    <i class="bx bx-flag"></i> 🇬🇧 English
                                    <?php if (!$has_en): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $current_lang == 'sw' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#swahili" type="button" role="tab">
                                    <i class="bx bx-flag"></i> 🇹🇿 Kiswahili
                                    <?php if (!$has_sw): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- FRANÇAIS -->
                            <div class="tab-pane fade <?= $current_lang == 'fr' ? 'show active' : '' ?>" id="french" role="tabpanel">
                                <div class="section-preview">
                                    <?php 
                                    $titre = $detail['titre_section_fr'] ?? '';
                                    $sous_titre = $detail['sous_titre_fr'] ?? '';
                                    $contenu = $detail['contenu_texte_fr'] ?? '';
                                    $bouton = $detail['bouton_texte_fr'] ?? '';
                                    $image = $detail['image_url'] ?? '';
                                    $image_droite = ($detail['image_droite'] ?? 0) == 1;
                                    ?>
                                    
                                    <?php if (!empty($titre)): ?>
                                        <div class="preview-title">
                                            <h2><?= htmlspecialchars($titre) ?></h2>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($sous_titre)): ?>
                                        <div class="preview-subtitle">
                                            <h4 class="text-muted"><?= htmlspecialchars($sous_titre) ?></h4>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="preview-content">
                                        <?php if (!empty($image)): ?>
                                            <div class="row align-items-center">
                                                <?php if (!$image_droite): ?>
                                                    <div class="col-md-5 mb-3 mb-md-0">
                                                        <img src="<?= base_url($image) ?>" class="img-fluid rounded shadow-sm" alt="Image section">
                                                    </div>
                                                    <div class="col-md-7">
                                                        <?= !empty($contenu) ? $contenu : '<em class="text-muted">Aucun contenu en français</em>' ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="col-md-7">
                                                        <?= !empty($contenu) ? $contenu : '<em class="text-muted">Aucun contenu en français</em>' ?>
                                                    </div>
                                                    <div class="col-md-5 mt-3 mt-md-0">
                                                        <img src="<?= base_url($image) ?>" class="img-fluid rounded shadow-sm" alt="Image section">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div>
                                                <?= !empty($contenu) ? $contenu : '<em class="text-muted">Aucun contenu en français</em>' ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($bouton) && !empty($detail['bouton_lien'])): ?>
                                        <div class="preview-button mt-4">
                                            <a href="<?= htmlspecialchars($detail['bouton_lien']) ?>" class="btn btn-primary" target="_blank">
                                                <?= htmlspecialchars($bouton) ?>
                                                <i class="bx bx-link-external ms-1"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- ENGLISH -->
                            <div class="tab-pane fade <?= $current_lang == 'en' ? 'show active' : '' ?>" id="english" role="tabpanel">
                                <div class="section-preview">
                                    <?php 
                                    $titre = $detail['titre_section_en'] ?? '';
                                    $sous_titre = $detail['sous_titre_en'] ?? '';
                                    $contenu = $detail['contenu_texte_en'] ?? '';
                                    $bouton = $detail['bouton_texte_en'] ?? '';
                                    $image = $detail['image_url'] ?? '';
                                    $image_droite = ($detail['image_droite'] ?? 0) == 1;
                                    ?>
                                    
                                    <?php if (!empty($titre)): ?>
                                        <div class="preview-title">
                                            <h2><?= htmlspecialchars($titre) ?></h2>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($sous_titre)): ?>
                                        <div class="preview-subtitle">
                                            <h4 class="text-muted"><?= htmlspecialchars($sous_titre) ?></h4>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="preview-content">
                                        <?php if (!empty($image)): ?>
                                            <div class="row align-items-center">
                                                <?php if (!$image_droite): ?>
                                                    <div class="col-md-5 mb-3 mb-md-0">
                                                        <img src="<?= base_url($image) ?>" class="img-fluid rounded shadow-sm" alt="Section image">
                                                    </div>
                                                    <div class="col-md-7">
                                                        <?= !empty($contenu) ? $contenu : '<em class="text-muted">No English content</em>' ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="col-md-7">
                                                        <?= !empty($contenu) ? $contenu : '<em class="text-muted">No English content</em>' ?>
                                                    </div>
                                                    <div class="col-md-5 mt-3 mt-md-0">
                                                        <img src="<?= base_url($image) ?>" class="img-fluid rounded shadow-sm" alt="Section image">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div>
                                                <?= !empty($contenu) ? $contenu : '<em class="text-muted">No English content</em>' ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($bouton) && !empty($detail['bouton_lien'])): ?>
                                        <div class="preview-button mt-4">
                                            <a href="<?= htmlspecialchars($detail['bouton_lien']) ?>" class="btn btn-primary" target="_blank">
                                                <?= htmlspecialchars($bouton) ?>
                                                <i class="bx bx-link-external ms-1"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- KISWAHILI -->
                            <div class="tab-pane fade <?= $current_lang == 'sw' ? 'show active' : '' ?>" id="swahili" role="tabpanel">
                                <div class="section-preview">
                                    <?php 
                                    $titre = $detail['titre_section_sw'] ?? '';
                                    $sous_titre = $detail['sous_titre_sw'] ?? '';
                                    $contenu = $detail['contenu_texte_sw'] ?? '';
                                    $bouton = $detail['bouton_texte_sw'] ?? '';
                                    $image = $detail['image_url'] ?? '';
                                    $image_droite = ($detail['image_droite'] ?? 0) == 1;
                                    ?>
                                    
                                    <?php if (!empty($titre)): ?>
                                        <div class="preview-title">
                                            <h2><?= htmlspecialchars($titre) ?></h2>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($sous_titre)): ?>
                                        <div class="preview-subtitle">
                                            <h4 class="text-muted"><?= htmlspecialchars($sous_titre) ?></h4>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="preview-content">
                                        <?php if (!empty($image)): ?>
                                            <div class="row align-items-center">
                                                <?php if (!$image_droite): ?>
                                                    <div class="col-md-5 mb-3 mb-md-0">
                                                        <img src="<?= base_url($image) ?>" class="img-fluid rounded shadow-sm" alt="Picha ya sehemu">
                                                    </div>
                                                    <div class="col-md-7">
                                                        <?= !empty($contenu) ? $contenu : '<em class="text-muted">Hakuna maudhui kwa Kiswahili</em>' ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="col-md-7">
                                                        <?= !empty($contenu) ? $contenu : '<em class="text-muted">Hakuna maudhui kwa Kiswahili</em>' ?>
                                                    </div>
                                                    <div class="col-md-5 mt-3 mt-md-0">
                                                        <img src="<?= base_url($image) ?>" class="img-fluid rounded shadow-sm" alt="Picha ya sehemu">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div>
                                                <?= !empty($contenu) ? $contenu : '<em class="text-muted">Hakuna maudhui kwa Kiswahili</em>' ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($bouton) && !empty($detail['bouton_lien'])): ?>
                                        <div class="preview-button mt-4">
                                            <a href="<?= htmlspecialchars($detail['bouton_lien']) ?>" class="btn btn-primary" target="_blank">
                                                <?= htmlspecialchars($bouton) ?>
                                                <i class="bx bx-link-external ms-1"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Note d'information -->
                <div class="alert alert-info mt-4">
                    <i class="bx bx-info-circle"></i>
                    <strong>Note :</strong> Cet aperçu est une représentation du contenu. Le rendu final peut varier selon le thème et la mise en page du site.
                </div>

                <!-- Actions -->
                <div class="mt-4 text-end">
                    <a href="<?= base_url('Sections') ?>" class="btn btn-secondary me-2">
                        <i class="bx bx-arrow-back"></i> Retour à la liste
                    </a>
                    <a href="<?= base_url('Sections/edit/'.$detail['id_section']) ?>" class="btn btn-warning">
                        <i class="bx bx-edit"></i> Modifier cette section
                    </a>
                </div>

            </div>
        </div>

    </div>

<style>
.section-preview {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.section-preview .preview-title h2 {
    font-size: 1.75rem;
    margin-bottom: 1rem;
    color: #1a1e2b;
}

.section-preview .preview-subtitle h4 {
    font-size: 1.25rem;
    margin-bottom: 1.5rem;
    color: #6c757d;
}

.section-preview .preview-content {
    font-size: 1rem;
    line-height: 1.7;
    color: #495057;
}

.section-preview .preview-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

.section-preview .preview-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
}

.section-preview .preview-content table td,
.section-preview .preview-content table th {
    border: 1px solid #dee2e6;
    padding: 10px;
}

.section-preview .preview-content table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.section-preview .preview-content ul,
.section-preview .preview-content ol {
    padding-left: 1.5rem;
}

.section-preview .preview-content blockquote {
    border-left: 4px solid #667eea;
    padding-left: 1rem;
    margin: 1rem 0;
    color: #6c757d;
}

.nav-tabs .nav-link {
    color: #495057;
    font-weight: 500;
    padding: 10px 20px;
    border: none;
    border-bottom: 2px solid transparent;
}

.nav-tabs .nav-link:hover {
    border-color: transparent;
    color: #667eea;
}

.nav-tabs .nav-link.active {
    color: #667eea;
    border-bottom: 2px solid #667eea;
    background: transparent;
}

.table-borderless td, 
.table-borderless th {
    padding: 8px 0;
}

pre {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    font-size: 12px;
    font-family: 'Monaco', 'Menlo', monospace;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border: none;
    color: white;
}

.btn-warning:hover {
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
}

.badge {
    font-weight: 500;
    padding: 6px 12px;
}

.card {
    border-radius: 16px;
    overflow: hidden;
}

.card-header {
    border-bottom: 1px solid rgba(0,0,0,0.08);
}

.alert {
    border-radius: 12px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Activer les tooltips si nécessaire
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Scroll to top of tab content when switching tabs
    var tabButtons = document.querySelectorAll('.nav-tabs button');
    tabButtons.forEach(function(button) {
        button.addEventListener('shown.bs.tab', function() {
            var tabContent = document.querySelector('.tab-content');
            if (tabContent) {
                tabContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>