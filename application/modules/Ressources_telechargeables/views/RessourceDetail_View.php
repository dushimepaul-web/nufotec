<?php
// =====================================================================
// PAGES STATIQUES — la table `pages` a été supprimée de la base.
// Ces données sont définies en dur directement dans les vues.
// =====================================================================
$pages = array(
    array('id_page' => 1,  'titre_page' => 'Home',                                    'slug' => 'home'),
    array('id_page' => 2,  'titre_page' => 'About',                                   'slug' => 'about'),
    array('id_page' => 3,  'titre_page' => 'Background & Strategic Rationale',        'slug' => 'background-strategic-rationale'),
    array('id_page' => 4,  'titre_page' => 'ESG & Sustainability',                    'slug' => 'esg-sustainability'),
    array('id_page' => 5,  'titre_page' => 'Research & Innovation',                   'slug' => 'research-innovation'),
    array('id_page' => 6,  'titre_page' => 'Corporate Structure & Governance',        'slug' => 'corporate-structure-governance'),
    array('id_page' => 7,  'titre_page' => 'nufotec-phytomed-industries-facility',    'slug' => 'nufotec-phytomed-industries-facility'),
    array('id_page' => 8,  'titre_page' => 'Our Product Categories',                  'slug' => 'product-categories'),
    array('id_page' => 9,  'titre_page' => 'Raw Material Acquisition',                'slug' => 'raw-material-acquisition'),
    array('id_page' => 10, 'titre_page' => 'Industrial Technology & Processing Systems', 'slug' => 'industrial-technology'),
    array('id_page' => 11, 'titre_page' => 'Market & Industry Outlook',               'slug' => 'market-outlook'),
    array('id_page' => 12, 'titre_page' => 'Digital Growth & Market Expansion Platform', 'slug' => 'digital-growth'),
    array('id_page' => 13, 'titre_page' => 'Digital Health Consultation',             'slug' => 'digital-health'),
    array('id_page' => 14, 'titre_page' => 'Phased Investment Projection',            'slug' => 'investment-projection'),
    array('id_page' => 15, 'titre_page' => 'Our Investor & Partner Commitment',       'slug' => 'investor-commitment'),
    array('id_page' => 16, 'titre_page' => 'Commission Fee Payment to Brokers',       'slug' => 'broker-commission'),
    array('id_page' => 17, 'titre_page' => 'Risk Analysis & Mitigation Strategies',   'slug' => 'risk-analysis'),
    array('id_page' => 18, 'titre_page' => 'Strategic Partnerships',                  'slug' => 'strategic-partnerships'),
    array('id_page' => 19, 'titre_page' => 'Our Services',                            'slug' => 'our-services'),
    array('id_page' => 22, 'titre_page' => 'Vision & Mission',                        'slug' => 'vision-mission'),
    array('id_page' => 23, 'titre_page' => 'Brokers Form',                            'slug' => 'brokers-form'),
    array('id_page' => 24, 'titre_page' => 'Investors form',                          'slug' => 'investors-form'),
);
$pages_list = $pages; ?>
<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Gestion Contenu</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('Ressources_telechargeables') ?>">Ressources</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Détail</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-secondary" href="<?= base_url('Ressources_telechargeables') ?>">
                <i class="bx bx-arrow-back"></i> Retour à la liste
            </a>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-check-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary">
                        <i class="bx <?= $this->Ressources_telechargeables->get_type_icon($detail['type']) ?> me-2"></i>
                        <?= htmlspecialchars($detail['titre']) ?>
                    </h5>
                    <span class="badge bg-<?= $this->Ressources_telechargeables->get_type_color($detail['type']) ?>">
                        <?= $this->Ressources_telechargeables->get_type_label($detail['type']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if ($detail['type'] == 'video'): ?>
                        <div class="ratio ratio-16x9 mb-3">
                            <video controls>
                                <source src="<?= base_url($detail['fichier_url']) ?>" type="video/mp4">
                                Votre navigateur ne supporte pas la lecture vidéo.
                            </video>
                        </div>
                    <?php elseif (in_array($detail['type'], ['pdf', 'rapport_annuel', 'brochure', 'dossier_investisseur', 'etude_clinique', 'fiche_technique'])): ?>
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="bx bx-info-circle fs-4 me-2"></i>
                            <div>Ce document PDF peut être visualisé ci-dessous ou téléchargé.</div>
                        </div>
                        <div class="ratio ratio-4x3">
                            <iframe src="<?= base_url($detail['fichier_url']) ?>" allowfullscreen></iframe>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 bg-light rounded">
                            <i class="bx <?= $this->Ressources_telechargeables->get_type_icon($detail['type']) ?> fs-1 text-muted mb-3"></i>
                            <h5><?= htmlspecialchars($detail['titre']) ?></h5>
                            <p class="text-muted">Ce fichier doit être téléchargé pour être consulté.</p>
                            <a href="<?= base_url($detail['fichier_url']) ?>" target="_blank" class="btn btn-primary btn-lg">
                                <i class="bx bx-download me-2"></i>Télécharger
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Type:</td>
                            <td class="text-end">
                                <span class="badge bg-<?= $this->Ressources_telechargeables->get_type_color($detail['type']) ?>">
                                    <?= $this->Ressources_telechargeables->get_type_label($detail['type']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Langue:</td>
                            <td class="text-end"><span class="badge bg-light text-dark border"><?= strtoupper($detail['langue'] ?? 'FR') ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Date publication:</td>
                            <td class="text-end fw-bold"><?= !empty($detail['date_publication']) ? date('d/m/Y', strtotime($detail['date_publication'])) : 'Non spécifiée' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Taille fichier:</td>
                            <td class="text-end fw-bold"><?= $detail['taille_fichier'] ?: 'Inconnue' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Visibilité:</td>
                            <td class="text-end">
                                <?php if (!empty($detail['est_public']) && $detail['est_public'] == 1): ?>
                                    <span class="badge bg-success"><i class="bx bx-globe"></i> Public</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="bx bx-lock"></i> Privé</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Fichier:</td>
                            <td class="text-end text-break small"><?= basename($detail['fichier_url']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Page associée:</td>
                            <td class="text-end">
                                <?php 
                                $page_name = 'Aucune';
                                if (!empty($pages) && !empty($detail['id_page_associee'])) {
                                    foreach ($pages as $page) {
                                        if ($page['id_page'] == $detail['id_page_associee']) {
                                            $page_name = $page['titre_page'];
                                            break;
                                        }
                                    }
                                }
                                echo htmlspecialchars($page_name);
                                ?>
                            </td>
                        </tr>
                    </table>

                    <hr>

                    <h6 class="text-muted mb-2">Description</h6>
                    <p class="text-dark"><?= nl2br(htmlspecialchars($detail['description'] ?? '<em>Aucune description</em>')) ?></p>

                    <hr>

                    <div class="d-grid gap-2">
                        <a href="<?= base_url($detail['fichier_url']) ?>" target="_blank" class="btn btn-primary">
                            <i class="bx bx-download me-2"></i>Télécharger
                        </a>
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal">
                            <i class="bx bx-edit me-2"></i>Modifier
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


<!-- MODAL EDIT -->
<div class="modal fade" id="editModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la ressource</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Ressources_telechargeables/Update') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_ressource" value="<?= $detail['id_ressource'] ?>">
                
                <div class="modal-body p-4">
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations générales</h6>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($detail['titre']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                    <select class="form-select" name="type" required>
                                        <option value="pdf" <?= $detail['type'] == 'pdf' ? 'selected' : '' ?>>PDF</option>
                                        <option value="video" <?= $detail['type'] == 'video' ? 'selected' : '' ?>>Vidéo</option>
                                        <option value="etude_clinique" <?= $detail['type'] == 'etude_clinique' ? 'selected' : '' ?>>Étude clinique</option>
                                        <option value="rapport_annuel" <?= $detail['type'] == 'rapport_annuel' ? 'selected' : '' ?>>Rapport annuel</option>
                                        <option value="fiche_technique" <?= $detail['type'] == 'fiche_technique' ? 'selected' : '' ?>>Fiche technique</option>
                                        <option value="brochure" <?= $detail['type'] == 'brochure' ? 'selected' : '' ?>>Brochure</option>
                                        <option value="dossier_investisseur" <?= $detail['type'] == 'dossier_investisseur' ? 'selected' : '' ?>>Dossier investisseur</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-file me-2"></i>Fichier</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Nouveau fichier (laisser vide pour conserver l'actuel)</label>
                                    <input type="file" class="form-control" name="fichier">
                                    <small class="text-muted">Fichier actuel: <strong><?= basename($detail['fichier_url']) ?></strong></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-detail me-2"></i>Détails</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($detail['description'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Date de publication</label>
                                    <input type="date" class="form-control" name="date_publication" value="<?= $detail['date_publication'] ?? '' ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Langue</label>
                                    <select class="form-select" name="langue">
                                        <option value="fr" <?= ($detail['langue'] ?? 'fr') == 'fr' ? 'selected' : '' ?>>Français</option>
                                        <option value="en" <?= ($detail['langue'] ?? '') == 'en' ? 'selected' : '' ?>>English</option>
                                        <option value="es" <?= ($detail['langue'] ?? '') == 'es' ? 'selected' : '' ?>>Español</option>
                                        <option value="de" <?= ($detail['langue'] ?? '') == 'de' ? 'selected' : '' ?>>Deutsch</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Page associée</label>
                                    <select class="form-select" name="id_page_associee">
                                        <option value="">-- Aucune --</option>
                                        <?php foreach ($pages as $page): ?>
                                            <option value="<?= $page['id_page'] ?>" <?= ($detail['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($page['titre_page']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-cog me-2"></i>Paramètres</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="est_public" id="edit_est_public" value="1" <?= (!empty($detail['est_public']) && $detail['est_public'] == 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="edit_est_public">Ressource publique (visible par tous)</label>
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

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>