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
                    <li class="breadcrumb-item active" aria-current="page">Documents</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#upload_document">
                <i class="bx bx-upload"></i> Importer un document
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
            <h5 class="mb-0 text-primary"><i class="bx bx-file me-2"></i>Liste des documents</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="documentsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Propriétaire</th>
                            <th>Nom du fichier</th>
                            <th>Type</th>
                            <th>Consultation</th>
                            <th>Date d'ajout</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($documents)): $i = 1; foreach ($documents as $d): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($d['user_nom'] . ' ' . $d['user_prenom']) ?></td>
                            <td>
                                <a href="<?= base_url('Documents/Download/'.$d['id']) ?>" target="_blank" title="Télécharger">
                                    <i class="bx bx-download me-1"></i><?= htmlspecialchars($d['original_name'] ?: $d['filename']) ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                $badges = [
                                    'consultation' => '<span class="badge bg-info">Consultation</span>',
                                    'analyse'      => '<span class="badge bg-warning text-dark">Analyse</span>',
                                    'prescription' => '<span class="badge bg-success">Prescription</span>',
                                    'autre'        => '<span class="badge bg-secondary">Autre</span>'
                                ];
                                echo $badges[$d['type']] ?? '<span class="badge bg-light">Inconnu</span>';
                                ?>
                            </td>
                            <td><?= $d['numero_consultation'] ?? '<span class="text-muted">—</span>' ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('Documents/Download/'.$d['id']) ?>" target="_blank">
                                                <i class="bx bx-download text-info me-2"></i>Télécharger
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('Documents/send_email/'.$d['id']) ?>" onclick="return confirm('Envoyer ce document par email au patient ?');">
                                                <i class="bx bx-envelope text-primary me-2"></i>Envoyer par email
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#edit_<?= $d['id'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $d['id'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL EDIT -->
                        <div class="modal fade" id="edit_<?= $d['id'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le document</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('Documents/Update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Type de document</label>
                                                <select class="form-select" name="type" required>
                                                    <option value="consultation" <?= $d['type']=='consultation'?'selected':'' ?>>Consultation</option>
                                                    <option value="analyse" <?= $d['type']=='analyse'?'selected':'' ?>>Analyse</option>
                                                    <option value="prescription" <?= $d['type']=='prescription'?'selected':'' ?>>Prescription</option>
                                                    <option value="autre" <?= $d['type']=='autre'?'selected':'' ?>>Autre</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Lier à une consultation (optionnel)</label>
                                                <select class="form-select" name="consultation_id">
                                                    <option value="">-- Aucune --</option>
                                                    <?php foreach ($consultations as $c): ?>
                                                        <option value="<?= $c['id'] ?>" <?= ($d['consultation_id'] == $c['id']) ? 'selected' : '' ?>>
                                                            <?= $c['numero_consultation'] ?> (<?= date('d/m/Y', strtotime($c['date_souhaitee'])) ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
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

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $d['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmation</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <p class="mt-3">Voulez-vous vraiment supprimer ce document ?</p>
                                        <p class="text-muted"><?= htmlspecialchars($d['original_name'] ?: $d['filename']) ?></p>
                                    </div>
                                    <form action="<?= base_url('Documents/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">Supprimer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-center py-4">Aucun document trouvé</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

<!-- MODAL UPLOAD -->
<div class="modal fade" id="upload_document" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-upload me-2"></i>Importer un document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Documents/Upload') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Propriétaire <span class="text-danger">*</span></label>
                        <select class="form-select" name="user_id" required>
                            <option value="">-- Sélectionner un utilisateur --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nom'] . ' ' . $u['prenom'] . ' (' . $u['email'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Type de document <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" required>
                            <option value="consultation">Consultation</option>
                            <option value="analyse">Analyse</option>
                            <option value="prescription">Prescription</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lier à une consultation (optionnel)</label>
                        <select class="form-select" name="consultation_id">
                            <option value="">-- Aucune --</option>
                            <?php foreach ($consultations as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['numero_consultation'] ?> (<?= date('d/m/Y', strtotime($c['date_souhaitee'])) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fichier <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="document_file" required accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.txt">
                        <small class="text-muted">Formats acceptés: PDF, images, Word, Excel, TXT</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Importer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#documentsTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' },
        order: [[5, 'desc']],
        pageLength: 25
    });

    // Auto-hide alerts
    setTimeout(() => $('.alert').fadeOut('slow'), 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>