<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Admin</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="javascript:;">
                                <i class="bx bx-home-alt"></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Modes de Paiement</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a class="btn btn-outline-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#addModePayement">
                    <i class="bx bx-plus"></i> Nouveau Mode
                </a>
            </div>
        </div>
        <!--end breadcrumb-->

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

        <hr/>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Étape paiement</th>
                                <th>N° Compte</th>
                                <th>Nom compte</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($mode_payements as $value): ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= htmlspecialchars($value['description']); ?></td>
                                    <td><?= !empty($value['etapepaiement']) ? htmlspecialchars($value['etapepaiement']) : '<span class="text-muted">-</span>' ?></td>
                                    <td><?= !empty($value['numero_compte']) ? htmlspecialchars($value['numero_compte']) : '<span class="text-muted">-</span>' ?></td>
                                    <td><?= !empty($value['nom_compte']) ? htmlspecialchars($value['nom_compte']) : '<span class="text-muted">-</span>' ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($value['est_actif']) && $value['est_actif'] == 1): ?>
                                            <span class="badge bg-success">Actif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-bs-toggle="dropdown">
                                                Options
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item text-info" href="#" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_mode_payement']; ?>">
                                                    <i class="bx bx-edit me-2"></i>Modifier
                                                </a>
                                                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_mode_payement']; ?>">
                                                    <i class="bx bx-trash me-2"></i>Supprimer
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Modifier -->
                                <div class="modal fade" id="update_<?= $value['id_mode_payement']; ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Modifier le mode de paiement</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?= base_url('Mode_payement/UpdateModePayement') ?>" method="POST">
                                                <input type="hidden" name="id_mode_payement" value="<?= $value['id_mode_payement']; ?>">
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="description" value="<?= htmlspecialchars($value['description']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Étape de paiement</label>
                                                        <input type="text" class="form-control" name="etapepaiement" value="<?= htmlspecialchars($value['etapepaiement'] ?? ''); ?>" placeholder="Ex: initial, final, confirmation...">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Numéro de compte</label>
                                                        <input type="text" class="form-control" name="numero_compte" value="<?= htmlspecialchars($value['numero_compte'] ?? ''); ?>" placeholder="Ex: 1234567890">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Nom du compte</label>
                                                        <input type="text" class="form-control" name="nom_compte" value="<?= htmlspecialchars($value['nom_compte'] ?? ''); ?>" placeholder="Ex: AGF SA">
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" name="est_actif" id="est_actif_<?= $value['id_mode_payement']; ?>" value="1" <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="est_actif_<?= $value['id_mode_payement']; ?>">Actif</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                                                    <button type="submit" class="btn btn-info">Modifier</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Supprimer -->
                                <div class="modal fade" id="delete_<?= $value['id_mode_payement']; ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirmer la suppression</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?= base_url('Mode_payement/DeleteModePayement') ?>" method="POST">
                                                <input type="hidden" name="id_mode_payement" value="<?= $value['id_mode_payement']; ?>">
                                                <div class="modal-body">
                                                    <p>Voulez-vous vraiment supprimer le mode de paiement "<?= htmlspecialchars($value['description']); ?>" ?</p>
                                                    <div class="alert alert-warning mt-2">
                                                        <small>
                                                            <i class="bx bx-info-circle"></i>
                                                            Attention : Ce mode de paiement pourrait être utilisé dans des inscriptions existantes.
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-info">Supprimer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Étape paiement</th>
                                <th>N° Compte</th>
                                <th>Nom compte</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Ajouter Mode de Paiement -->
        <div class="modal fade" id="addModePayement" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nouveau mode de paiement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="<?= base_url('Mode_payement/CreateModePayement') ?>" method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="description" placeholder="Ex: Carte bancaire, Mobile Money, Virement..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Étape de paiement</label>
                                <input type="text" class="form-control" name="etapepaiement" placeholder="Ex: initial, final, confirmation...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Numéro de compte</label>
                                <input type="text" class="form-control" name="numero_compte" placeholder="Ex: 1234567890">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nom du compte</label>
                                <input type="text" class="form-control" name="nom_compte" placeholder="Ex: AGF SA">
                            </div>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="est_actif" id="est_actif_add" value="1" checked>
                                <label class="form-check-label" for="est_actif_add">Actif</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-info">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
<!--end page wrapper -->

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>