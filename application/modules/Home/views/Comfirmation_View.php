<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
    .confirmation-container {
        background: white;
        border-radius: 20px;
        padding: 40px 30px;
        box-shadow: var(--shadow-lg);
        margin: 40px 0;
        text-align: center;
    }

    .confirmation-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        font-size: 50px;
        color: white;
        box-shadow: var(--shadow-glow);
    }

    .confirmation-title {
        font-size: 32px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 15px;
    }

    .confirmation-message {
        font-size: 18px;
        color: var(--gray);
        margin-bottom: 30px;
    }

    .order-details {
        background: var(--light);
        border-radius: 15px;
        padding: 25px;
        margin: 30px 0;
        text-align: left;
    }

    .order-details h3 {
        font-size: 20px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .order-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .info-item {
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: var(--shadow);
    }

    .info-item .label {
        font-size: 13px;
        color: var(--gray);
        margin-bottom: 5px;
    }

    .info-item .value {
        font-size: 16px;
        font-weight: 600;
        color: var(--primary);
    }

    .products-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .products-table th {
        text-align: left;
        padding: 12px 10px;
        background: rgba(212, 175, 55, 0.1);
        color: var(--primary);
        font-weight: 600;
        font-size: 14px;
    }

    .products-table td {
        padding: 12px 10px;
        border-bottom: 1px solid var(--gray-light);
    }

    .products-table tr:last-child td {
        border-bottom: none;
    }

    .products-table .produit-nom {
        font-weight: 600;
        color: var(--dark);
    }

    .products-table .produit-prix {
        font-weight: 700;
        color: var(--primary);
    }

    .total-row {
        background: var(--light);
        font-weight: 700;
    }

    .total-row td {
        padding: 15px 10px;
    }

    .btn-retour {
        background: var(--accent);
        color: var(--primary-dark);
        border: none;
        padding: 15px 40px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 16px;
        transition: var(--transition);
        display: inline-block;
        text-decoration: none;
        margin-top: 20px;
    }

    .btn-retour:hover {
        background: var(--accent-hover);
        transform: translateY(-2px);
        box-shadow: var(--shadow);
        color: var(--primary-dark);
    }

    @media (max-width: 768px) {
        .order-info-grid {
            grid-template-columns: 1fr;
        }
        .products-table {
            display: block;
            overflow-x: auto;
        }
    }
</style>

<div class="container py-4">
    <div class="confirmation-container">
        <div class="confirmation-icon">
            <i class="bi bi-check-lg"></i>
        </div>

        <h1 class="confirmation-title">Thank you for your order!</h1>
        <p class="confirmation-message">
            Your order has been successfully placed. You will receive a confirmation email shortly.
        </p>

        <?php if (!empty($commande)): ?>
            <div class="order-details">
                <h3><i class="bi bi-receipt"></i> Order Details #<?php echo $commande->numero_commande; ?></h3>

                <div class="order-info-grid">
                    <div class="info-item">
                        <div class="label">Order Date</div>
                        <div class="value"><?php echo date('d/m/Y H:i', strtotime($commande->created_at)); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Status</div>
                        <div class="value">
                            <?php
                            $statuts = [
                                'en_attente' => 'Pending',
                                'confirmee' => 'Confirmed',
                                'preparation' => 'In Preparation',
                                'expediee' => 'Shipped',
                                'livree' => 'Delivered',
                                'annulee' => 'Cancelled',
                                'remboursee' => 'Refunded'
                            ];
                            echo $statuts[$commande->statut] ?? $commande->statut;
                            ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="label">Payment Method</div>
                        <div class="value"><?php echo $commande->mode_paiement; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Total incl. Tax</div>
                        <div class="value"><?php echo number_format($commande->total_ttc, 0, ',', ' '); ?> FCFA</div>
                    </div>
                </div>

                <h3 style="margin-top: 25px;"><i class="bi bi-box"></i> Ordered Products</h3>

                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Reference</th>
                            <th>Quantity</th>
                            <th>Total incl. Tax</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lignes as $ligne): ?>
                            <tr>
                                <td class="produit-nom"><?php echo $ligne->nom_produit; ?></td>
                                <td><?php echo $ligne->reference_produit; ?></td>
                                <td><?php echo $ligne->quantite; ?></td>
                                
                                <td class="produit-prix"><?php echo number_format($ligne->total_ligne_ttc, 0, ',', ' '); ?> $</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="5" style="text-align: right;">Total incl. Tax</td>
                            <td class="produit-prix"><?php echo number_format($commande->total_ttc, 0, ',', ' '); ?> $</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">No order information available.</p>
        <?php endif; ?>

        <a href="<?php echo base_url('boutique'); ?>" class="btn-retour">
            <i class="bi bi-arrow-left"></i> Continue Shopping
        </a>
    </div>
</div>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>