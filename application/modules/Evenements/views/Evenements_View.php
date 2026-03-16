<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Contenu</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Événements</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_evenement">
                <i class="bx bx-plus"></i> Nouvel Événement
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
                <h5 class="mb-0 text-primary"><i class="bx bx-calendar-event me-2"></i>Liste des Événements</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="evenementsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Image</th>
                            <th width="20%">Titre</th>
                            <th width="12%">Lieu</th>
                            <th width="15%">Dates</th>
                            <th width="10%">Capacité</th>
                            <th width="8%">Visibilité</th>
                            <th width="10%">Page</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($evenements)): $i = 1; foreach ($evenements as $value): 
                        // Statut de l'événement (passé, en cours, à venir)
                        $now = new DateTime();
                        $date_debut = !empty($value['date_debut']) ? new DateTime($value['date_debut']) : null;
                        $date_fin = !empty($value['date_fin']) ? new DateTime($value['date_fin']) : null;
                        
                        if ($date_debut) {
                            if ($now < $date_debut) {
                                $statut = '<span class="badge bg-info">À venir</span>';
                            } elseif ($date_fin && $now > $date_fin) {
                                $statut = '<span class="badge bg-secondary">Terminé</span>';
                            } else {
                                $statut = '<span class="badge bg-success">En cours</span>';
                            }
                        } else {
                            $statut = '<span class="badge bg-light text-dark">-</span>';
                        }
                        
                        // Image
                        $image_path = !empty($value['image_url']) ? 'attachments/Evenements/'.$value['image_url'] : 'assets/images/no-image.png';
                        
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
                        
                        // Pourcentage d'inscriptions
                        $pourcentage = 0;
                        if (!empty($value['capacite_max']) && $value['capacite_max'] > 0) {
                            $pourcentage = min(100, round((($value['inscriptions_actuelles'] ?? 0) / $value['capacite_max']) * 100));
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
                                    <?= $statut ?>
                                    <?php if (!empty($value['description'])): ?>
                                        <small class="text-muted text-truncate" style="max-width: 200px;"><?= htmlspecialchars($value['description']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <?php if (!empty($value['lieu'])): ?>
                                    <span class="text-dark"><i class="bx bx-map-pin me-1 text-danger"></i><?= htmlspecialchars($value['lieu']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <small>
                                        <i class="bx bx-calendar me-1 text-primary"></i>
                                        <?= !empty($value['date_debut']) ? date('d/m/Y H:i', strtotime($value['date_debut'])) : '-' ?>
                                    </small>
                                    <?php if (!empty($value['date_fin'])): ?>
                                        <small>
                                            <i class="bx bx-calendar-check me-1 text-success"></i>
                                            <?= date('d/m/Y H:i', strtotime($value['date_fin'])) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td class="text-center">
                                <?php if (!empty($value['capacite_max'])): ?>
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="badge bg-light text-dark border">
                                            <?= $value['inscriptions_actuelles'] ?? 0 ?> / <?= $value['capacite_max'] ?>
                                        </span>
                                        <div class="progress mt-1" style="width: 60px; height: 4px;">
                                            <div class="progress-bar bg-<?= $pourcentage >= 90 ? 'danger' : ($pourcentage >= 70 ? 'warning' : 'success') ?>" 
                                                 style="width: <?= $pourcentage ?>%"></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Illimité</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_evenement'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['est_public']) && $value['est_public'] == 1): ?>
                                        <span class="badge bg-success">Public</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Privé</span>
                                    <?php endif; ?>
                                </a>
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
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_evenement'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_evenement'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_evenement'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id_evenement'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-calendar-event me-2"></i>Détails de l'événement</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 text-center border-end">
                                                <img src="<?= base_url($image_path) ?>" 
                                                     class="rounded border border-3 border-primary mb-3"
                                                     style="width:100%; max-height:200px; object-fit:cover;"
                                                     onerror="this.src='<?= base_url('assets/images/no-image.png') ?>'"
                                                     alt="<?= htmlspecialchars($value['titre'] ?? '') ?>">
                                                
                                                <?= $statut ?>
                                                
                                                <h5 class="mt-3 mb-1"><?= htmlspecialchars($value['titre'] ?? '') ?></h5>
                                                
                                                <?php if (!empty($value['lieu'])): ?>
                                                    <p class="text-muted"><i class="bx bx-map-pin me-1"></i><?= htmlspecialchars($value['lieu']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="text-muted small">Titre</label>
                                                        <p class="mb-0 fw-bold fs-5"><?= htmlspecialchars($value['titre'] ?? '-') ?></p>
                                                    </div>
                                                    
                                                    <div class="col-12">
                                                        <label class="text-muted small">Slug</label>
                                                        <p class="mb-0 font-monospace text-muted"><?= htmlspecialchars($value['slug'] ?? '-') ?></p>
                                                    </div>
                                                    
                                                    <?php if (!empty($value['description'])): ?>
                                                    <div class="col-12">
                                                        <label class="text-muted small">Description</label>
                                                        <div class="p-3 bg-light rounded">
                                                            <?= nl2br(htmlspecialchars($value['description'])) ?>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="col-6">
                                                        <label class="text-muted small">Date de début</label>
                                                        <p class="mb-0 fw-bold">
                                                            <i class="bx bx-calendar text-primary me-1"></i>
                                                            <?= !empty($value['date_debut']) ? date('d/m/Y H:i', strtotime($value['date_debut'])) : '-' ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Date de fin</label>
                                                        <p class="mb-0 fw-bold">
                                                            <i class="bx bx-calendar-check text-success me-1"></i>
                                                            <?= !empty($value['date_fin']) ? date('d/m/Y H:i', strtotime($value['date_fin'])) : '-' ?>
                                                        </p>
                                                    </div>
                                                    
                                                    <div class="col-6">
                                                        <label class="text-muted small">Capacité</label>
                                                        <p class="mb-0 fw-bold">
                                                            <?php if (!empty($value['capacite_max'])): ?>
                                                                <?= $value['inscriptions_actuelles'] ?? 0 ?> inscrits / <?= $value['capacite_max'] ?> places
                                                            <?php else: ?>
                                                                Illimité
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Visibilité</label>
                                                        <p class="mb-0">
                                                            <?php if (!empty($value['est_public']) && $value['est_public'] == 1): ?>
                                                                <span class="badge bg-success">Public</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Privé</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                    
                                                    <?php if (!empty($value['lien_inscription'])): ?>
                                                    <div class="col-12">
                                                        <label class="text-muted small">Lien d'inscription</label>
                                                        <p class="mb-0">
                                                            <a href="<?= htmlspecialchars($value['lien_inscription']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="bx bx-link-external me-1"></i>Ouvrir le lien
                                                            </a>
                                                        </p>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="col-6">
                                                        <label class="text-muted small">Page associée</label>
                                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($page_titre) ?></p>
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
                        <div class="modal fade" id="update_<?= $value['id_evenement'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier l'événement</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Evenements/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $value['id_evenement'] ?>">
                                        
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
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Description</label>
                                                            <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Lieu</label>
                                                            <input type="text" class="form-control" name="lieu" value="<?= htmlspecialchars($value['lieu'] ?? '') ?>" placeholder="Adresse ou lieu de l'événement">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Lien d'inscription</label>
                                                            <input type="url" class="form-control" name="lien_inscription" value="<?= htmlspecialchars($value['lien_inscription'] ?? '') ?>" placeholder="https://...">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Dates -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-calendar me-2"></i>Dates & Capacité</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Date de début <span class="text-danger">*</span></label>
                                                            <input type="datetime-local" class="form-control" name="date_debut" value="<?= !empty($value['date_debut']) ? date('Y-m-d\TH:i', strtotime($value['date_debut'])) : '' ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Date de fin</label>
                                                            <input type="datetime-local" class="form-control" name="date_fin" value="<?= !empty($value['date_fin']) ? date('Y-m-d\TH:i', strtotime($value['date_fin'])) : '' ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Capacité max</label>
                                                            <input type="number" class="form-control" name="capacite_max" value="<?= $value['capacite_max'] ?? '' ?>" min="0" placeholder="Illimité si vide">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Image & Publication -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Image & Publication</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Nouvelle image</label>
                                                            <input type="file" class="form-control" name="image_url" accept="image/*">
                                                            <small class="text-muted">Formats: JPG, PNG, GIF, WEBP (max 2MB)</small>
                                                            <?php if (!empty($value['image_url'])): ?>
                                                                <div class="mt-2">
                                                                    <img src="<?= base_url('attachments/Evenements/'.$value['image_url']) ?>" style="max-width: 150px; max-height: 100px;" class="rounded border">
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">Page associée</label>
                                                            <select class="form-select" name="id_page_associee">
                                                                <option value="">Aucune</option>
                                                                <?php foreach ($pages as $page): ?>
                                                                    <option value="<?= $page['id_page'] ?>" <?= ($value['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($page['titre_page']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">Options</label>
                                                            <div class="form-check form-switch mt-2">
                                                                <input type="checkbox" class="form-check-input" name="est_public" id="est_public_<?= $value['id_evenement'] ?>" value="1" <?= (!empty($value['est_public']) && $value['est_public'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="est_public_<?= $value['id_evenement'] ?>">Rendre public</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bx bx-save me-2"></i>Enregistrer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $value['id_evenement'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer :</p>
                                        <p class="fw-bold fs-5">"<?= htmlspecialchars($value['titre'] ?? '') ?>"</p>
                                        <?php if (!empty($value['image_url'])): ?>
                                            <p class="text-warning small"><i class="bx bx-info-circle me-1"></i>L'image sera supprimée.</p>
                                        <?php endif; ?>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Evenements/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_evenement'] ?>">
                                        <div class="modal-footer bg-light justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL STATUS -->
                        <div class="modal fade" id="status_<?= $value['id_evenement'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['est_public']) && $value['est_public'] == 1) ? 'bg-secondary' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['est_public']) && $value['est_public'] == 1) ? '<i class="bx bx-hide me-2"></i>Masquer' : '<i class="bx bx-show me-2"></i>Rendre public' ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['est_public']) && $value['est_public'] == 1) ? 'masquer' : 'rendre public' ?></strong> :</p>
                                        <p class="fw-bold">"<?= htmlspecialchars($value['titre'] ?? '') ?>"</p>
                                    </div>
                                    <form action="<?= base_url('Evenements/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_evenement'] ?>">
                                        <input type="hidden" name="est_public" value="<?= (!empty($value['est_public']) && $value['est_public'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['est_public']) && $value['est_public'] == 1) ? 'btn-secondary' : 'btn-success' ?>">
                                                <?= (!empty($value['est_public']) && $value['est_public'] == 1) ? 'Masquer' : 'Rendre public' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bx bx-calendar-event text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun événement trouvé</p>
                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#create_evenement">
                                    <i class="bx bx-plus me-2"></i>Créer votre premier événement
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

<!-- MODAL CREATE EVENEMENT -->
<div class="modal fade" id="create_evenement" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvel Événement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Evenements/Create') ?>" method="POST" enctype="multipart/form-data">
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
                                    <input type="text" class="form-control" name="slug" required id="create_slug">
                                    <small class="text-muted">Généré auto. ou modifiez</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea class="form-control" name="description" rows="3"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Lieu</label>
                                    <input type="text" class="form-control" name="lieu" placeholder="Adresse ou lieu de l'événement">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Lien d'inscription</label>
                                    <input type="url" class="form-control" name="lien_inscription" placeholder="https://...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Dates -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-calendar me-2"></i>Dates & Capacité</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Date de début <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="date_debut" required value="<?= date('Y-m-d\TH:i') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Date de fin</label>
                                    <input type="datetime-local" class="form-control" name="date_fin">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Capacité max</label>
                                    <input type="number" class="form-control" name="capacite_max" min="0" placeholder="Illimité si vide">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Image & Publication -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Image & Publication</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Image</label>
                                    <input type="file" class="form-control" name="image_url" accept="image/*">
                                    <small class="text-muted">Formats: JPG, PNG, GIF, WEBP (max 2MB)</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Page associée</label>
                                    <select class="form-select" name="id_page_associee">
                                        <option value="">Aucune</option>
                                        <?php foreach ($pages as $page): ?>
                                            <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Options</label>
                                    <div class="form-check form-switch mt-2">
                                        <input type="checkbox" class="form-check-input" name="est_public" id="create_est_public" value="1" checked>
                                        <label class="form-check-label" for="create_est_public">Rendre public</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer l'événement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#evenementsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[4, 'desc']], // Tri par date de début
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [1, 8] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Génération automatique du slug (optionnel)
    $('#create_titre').on('blur', function() {
        var slugField = $('#create_slug');
        if (slugField.val() === '') {
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
            slugField.val(slug);
        }
    });
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>