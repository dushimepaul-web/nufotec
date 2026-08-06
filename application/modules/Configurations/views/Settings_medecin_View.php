<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Paramètres</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Mon profil</li>
                </ol>
            </nav>
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
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php 
                        $photo = !empty($user['photo']) ? $user['photo'] : 'default-avatar.png';
                        ?>
                        <img src="<?= base_url('attachments/Users/' . $photo) ?>" 
                             class="rounded-circle border border-3 border-primary" 
                             width="120" height="120" 
                             style="object-fit: cover;"
                             onerror="this.src='<?= base_url('assets/frontend/img/default-avatar.jpg') ?>'"
                             alt="Photo de profil">
                    </div>
                    <h4 class="mb-1"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h4>
                    <p class="text-muted mb-2"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="badge bg-info">Médecin</span>
                    <hr>
                    <div class="text-start">
                        <p><i class="bx bx-phone me-2"></i> <?= htmlspecialchars($user['telephone'] ?? '-') ?></p>
                        <p><i class="bx bx-briefcase me-2"></i> <?= htmlspecialchars($medecin['specialite'] ?? '-') ?></p>
                        <p><i class="bx bx-id-card me-2"></i> Licence: <?= htmlspecialchars($medecin['numero_licence'] ?? '-') ?></p>
                        <p><i class="bx bx-time me-2"></i> Expérience: <?= htmlspecialchars($medecin['annees_experience'] ?? '0') ?> ans</p>
                        <p><i class="bx bx-money me-2"></i> Honoraires: <?= htmlspecialchars($medecin['honoraires_consultation'] ?? '0') ?> €</p>
                        <p><i class="bx bx-globe me-2"></i> Langues: <?= htmlspecialchars($medecin['langues_parlees'] ?? '-') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <!-- Formulaire d'édition des informations personnelles -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 text-primary"><i class="bx bx-user me-2"></i>Informations personnelles</h6>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('update_info') ?>" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Téléphone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Photo de profil</label>
                                <input type="file" class="form-control" name="photo" accept="image/*">
                                <small class="text-muted">Laissez vide pour conserver l'actuelle.</small>
                            </div>
                        </div>
                        <hr>
                        <h6 class="text-primary mb-3"><i class="bx bx-briefcase me-2"></i>Informations professionnelles</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Spécialité <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="specialite" value="<?= htmlspecialchars($medecin['specialite'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Numéro de licence <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="numero_licence" value="<?= htmlspecialchars($medecin['numero_licence'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Années d'expérience</label>
                                <input type="number" class="form-control" name="annees_experience" value="<?= htmlspecialchars($medecin['annees_experience'] ?? '0') ?>" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Langues parlées</label>
                                <input type="text" class="form-control" name="langues_parlees" value="<?= htmlspecialchars($medecin['langues_parlees'] ?? '') ?>" placeholder="ex: Français, Anglais">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Honoraires (€)</label>
                                <input type="number" step="0.01" class="form-control" name="honoraires_consultation" value="<?= htmlspecialchars($medecin['honoraires_consultation'] ?? '0') ?>" min="0">
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-2"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Formulaire de changement de mot de passe -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 text-primary"><i class="bx bx-lock me-2"></i>Changer le mot de passe</h6>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('change-password') ?>" method="POST">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Mot de passe actuel <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nouveau mot de passe <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="new_password" required minlength="8">
                                <small class="text-muted">Minimum 8 caractères</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Confirmer <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="bx bx-key me-2"></i>Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>


<script>
$(document).ready(function() {
    // Auto-hide alerts
    setTimeout(() => $('.alert').fadeOut('slow'), 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>