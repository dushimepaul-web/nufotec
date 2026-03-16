<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Médecin</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Mon Calendrier</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bx bx-plus"></i> Ajouter un créneau
            </a>
        </div>
    </div>

    <!-- Flash messages -->
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
            <h5 class="mb-0 text-primary"><i class="bx bx-calendar me-2"></i>Mon planning hebdomadaire</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Jour</th>
                            <th>Créneaux</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($jours as $jour): ?>
                        <tr>
                            <td class="fw-bold text-capitalize"><?= $jour ?></td>
                            <td>
                                <?php if (empty($planning[$jour])): ?>
                                    <span class="text-muted">Aucun créneau</span>
                                <?php else: ?>
                                    <?php foreach ($planning[$jour] as $creneau): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                            <span>
                                                <i class="bx bx-time me-1"></i>
                                                <?= substr($creneau['heure_debut'], 0, 5) ?> - <?= substr($creneau['heure_fin'], 0, 5) ?>
                                                <?php if (!$creneau['est_actif']): ?>
                                                    <span class="badge bg-secondary ms-2">Inactif</span>
                                                <?php endif; ?>
                                            </span>
                                            <div>
                                                <a href="javascript:;" class="text-warning me-2" 
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#editModal_<?= $creneau['id'] ?>">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <a href="<?= base_url('Medecin_Calendrier/delete/'.$creneau['id']) ?>" 
                                                   class="text-danger" 
                                                   onclick="return confirm('Supprimer ce créneau ?')">
                                                    <i class="bx bx-trash"></i>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Modal Édition pour ce créneau -->
                                        <div class="modal fade" id="editModal_<?= $creneau['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-warning text-dark">
                                                        <h5 class="modal-title">Modifier le créneau</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="<?= base_url('calendriersave') ?>" method="POST">
                                                        <input type="hidden" name="id" value="<?= $creneau['id'] ?>">
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Jour</label>
                                                                <select class="form-select" name="jour" required>
                                                                    <?php foreach ($jours as $j): ?>
                                                                        <option value="<?= $j ?>" <?= ($j == $creneau['jour_semaine']) ? 'selected' : '' ?>>
                                                                            <?= ucfirst($j) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col">
                                                                    <label class="form-label fw-bold">Heure début</label>
                                                                    <input type="time" class="form-control" name="heure_debut" value="<?= substr($creneau['heure_debut'], 0, 5) ?>" required>
                                                                </div>
                                                                <div class="col">
                                                                    <label class="form-label fw-bold">Heure fin</label>
                                                                    <input type="time" class="form-control" name="heure_fin" value="<?= substr($creneau['heure_fin'], 0, 5) ?>" required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" class="btn btn-warning">Enregistrer</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <!-- Lien pour ajouter sur ce jour directement -->
                                <a href="javascript:;" class="btn btn-sm btn-outline-primary" 
                                   data-bs-toggle="modal" 
                                   data-bs-target="#addModal"
                                   onclick="document.getElementById('add_jour').value='<?= $jour ?>'">
                                    <i class="bx bx-plus"></i> Ajouter
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

<!-- Modal Ajout -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Ajouter un créneau</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('calendriersave') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jour</label>
                        <select class="form-select" name="jour" id="add_jour" required>
                            <?php foreach ($jours as $j): ?>
                                <option value="<?= $j ?>"><?= ucfirst($j) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label fw-bold">Heure début</label>
                            <input type="time" class="form-control" name="heure_debut" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-bold">Heure fin</label>
                            <input type="time" class="form-control" name="heure_fin" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Ajouter</button>
                </div>
            </form>
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