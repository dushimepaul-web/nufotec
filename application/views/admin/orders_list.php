<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
// Vérifier et définir les variables par défaut si elles n'existent pas
$total_orders = $total_orders ?? 0;
$pending_orders = $pending_orders ?? 0;
$processing_orders = $processing_orders ?? 0;
$completed_orders = $completed_orders ?? 0;
$cancelled_orders = $cancelled_orders ?? 0;
$orders = $orders ?? [];
?>

<style>
/* Styles adaptés au thème admin */
.page-wrapper {
    overflow-x: auto !important;
}

.page-content {
    min-width: 100%;
    overflow-x: auto;
}

/* Conteneur du tableau avec scroll horizontal */
.table-responsive-custom {
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 1rem;
    position: relative;
}

/* Style du tableau pour le scroll */
#ordersTable {
    width: 100%;
    min-width: 1200px;
    white-space: nowrap;
}

#ordersTable th,
#ordersTable td {
    white-space: nowrap;
    vertical-align: middle;
}

/* Pour les colonnes qui peuvent avoir du contenu long */
#ordersTable td:nth-child(6) {
    white-space: normal;
    min-width: 200px;
    max-width: 250px;
}

#ordersTable td:nth-child(3) .d-flex {
    white-space: normal;
}

/* Cartes statistiques responsives */
.stat-card {
    transition: transform 0.3s ease;
    cursor: pointer;
    margin-bottom: 1rem;
}

.stat-card:hover {
    transform: translateY(-5px);
}

/* Adaptation mobile */
@media (max-width: 768px) {
    .stat-card h3 {
        font-size: 1.5rem;
    }
    .stat-card h6 {
        font-size: 0.75rem;
    }
    .btn-group {
        flex-wrap: wrap;
    }
}

/* Badge de statut */
.badge-status {
    display: inline-block;
    padding: 0.35rem 0.65rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 30px;
}

/* Scrollbar personnalisée */
.table-responsive-custom::-webkit-scrollbar {
    height: 8px;
}

.table-responsive-custom::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.table-responsive-custom::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.table-responsive-custom::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.text-accent { 
    color: #d4af37 !important; 
}

.status-select {
    min-width: 130px;
    font-size: 0.8rem;
}

