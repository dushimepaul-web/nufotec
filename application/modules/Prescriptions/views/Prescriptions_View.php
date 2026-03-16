<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Pharmacie</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Prescriptions</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_prescription">
                <i class="bx bx-plus"></i> Nouvelle Prescription
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
            <h5 class="mb-0 text-primary"><i class="bx bx-capsule me-2"></i>Liste des Prescriptions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="prescriptionsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Médecin</th>
                            <th>Médicament</th>
                            <th>Dosage</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($prescriptions)): $i = 1; foreach ($prescriptions as $p): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($p['patient_nom'] . ' ' . $p['patient_prenom']) ?></td>
                            <td><?= htmlspecialchars($p['medecin_nom'] . ' ' . $p['medecin_prenom']) ?></td>
                            <td><?= htmlspecialchars($p['medicament']) ?></td>
                            <td><?= htmlspecialchars($p['dosage'] ?? '-') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($p['date_prescription'])) ?></td>
                            <td>
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $p['id'] ?>" class="text-decoration-none">
                                    <?php if ($p['is_active'] == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#documentsModal_<?= $p['id'] ?>">
                                                <i class="bx bx-file text-primary me-2"></i>Documents
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $p['id'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $p['id'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $p['id'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW -->
                        <div class="modal fade" id="view_<?= $p['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title"><i class="bx bx-detail me-2"></i>Détails de la prescription</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Patient :</strong> <?= htmlspecialchars($p['patient_nom'].' '.$p['patient_prenom']) ?></p>
                                        <p><strong>Médecin :</strong> <?= htmlspecialchars($p['medecin_nom'].' '.$p['medecin_prenom']) ?></p>
                                        <p><strong>Médicament :</strong> <?= htmlspecialchars($p['medicament']) ?></p>
                                        <p><strong>Dosage :</strong> <?= htmlspecialchars($p['dosage'] ?? '-') ?></p>
                                        <p><strong>Instructions :</strong><br><?= nl2br(htmlspecialchars($p['instructions'] ?? '-')) ?></p>
                                        <p><strong>Prescrit le :</strong> <?= date('d/m/Y H:i', strtotime($p['date_prescription'])) ?></p>
                                        <p><strong>Statut :</strong> <?= $p['is_active'] ? 'Active' : 'Inactive' ?></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $p['id'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la prescription</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('Prescriptions/Update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Médicament <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="medicament" value="<?= htmlspecialchars($p['medicament']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Dosage</label>
                                                <input type="text" class="form-control" name="dosage" value="<?= htmlspecialchars($p['dosage'] ?? '') ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Instructions</label>
                                                <textarea class="form-control" name="instructions" rows="3"><?= htmlspecialchars($p['instructions'] ?? '') ?></textarea>
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

                        <!-- MODAL DOCUMENTS -->
                        <div class="modal fade" id="documentsModal_<?= $p['id'] ?>" tabindex="-1" data-bs-backdrop="static">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-file me-2"></i>Documents de la prescription</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Liste des documents -->
                                        <div id="docList_<?= $p['id'] ?>" class="mb-3">
                                            <div class="text-center py-3">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Chargement...</span>
                                                </div>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Formulaire d'upload -->
                                        <h6 class="mb-3">Ajouter un nouveau document</h6>
                                        <form action="<?= base_url('Prescriptions/upload_document') ?>" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="prescription_id" value="<?= $p['id'] ?>">
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <select class="form-select" name="type" required>
                                                        <option value="prescription">Prescription</option>
                                                        <option value="analyse">Analyse</option>
                                                        <option value="consultation">Consultation</option>
                                                        <option value="autre">Autre</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="file" class="form-control" name="document_file" required accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.txt">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="submit" class="btn btn-success w-100">Importer</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Script pour charger les documents (JavaScript pur) -->
                        <script>
                        (function() {
                            var modal = document.getElementById('documentsModal_<?= $p['id'] ?>');
                            if (!modal) return;

                            modal.addEventListener('show.bs.modal', function() {
                                var listDiv = document.getElementById('docList_<?= $p['id'] ?>');
                                if (!listDiv) return;

                                // Afficher le spinner
                                listDiv.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div></div>';

                                console.log('Chargement des documents pour la prescription <?= $p['id'] ?>...');

                                fetch('<?= base_url("Prescriptions/documents_list/".$p['id']) ?>')
                                    .then(function(response) {
                                        if (!response.ok) {
                                            throw new Error('Erreur HTTP ' + response.status);
                                        }
                                        return response.json();
                                    })
                                    .then(function(docs) {
                                        console.log('Réponse reçue :', docs);
                                        var html = '';
                                        if (!docs || docs.length === 0) {
                                            html = '<p class="text-muted text-center">Aucun document associé.</p>';
                                        } else {
                                            html = '<ul class="list-group">';
                                            docs.forEach(function(doc) {
                                                var icon = 'bx-file';
                                                if (doc.mime_type && doc.mime_type.includes('pdf')) {
                                                    icon = 'bxs-file-pdf';
                                                } else if (doc.mime_type && doc.mime_type.includes('image')) {
                                                    icon = 'bxs-file-image';
                                                } else if (doc.mime_type && (doc.mime_type.includes('word') || doc.mime_type.includes('document'))) {
                                                    icon = 'bxs-file-doc';
                                                } else if (doc.mime_type && (doc.mime_type.includes('excel') || doc.mime_type.includes('sheet'))) {
                                                    icon = 'bxs-file';
                                                }

                                                var date = new Date(doc.created_at);
                                                var formattedDate = date.toLocaleDateString('fr-FR');

                                                html += `
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="bx ${icon} me-2"></i>
                                                            <a href="<?= base_url('Prescriptions/download/') ?>${doc.id}" target="_blank">${doc.original_name || doc.filename}</a>
                                                            <small class="text-muted ms-2">(${formattedDate})</small>
                                                        </div>
                                                        <span class="badge bg-secondary">${doc.type}</span>
                                                    </li>
                                                `;
                                            });
                                            html += '</ul>';
                                        }
                                        listDiv.innerHTML = html;
                                    })
                                    .catch(function(error) {
                                        console.error('Erreur AJAX :', error);
                                        listDiv.innerHTML = '<p class="text-danger text-center">Erreur de chargement. Voir la console (F12).</p>';
                                    });
                            });
                        })();
                        </script>

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $p['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmation</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <p class="mt-3">Voulez-vous vraiment supprimer cette prescription ?</p>
                                        <p class="text-muted"><?= htmlspecialchars($p['medicament']) ?></p>
                                    </div>
                                    <form action="<?= base_url('Prescriptions/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">Supprimer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL STATUS -->
                        <div class="modal fade" id="status_<?= $p['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header <?= ($p['is_active'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= ($p['is_active'] == 1) ? '<i class="bx bx-block me-2"></i>Désactiver' : '<i class="bx bx-check-circle me-2"></i>Activer' ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Voulez-vous <strong><?= ($p['is_active'] == 1) ? 'désactiver' : 'activer' ?></strong> cette prescription ?</p>
                                    </div>
                                    <form action="<?= base_url('Prescriptions/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="is_active" value="<?= $p['is_active'] ?>">
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= ($p['is_active'] == 1) ? 'btn-warning' : 'btn-success' ?>">
                                                <?= ($p['is_active'] == 1) ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr><td colspan="8" class="text-center py-4">Aucune prescription trouvée</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

<!-- MODAL CREATE (global) -->
<div class="modal fade" id="create_prescription" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle prescription</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Prescriptions/Create') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Consultation <span class="text-danger">*</span></label>
                        <select class="form-select" name="consultation_id" required>
                            <option value="">-- Sélectionner une consultation --</option>
                            <?php foreach ($consultations as $c): ?>
                                <option value="<?= $c['id'] ?>">
                                    <?= $c['numero_consultation'] ?> (<?= date('d/m/Y', strtotime($c['date_souhaitee'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Médicament <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="medicament" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Dosage</label>
                        <input type="text" class="form-control" name="dosage" placeholder="ex: 500mg, 2 fois par jour">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Instructions</label>
                        <textarea class="form-control" name="instructions" rows="3" placeholder="Prendre après les repas..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script global DataTable -->
<script>
$(document).ready(function() {
    $('#prescriptionsTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' },
        order: [[5, 'desc']],
        pageLength: 25
    });

    // Auto-hide alerts
    setTimeout(() => $('.alert').fadeOut('slow'), 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>