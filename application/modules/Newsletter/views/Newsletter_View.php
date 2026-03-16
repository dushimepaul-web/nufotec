<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
        <div class="breadcrumb-title pe-3">Administration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Newsletter</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="<?= base_url('Newsletter/export_csv') ?>" class="btn btn-success me-2">
                <i class="bx bx-download me-2"></i>Export CSV
            </a>
            <button class="btn btn-danger" id="btnDeleteMultiple" disabled>
                <i class="bx bx-trash me-2"></i>Supprimer sélection
            </button>
        </div>
    </div>

    <!-- Messages Flash -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-check-circle fs-5 me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Cartes Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bx bx-envelope"></i></div>
                        <div>
                            <h6 class="mb-0">Total inscriptions</h6>
                            <h3 class="mb-0"><?= $total ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bx bx-at"></i></div>
                        <div>
                            <h6 class="mb-0">Avec email</h6>
                            <h3 class="mb-0"><?= $avec_email ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bx bx-phone"></i></div>
                        <div>
                            <h6 class="mb-0">Avec téléphone</h6>
                            <h3 class="mb-0"><?= $avec_telephone ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des inscriptions -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Liste des inscrits</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="newsletterTable">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>ID</th>
                            <th><i class="bx bx-envelope me-1"></i>Email</th>
                            <th><i class="bx bx-phone me-1"></i>Téléphone</th>
                            <th><i class="bx bx-calendar me-1"></i>Date d'inscription</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($Newsletter)): ?>
                            <?php foreach ($Newsletter as $n): ?>
                            <tr id="row-<?= $n['id_newsletter'] ?>">
                                <td>
                                    <input type="checkbox" class="form-check-input row-checkbox" value="<?= $n['id_newsletter'] ?>">
                                </td>
                                <td><span class="badge bg-light text-dark">#<?= $n['id_newsletter'] ?></span></td>
                                <td>
                                    <?php if (!empty($n['email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($n['email']) ?>" class="text-decoration-none">
                                            <i class="bx bx-envelope me-1 text-primary"></i><?= htmlspecialchars($n['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($n['telephone'])): ?>
                                        <span class="text-success"><i class="bx bx-phone me-1"></i><?= htmlspecialchars($n['telephone']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="bx bx-time me-1"></i><?= date('d/m/Y H:i', strtotime($n['date_inscription'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" 
                                            data-id="<?= $n['id_newsletter'] ?>" 
                                            data-email="<?= htmlspecialchars($n['email'] ?? 'N/A') ?>"
                                            title="Supprimer">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bx bx-inbox text-muted" style="font-size: 3rem;"></i>
                                    <p class="mt-3 text-muted">Aucune inscription pour le moment</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal Suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="bx bx-error-circle text-danger" style="font-size: 3rem;"></i>
                <p class="mt-3">Supprimer l'inscription <strong id="deleteEmail"></strong> ?</p>
                <p class="text-danger small">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer bg-light justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDelete">
                    <span class="btn-text"><i class="bx bx-trash me-2"></i>Supprimer</span>
                    <span class="d-none btn-loading"><i class="bx bx-loader-alt bx-spin me-2"></i>Suppression...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="liveToast" class="toast align-items-center border-0 bg-white shadow" role="alert">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center" id="toastMessage">
                <i class="bx bx-check-circle text-success me-2 fs-5"></i>
                <span>Action réussie !</span>
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const toast = new bootstrap.Toast(document.getElementById('liveToast'), { delay: 3000 });
    
    function showToast(message, type = 'success') {
        const icon = type === 'success' ? 'bx-check-circle text-success' : 'bx-error-circle text-danger';
        document.getElementById('toastMessage').innerHTML = 
            `<i class="bx ${icon} me-2 fs-5"></i><span>${message}</span>`;
        toast.show();
    }

    // Sélection multiple
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const btnDeleteMultiple = document.getElementById('btnDeleteMultiple');
    let selectedIds = [];

    selectAll.addEventListener('change', function() {
        rowCheckboxes.forEach(cb => {
            cb.checked = this.checked;
            updateSelection();
        });
    });

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateSelection);
    });

    function updateSelection() {
        selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        btnDeleteMultiple.disabled = selectedIds.length === 0;
        btnDeleteMultiple.innerHTML = `<i class="bx bx-trash me-2"></i>Supprimer (${selectedIds.length})`;
    }

    // Suppression multiple
    btnDeleteMultiple.addEventListener('click', function() {
        if (selectedIds.length === 0) return;
        
        if (!confirm(`Supprimer ${selectedIds.length} inscription(s) ?`)) return;

        const formData = new FormData();
        formData.append('ids', selectedIds);

        fetch('<?= base_url('Newsletter/delete_multiple') ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);
                selectedIds.forEach(id => document.getElementById(`row-${id}`).remove());
                updateSelection();
            } else {
                showToast(data.message, 'error');
            }
        });
    });

    // Suppression individuelle
    let deleteId = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            deleteId = this.getAttribute('data-id');
            document.getElementById('deleteEmail').textContent = this.getAttribute('data-email');
            deleteModal.show();
        });
    });

    document.getElementById('btnConfirmDelete').addEventListener('click', function() {
        if (!deleteId) return;

        const btnText = this.querySelector('.btn-text');
        const btnLoading = this.querySelector('.btn-loading');
        
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');
        this.disabled = true;

        const formData = new FormData();
        formData.append('id', deleteId);

        fetch('<?= base_url('Newsletter/delete') ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            deleteModal.hide();
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');
            this.disabled = false;

            if (data.success) {
                showToast(data.message);
                document.getElementById(`row-${deleteId}`).remove();
                deleteId = null;
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(err => {
            showToast('Erreur de connexion', 'error');
            console.error(err);
        });
    });

    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>