.status-select:focus {
    border-color: #d4af37;
    box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

/* Indicateur de scroll horizontal */
.scroll-indicator {
    text-align: center;
    padding: 5px;
    background: #f8f9fa;
    border-radius: 20px;
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 10px;
    display: inline-block;
}

/* Désactiver ApexCharts */
.apexcharts-canvas, [id*="chart"], [class*="apexcharts"], #chart, .chart-container {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}
</style>

<script>
// Bloquer ApexCharts avant qu'il ne se charge
window.ApexCharts = undefined;
Object.defineProperty(window, 'ApexCharts', {
    get: function() { return undefined; },
    set: function() { return undefined; }
});
</script>

<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Ventes</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Commandes</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <button class="btn btn-sm btn-success" id="exportBtn">
                        <i class="bx bx-download"></i> Exporter CSV
                    </button>
                    <a href="<?= base_url('products/admin_stats') ?>" class="btn btn-sm btn-info">
                        <i class="bx bx-stats"></i> Statistiques
                    </a>
                </div>
            </div>
        </div>

        <!-- Cartes statistiques -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Total commandes</h6>
                                <h3 class="mb-0"><?= $total_orders ?></h3>
                            </div>
                            <i class="bx bx-package fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">En attente</h6>
                                <h3 class="mb-0"><?= $pending_orders ?></h3>
                            </div>
                            <i class="bx bx-time fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">En traitement</h6>
                                <h3 class="mb-0"><?= $processing_orders ?></h3>
                            </div>
                            <i class="bx bx-refresh fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Terminées</h6>
                                <h3 class="mb-0"><?= $completed_orders ?></h3>
                            </div>
                            <i class="bx bx-check-circle fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deuxième ligne de stats -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="card stat-card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Annulées</h6>
                                <h3 class="mb-0"><?= $cancelled_orders ?></h3>
                            </div>
                            <i class="bx bx-x-circle fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card stat-card bg-secondary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Taux de complétion</h6>
                                <h3 class="mb-0">
                                    <?php 
                                    $completion_rate = $total_orders > 0 ? round(($completed_orders / $total_orders) * 100) : 0;
                                    echo $completion_rate . '%';
                                    ?>
                                </h3>
                            </div>
                            <i class="bx bx-trending-up fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12">
                <div class="card stat-card bg-dark text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">En cours</h6>
                                <h3 class="mb-0"><?= $pending_orders + $processing_orders ?></h3>
                            </div>
                            <i class="bx bx-loader-circle fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des commandes avec scroll horizontal -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="mb-0">Liste des commandes</h5>
                <div class="input-group w-auto mt-2 mt-sm-0">
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Rechercher..." style="width: 200px;">
                    <button class="btn btn-sm btn-outline-secondary" id="searchBtn">
                        <i class="bx bx-search"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0 p-md-3">
                <!-- Indicateur de scroll horizontal -->
                <div class="scroll-indicator d-md-none">
                    <i class="bx bx-left-arrow-alt"></i> Glisser pour voir plus <i class="bx bx-right-arrow-alt"></i>
                </div>
                
                <div class="table-responsive-custom">
                    <table class="table table-bordered table-hover" id="ordersTable">
                        <thead class="table-dark">
                            <tr>
                                <th style="min-width: 60px;">ID</th>
                                <th style="min-width: 120px;">N° Commande</th>
                                <th style="min-width: 200px;">Produit</th>
                                <th style="min-width: 150px;">Client</th>
                                <th style="min-width: 130px;">Téléphone</th>
                                <th style="min-width: 250px;">Adresse</th>
                                <th style="min-width: 100px;">Montant</th>
                                <th style="min-width: 130px;">Statut</th>
                                <th style="min-width: 150px;">Date</th>
                                <th style="min-width: 130px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($orders)): ?>
                                <?php foreach($orders as $order): ?>
                                <tr>
                                    <td class="align-middle">#<?= $order['id'] ?></td>
                                    <td class="align-middle">
                                        <span class="fw-bold"><?= 'CMD-' . str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                                <i class="bx bx-package text-muted"></i>
                                            </div>
                                            <div>
                                                <small class="d-block fw-bold"><?= htmlspecialchars($order['product_title'] ?? 'Produit') ?></small>
                                                <small class="text-muted">ID: <?= $order['product_id'] ?></small>
                                            </div>
                                        </div>
                                     </td>
                                    <td class="align-middle"><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td class="align-middle">
                                        <a href="https://wa.me/<?= $order['customer_phone'] ?>" target="_blank" class="text-success text-decoration-none">
                                            <i class="bx bxl-whatsapp"></i> <?= htmlspecialchars($order['customer_phone']) ?>
                                        </a>
                                     </td>
                                    <td class="align-middle">
                                        <div class="text-wrap" style="max-width: 220px;">
                                            <?= htmlspecialchars($order['customer_address']) ?><br>
                                            <span class="text-muted"><?= htmlspecialchars($order['customer_city']) ?>, <?= htmlspecialchars($order['customer_country']) ?></span>
                                        </div>
                                     </td>
                                    <td class="align-middle fw-bold text-accent"><?= htmlspecialchars($order['product_price']) ?></td>
                                    <td class="align-middle">
                                        <select class="form-select form-select-sm status-select" 
                                                data-id="<?= $order['id'] ?>" 
                                                style="width: 130px; font-size: 0.75rem;">
                                            <option value="pending" <?= $order['order_status'] == 'pending' ? 'selected' : '' ?>>
                                                ⏳ En attente
                                            </option>
                                            <option value="processing" <?= $order['order_status'] == 'processing' ? 'selected' : '' ?>>
                                                🔄 En traitement
                                            </option>
                                            <option value="completed" <?= $order['order_status'] == 'completed' ? 'selected' : '' ?>>
                                                ✅ Terminée
                                            </option>
                                            <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>
                                                ❌ Annulée
                                            </option>
                                        </select>
                                     </td>
                                    <td class="align-middle">
                                        <small><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small>
                                     </td>
                                    <td class="align-middle">
                                        <div class="btn-group" role="group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-info view-btn" 
                                                    data-id="<?= $order['id'] ?>"
                                                    data-name="<?= htmlspecialchars($order['customer_name']) ?>"
                                                    data-product="<?= htmlspecialchars($order['product_title']) ?>"
                                                    data-phone="<?= $order['customer_phone'] ?>"
                                                    data-address="<?= htmlspecialchars($order['customer_address']) ?>"
                                                    data-city="<?= htmlspecialchars($order['customer_city']) ?>"
                                                    data-country="<?= htmlspecialchars($order['customer_country']) ?>"
                                                    data-price="<?= htmlspecialchars($order['product_price']) ?>"
                                                    data-date="<?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>"
                                                    data-status="<?= $order['order_status'] ?>"
                                                    title="Voir détails">
                                                <i class="bx bx-show"></i>
                                            </button>
                                            <a href="https://wa.me/<?= $order['customer_phone'] ?>?text=Bonjour%20<?= urlencode($order['customer_name']) ?>%2C%20nous%20accusons%20r%C3%A9ception%20de%20votre%20commande%20pour%20<?= urlencode($order['product_title']) ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-success" 
                                               title="WhatsApp">
                                                <i class="bx bxl-whatsapp"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-btn" 
                                                    data-id="<?= $order['id'] ?>"
                                                    data-name="<?= htmlspecialchars($order['customer_name']) ?>"
                                                    title="Supprimer">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                     </td>
                                 </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                 <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <i class="bx bx-inbox fs-1 text-muted"></i>
                                        <p class="mt-2 text-muted">Aucune commande trouvée</p>
                                      </tr>
                                 </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


<!-- Modal Détails -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bx bx-info-circle me-2"></i>Détails de la commande
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="orderDetailContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-success" id="whatsappFromModal">
                    <i class="bx bxl-whatsapp me-1"></i> Contacter client
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let dataTable = null;
    
    // Détecter si on est sur mobile pour adapter DataTables
    const isMobile = window.innerWidth < 768;
    
    // Initialisation DataTables
    if ($('#ordersTable tbody tr').length > 0) {
        try {
            dataTable = $('#ordersTable').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' },
                order: [[0, 'desc']],
                pageLength: isMobile ? 10 : 25,
                responsive: isMobile,
                scrollX: true,
                scrollCollapse: true,
                autoWidth: false
            });
        } catch(e) { console.log('DataTables error:', e); }
    }
    
    // Recherche personnalisée
    $('#searchBtn, #searchInput').on('click keypress', function(e) {
        if (e.type === 'keypress' && e.which !== 13) return;
        const searchTerm = $('#searchInput').val();
        if (dataTable) {
            dataTable.search(searchTerm).draw();
        }
    });
    
    let currentPhone = null;
    
    // Modal détails
    $('.view-btn').click(function() {
        const data = $(this).data();
        currentPhone = data.phone;
        
        const statusBadge = {
            'pending': '<span class="badge bg-warning">⏳ En attente</span>',
            'processing': '<span class="badge bg-info">🔄 En traitement</span>',
            'completed': '<span class="badge bg-success">✅ Terminée</span>',
            'cancelled': '<span class="badge bg-danger">❌ Annulée</span>'
        }[data.status] || '<span class="badge bg-secondary">Inconnu</span>';
        
        $('#orderDetailContent').html(`
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light"><strong><i class="bx bx-user"></i> Client</strong></div>
                    <div class="card-body">
                        <p><strong>Nom:</strong> ${data.name}</p>
                        <p><strong>Téléphone:</strong> <a href="https://wa.me/${data.phone}" target="_blank">${data.phone}</a></p>
                        <p><strong>Adresse:</strong> ${data.address}</p>
                        <p><strong>Ville:</strong> ${data.city}</p>
                        <p><strong>Pays:</strong> ${data.country}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light"><strong><i class="bx bx-package"></i> Commande</strong></div>
                    <div class="card-body">
                        <p><strong>N° commande:</strong> #${data.id}</p>
                        <p><strong>Produit:</strong> ${data.product}</p>
                        <p><strong>Montant:</strong> <span class="text-accent fw-bold">${data.price}</span></p>
                        <p><strong>Date:</strong> ${data.date}</p>
                        <p><strong>Statut:</strong> ${statusBadge}</p>
                    </div>
                </div>
            </div>
        `);
        $('#orderDetailModal').modal('show');
    });
    
    $('#whatsappFromModal').click(() => { if(currentPhone) window.open(`https://wa.me/${currentPhone}`, '_blank'); });
    
    // Mise à jour du statut SANS rechargement de la page
