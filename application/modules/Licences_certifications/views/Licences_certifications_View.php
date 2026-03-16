<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
// Définir les statuts et badges directement dans la vue
$statut_labels = [
    'obtenue' => 'Obtenue',
    'en_cours' => 'En cours',
    'a_renouveler' => 'À renouveler'
];

$statut_badges = [
    'obtenue' => 'bg-success',
    'en_cours' => 'bg-warning text-dark',
    'a_renouveler' => 'bg-danger'
];

$organismes_list = [
    'PACRA' => 'PACRA',
    'ZDA' => 'ZDA',
    'ZEMA' => 'ZEMA',
    'ZMRA' => 'ZMRA',
    'ZAMRA' => 'ZAMRA',
    'ZABS' => 'ZABS',
    'IMMIGRATION' => 'IMMIGRATION',
    'Local Health Department' => 'Local Health Department',
    'Ministry of Land' => 'Ministry of Land',
    'ISO' => 'ISO',
    'Autre' => 'Autre'
];

// Fonction helper pour vérifier si une licence expire bientôt
function get_expiration_alert($date_expiration) {
    if (empty($date_expiration)) return null;
    
    $expiration = new DateTime($date_expiration);
    $now = new DateTime();
    $interval = $now->diff($expiration);
    $days = (int)$interval->format('%r%a');
    
    if ($days < 0) {
        return ['class' => 'text-danger fw-bold', 'icon' => 'bx-error-circle', 'text' => 'Expirée'];
    } elseif ($days <= 30) {
        return ['class' => 'text-warning fw-bold', 'icon' => 'bx-error', 'text' => 'Expire bientôt ('.$days.' j)'];
    }
    return null;
}
?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Conformité</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Licences & Certifications</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_licence">
                <i class="bx bx-plus"></i> Nouvelle Licence
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
                <h5 class="mb-0 text-primary"><i class="bx bx-certification me-2"></i>Liste des Licences & Certifications</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="licencesTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">Nom de la licence</th>
                            <th width="12%">Organisme</th>
                            <th width="10%">Obtention</th>
                            <th width="12%">Expiration</th>
                            <th width="10%">Statut</th>
                            <th width="8%">Document</th>
                            <th width="8%">Page</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($licences)): $i = 1; foreach ($licences as $value): 
                        $statut_label = $statut_labels[$value['statut'] ?? ''] ?? ucfirst($value['statut'] ?? 'Inconnu');
                        $statut_badge = $statut_badges[$value['statut'] ?? ''] ?? 'bg-light text-dark';
                        
                        // Alertes expiration
                        $expiration_alert = get_expiration_alert($value['date_expiration'] ?? null);
                        
                        // Page associée
                        $page_titre = '-';
                        if (!empty($value['id_page_associee']) && !empty($pages)) {
                            foreach ($pages as $page) {
                                if ($page['id_page'] == $value['id_page_associee']) {
                                    $page_titre = $page['titre_page'];
                                    break;
                                }
                            }
                        }
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['nom_licence'] ?? '') ?></strong>
                                    <?php if ($expiration_alert): ?>
                                        <small class="<?= $expiration_alert['class'] ?>">
                                            <i class="bx <?= $expiration_alert['icon'] ?> me-1"></i><?= $expiration_alert['text'] ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($value['organisme'] ?? '-') ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <?= !empty($value['date_obtention']) ? date('d/m/Y', strtotime($value['date_obtention'])) : '<span class="text-muted">-</span>' ?>
                            </td>

                            <td class="text-center">
                                <?php if (!empty($value['date_expiration'])): ?>
                                    <?php if ($expiration_alert && $expiration_alert['text'] == 'Expirée'): ?>
                                        <span class="badge bg-danger"><?= date('d/m/Y', strtotime($value['date_expiration'])) ?></span>
                                    <?php else: ?>
                                        <?= date('d/m/Y', strtotime($value['date_expiration'])) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_licence'] ?>" class="text-decoration-none">
                                    <span class="badge <?= $statut_badge ?>"><?= $statut_label ?></span>
                                </a>
                            </td>

                            <td class="text-center">
                                <?php if (!empty($value['fichier_url'])): ?>
                                    <a href="<?= base_url('attachments/Licences/'.$value['fichier_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Voir le document">
                                        <i class="bx bx-file"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted"><i class="bx bx-file-blank"></i></span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <?php if ($page_titre != '-'): ?>
                                    <small class="text-info"><?= htmlspecialchars(strlen($page_titre) > 12 ? substr($page_titre, 0, 12).'...' : $page_titre) ?></small>
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
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_licence'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_licence'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_licence'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id_licence'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-certification me-2"></i>Détails de la licence</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h4 class="mb-1"><?= htmlspecialchars($value['nom_licence'] ?? '') ?></h4>
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="bx bx-building me-1"></i><?= htmlspecialchars($value['organisme'] ?? '-') ?>
                                                        </span>
                                                    </div>
                                                    <span class="badge <?= $statut_badge ?> fs-6"><?= $statut_label ?></span>
                                                </div>
                                                
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="text-muted small">Date d'obtention</label>
                                                        <p class="mb-0 fw-bold">
                                                            <i class="bx bx-calendar-check text-success me-1"></i>
                                                            <?= !empty($value['date_obtention']) ? date('d/m/Y', strtotime($value['date_obtention'])) : 'Non spécifiée' ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="text-muted small">Date d'expiration</label>
                                                        <p class="mb-0 fw-bold">
                                                            <?php if (!empty($value['date_expiration'])): ?>
                                                                <?php if ($expiration_alert && $expiration_alert['text'] == 'Expirée'): ?>
                                                                    <i class="bx bx-calendar-x text-danger me-1"></i>
                                                                    <span class="text-danger"><?= date('d/m/Y', strtotime($value['date_expiration'])) ?></span>
                                                                <?php else: ?>
                                                                    <i class="bx bx-calendar text-primary me-1"></i>
                                                                    <?= date('d/m/Y', strtotime($value['date_expiration'])) ?>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">Non spécifiée</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                    
                                                    <?php if (!empty($value['fichier_url'])): ?>
                                                    <div class="col-12">
                                                        <label class="text-muted small">Document</label>
                                                        <p class="mb-0">
                                                            <a href="<?= base_url('attachments/Licences/'.$value['fichier_url']) ?>" target="_blank" class="btn btn-outline-primary">
                                                                <i class="bx bx-download me-2"></i>Télécharger le document
                                                            </a>
                                                        </p>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="col-md-6">
                                                        <label class="text-muted small">Page associée</label>
                                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($page_titre) ?></p>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <label class="text-muted small">ID Licence</label>
                                                        <p class="mb-0 font-monospace text-muted">#<?= $value['id_licence'] ?></p>
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
                        <div class="modal fade" id="update_<?= $value['id_licence'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la licence</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Licences_certifications/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $value['id_licence'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Nom de la licence <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="nom_licence" value="<?= htmlspecialchars($value['nom_licence'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Organisme <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="organisme" required>
                                                                <option value="">Sélectionner...</option>
                                                                <?php foreach ($organismes_list as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= ($value['organisme'] ?? '') == $key ? 'selected' : '' ?>>
                                                                        <?= $label ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Statut <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="statut" required>
                                                                <option value="">Sélectionner...</option>
                                                                <?php foreach ($statut_labels as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= ($value['statut'] ?? '') == $key ? 'selected' : '' ?>>
                                                                        <?= $label ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Date d'obtention</label>
                                                            <input type="date" class="form-control" name="date_obtention" value="<?= $value['date_obtention'] ?? '' ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Date d'expiration</label>
                                                            <input type="date" class="form-control" name="date_expiration" value="<?= $value['date_expiration'] ?? '' ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-link me-2"></i>Associations</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Page associée</label>
                                                            <select class="form-select" name="id_page_associee">
                                                                <option value="">Aucune page</option>
                                                                <?php if (!empty($pages)): foreach ($pages as $page): ?>
                                                                    <option value="<?= $page['id_page'] ?>" <?= ($value['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($page['titre_page']) ?>
                                                                    </option>
                                                                <?php endforeach; endif; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Document actuel</label>
                                                            <?php if (!empty($value['fichier_url'])): ?>
                                                                <div class="d-flex align-items-center">
                                                                    <a href="<?= base_url('attachments/Licences/'.$value['fichier_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                                                        <i class="bx bx-file me-1"></i>Voir
                                                                    </a>
                                                                    <span class="text-muted small text-truncate" style="max-width: 150px;"><?= $value['fichier_url'] ?></span>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="text-muted">Aucun document</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Nouveau document (remplace l'actuel)</label>
                                                            <input type="file" class="form-control" name="fichier_licence" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                                            <small class="text-muted">Formats acceptés: PDF, DOC, DOCX, JPG, PNG. Laissez vide pour conserver le document actuel.</small>
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
                        <div class="modal fade" id="delete_<?= $value['id_licence'] ?>" tabindex="-1">
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
                                            Vous allez supprimer la licence <strong><?= htmlspecialchars($value['nom_licence'] ?? '') ?></strong>.<br>
                                            Cette action est irréversible.
                                        </p>
                                    </div>
                                    <div class="modal-footer bg-light justify-content-center">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <a href="<?= base_url('Licences_certifications/Delete/'.$value['id_licence']) ?>" class="btn btn-danger">
                                            <i class="bx bx-trash me-1"></i>Supprimer définitivement
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL CHANGE STATUS -->
                        <div class="modal fade" id="status_<?= $value['id_licence'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title"><i class="bx bx-transfer me-2"></i>Changer le statut</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('Licences_certifications/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_licence'] ?>">
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nouveau statut pour <span class="text-primary"><?= htmlspecialchars($value['nom_licence'] ?? '') ?></span></label>
                                                <select class="form-select form-select-lg" name="statut" required>
                                                    <?php foreach ($statut_labels as $key => $label): 
                                                        $badge_class = $statut_badges[$key] ?? 'bg-light text-dark';
                                                    ?>
                                                        <option value="<?= $key ?>" <?= ($value['statut'] ?? '') == $key ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="alert alert-info border-0">
                                                <i class="bx bx-info-circle me-2"></i>
                                                Le changement de statut sera appliqué immédiatement.
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-info text-white"><i class="bx bx-check me-1"></i>Appliquer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bx bx-certification" style="font-size: 3rem;"></i>
                                    <p class="mt-2 mb-0">Aucune licence ou certification enregistrée</p>
                                    <small>Cliquez sur "Nouvelle Licence" pour ajouter</small>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL CREATE -->
    <div class="modal fade" id="create_licence" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle Licence / Certification</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form action="<?= base_url('Licences_certifications/Create') ?>" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <div class="card border-0 bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations obligatoires</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Nom de la licence <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nom_licence" placeholder="Ex: Licence d'exploitation minière" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Organisme <span class="text-danger">*</span></label>
                                        <select class="form-select" name="organisme" required>
                                            <option value="">Sélectionner l'organisme...</option>
                                            <?php foreach ($organismes_list as $key => $label): ?>
                                                <option value="<?= $key ?>"><?= $label ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Statut <span class="text-danger">*</span></label>
                                        <select class="form-select" name="statut" required>
                                            <option value="">Sélectionner le statut...</option>
                                            <?php foreach ($statut_labels as $key => $label): ?>
                                                <option value="<?= $key ?>"><?= $label ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3"><i class="bx bx-calendar me-2"></i>Dates</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Date d'obtention</label>
                                        <input type="date" class="form-control" name="date_obtention">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Date d'expiration</label>
                                        <input type="date" class="form-control" name="date_expiration">
                                        <small class="text-muted">Laissez vide si la licence n'expire pas</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3"><i class="bx bx-link me-2"></i>Associations & Document</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Page associée</label>
                                        <select class="form-select" name="id_page_associee">
                                            <option value="">Aucune page</option>
                                            <?php if (!empty($pages)): foreach ($pages as $page): ?>
                                                <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page']) ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                        <small class="text-muted">Associez cette licence à une page du site</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Document</label>
                                        <input type="file" class="form-control" name="fichier_licence" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                        <small class="text-muted">Formats: PDF, DOC, DOCX, JPG, PNG (max 10Mo)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Créer la licence</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<script>
$(document).ready(function() {
    $('#licencesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true
    });
});
</script>