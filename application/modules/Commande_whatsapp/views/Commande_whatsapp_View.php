<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
$stats  = $stats  ?? array('total'=>0,'pending'=>0,'processing'=>0,'completed'=>0,'cancelled'=>0,'today'=>0);
$orders = $orders ?? array();
?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Ventes</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Commandes WhatsApp</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <form action="<?= base_url('commande_whatsapp/search') ?>" method="GET" class="d-flex me-2">
                <input type="text" name="q" value="<?= htmlspecialchars(($q ?? '')) ?>" class="form-control form-control-sm" placeholder="Rechercher un client / téléphone..." style="width: 230px;">
                <button class="btn btn-sm btn-outline-secondary ms-1" type="submit"><i class="bx bx-search"></i></button>
            </form>
            <a class="btn btn-success btn-sm" href="<?= base_url('commande_whatsapp/export') ?>">
                <i class="bx bx-download"></i> Exporter CSV
            </a>
        </div>
    </div>

    <!-- Messages flash -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Cartes statistiques -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm text-white bg-primary">
                <div class="card-body">
                    <h6 class="mb-1">Total</h6>
                    <h3 class="mb-0"><?= $stats['total'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm text-white bg-success">
                <div class="card-body">
                    <h6 class="mb-1">Aujourd'hui</h6>
                    <h3 class="mb-0"><?= $stats['today'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm text-white bg-warning">
                <div class="card-body">
                    <h6 class="mb-1">En attente</h6>
                    <h3 class="mb-0"><?= $stats['pending'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm text-white bg-info">
                <div class="card-body">
                    <h6 class="mb-1">En traitement</h6>
                    <h3 class="mb-0"><?= $stats['processing'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm text-white bg-success">
                <div class="card-body">
                    <h6 class="mb-1">Terminées</h6>
                    <h3 class="mb-0"><?= $stats['completed'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm text-white bg-danger">
                <div class="card-body">
                    <h6 class="mb-1">Annulées</h6>
                    <h3 class="mb-0"><?= $stats['cancelled'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-primary"><i class="bx bxl-whatsapp me-2"></i>Liste des commandes</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="commandeTable" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="8%">N°</th>
                            <th width="20%">Produit</th>
                            <th width="15%">Client</th>
                            <th width="12%">Téléphone</th>
                            <th width="12%">Localisation</th>
                            <th width="8%">Prix</th>
                            <th width="8%">Statut</th>
                            <th width="12%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $modals_html = ''; ?>
                    <?php if (!empty($orders)): $i = 1; foreach ($orders as $value): 

                        $phone = preg_replace('/[^0-9+]/', '', $value['customer_phone'] ?? '');
                        $wa_link = !empty($phone) ? 'https://wa.me/' . $phone : '#';
                    ?>
                         <tr>
                            <td><?= $i++ ?></td>
                            <td><strong>#<?= $value['id'] ?></strong><br><small class="text-muted">CMD-<?= str_pad($value['id'], 6, '0', STR_PAD_LEFT) ?></small></td>
                            <td>
                                <?php if (!empty($value['product_name'])): ?>
                                    <strong class="text-dark"><?= htmlspecialchars($value['product_name']) ?></strong>
                                <?php else: ?>
                                    <span class="text-muted"><?= htmlspecialchars($value['product_title']) ?></span>
                                <?php endif; ?>
                                <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($value['created_at'])) ?></small>
                            </td>
                            <td><strong><?= htmlspecialchars($value['customer_name'] ?? '') ?></strong></td>
                            <td>
                                <a href="<?= htmlspecialchars($wa_link) ?>" target="_blank" class="text-decoration-none">
                                    <i class="bx bxl-whatsapp text-success me-1"></i><?= htmlspecialchars($value['customer_phone'] ?? '') ?>
                                </a>
                            </td>
                            <td>
                                <?= htmlspecialchars($value['customer_city'] ?? '') ?>
                                <?php if (!empty($value['customer_country'])): ?><br><small class="text-muted"><?= htmlspecialchars($value['customer_country']) ?></small><?php endif; ?>
                            </td>
                            <td><span class="badge text-bg-success fs-6"><?= htmlspecialchars($value['product_price'] ?? '') ?></span></td>
                            <td>
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id'] ?>" class="text-decoration-none">
                                    <?php
                                    $badge = 'text-bg-secondary';
                                    $label = 'pending';
                                    switch ($value['order_status']) {
                                        case 'pending':    $badge = 'text-bg-warning'; break;
                                        case 'processing': $badge = 'text-bg-info'; break;
                                        case 'completed':  $badge = 'text-bg-success'; break;
                                        case 'cancelled':  $badge = 'text-bg-danger'; break;
                                    }
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= htmlspecialchars($value['order_status']) ?></span>
                                </a>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('commande_whatsapp/view_order/' . $value['id']) ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Changer le statut
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

                        <?php ob_start(); ?>

                        <!-- MODAL STATUS -->
                        <div class="modal fade" id="status_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Changer le statut</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Commande <strong>CMD-<?= str_pad($value['id'], 6, '0', STR_PAD_LEFT) ?></strong> - <strong><?= htmlspecialchars($value['customer_name'] ?? '') ?></strong></p>
                                        <label class="form-label fw-bold">Nouveau statut</label>
                                        <select class="form-select" id="statusSelect_<?= $value['id'] ?>">
                                            <option value="pending" <?= $value['order_status'] == 'pending' ? 'selected' : '' ?>>En attente</option>
                                            <option value="processing" <?= $value['order_status'] == 'processing' ? 'selected' : '' ?>>En traitement</option>
                                            <option value="completed" <?= $value['order_status'] == 'completed' ? 'selected' : '' ?>>Terminée</option>
                                            <option value="cancelled" <?= $value['order_status'] == 'cancelled' ? 'selected' : '' ?>>Annulée</option>
                                        </select>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="button" class="btn btn-primary btn-save-status" data-order-id="<?= $value['id'] ?>">
                                            <i class="bx bx-save me-2"></i>Enregistrer
                                        </button>
                                    </div>
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
                                        <p class="text-muted">La commande <strong>CMD-<?= str_pad($value['id'], 6, '0', STR_PAD_LEFT) ?></strong> (<?= htmlspecialchars($value['customer_name'] ?? '') ?>) sera définitivement supprimée.</p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <div class="modal-footer bg-light justify-content-center">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="button" class="btn btn-danger btn-delete-order" data-order-id="<?= $value['id'] ?>">
                                            <i class="bx bx-trash me-2"></i>Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php $modals_html .= ob_get_clean(); ?>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bx bxl-whatsapp text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucune commande trouvée</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <?= $modals_html ?>
            </div>
        </div>
    </div>

</div>
</div>

<script>
$(document).ready(function() {
    $('#commandeTable').DataTable({
        language: {
            url: '<?= base_url("assets/backend/plugins/datatable/js/fr-FR.json") ?>'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [8] }
        ]
    });

    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    $('.btn-save-status').on('click', function() {
        const orderId = $(this).data('order-id');
        const status = $('#statusSelect_' + orderId).val();
        const btn = $(this);

        btn.prop('disabled', true);

        $.ajax({
            url: '<?= base_url('commande_whatsapp/ChangeStatus') ?>',
            type: 'POST',
            dataType: 'json',
            data: { order_id: orderId, status: status },
            success: function(res) {
                if (res.success) {
                    $('#status_' + orderId).modal('hide');
                    location.reload();
                } else {
                    alert(res.message || 'Erreur lors de la mise à jour');
                }
            },
            error: function() {
                alert('Erreur réseau');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    $('.btn-delete-order').on('click', function() {
        const orderId = $(this).data('order-id');
        const btn = $(this);

        btn.prop('disabled', true);

        $.ajax({
            url: '<?= base_url("commande_whatsapp/Delete") ?>',
            type: 'POST',
            dataType: 'json',
            data: { order_id: orderId },
            success: function(res) {
                if (res.success) {
                    $('#delete_' + orderId).modal('hide');
                    location.reload();
                } else {
                    alert(res.message || 'Erreur de suppression');
                }
            },
            error: function() {
                alert('Erreur réseau');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>