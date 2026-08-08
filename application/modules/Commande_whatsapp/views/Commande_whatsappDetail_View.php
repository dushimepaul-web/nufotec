<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <?php if (!empty($detail)): ?>
    <?php
        $phone = preg_replace('/[^0-9+]/', '', $detail['customer_phone'] ?? '');
        $wa_link = !empty($phone) ? 'https://wa.me/' . $phone : '#';
        $badge = 'text-bg-secondary';
        switch ($detail['order_status']) {
            case 'pending':    $badge = 'text-bg-warning'; break;
            case 'processing': $badge = 'text-bg-info'; break;
            case 'completed':  $badge = 'text-bg-success'; break;
            case 'cancelled':  $badge = 'text-bg-danger'; break;
        }
    ?>

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Ventes</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('commande_whatsapp') ?>">Commandes WhatsApp</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Commande #<?= $detail['id'] ?></li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-success" href="<?= htmlspecialchars($wa_link) ?>" target="_blank">
                <i class="bx bxl-whatsapp me-1"></i>Contacter le client
            </a>
            <a class="btn btn-secondary" href="<?= base_url('commande_whatsapp') ?>">
                <i class="bx bx-arrow-back me-1"></i>Retour
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 d-flex flex-wrap align-items-center gap-2">
            <h5 class="mb-0"><strong>CMD-<?= str_pad($detail['id'], 6, '0', STR_PAD_LEFT) ?></strong></h5>
            <span class="badge <?= $badge ?> fs-6"><?= htmlspecialchars($detail['order_status']) ?></span>
            <span class="text-muted"><?= date('d/m/Y H:i', strtotime($detail['created_at'])) ?></span>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 text-primary"><i class="bx bx-package me-2"></i>Produit commandé</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($detail['main_image'])): ?>
                        <img src="<?= base_url('attachments/Products/' . $detail['main_image']) ?>" 
                             class="rounded border mb-3"
                             style="width:120px; height:120px; object-fit:cover;"
                             onerror="this.src='<?= base_url('attachments/Products/default-product.png') ?>'"
                             alt="Produit">
                    <?php endif; ?>
                    <h5 class="mb-1"><?= htmlspecialchars($detail['product_name'] ?? $detail['product_title']) ?></h5>
                    <span class="badge text-bg-success fs-6"><?= htmlspecialchars($detail['product_price'] ?? '') ?></span>
                    <?php if (!empty($detail['slug'])): ?>
                        <br><a class="small" href="<?= base_url('product/' . $detail['slug']) ?>" target="_blank">Voir sur le site <i class="bx bx-link-external"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 text-primary"><i class="bx bx-user me-2"></i>Client</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">Nom</label>
                        <span><?= htmlspecialchars($detail['customer_name'] ?? '') ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">Téléphone</label>
                        <a href="<?= htmlspecialchars($wa_link) ?>" target="_blank" class="text-decoration-none">
                            <i class="bx bxl-whatsapp text-success me-1"></i><?= htmlspecialchars($detail['customer_phone'] ?? '') ?>
                        </a>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">Pays</label>
                        <span><?= htmlspecialchars($detail['customer_country'] ?? '-') ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">Ville</label>
                        <span><?= htmlspecialchars($detail['customer_city'] ?? '-') ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">Adresse</label>
                        <span><?= htmlspecialchars($detail['customer_address'] ?? '-') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 text-primary"><i class="bx bx-info-circle me-2"></i>Informations techniques</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">IP</label>
                        <span><?= htmlspecialchars($detail['ip_address'] ?? '-') ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">User Agent</label>
                        <span><?= htmlspecialchars($detail['user_agent'] ?? '-') ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">Dernière mise à jour</label>
                        <span><?= !empty($detail['updated_at']) ? date('d/m/Y H:i', strtotime($detail['updated_at'])) : '-' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 text-primary"><i class="bx bx-note me-2"></i>Notes du client</h6>
                </div>
                <div class="card-body">
                    <div class="p-2 bg-light rounded"><?= !empty($detail['customer_notes']) ? nl2br(htmlspecialchars($detail['customer_notes'])) : 'Aucune note' ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bx bxl-whatsapp text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">Commande introuvable</h5>
                <a class="btn btn-primary mt-2" href="<?= base_url('commande_whatsapp') ?>">
                    <i class="bx bx-arrow-back me-1"></i>Retour aux commandes
                </a>
            </div>
        </div>
    <?php endif; ?>

</div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>