$('.status-select').change(function() {
    const $select = $(this);
    const orderId = $select.data('id');
    const newStatus = $select.val();
    const oldStatus = $select.data('original-value');
    const oldText = $select.find('option:selected').text();
    
    // Sauvegarder l'ancienne valeur
    $select.data('original-value', newStatus);
    
    // Afficher un indicateur de chargement
    const originalHtml = $select.html();
    $select.html('<option>Chargement...</option>');
    $select.prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url("products/update_order_status") ?>',
        method: 'POST',
        data: { 
            order_id: orderId, 
            status: newStatus 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Mettre à jour le select avec la nouvelle valeur
                $select.html(originalHtml);
                $select.val(newStatus);
                
                // Afficher une notification
                Swal.fire({
                    title: 'Succès!',
                    text: response.message,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Mettre à jour les compteurs de statistiques
                updateStatsCounters(orderId, oldStatus, newStatus);
                
            } else {
                // Restaurer l'ancienne valeur en cas d'erreur
                $select.html(originalHtml);
                $select.val(oldStatus);
                Swal.fire('Erreur', response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur AJAX:', error);
            $select.html(originalHtml);
            $select.val(oldStatus);
            Swal.fire('Erreur', 'Erreur de connexion au serveur', 'error');
        },
        complete: function() {
            $select.prop('disabled', false);
        }
    });
});

// Sauvegarder la valeur originale au focus
$('.status-select').focus(function() {
    $(this).data('original-value', $(this).val());
});

// Fonction pour mettre à jour les compteurs de statistiques dynamiquement
function updateStatsCounters(orderId, oldStatus, newStatus) {
    // Décrémenter l'ancien compteur
    if (oldStatus) {
        const oldCounter = $('.stat-card:contains("' + getStatusLabel(oldStatus) + '") .mb-0, .stat-card:contains("' + getStatusLabel(oldStatus) + '") h3');
        if (oldCounter.length) {
            let oldValue = parseInt(oldCounter.text());
            if (!isNaN(oldValue) && oldValue > 0) {
                oldCounter.text(oldValue - 1);
            }
        }
    }
    
    // Incrémenter le nouveau compteur
    const newCounter = $('.stat-card:contains("' + getStatusLabel(newStatus) + '") .mb-0, .stat-card:contains("' + getStatusLabel(newStatus) + '") h3');
    if (newCounter.length) {
        let newValue = parseInt(newCounter.text());
        if (!isNaN(newValue)) {
            newCounter.text(newValue + 1);
        }
    }
    
    // Mettre à jour le total "En cours" (pending + processing)
    updateInProgressCounter();
    
    // Mettre à jour le taux de complétion
    updateCompletionRate();
}

// Fonction pour obtenir le libellé du statut
function getStatusLabel(status) {
    const labels = {
        'pending': 'En attente',
        'processing': 'En traitement',
        'completed': 'Terminées',
        'cancelled': 'Annulées'
    };
    return labels[status] || status;
}

// Mettre à jour le compteur "En cours"
function updateInProgressCounter() {
    const pendingText = $('.stat-card.bg-warning .mb-0').text();
    const processingText = $('.stat-card.bg-info .mb-0').text();
    
    const pending = parseInt(pendingText) || 0;
    const processing = parseInt(processingText) || 0;
    const inProgress = pending + processing;
    
    $('.stat-card.bg-dark .mb-0').text(inProgress);
}

// Mettre à jour le taux de complétion
function updateCompletionRate() {
    const totalText = $('.stat-card.bg-primary .mb-0').text();
    const completedText = $('.stat-card.bg-success .mb-0').text();
    
    const total = parseInt(totalText) || 0;
    const completed = parseInt(completedText) || 0;
    
    let rate = 0;
    if (total > 0) {
        rate = Math.round((completed / total) * 100);
    }
    
    $('.stat-card.bg-secondary .mb-0').text(rate + '%');
}
    
    // Suppression
    $('.delete-btn').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Confirmation', text: `Supprimer la commande de ${name} ?`, icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Oui, supprimer'
        }).then((r) => {
            if(r.isConfirmed) {
                $.ajax({
                    url: '<?= base_url("products/delete_order") ?>', method: 'POST', data: { order_id: id }, dataType: 'json',
                    success: (res) => { 
                        if(res.success) { 
                            Swal.fire('Supprimé!', res.message, 'success'); 
                            setTimeout(() => location.reload(), 1500); 
                        } else Swal.fire('Erreur', res.message, 'error');
                    },
                    error: () => Swal.fire('Erreur', 'Impossible de supprimer', 'error')
                });
            }
        });
    });
    
    $('#exportBtn').click(e => { e.preventDefault(); window.location.href = '<?= base_url("products/export_orders_csv") ?>'; });
    
    // Détection du scroll horizontal sur mobile
    const tableContainer = document.querySelector('.table-responsive-custom');
    if (tableContainer && isMobile) {
        tableContainer.addEventListener('scroll', function() {
            const indicator = document.querySelector('.scroll-indicator');
            if (indicator) indicator.style.opacity = '0.5';
        });
    }
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>