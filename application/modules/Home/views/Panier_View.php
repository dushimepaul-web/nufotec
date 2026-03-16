<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
<style>
    :root {
        --primary: #0f4c3a;
        --primary-light: #1a6b52;
        --primary-dark: #0a3326;
        --accent: #d4af37;
        --accent-hover: #b8962e;
        --accent-light: #f4d03f;
        --light: #f8f9fa;
        --dark: #212529;
        --gray: #6c757d;
        --gray-light: #dee2e6;
        --shadow: 0 4px 6px rgba(0,0,0,0.1);
        --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
        --transition: all 0.3s ease;
    }

    /* ===== SECTION HELLO ===== */
    .hello-section {
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        color: white;
        padding: 20px 30px;
        border-radius: 20px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
        box-shadow: var(--shadow);
    }

    .hello-message {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .hello-icon {
        width: 50px;
        height: 50px;
        background: var(--accent);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: var(--primary-dark);
    }

    .hello-text h2 {
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 5px;
    }

    .hello-text p {
        font-size: 14px;
        margin: 0;
        opacity: 0.9;
    }

    .hello-offer {
        background: rgba(255,255,255,0.2);
        padding: 10px 20px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        backdrop-filter: blur(5px);
    }

    /* ===== PAGE PANIER ===== */
    .cart-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .cart-layout {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 30px;
        align-items: start;
    }

    .cart-main {
        min-width: 0;
    }

    .cart-sidebar {
        position: sticky;
        top: 100px;
    }

    .cart-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
    }

    .cart-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: var(--primary);
        margin: 0;
    }

    .cart-header .badge {
        background: var(--accent);
        color: var(--primary-dark);
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    /* ===== TABLEAU ===== */
    .cart-table {
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .cart-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .cart-table th {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 14px;
        text-align: left;
    }

    .cart-table td {
        padding: 20px;
        border-bottom: 1px solid var(--gray-light);
        vertical-align: middle;
    }

    .cart-table tr:last-child td {
        border-bottom: none;
    }

    .cart-product {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .cart-product-image {
        width: 80px;
        height: 80px;
        background: var(--light);
        border-radius: 10px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .cart-product-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .cart-product-info h3 {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 5px;
        color: var(--dark);
    }

    .cart-product-info p {
        font-size: 13px;
        color: var(--gray);
        margin: 0;
    }

    .cart-price {
        font-size: 16px;
        font-weight: 700;
        color: var(--primary);
        white-space: nowrap;
    }

    .cart-quantity {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        border: 1px solid var(--gray-light);
        border-radius: 25px;
        overflow: hidden;
        background: white;
    }

    .quantity-control button {
        width: 35px;
        height: 35px;
        background: white;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        font-size: 16px;
        color: var(--primary);
    }

    .quantity-control button:hover {
        background: var(--accent-light);
    }

    .quantity-control input {
        width: 50px;
        height: 35px;
        border: none;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
        -moz-appearance: textfield;
        background: transparent;
    }

    .quantity-control input::-webkit-outer-spin-button,
    .quantity-control input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .cart-total-line {
        font-size: 16px;
        font-weight: 700;
        color: var(--primary);
        white-space: nowrap;
    }

    .btn-remove {
        background: none;
        border: none;
        color: var(--gray);
        font-size: 20px;
        cursor: pointer;
        transition: var(--transition);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .btn-remove:hover {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    /* ===== RÉCAPITULATIF ET PROCESS ===== */
    .cart-summary {
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow);
        padding: 25px;
        margin-bottom: 25px;
    }

    .cart-summary h3 {
        font-size: 18px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--accent-light);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 15px;
    }

    .summary-row.total {
        font-size: 20px;
        font-weight: 700;
        color: var(--primary);
        border-top: 2px solid var(--gray-light);
        padding-top: 15px;
        margin-top: 15px;
    }

    /* ===== ORDER PROCESS CARD ===== */
    .process-card {
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow);
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid var(--accent-light);
    }

    .process-card h3 {
        font-size: 18px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .process-card h3 i {
        color: var(--accent);
        font-size: 24px;
    }

    .process-steps {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .process-step {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        align-items: flex-start;
    }

    .step-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
        flex-shrink: 0;
    }

    .step-content {
        flex: 1;
    }

    .step-title {
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 5px;
        font-size: 15px;
    }

    .step-desc {
        color: var(--gray);
        font-size: 13px;
        line-height: 1.4;
    }

    .cart-actions {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        margin-top: 25px;
    }

    .btn-continue {
        background: white;
        border: 2px solid var(--primary);
        color: var(--primary);
        padding: 14px 30px;
        border-radius: 30px;
        font-weight: 600;
        text-decoration: none;
        transition: var(--transition);
        font-size: 15px;
        flex: 1;
        text-align: center;
    }

    .btn-continue:hover {
        background: var(--primary);
        color: white;
    }

    .btn-checkout {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
        color: var(--primary-dark);
        border: none;
        padding: 14px 40px;
        border-radius: 30px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        flex: 1;
        text-align: center;
    }

    .btn-checkout:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }

    .empty-cart {
        text-align: center;
        padding: 80px 40px;
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow);
    }

    .empty-cart i {
        font-size: 80px;
        color: var(--gray-light);
        margin-bottom: 20px;
    }

    .empty-cart h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 15px;
    }

    .empty-cart p {
        color: var(--gray);
        margin-bottom: 25px;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        border: none;
        padding: 14px 30px;
        border-radius: 30px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: var(--transition);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }

    /* ===== TOAST NOTIFICATION ===== */
    .toast-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 9999;
    }

    .custom-toast {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 16px 25px;
        border-radius: 16px;
        box-shadow: var(--shadow-xl);
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 10px;
        animation: slideIn 0.3s ease-out;
        min-width: 300px;
        font-size: 15px;
    }

    .custom-toast.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .custom-toast.error {
        background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* ===== LOADING OVERLAY ===== */
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .loading-overlay.active {
        display: flex;
    }

    .spinner-ring {
        width: 60px;
        height: 60px;
        border: 4px solid var(--gray-light);
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .cart-layout {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        .cart-sidebar {
            position: static;
            margin-top: 20px;
        }
    }

    @media (max-width: 768px) {
        .cart-page {
            padding: 20px 15px;
        }

        .hello-section {
            padding: 15px 20px;
            flex-direction: column;
            align-items: flex-start;
        }

        .hello-message {
            width: 100%;
        }

        .hello-offer {
            width: 100%;
            justify-content: center;
        }

        .cart-header h1 {
            font-size: 24px;
        }

        /* === REFONTE MOBILE : LIGNES HORIZONTALES === */
        .cart-table table, 
        .cart-table tbody, 
        .cart-table tr, 
        .cart-table td {
            display: block;
        }

        .cart-table thead {
            display: none;
        }

        .cart-table tr {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            padding: 15px;
            border-bottom: 1px solid var(--gray-light);
            background: white;
        }

        .cart-table td {
            padding: 0;
            border: none;
            flex: 0 0 auto;
        }

        /* Suppression des labels data-label */
        .cart-table td:before {
            display: none;
        }

        /* Ajustements des colonnes */
        .cart-table td[data-label="Product"] {
            flex: 2 1 200px;
            min-width: 150px;
        }

        .cart-table td[data-label="Unit Price"] {
            flex: 0 1 auto;
            margin-left: auto;
        }

        .cart-table td[data-label="Quantity"] {
            flex: 0 1 auto;
        }

        .cart-table td[data-label="Total"] {
            flex: 0 1 auto;
        }

        .cart-table td:last-child {
            flex: 0 0 auto;
        }

        .cart-product {
            gap: 10px;
        }

        .cart-product-image {
            width: 50px;
            height: 50px;
        }

        .cart-product-info h3 {
            font-size: 14px;
        }

        .cart-product-info p {
            font-size: 11px;
        }

        .cart-price, .cart-total-line {
            font-size: 14px;
        }

        .quantity-control button {
            width: 36px;
            height: 36px;
            font-size: 16px;
        }

        .quantity-control input {
            width: 40px;
            height: 36px;
            font-size: 14px;
        }

        .btn-remove {
            width: 36px;
            height: 36px;
            font-size: 18px;
        }

        .cart-actions {
            flex-direction: column;
        }

        .btn-continue, .btn-checkout {
            width: 100%;
            padding: 16px;
        }

        .toast-container {
            bottom: 20px;
            right: 20px;
            left: 20px;
        }
        .custom-toast {
            min-width: auto;
            width: 100%;
        }
    }
</style>

<!-- ===== TOAST CONTAINER ===== -->
<div class="toast-container" id="toastContainer"></div>

<!-- ===== PAGE PANIER ===== -->
<div class="cart-page">

    <!-- SECTION HELLO (accueil) -->
    <div class="hello-section">
        <div class="hello-message">
            <div class="hello-icon">
                <i class="bi bi-hand-index-thumb"></i>
            </div>
            <div class="hello-text">
                <h2>Hello! Ready to complete your order?</h2>
                <p>You're just a few steps away from receiving your products.</p>
            </div>
        </div>
        <div class="hello-offer">
            <i class="bi bi-gift"></i> Free shipping on orders over $100
        </div>
    </div>

    <div class="cart-header">
        <h1><i class="bi bi-cart-fill me-2"></i> My Cart</h1>
        <span class="badge" id="Badge">0</span><span class="badge">item(s)</span>
        
    </div>

    <div id="cartContent">
        <?php if (empty($lignes)): ?>
            <div class="empty-cart">
                <i class="bi bi-cart-x"></i>
                <h2>Your cart is empty</h2>
                <p>Discover our products and add them to your cart.</p>
                <a href="<?php echo base_url('boutique'); ?>" class="btn-primary">
                    <i class="bi bi-arrow-left me-2"></i> Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-main">
                    <div class="cart-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Unit Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lignes as $ligne): ?>
                                <tr data-ligne-id="<?php echo $ligne->id; ?>">
                                    <td data-label="Product">
                                        <div class="cart-product">
                                            <div class="cart-product-image">
                                                <img src="<?php echo $ligne->image_principale ? base_url('attachments/Produits/' . $ligne->image_principale) : 'https://placehold.co/200x200/0f4c3a/d4af37?text=AGF'; ?>" 
                                                     alt="<?php echo htmlspecialchars($ligne->nom_produit); ?>">
                                            </div>
                                            <div class="cart-product-info">
                                                <h3><?php echo htmlspecialchars($ligne->nom_produit); ?></h3>
                                                <?php if (!empty($ligne->code_produit)): ?>
                                                    <p>Code: <?php echo $ligne->code_produit; ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Unit Price" class="cart-price">
                                        <?php echo number_format($ligne->prix_unitaire_ht, 2); ?> USD
                                    </td>
                                    <td data-label="Quantity">
                                        <div class="cart-quantity">
                                            <div class="quantity-control">
                                                <button type="button" onclick="updateQuantity(<?php echo $ligne->id; ?>, <?php echo $ligne->quantite - 1; ?>)">−</button>
                                                <input type="number" value="<?php echo $ligne->quantite; ?>" min="1" max="99" readonly>
                                                <button type="button" onclick="updateQuantity(<?php echo $ligne->id; ?>, <?php echo $ligne->quantite + 1; ?>)">+</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Total" class="cart-total-line">
                                        <?php echo number_format($ligne->total_ligne_ttc, 2); ?> USD
                                    </td>
                                    <td>
                                        <button class="btn-remove" onclick="deleteLine(<?php echo $ligne->id; ?>)" title="Remove">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="cart-sidebar">
                    <div class="cart-summary">
                        <h3>Summary</h3>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span><?php echo number_format($total_ttc, 2); ?> USD</span>
                        </div>
                    </div>

                    <div class="process-card">
                        <h3><i class="bi bi-info-circle"></i> Order Process</h3>
                        <ul class="process-steps">
                            <li class="process-step">
                                <span class="step-icon"><i class="bi bi-cart-check"></i></span>
                                <div class="step-content">
                                    <div class="step-title">1. Review your cart</div>
                                    <div class="step-desc">Check items, quantities and prices.</div>
                                </div>
                            </li>
                            <li class="process-step">
                                <span class="step-icon"><i class="bi bi-person"></i></span>
                                <div class="step-content">
                                    <div class="step-title">2. Enter your details</div>
                                    <div class="step-desc">Provide shipping and contact info.</div>
                                </div>
                            </li>
                            <li class="process-step">
                                <span class="step-icon"><i class="bi bi-credit-card"></i></span>
                                <div class="step-content">
                                    <div class="step-title">3. Payment</div>
                                    <div class="step-desc">Choose payment method and confirm.</div>
                                </div>
                            </li>
                            <li class="process-step">
                                <span class="step-icon"><i class="bi bi-check-circle"></i></span>
                                <div class="step-content">
                                    <div class="step-title">4. Order confirmation</div>
                                    <div class="step-desc">Receive order summary and tracking.</div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="cart-actions">
                        <a href="<?php echo base_url('boutique'); ?>" class="btn-continue">
                            <i class="bi bi-arrow-left me-2"></i> Continue Shopping
                        </a>
                        <a href="<?php echo base_url('commande'); ?>" class="btn-checkout">
                            Proceed to Checkout <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// ============================================
// PANIER.JS - Gestion de la page panier (AJAX sans rechargement)
// Version améliorée : fluide, responsive, sans confirmation
// ============================================

(function() {
    'use strict';

    if (window.AGF_PanierPage_Loaded) return;
    window.AGF_PanierPage_Loaded = true;

    var BASE_URL = '<?php echo base_url(); ?>';

    function showToast(message, type) {
        var container = document.getElementById('toastContainer');
        if (!container) return;
        
        var toast = document.createElement('div');
        toast.className = 'custom-toast ' + (type || 'success');
        toast.innerHTML = '<i class="bi bi-' + (type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill') + ' fs-5"></i>' +
            '<div><div class="fw-bold">' + (type === 'success' ? 'Success' : 'Error') + '</div>' +
            '<div style="font-size: 14px;">' + message + '</div></div>';
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    function updateCartBadge(count) {
        var badge = document.getElementById('cartCountHeader');
        if (badge) badge.textContent = count + ' item' + (count > 1 ? 's' : '');
    }

    function updateTotal(totalFormatted) {
        var totalSpan = document.querySelector('.summary-row.total span:last-child');
        if (totalSpan) totalSpan.textContent = totalFormatted;
    }

    function updateLine(ligneId, newTotal, newQuantity) {
        var row = document.querySelector('tr[data-ligne-id="' + ligneId + '"]');
        if (!row) return;
        var totalCell = row.querySelector('.cart-total-line');
        if (totalCell) totalCell.textContent = newTotal;
        var qtyInput = row.querySelector('.quantity-control input');
        if (qtyInput) qtyInput.value = newQuantity;
    }

    function removeLine(ligneId) {
        var row = document.querySelector('tr[data-ligne-id="' + ligneId + '"]');
        if (row) row.remove();
    }

    function showEmptyCart() {
        var cartContent = document.getElementById('cartContent');
        if (cartContent) {
            cartContent.innerHTML = `
                <div class="empty-cart">
                    <i class="bi bi-cart-x"></i>
                    <h2>Your cart is empty</h2>
                    <p>Discover our products and add them to your cart.</p>
                    <a href="${BASE_URL}boutique" class="btn-primary">
                        <i class="bi bi-arrow-left me-2"></i> Continue Shopping
                    </a>
                </div>
            `;
        }
    }

    window.updateQuantity = function(ligneId, newQty) {
        if (newQty < 1) {
            deleteLine(ligneId);
            return;
        }

        fetch(BASE_URL + 'panier/update_quantity', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'ligne_id=' + encodeURIComponent(ligneId) + '&quantite=' + encodeURIComponent(newQty)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('Quantity updated', 'success');
                updateCartBadge(data.nb_articles);
                updateTotal(data.total_formatted);
                var updatedLigne = data.lignes.find(l => l.id == ligneId);
                if (updatedLigne) {
                    var totalLigneFormatted = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(updatedLigne.total_ligne_ttc) + ' USD';
                    updateLine(ligneId, totalLigneFormatted, updatedLigne.quantite);
                }
            } else {
                showToast(data.message || 'Error', 'error');
            }
        })
        .catch(() => showToast('Connection error', 'error'));
    };

    window.deleteLine = function(ligneId) {
        fetch(BASE_URL + 'panier/delete_line', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'ligne_id=' + encodeURIComponent(ligneId)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('Item removed', 'success');
                updateCartBadge(data.nb_articles);
                if (data.nb_articles > 0) {
                    updateTotal(data.total_formatted);
                    removeLine(ligneId);
                } else {
                    showEmptyCart();
                }
            } else {
                showToast(data.message || 'Error', 'error');
            }
        })
        .catch(() => showToast('Connection error', 'error'));
    };

})();





// ============================================
// SCRIPT GLOBAL DE MISE À JOUR DU BADGE PANIER
// ============================================
(function() {
    // Définir BASE_URL si ce n'est pas déjà fait
    if (typeof BASE_URL === 'undefined') {
        window.BASE_URL = '<?php echo base_url(); ?>';
    }

    // Fonction de mise à jour du badge (rendue globale pour être appelée depuis les autres pages)
    window.updateCartBadgeBadge = function() {
        fetch(BASE_URL + 'panier/get_cart')
            .then(response => response.json())
            .then(data => {
                var badge = document.getElementById('Badge');
                if (badge) {
                    badge.textContent = data.nb_articles || 0;
                }
            })
            .catch(error => console.error('Erreur mise à jour badge:', error));
    };

    // Première mise à jour immédiate
    window.updateCartBadgeBadge();

    // Mise à jour toutes les 2 secondes
    setInterval(window.updateCartBadgeBadge, 2000);
})();

</script>
<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
