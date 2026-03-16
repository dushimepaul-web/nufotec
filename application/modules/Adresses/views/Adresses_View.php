<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Gestion</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('Users') ?>">Utilisateurs</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Adresses</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-success me-2" href="<?= base_url('Adresses/Export?' . http_build_query($filters)) ?>" target="_blank">
                <i class="bx bx-export"></i> Exporter CSV
            </a>
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_adresse">
                <i class="bx bx-plus"></i> Nouvelle Adresse
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

    <!-- FILTRES AVANCÉS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bx bx-filter-alt me-2"></i>Filtres de recherche</h6>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                <i class="bx bx-chevron-down"></i> Afficher/Masquer
            </button>
        </div>
        <div class="collapse show" id="filtersCollapse">
            <div class="card-body">
                <form method="GET" action="<?= base_url('Adresses') ?>" id="filterForm">
                    <div class="row g-3">
                        <!-- Recherche texte -->
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Recherche</label>
                            <input type="text" class="form-control" name="search" value="<?= $filters['search'] ?? '' ?>" placeholder="Nom, adresse, ville, email...">
                        </div>
                        
                        <!-- Utilisateur -->
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Utilisateur</label>
                            <select class="form-select" name="user_id">
                                <option value="">Tous</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ($filters['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '') . ' (' . ($u['email'] ?? '') . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Type d'adresse -->
                        <div class="col-md-2">
                            <label class="form-label small text-muted">Type</label>
                            <select class="form-select" name="type">
                                <option value="">Tous</option>
                                <option value="livraison" <?= ($filters['type'] ?? '') == 'livraison' ? 'selected' : '' ?>>Livraison</option>
                                <option value="facturation" <?= ($filters['type'] ?? '') == 'facturation' ? 'selected' : '' ?>>Facturation</option>
                                <option value="tous" <?= ($filters['type'] ?? '') == 'tous' ? 'selected' : '' ?>>Les deux</option>
                            </select>
                        </div>
                        
                        <!-- Pays -->
                        <div class="col-md-2">
                            <label class="form-label small text-muted">Pays</label>
                            <select class="form-select" name="pays_id">
                                <option value="">Tous</option>
                                <?php foreach ($pays as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= ($filters['pays_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['pays']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Adresse principale -->
                        <div class="col-md-2">
                            <label class="form-label small text-muted">Principale</label>
                            <select class="form-select" name="est_principale">
                                <option value="">Toutes</option>
                                <option value="1" <?= ($filters['est_principale'] ?? '') === '1' ? 'selected' : '' ?>>Oui</option>
                                <option value="0" <?= ($filters['est_principale'] ?? '') === '0' ? 'selected' : '' ?>>Non</option>
                            </select>
                        </div>
                        
                        <!-- Ville -->
                        <div class="col-md-2">
                            <label class="form-label small text-muted">Ville</label>
                            <input type="text" class="form-control" name="ville" value="<?= $filters['ville'] ?? '' ?>" placeholder="Ville...">
                        </div>
                        
                        <!-- Code postal -->
                        <div class="col-md-2">
                            <label class="form-label small text-muted">Code postal</label>
                            <input type="text" class="form-control" name="code_postal" value="<?= $filters['code_postal'] ?? '' ?>" placeholder="CP...">
                        </div>
                        
                        <!-- Boutons -->
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 me-2">
                                <i class="bx bx-search"></i> Filtrer
                            </button>
                            <a href="<?= base_url('Adresses') ?>" class="btn btn-outline-secondary" title="Réinitialiser">
                                <i class="bx bx-reset"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TABLEAU DES ADRESSES -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-primary"><i class="bx bx-map-pin me-2"></i>Liste des Adresses</h5>
                <span class="badge bg-secondary"><?= count($adresses) ?> adresse(s)</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="adressesTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Utilisateur</th>
                            <th width="20%">Destinataire</th>
                            <th width="25%">Adresse</th>
                            <th width="10%">Contact</th>
                            <th width="8%">Type</th>
                            <th width="7%">Principal</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($adresses)): $i = 1; foreach ($adresses as $value): 
                        // Badge type
                        $type_badges = [
                            'livraison' => '<span class="badge bg-info"><i class="bx bx-package"></i> Livraison</span>',
                            'facturation' => '<span class="badge bg-warning text-dark"><i class="bx bx-file"></i> Facturation</span>',
                            'tous' => '<span class="badge bg-success"><i class="bx bx-check-double"></i> Les deux</span>'
                        ];
                        $type_badge = $type_badges[$value['type'] ?? 'tous'];
                        
                        // Formatage de l'adresse complète
                        $adresse_complete = $value['adresse_ligne1'];
                        if (!empty($value['adresse_ligne2'])) {
                            $adresse_complete .= '<br>' . $value['adresse_ligne2'];
                        }
                        
                        // Utilisateur
                        $user_nom = htmlspecialchars(($value['user_prenom'] ?? '') . ' ' . ($value['user_nom'] ?? ''));
                        $user_email = htmlspecialchars($value['user_email'] ?? '');
                        
                        // Code pays (ISO 2 lettres)
                        $pays_code = !empty($value['pays_code']) ? strtoupper($value['pays_code']) : '';
                    ?>
                        <tr class="<?= !empty($value['est_principale']) ? 'table-primary bg-opacity-25' : '' ?>">
                            <td><?= $i++ ?></td>
                            
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= $user_nom ?></strong>
                                    <small class="text-muted"><i class="bx bx-envelope me-1"></i><?= $user_email ?></small>
                                    <?php if (!empty($value['entreprise'])): ?>
                                        <small class="text-info"><i class="bx bx-buildings me-1"></i><?= htmlspecialchars($value['entreprise']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong><?= htmlspecialchars($value['nom_complet']) ?></strong>
                                    <small class="text-muted"><i class="bx bx-phone me-1"></i><?= htmlspecialchars($value['telephone']) ?></small>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span><?= nl2br(htmlspecialchars($adresse_complete)) ?></span>
                                    <span class="fw-bold text-primary">
                                        <?= htmlspecialchars($value['code_postal']) ?> <?= htmlspecialchars($value['ville']) ?>
                                    </span>
                                    <small class="text-muted">
                                        <i class="bx bx-globe me-1"></i><?= htmlspecialchars($value['pays_nom'] ?? 'N/A') ?> 
                                        <?php if (!empty($pays_code)): ?>
                                            <span class="badge bg-light text-dark border">(<?= $pays_code ?>)</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bx bx-phone me-1"></i><?= htmlspecialchars($value['telephone']) ?>
                                </span>
                            </td>

                            <td><?= $type_badge ?></td>

                            <td class="text-center">
                                <?php if (!empty($value['est_principale'])): ?>
                                    <span class="badge bg-warning text-dark" title="Adresse principale">
                                        <i class="bx bx-star"></i> Oui
                                    </span>
                                <?php else: ?>
                                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#setprincipale_<?= $value['id'] ?>" class="text-decoration-none" title="Définir comme principale">
                                        <span class="badge bg-secondary">
                                            <i class="bx bx-star"></i> Non
                                        </span>
                                    </a>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <?php if (empty($value['est_principale'])): ?>
                                        <li>
                                            <a class="dropdown-item text-success" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#setprincipale_<?= $value['id'] ?>">
                                                <i class="bx bx-star me-2"></i>Définir principale
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-map-pin me-2"></i>Détails de l'adresse</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-6 border-end">
                                                <h6 class="text-primary mb-3"><i class="bx bx-user me-2"></i>Informations destinataire</h6>
                                                <div class="mb-3">
                                                    <label class="text-muted small">Nom complet</label>
                                                    <p class="mb-0 fw-bold fs-5"><?= htmlspecialchars($value['nom_complet']) ?></p>
                                                </div>
                                                <?php if (!empty($value['entreprise'])): ?>
                                                <div class="mb-3">
                                                    <label class="text-muted small">Entreprise</label>
                                                    <p class="mb-0 fw-bold text-info"><?= htmlspecialchars($value['entreprise']) ?></p>
                                                </div>
                                                <?php endif; ?>
                                                <div class="mb-3">
                                                    <label class="text-muted small">Téléphone</label>
                                                    <p class="mb-0 fw-bold"><i class="bx bx-phone me-1"></i><?= htmlspecialchars($value['telephone']) ?></p>
                                                </div>
                                                
                                                <div class="mt-4">
                                                    <h6 class="text-primary mb-3"><i class="bx bx-cog me-2"></i>Paramètres</h6>
                                                    <div class="d-flex gap-2 mb-2">
                                                        <span class="text-muted small">Type:</span>
                                                        <?= $type_badge ?>
                                                    </div>
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <span class="text-muted small">Adresse principale:</span>
                                                        <?php if (!empty($value['est_principale'])): ?>
                                                            <span class="badge bg-warning text-dark"><i class="bx bx-star"></i> Oui</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Non</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <h6 class="text-primary mb-3"><i class="bx bx-map me-2"></i>Adresse complète</h6>
                                                <div class="card bg-light border-0">
                                                    <div class="card-body">
                                                        <p class="mb-2 fw-bold"><?= htmlspecialchars($value['adresse_ligne1']) ?></p>
                                                        <?php if (!empty($value['adresse_ligne2'])): ?>
                                                            <p class="mb-2 text-muted"><?= htmlspecialchars($value['adresse_ligne2']) ?></p>
                                                        <?php endif; ?>
                                                        <p class="mb-2">
                                                            <span class="badge bg-primary fs-6"><?= htmlspecialchars($value['code_postal']) ?></span>
                                                            <span class="fw-bold ms-2"><?= htmlspecialchars($value['ville']) ?></span>
                                                        </p>
                                                        <hr class="my-2">
                                                        <p class="mb-0">
                                                            <i class="bx bx-globe me-2"></i>
                                                            <strong><?= htmlspecialchars($value['pays_nom'] ?? 'N/A') ?></strong>
                                                            <?php if (!empty($pays_code)): ?>
                                                                <span class="text-muted">(<?= $pays_code ?>)</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-3">
                                                    <h6 class="text-primary mb-2"><i class="bx bx-user-circle me-2"></i>Propriétaire</h6>
                                                    <div class="d-flex align-items-center p-2 bg-light rounded">
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:40px; height:40px">
                                                            <i class="bx bx-user"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-0 fw-bold"><?= $user_nom ?></p>
                                                            <small class="text-muted"><?= $user_email ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between text-muted small border-top pt-2">
                                                    <span><i class="bx bx-calendar me-1"></i>Créée le: <?= date('d/m/Y H:i', strtotime($value['created_at'])) ?></span>
                                                    <span><i class="bx bx-refresh me-1"></i>Modifiée le: <?= date('d/m/Y H:i', strtotime($value['updated_at'])) ?></span>
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
                        <div class="modal fade" id="update_<?= $value['id'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier l'adresse</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Adresses/Update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <!-- Section Utilisateur -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-user me-2"></i>Propriétaire</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-bold">Utilisateur <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="user_id" required>
                                                                <option value="">Sélectionner un utilisateur...</option>
                                                                <?php foreach ($users as $u): ?>
                                                                    <option value="<?= $u['id'] ?>" <?= ($value['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '') . ' - ' . ($u['email'] ?? '')) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Destinataire -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-user-pin me-2"></i>Destinataire</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="nom_complet" value="<?= htmlspecialchars($value['nom_complet']) ?>" required placeholder="Nom et prénom du destinataire">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Entreprise (optionnel)</label>
                                                            <input type="text" class="form-control" name="entreprise" value="<?= htmlspecialchars($value['entreprise'] ?? '') ?>" placeholder="Nom de l'entreprise si applicable">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Téléphone <span class="text-danger">*</span></label>
                                                            <input type="tel" class="form-control" name="telephone" value="<?= htmlspecialchars($value['telephone']) ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Adresse -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-map me-2"></i>Adresse</h6>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Adresse ligne 1 <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="adresse_ligne1" value="<?= htmlspecialchars($value['adresse_ligne1']) ?>" required placeholder="Numéro et nom de rue">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Adresse ligne 2 (optionnel)</label>
                                                            <input type="text" class="form-control" name="adresse_ligne2" value="<?= htmlspecialchars($value['adresse_ligne2'] ?? '') ?>" placeholder="Appartement, étage, bâtiment...">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Code postal <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="code_postal" value="<?= htmlspecialchars($value['code_postal']) ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Ville <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="ville" value="<?= htmlspecialchars($value['ville']) ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Pays <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="pays_id" required>
                                                                <option value="">Sélectionner...</option>
                                                                <?php foreach ($pays as $p): ?>
                                                                    <option value="<?= $p['id'] ?>" <?= ($value['pays_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($p['pays']) ?> (<?= strtoupper($p['ISO_3166_1_2_Letter_Code'] ?? '') ?>)
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Type -->
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-cog me-2"></i>Configuration</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Type d'adresse <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="type" required>
                                                                <option value="livraison" <?= ($value['type'] ?? '') == 'livraison' ? 'selected' : '' ?>>Livraison uniquement</option>
                                                                <option value="facturation" <?= ($value['type'] ?? '') == 'facturation' ? 'selected' : '' ?>>Facturation uniquement</option>
                                                                <option value="tous" <?= ($value['type'] ?? '') == 'tous' ? 'selected' : '' ?>>Livraison & Facturation</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch mt-4">
                                                                <input type="checkbox" class="form-check-input" name="est_principale" id="est_principale_<?= $value['id'] ?>" value="1" <?= (!empty($value['est_principale']) && $value['est_principale'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label fw-bold" for="est_principale_<?= $value['id'] ?>">
                                                                    Définir comme adresse principale
                                                                </label>
                                                                <small class="text-muted d-block">Une seule adresse principale par utilisateur</small>
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
                        <div class="modal fade" id="delete_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer l'adresse de <strong><?= htmlspecialchars($value['nom_complet']) ?></strong>.</p>
                                        <div class="alert alert-warning text-start">
                                            <i class="bx bx-map-pin me-2"></i>
                                            <strong><?= htmlspecialchars($value['adresse_ligne1']) ?></strong><br>
                                            <?= htmlspecialchars($value['code_postal']) ?> <?= htmlspecialchars($value['ville']) ?>
                                        </div>
                                        <?php if (!empty($value['est_principale'])): ?>
                                            <div class="alert alert-danger">
                                                <i class="bx bx-error-circle"></i> C'est l'adresse principale de l'utilisateur !
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <form action="<?= base_url('Adresses/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
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

                        <!-- MODAL SET PRINCIPALE -->
                        <div class="modal fade" id="setprincipale_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-star me-2"></i>Définir comme adresse principale</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous définir cette adresse comme <strong>principale</strong> pour l'utilisateur <strong><?= $user_nom ?></strong> ?</p>
                                        
                                        <div class="card bg-light border-0 mb-3">
                                            <div class="card-body">
                                                <p class="mb-1 fw-bold"><?= htmlspecialchars($value['nom_complet']) ?></p>
                                                <p class="mb-1 small"><?= htmlspecialchars($value['adresse_ligne1']) ?></p>
                                                <p class="mb-0 small text-primary"><?= htmlspecialchars($value['code_postal']) ?> <?= htmlspecialchars($value['ville']) ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle"></i> L'adresse principale actuelle sera remplacée par celle-ci.
                                        </div>
                                    </div>
                                    <form action="<?= base_url('Adresses/SetPrincipale') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bx bx-star me-2"></i>Définir comme principale
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bx bx-map-pin text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucune adresse trouvée</p>
                                <a href="<?= base_url('Adresses') ?>" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="bx bx-reset"></i> Réinitialiser les filtres
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- MODAL CREATE ADRESSE -->
<div class="modal fade" id="create_adresse" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle Adresse</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Adresses/Create') ?>" method="POST">
                <div class="modal-body p-4">
                    <!-- Section Utilisateur -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-user me-2"></i>Propriétaire</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Utilisateur <span class="text-danger">*</span></label>
                                    <select class="form-select" name="user_id" required id="create_user_id">
                                        <option value="">Sélectionner un utilisateur...</option>
                                        <?php foreach ($users as $u): ?>
                                            <option value="<?= $u['id'] ?>">
                                                <?= htmlspecialchars(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '') . ' - ' . ($u['email'] ?? '')) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Destinataire -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-user-pin me-2"></i>Destinataire</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nom_complet" required placeholder="Nom et prénom du destinataire">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Entreprise (optionnel)</label>
                                    <input type="text" class="form-control" name="entreprise" placeholder="Nom de l'entreprise si applicable">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Téléphone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" name="telephone" required placeholder="+33 6 12 34 56 78">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Adresse -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-map me-2"></i>Adresse</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Adresse ligne 1 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="adresse_ligne1" required placeholder="Numéro et nom de rue">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Adresse ligne 2 (optionnel)</label>
                                    <input type="text" class="form-control" name="adresse_ligne2" placeholder="Appartement, étage, bâtiment...">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Code postal <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="code_postal" required placeholder="75001">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Ville <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="ville" required placeholder="Paris">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Pays <span class="text-danger">*</span></label>
                                    <select class="form-select" name="pays_id" required>
                                        <option value="">Sélectionner...</option>
                                        <?php foreach ($pays as $p): ?>
                                            <option value="<?= $p['id'] ?>" <?= ($p['ISO_3166_1_2_Letter_Code'] ?? '') == 'FR' ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['pays']) ?> (<?= strtoupper($p['ISO_3166_1_2_Letter_Code'] ?? '') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Type -->
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-cog me-2"></i>Configuration</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Type d'adresse <span class="text-danger">*</span></label>
                                    <select class="form-select" name="type" required>
                                        <option value="livraison">Livraison uniquement</option>
                                        <option value="facturation">Facturation uniquement</option>
                                        <option value="tous" selected>Livraison & Facturation</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input type="checkbox" class="form-check-input" name="est_principale" id="create_est_principale" value="1">
                                        <label class="form-check-label fw-bold" for="create_est_principale">
                                            Définir comme adresse principale
                                        </label>
                                        <small class="text-muted d-block">Remplacera l'adresse principale actuelle si existe</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer l'adresse
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#adressesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'desc']],
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
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>