<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Administration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Utilisateurs</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_user">
                <i class="bx bx-plus"></i> Nouvel Utilisateur
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
                <h5 class="mb-0 text-primary"><i class="bx bx-users me-2"></i>Liste des Utilisateurs</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="usersTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="8%">Photo</th>
                            <th width="15%">Identité</th>
                            <th width="12%">Type</th>
                            <th width="15%">Contact</th>
                            <th width="10%">Rôle</th>
                            <th width="8%">KYC</th>
                            <th width="8%">Statut</th>
                            <th width="10%">Créé le</th>
                            <th width="9%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($users)): $i = 1; foreach ($users as $value): 
                        // Récupérer le nom du rôle
                        $role_name = 'Inconnu';
                        if (!empty($roles)) {
                            foreach ($roles as $role) {
                                if ($role['id'] == $value['role_id']) {
                                    $role_name = $role['nom'] ?? $role['name'] ?? 'Inconnu';
                                    break;
                                }
                            }
                        }
                        
                        // Badge type utilisateur
                        $type_badges = [
                            'admin' => '<span class="badge bg-danger"><i class="bx bx-shield"></i> Admin</span>',
                            'medecin' => '<span class="badge bg-info"><i class="bx bx-plus-medical"></i> Médecin</span>',
                            'patient' => '<span class="badge bg-success"><i class="bx bx-user"></i> Patient</span>',
                            'entreprise' => '<span class="badge bg-primary"><i class="bx bx-buildings"></i> Entreprise</span>',
                            'investisseur' => '<span class="badge bg-warning text-dark"><i class="bx bx-money"></i> Investisseur</span>',
                            'partenaire' => '<span class="badge bg-secondary"><i class="bx bx-handshake"></i> Partenaire</span>',
                            'broker' => '<span class="badge bg-dark"><i class="bx bx-transfer"></i> Broker</span>'
                        ];
                        
                        $type_badge = $type_badges[$value['type_utilisateur'] ?? 'entreprise'] ?? '<span class="badge bg-light text-dark">Inconnu</span>';
                        
                        // Photo
                        $photo_path = !empty($value['photo']) ? 'attachments/Users/'.$value['photo'] : 'attachments/Users/default-avatar.png';
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            
                            <td>
                                <img src="<?= base_url($photo_path) ?>" 
                                     class="rounded-circle border"
                                     style="width:45px; height:45px; object-fit:cover;"
                                     onerror="this.src='<?= base_url('attachments/Users/default-avatar.png') ?>'"
                                     alt="Photo">
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars(($value['nom'] ?? '').' '.($value['prenom'] ?? '')) ?></strong>
                                    <?php if (!empty($value['nom_entreprise'])): ?>
                                        <small class="text-muted"><?= htmlspecialchars($value['nom_entreprise']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td><?= $type_badge ?></td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span><i class="bx bx-envelope text-muted me-1"></i><?= htmlspecialchars($value['email'] ?? '-') ?></span>
                                    <?php if (!empty($value['telephone'])): ?>
                                        <small><i class="bx bx-phone text-muted me-1"></i><?= htmlspecialchars($value['telephone']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($role_name) ?></span>
                            </td>

                            <td class="text-center">
                                <?php if (in_array($value['type_utilisateur'], ['entreprise', 'investisseur', 'broker'])): ?>
                                    <?php if (!empty($value['est_verifie']) && $value['est_verifie'] == 1): ?>
                                        <span class="badge bg-success" title="KYC Vérifié"><i class="bx bx-check-circle"></i> Vérifié</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark" title="KYC Non vérifié"><i class="bx bx-time"></i> En attente</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['is_active']) && $value['is_active'] == 1): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactif</span>
                                    <?php endif; ?>
                                </a>
                            </td>

                            <td><?= !empty($value['created_at']) ? date('d/m/Y', strtotime($value['created_at'])) : '-' ?></td>

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
                                        <h5 class="modal-title"><i class="bx bx-user-circle me-2"></i>Détails de l'utilisateur</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 text-center border-end">
                                                <img src="<?= base_url($photo_path) ?>" 
                                                     class="rounded-circle border border-3 border-primary mb-3"
                                                     style="width:120px; height:120px; object-fit:cover;"
                                                     onerror="this.src='<?= base_url('attachments/Users/default-avatar.png') ?>'"
                                                     alt="Photo">
                                                <h5 class="mb-1"><?= htmlspecialchars(($value['nom'] ?? '').' '.($value['prenom'] ?? '')) ?></h5>
                                                <p class="text-muted mb-2"><?= htmlspecialchars($value['email'] ?? '') ?></p>
                                                <?= $type_badge ?>
                                                
                                                <?php if (!empty($value['nom_entreprise'])): ?>
                                                    <div class="mt-3 p-2 bg-light rounded">
                                                        <small class="text-muted d-block">Entreprise</small>
                                                        <strong><?= htmlspecialchars($value['nom_entreprise']) ?></strong>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row g-3">
                                                    <div class="col-6">
                                                        <label class="text-muted small">Téléphone</label>
                                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($value['telephone'] ?? 'Non renseigné') ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Genre</label>
                                                        <p class="mb-0 fw-bold">
                                                            <?php 
                                                            $genres = ['M' => 'Masculin', 'F' => 'Féminin', 'Autre' => 'Autre'];
                                                            echo $genres[$value['genre'] ?? ''] ?? 'Non renseigné';
                                                            ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Date de naissance</label>
                                                        <p class="mb-0 fw-bold"><?= !empty($value['date_naissance']) ? date('d/m/Y', strtotime($value['date_naissance'])) : 'Non renseignée' ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Rôle</label>
                                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($role_name) ?></p>
                                                    </div>
                                                    
                                                    <?php if (!empty($value['secteur_activite'])): ?>
                                                    <div class="col-12">
                                                        <label class="text-muted small">Secteur d'activité</label>
                                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($value['secteur_activite']) ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($value['numero_registre_commerce'])): ?>
                                                    <div class="col-6">
                                                        <label class="text-muted small">N° Registre Commerce</label>
                                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($value['numero_registre_commerce']) ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($value['interet_investissement'])): ?>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Intérêt investissement</label>
                                                        <p class="mb-0 fw-bold text-success"><?= number_format($value['interet_investissement'], 2, ',', ' ') ?> €</p>
                                                    </div>
                                                    <?php endif; ?>

                                                    <div class="col-6">
                                                        <label class="text-muted small">Statut compte</label>
                                                        <p class="mb-0">
                                                            <?php if (!empty($value['is_active']) && $value['is_active'] == 1): ?>
                                                                <span class="badge bg-success">Actif</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Inactif</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Email vérifié</label>
                                                        <p class="mb-0">
                                                            <?php if (!empty($value['email_verified_at'])): ?>
                                                                <span class="badge bg-success">Oui</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning text-dark">Non</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">2FA</label>
                                                        <p class="mb-0">
                                                            <?php if (!empty($value['two_factor_enabled']) && $value['two_factor_enabled'] == 1): ?>
                                                                <span class="badge bg-info">Activé</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Désactivé</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Dernière connexion</label>
                                                        <p class="mb-0 fw-bold"><?= !empty($value['last_login_at']) ? date('d/m/Y H:i', strtotime($value['last_login_at'])) : 'Jamais' ?></p>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="text-muted small">UUID</label>
                                                        <p class="mb-0 font-monospace small text-muted"><?= $value['uuid'] ?? '-' ?></p>
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
                        <div class="modal fade" id="update_<?= $value['id'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier l'utilisateur</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('users-update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <!-- Section Identité -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-user me-2"></i>Informations personnelles</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($value['nom'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="prenom" value="<?= htmlspecialchars($value['prenom'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($value['email'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Téléphone</label>
                                                            <input type="tel" class="form-control" name="telephone" value="<?= htmlspecialchars($value['telephone'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Date de naissance</label>
                                                            <input type="date" class="form-control" name="date_naissance" value="<?= $value['date_naissance'] ?? '' ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Genre</label>
                                                            <select class="form-select" name="genre">
                                                                <option value="">Sélectionner...</option>
                                                                <option value="M" <?= ($value['genre'] ?? '') == 'M' ? 'selected' : '' ?>>Masculin</option>
                                                                <option value="F" <?= ($value['genre'] ?? '') == 'F' ? 'selected' : '' ?>>Féminin</option>
                                                                <option value="Autre" <?= ($value['genre'] ?? '') == 'Autre' ? 'selected' : '' ?>>Autre</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Type & Rôle -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-cog me-2"></i>Type et Rôle</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Type d'utilisateur <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="type_utilisateur" required id="type_utilisateur_<?= $value['id'] ?>" onchange="toggleEntrepriseFields(<?= $value['id'] ?>)">
                                                                <option value="admin" <?= ($value['type_utilisateur'] ?? '') == 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                                                <option value="medecin" <?= ($value['type_utilisateur'] ?? '') == 'medecin' ? 'selected' : '' ?>>Médecin</option>
                                                                <option value="patient" <?= ($value['type_utilisateur'] ?? '') == 'patient' ? 'selected' : '' ?>>Patient</option>
                                                                <option value="entreprise" <?= ($value['type_utilisateur'] ?? '') == 'entreprise' ? 'selected' : '' ?>>Entreprise</option>
                                                                <option value="investisseur" <?= ($value['type_utilisateur'] ?? '') == 'investisseur' ? 'selected' : '' ?>>Investisseur</option>
                                                                <option value="partenaire" <?= ($value['type_utilisateur'] ?? '') == 'partenaire' ? 'selected' : '' ?>>Partenaire</option>
                                                                <option value="broker" <?= ($value['type_utilisateur'] ?? '') == 'broker' ? 'selected' : '' ?>>Broker</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Rôle <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="role_id" required>
                                                                <?php foreach ($roles as $role): ?>
                                                                    <option value="<?= $role['id'] ?>" <?= ($value['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($role['nom'] ?? $role['name'] ?? 'Rôle') ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Entreprise (conditionnelle) -->
                                            <div class="card border-0 bg-light mb-3 entreprise-fields-<?= $value['id'] ?>" 
                                                 style="<?= in_array($value['type_utilisateur'] ?? '', ['entreprise', 'investisseur', 'broker']) ? '' : 'display:none;' ?>">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-buildings me-2"></i>Informations professionnelles</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Nom de l'entreprise</label>
                                                            <input type="text" class="form-control" name="nom_entreprise" value="<?= htmlspecialchars($value['nom_entreprise'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Secteur d'activité</label>
                                                            <input type="text" class="form-control" name="secteur_activite" value="<?= htmlspecialchars($value['secteur_activite'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">N° Registre Commerce</label>
                                                            <input type="text" class="form-control" name="numero_registre_commerce" value="<?= htmlspecialchars($value['numero_registre_commerce'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Intérêt investissement (€)</label>
                                                            <input type="number" step="0.01" class="form-control" name="interet_investissement" value="<?= $value['interet_investissement'] ?? '' ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Sécurité -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-lock me-2"></i>Sécurité & Accès</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Nouveau mot de passe</label>
                                                            <input type="password" class="form-control" name="password" placeholder="Laisser vide pour ne pas changer" minlength="8">
                                                            <small class="text-muted">Minimum 8 caractères</small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Photo de profil</label>
                                                            <input type="file" class="form-control" name="photo" accept="image/*">
                                                            <?php if (!empty($value['photo']) && $value['photo'] != 'default-avatar.png'): ?>
                                                                <small class="text-muted">Actuelle: <?= $value['photo'] ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row g-3 mt-2">
                                                        <div class="col-md-3">
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="form-check-input" name="is_active" id="is_active_<?= $value['id'] ?>" value="1" <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="is_active_<?= $value['id'] ?>">Compte actif</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="form-check-input" name="email_verified" id="email_verified_<?= $value['id'] ?>" value="1" <?= !empty($value['email_verified_at']) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="email_verified_<?= $value['id'] ?>">Email vérifié</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="form-check-input" name="est_verifie" id="est_verifie_<?= $value['id'] ?>" value="1" <?= (!empty($value['est_verifie']) && $value['est_verifie'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="est_verifie_<?= $value['id'] ?>">KYC Vérifié</label>
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
                                        <p class="text-muted">Vous êtes sur le point de supprimer <strong><?= htmlspecialchars(($value['nom'] ?? '').' '.($value['prenom'] ?? '')) ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible (suppression logique).</p>
                                    </div>
                                    <form action="<?= base_url('users-delete') ?>" method="POST">
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

                        <!-- MODAL STATUS -->
                        <div class="modal fade" id="status_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? '<i class="bx bx-block me-2"></i>Désactiver' : '<i class="bx bx-check-circle me-2"></i>Activer' ?> le compte
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'désactiver' : 'activer' ?></strong> le compte de <strong><?= htmlspecialchars(($value['nom'] ?? '').' '.($value['prenom'] ?? '')) ?></strong> ?</p>
                                    </div>
                                    <form action="<?= base_url('Users/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="is_active" value="<?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'btn-warning' : 'btn-success' ?>">
                                                <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="bx bx-user-x text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun utilisateur trouvé</p>
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

<!-- MODAL CREATE USER -->
<div class="modal fade" id="create_user" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-user-plus me-2"></i>Nouvel Utilisateur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('users-create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <!-- Section Identité -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-user me-2"></i>Informations personnelles</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nom" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="prenom" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Mot de passe <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="password" required minlength="8">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Téléphone</label>
                                    <input type="tel" class="form-control" name="telephone">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Date de naissance</label>
                                    <input type="date" class="form-control" name="date_naissance">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Genre</label>
                                    <select class="form-select" name="genre">
                                        <option value="">Sélectionner...</option>
                                        <option value="M">Masculin</option>
                                        <option value="F">Féminin</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Type & Rôle -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-cog me-2"></i>Type et Rôle</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Type d'utilisateur <span class="text-danger">*</span></label>
                                    <select class="form-select" name="type_utilisateur" required id="create_type_utilisateur" onchange="toggleCreateEntrepriseFields()">
                                        <option value="entreprise" selected>Entreprise</option>
                                        <option value="admin">Administrateur</option>
                                        <option value="medecin">Médecin</option>
                                        <option value="patient">Patient</option>
                                        <option value="investisseur">Investisseur</option>
                                        <option value="partenaire">Partenaire</option>
                                        <option value="broker">Broker</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Rôle <span class="text-danger">*</span></label>
                                    <select class="form-select" name="role_id" required>
                                        <option value="">Sélectionner un rôle...</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['nom'] ?? $role['name'] ?? 'Rôle') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Entreprise (conditionnelle) -->
                    <div class="card border-0 bg-light mb-3" id="create-entreprise-fields">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-buildings me-2"></i>Informations professionnelles</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nom de l'entreprise</label>
                                    <input type="text" class="form-control" name="nom_entreprise">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Secteur d'activité</label>
                                    <input type="text" class="form-control" name="secteur_activite">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">N° Registre Commerce</label>
                                    <input type="text" class="form-control" name="numero_registre_commerce">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Intérêt investissement (€)</label>
                                    <input type="number" step="0.01" class="form-control" name="interet_investissement">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="est_verifie" id="create_est_verifie" value="1">
                                        <label class="form-check-label" for="create_est_verifie">KYC Vérifié</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Photo -->
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Photo de profil</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="file" class="form-control" name="photo" accept="image/*">
                                    <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WEBP (max 2MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer l'utilisateur
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#usersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [1, 9] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Toggle fields entreprise pour modification
function toggleEntrepriseFields(id) {
    const type = document.getElementById('type_utilisateur_' + id).value;
    const fields = document.querySelector('.entreprise-fields-' + id);
    const typesWithEntreprise = ['entreprise', 'investisseur', 'broker'];
    
    if (typesWithEntreprise.includes(type)) {
        fields.style.display = 'block';
    } else {
        fields.style.display = 'none';
    }
}

// Toggle fields entreprise pour création
function toggleCreateEntrepriseFields() {
    const type = document.getElementById('create_type_utilisateur').value;
    const fields = document.getElementById('create-entreprise-fields');
    const typesWithEntreprise = ['entreprise', 'investisseur', 'broker'];
    
    if (typesWithEntreprise.includes(type)) {
        fields.style.display = 'block';
    } else {
        fields.style.display = 'none';
    }
}

// Preview image avant upload
document.querySelectorAll('input[type="file"][name="photo"]').forEach(function(input) {
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validation taille
            if (file.size > 2 * 1024 * 1024) {
                alert('L\'image ne doit pas dépasser 2MB');
                this.value = '';
                return;
            }
            
            // Preview
            const reader = new FileReader();
            reader.onload = function(e) {
                // Créer ou mettre à jour la preview
                let preview = document.getElementById('photo-preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = 'photo-preview';
                    preview.className = 'rounded-circle mt-2 border';
                    preview.style.width = '100px';
                    preview.style.height = '100px';
                    preview.style.objectFit = 'cover';
                    this.parentNode.appendChild(preview);
                }
                preview.src = e.target.result;
            }.bind(this);
            reader.readAsDataURL(file);
        }
    });
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>