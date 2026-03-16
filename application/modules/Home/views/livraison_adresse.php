<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
:root {
    --primary-green: #0B4F2E;
    --secondary-green: #1B7B4B;
    --accent-green: #27ae60;
    --jaune: #FFD700;
    --light-green: #2ecc71;
    --dark-bg: #0a3d24;
    --text-dark: #1a2e3f;
    --text-muted: #6c757d;
    --border-light: #e9ecef;
    --shadow-soft: 0 10px 30px rgba(0,0,0,0.05);
    --shadow-hover: 0 20px 40px rgba(0,0,0,0.1);
    --danger: #dc3545;
    --success: #28a745;
    --rouge: #E74C3C;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.checkout-page {
    padding: 60px 0;
    background: linear-gradient(135deg, #f8faf9 0%, #ffffff 100%);
    min-height: 100vh;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ============================================
   ÉTAPES DE COMMANDE
   ============================================ */
.checkout-steps {
    display: flex;
    justify-content: center;
    gap: 60px;
    margin-bottom: 50px;
    position: relative;
}

.checkout-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    width: 60%;
    height: 2px;
    background: var(--border-light);
    z-index: 0;
}

.step-item {
    text-align: center;
    position: relative;
    z-index: 2;
    background: white;
    padding: 0 20px;
}

.step-number {
    width: 40px;
    height: 40px;
    background: white;
    border: 2px solid var(--border-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    margin: 0 auto 10px;
    color: var(--text-muted);
    transition: var(--transition);
}

.step-item.active .step-number {
    background: var(--secondary-green);
    border-color: var(--secondary-green);
    color: white;
    box-shadow: 0 0 0 4px rgba(27, 123, 75, 0.2);
}

.step-item.completed .step-number {
    background: var(--secondary-green);
    border-color: var(--secondary-green);
    color: white;
}

.step-item.completed .step-number::after {
    content: '✓';
}

.step-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-muted);
    white-space: nowrap;
}

.step-item.active .step-label {
    color: var(--primary-green);
}

.step-item.completed .step-label {
    color: var(--secondary-green);
}

/* ============================================
   GRILLE PRINCIPALE
   ============================================ */
.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
    align-items: start;
}

/* ============================================
   FORMULAIRE ADRESSE
   ============================================ */
.address-form {
    background: white;
    border-radius: 30px;
    padding: 40px;
    box-shadow: var(--shadow-soft);
    border: 1px solid var(--border-light);
}

.form-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-green);
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-title i {
    color: var(--jaune);
    font-size: 28px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-group {
    margin-bottom: 5px;
}

.form-group.full-width {
    grid-column: span 2;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.form-label .required {
    color: var(--rouge);
}

.form-control {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid var(--border-light);
    border-radius: 15px;
    font-size: 15px;
    transition: var(--transition);
    background: white;
}

.form-control:focus {
    border-color: var(--secondary-green);
    outline: none;
    box-shadow: 0 0 0 4px rgba(27, 123, 75, 0.1);
}

.form-control:hover {
    border-color: var(--accent-green);
}

select.form-control {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 15px center;
    padding-right: 45px;
}

/* ============================================
   MÉTHODES DE LIVRAISON
   ============================================ */
.shipping-methods {
    margin: 40px 0;
    padding-top: 30px;
    border-top: 2px solid var(--border-light);
}

.shipping-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-green);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.shipping-title i {
    color: var(--jaune);
}

.shipping-method {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    border: 2px solid var(--border-light);
    border-radius: 15px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: var(--transition);
    background: white;
}

.shipping-method:hover {
    border-color: var(--accent-green);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.shipping-method.selected {
    border-color: var(--secondary-green);
    background: linear-gradient(145deg, rgba(27, 123, 75, 0.05), rgba(27, 123, 75, 0.02));
    box-shadow: 0 5px 20px rgba(27, 123, 75, 0.15);
}

.method-info {
    display: flex;
    align-items: center;
    gap: 15px;
    flex: 1;
}

.method-radio {
    width: 22px;
    height: 22px;
    accent-color: var(--secondary-green);
    cursor: pointer;
}

.method-details h4 {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.method-details p {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 5px;
}

.method-details p i {
    color: var(--success);
    font-size: 12px;
}

.method-price {
    font-weight: 800;
    color: var(--primary-green);
    font-size: 20px;
    white-space: nowrap;
}

/* ============================================
   BOUTONS NAVIGATION
   ============================================ */
.checkout-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid var(--border-light);
}

.btn-back {
    padding: 16px 32px;
    background: transparent;
    border: 2px solid var(--border-light);
    border-radius: 15px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-back:hover {
    border-color: var(--secondary-green);
    color: var(--secondary-green);
    background: rgba(27, 123, 75, 0.05);
}

.btn-next {
    padding: 16px 40px;
    background: linear-gradient(145deg, var(--secondary-green), var(--primary-green));
    color: white;
    border: none;
    border-radius: 15px;
    font-weight: 700;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: 0 10px 25px rgba(27, 123, 75, 0.3);
}

.btn-next:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(27, 123, 75, 0.4);
}

.btn-next i {
    font-size: 18px;
}

/* ============================================
   RÉSUMÉ COMMANDE (COLONNE DROITE)
   ============================================ */
.order-summary {
    background: white;
    border-radius: 30px;
    padding: 30px;
    box-shadow: var(--shadow-soft);
    position: sticky;
    top: 20px;
    border: 1px solid var(--border-light);
}

.summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--border-light);
}

.summary-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-green);
    display: flex;
    align-items: center;
    gap: 10px;
}

.summary-title i {
    color: var(--jaune);
}

.btn-modify {
    font-size: 13px;
    color: var(--secondary-green);
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-modify:hover {
    color: var(--primary-green);
}

/* Articles dynamiques */
.summary-items {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 15px 0;
    border-bottom: 1px solid var(--border-light);
    gap: 15px;
}

.summary-item:last-child {
    border-bottom: none;
}

.item-info {
    flex: 1;
}

.item-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 5px;
    line-height: 1.4;
}

.item-qty {
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 500;
}

.item-price {
    font-weight: 700;
    color: var(--primary-green);
    font-size: 15px;
    white-space: nowrap;
}

/* Totaux */
.summary-totals {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid var(--border-light);
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    font-size: 15px;
    color: var(--text-muted);
}

.total-row.discount {
    color: var(--success);
    font-weight: 600;
}

.total-row.grand-total {
    font-weight: 800;
    color: var(--primary-green);
    font-size: 20px;
    margin-top: 10px;
    padding-top: 15px;
    border-top: 2px solid var(--border-light);
}

.total-row.grand-total .amount {
    font-size: 24px;
}

/* Sécurité */
.security-badge {
    margin-top: 25px;
    padding: 15px;
    background: linear-gradient(145deg, #f8faf9, #ffffff);
    border-radius: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    border: 1px solid var(--border-light);
}

.security-icon {
    width: 40px;
    height: 40px;
    background: var(--jaune);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-green);
    font-size: 18px;
}

.security-text h5 {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 3px;
}

.security-text p {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
}

/* ============================================
   PANIER VIDE
   ============================================ */
.empty-checkout {
    text-align: center;
    padding: 80px 40px;
    background: white;
    border-radius: 30px;
    box-shadow: var(--shadow-soft);
    grid-column: 1 / -1;
}

.empty-checkout i {
    font-size: 80px;
    color: var(--border-light);
    margin-bottom: 30px;
}

.empty-checkout h2 {
    font-size: 28px;
    color: var(--text-dark);
    margin-bottom: 15px;
}

.empty-checkout p {
    font-size: 16px;
    color: var(--text-muted);
    margin-bottom: 30px;
}

/* ============================================
   NOTIFICATIONS
   ============================================ */
.toast-notification {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: var(--secondary-green);
    color: white;
    padding: 15px 25px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    z-index: 3000;
    animation: slideInRight 0.3s ease;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.toast-notification.error {
    background: var(--rouge);
}

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 1199px) {
    .checkout-grid {
        grid-template-columns: 1fr 350px;
    }
    
    .checkout-steps {
        gap: 40px;
    }
    
    .checkout-steps::before {
        width: 70%;
    }
}

@media (max-width: 991px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    
    .order-summary {
        position: static;
        margin-top: 30px;
    }
    
    .form-grid {
        gap: 15px;
    }
    
    .checkout-steps {
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .checkout-steps::before {
        display: none;
    }
    
    .step-item {
        padding: 0 10px;
    }
}

@media (max-width: 576px) {
    .checkout-page {
        padding: 30px 0;
    }
    
    .address-form {
        padding: 25px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-group.full-width {
        grid-column: span 1;
    }
    
    .checkout-actions {
        flex-direction: column-reverse;
        gap: 15px;
    }
    
    .btn-back, .btn-next {
        width: 100%;
        justify-content: center;
    }
    
    .shipping-method {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .method-price {
        align-self: flex-end;
    }
}
</style>



<style>
/* Vos styles existants... */

/* Styles supplémentaires pour l'autocomplete */
.autocomplete-container {
    position: relative;
}

.autocomplete-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    max-height: 250px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: var(--shadow-lg);
    margin-top: 4px;
    display: none;
}

.autocomplete-item {
    padding: 12px 16px;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item:hover,
.autocomplete-item:focus {
    background: var(--accent-bg);
    outline: none;
}

.autocomplete-item i {
    color: var(--primary-green);
    font-size: 14px;
    width: 20px;
}

/* Scrollbar personnalisée */
.autocomplete-results::-webkit-scrollbar {
    width: 6px;
}

.autocomplete-results::-webkit-scrollbar-track {
    background: var(--border);
    border-radius: 3px;
}

.autocomplete-results::-webkit-scrollbar-thumb {
    background: var(--primary-green);
    border-radius: 3px;
}

/* Animation pour la notification */
@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}
</style>

<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
:root {
    --primary-green: #0B4F2E;
    --secondary-green: #1B7B4B;
    --accent-green: #27ae60;
    --jaune: #FFD700;
    --light-green: #2ecc71;
    --dark-bg: #0a3d24;
    --text-dark: #1a2e3f;
    --text-muted: #6c757d;
    --border-light: #e9ecef;
    --shadow-soft: 0 10px 30px rgba(0,0,0,0.05);
    --shadow-hover: 0 20px 40px rgba(0,0,0,0.1);
    --danger: #dc3545;
    --success: #28a745;
    --rouge: #E74C3C;
    --warning: #ffc107;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.checkout-page {
    padding: 60px 0;
    background: linear-gradient(135deg, #f8faf9 0%, #ffffff 100%);
    min-height: 100vh;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ============================================
   ÉTAPES DE COMMANDE
   ============================================ */
.checkout-steps {
    display: flex;
    justify-content: center;
    gap: 60px;
    margin-bottom: 50px;
    position: relative;
}

.checkout-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    width: 60%;
    height: 2px;
    background: var(--border-light);
    z-index: 0;
}

.step-item {
    text-align: center;
    position: relative;
    z-index: 2;
    background: white;
    padding: 0 20px;
}

.step-number {
    width: 40px;
    height: 40px;
    background: white;
    border: 2px solid var(--border-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    margin: 0 auto 10px;
    color: var(--text-muted);
    transition: var(--transition);
}

.step-item.active .step-number {
    background: var(--secondary-green);
    border-color: var(--secondary-green);
    color: white;
    box-shadow: 0 0 0 4px rgba(27, 123, 75, 0.2);
}

.step-item.completed .step-number {
    background: var(--secondary-green);
    border-color: var(--secondary-green);
    color: white;
}

.step-item.completed .step-number::after {
    content: '✓';
}

.step-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-muted);
    white-space: nowrap;
}

.step-item.active .step-label {
    color: var(--primary-green);
}

.step-item.completed .step-label {
    color: var(--secondary-green);
}

/* ============================================
   GRILLE PRINCIPALE
   ============================================ */
.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
    align-items: start;
}

/* ============================================
   FORMULAIRE ADRESSE
   ============================================ */
.address-form {
    background: white;
    border-radius: 30px;
    padding: 40px;
    box-shadow: var(--shadow-soft);
    border: 1px solid var(--border-light);
}

.form-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-green);
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-title i {
    color: var(--jaune);
    font-size: 28px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-group {
    margin-bottom: 5px;
    position: relative;
}

.form-group.full-width {
    grid-column: span 2;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 8px;
    transition: var(--transition);
}

.form-label .required {
    color: var(--rouge);
}

/* Labels avec indicateur auto */
.form-label.auto-filled {
    color: var(--secondary-green);
}

.form-label.auto-filled::after {
    content: ' (auto)';
    font-size: 11px;
    color: var(--accent-green);
    font-weight: 500;
    margin-left: 4px;
}

.form-control {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid var(--border-light);
    border-radius: 15px;
    font-size: 15px;
    transition: var(--transition);
    background: white;
}

.form-control:focus {
    border-color: var(--secondary-green);
    outline: none;
    box-shadow: 0 0 0 4px rgba(27, 123, 75, 0.1);
}

.form-control:hover {
    border-color: var(--accent-green);
}

/* États de validation */
.form-control.is-valid {
    border-color: var(--success) !important;
    background-color: rgba(40, 167, 69, 0.05) !important;
}

.form-control.is-invalid {
    border-color: var(--danger) !important;
    background-color: rgba(220, 53, 69, 0.05) !important;
    animation: shake 0.5s ease;
}

.form-control.auto-completed {
    border-color: var(--accent-green) !important;
    background: linear-gradient(135deg, rgba(39, 174, 96, 0.1) 0%, rgba(39, 174, 96, 0.05) 100%) !important;
    color: var(--primary-green);
    font-weight: 500;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.invalid-feedback {
    display: none;
    color: var(--danger);
    font-size: 13px;
    margin-top: 6px;
    align-items: center;
    gap: 5px;
    animation: slideDown 0.3s ease;
}

.invalid-feedback.show {
    display: flex;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ============================================
   AUTOCOMPLETE PAYS AMÉLIORÉ
   ============================================ */
.autocomplete-container {
    position: relative;
}

.autocomplete-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 18px;
    z-index: 2;
    transition: var(--transition);
}

.autocomplete-wrapper:focus-within .input-icon {
    color: var(--secondary-green);
}

.autocomplete-input {
    width: 100%;
    padding: 14px 18px 14px 45px !important;
    border: 2px solid var(--border-light);
    border-radius: 15px;
    font-size: 15px;
    transition: var(--transition);
    background: white;
}

.autocomplete-input:focus {
    border-color: var(--secondary-green);
    outline: none;
    box-shadow: 0 0 0 4px rgba(27, 123, 75, 0.1);
}

.autocomplete-results {
    position: absolute;
    top: calc(100% + 5px);
    left: 0;
    right: 0;
    background: white;
    border: 2px solid var(--border-light);
    border-radius: 15px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.autocomplete-results.active {
    display: block;
    animation: fadeInDown 0.3s ease;
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.autocomplete-item {
    padding: 14px 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: var(--transition);
    border-bottom: 1px solid var(--border-light);
}

.autocomplete-item:last-child {
    border-bottom: none;
    border-radius: 0 0 13px 13px;
}

.autocomplete-item:first-child {
    border-radius: 13px 13px 0 0;
}

.autocomplete-item:hover,
.autocomplete-item.selected {
    background: linear-gradient(135deg, rgba(27, 123, 75, 0.1) 0%, rgba(27, 123, 75, 0.05) 100%);
}

.autocomplete-item-flag {
    font-size: 24px;
    width: 30px;
    text-align: center;
}

.autocomplete-item-info {
    flex: 1;
}

.autocomplete-item-name {
    font-weight: 600;
    color: var(--text-dark);
    font-size: 15px;
}

.autocomplete-item-details {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 2px;
    display: flex;
    gap: 15px;
}

.autocomplete-item-code {
    font-size: 12px;
    color: var(--secondary-green);
    background: rgba(27, 123, 75, 0.1);
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 600;
}

.autocomplete-no-results {
    padding: 20px;
    text-align: center;
    color: var(--text-muted);
    font-style: italic;
}

/* Indicateur de pays sélectionné */
.country-selected-indicator {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--success);
    font-size: 20px;
    display: none;
}

.country-selected-indicator.show {
    display: block;
    animation: bounceIn 0.5s ease;
}

@keyframes bounceIn {
    0% { transform: translateY(-50%) scale(0); }
    50% { transform: translateY(-50%) scale(1.2); }
    100% { transform: translateY(-50%) scale(1); }
}

/* ============================================
   CHAMPS AVEC INFO BULLE AUTO
   ============================================ */
.field-info {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--accent-green);
    font-size: 16px;
    cursor: help;
    display: none;
}

.field-info.show {
    display: block;
}

/* Tooltip pour info auto */
.auto-tooltip {
    position: absolute;
    bottom: calc(100% + 10px);
    left: 50%;
    transform: translateX(-50%);
    background: var(--secondary-green);
    color: white;
    padding: 8px 15px;
    border-radius: 10px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
    z-index: 100;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.auto-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: var(--secondary-green);
}

.field-info:hover .auto-tooltip {
    opacity: 1;
    visibility: visible;
}

/* ============================================
   MÉTHODES DE LIVRAISON
   ============================================ */
.shipping-methods {
    margin: 40px 0;
    padding-top: 30px;
    border-top: 2px solid var(--border-light);
}

.shipping-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-green);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.shipping-title i {
    color: var(--jaune);
}

.shipping-method {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    border: 2px solid var(--border-light);
    border-radius: 15px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: var(--transition);
    background: white;
}

.shipping-method:hover {
    border-color: var(--accent-green);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.shipping-method.selected {
    border-color: var(--secondary-green);
    background: linear-gradient(145deg, rgba(27, 123, 75, 0.05), rgba(27, 123, 75, 0.02));
    box-shadow: 0 5px 20px rgba(27, 123, 75, 0.15);
}

.method-info {
    display: flex;
    align-items: center;
    gap: 15px;
    flex: 1;
}

.method-radio {
    width: 22px;
    height: 22px;
    accent-color: var(--secondary-green);
    cursor: pointer;
}

.method-details h4 {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.method-details p {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 5px;
}

.method-details p i {
    color: var(--success);
    font-size: 12px;
}

.method-price {
    font-weight: 800;
    color: var(--primary-green);
    font-size: 20px;
    white-space: nowrap;
}

/* ============================================
   BOUTONS NAVIGATION
   ============================================ */
.checkout-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid var(--border-light);
}

.btn-back {
    padding: 16px 32px;
    background: transparent;
    border: 2px solid var(--border-light);
    border-radius: 15px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-back:hover {
    border-color: var(--secondary-green);
    color: var(--secondary-green);
    background: rgba(27, 123, 75, 0.05);
}

.btn-next {
    padding: 16px 40px;
    background: linear-gradient(145deg, var(--secondary-green), var(--primary-green));
    color: white;
    border: none;
    border-radius: 15px;
    font-weight: 700;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: 0 10px 25px rgba(27, 123, 75, 0.3);
}

.btn-next:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(27, 123, 75, 0.4);
}

.btn-next:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-next i {
    font-size: 18px;
}

/* ============================================
   RÉSUMÉ COMMANDE
   ============================================ */
.order-summary {
    background: white;
    border-radius: 30px;
    padding: 30px;
    box-shadow: var(--shadow-soft);
    position: sticky;
    top: 20px;
    border: 1px solid var(--border-light);
}

.summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--border-light);
}

.summary-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-green);
    display: flex;
    align-items: center;
    gap: 10px;
}

.summary-title i {
    color: var(--jaune);
}

.btn-modify {
    font-size: 13px;
    color: var(--secondary-green);
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-modify:hover {
    color: var(--primary-green);
}

.summary-items {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 15px 0;
    border-bottom: 1px solid var(--border-light);
    gap: 15px;
}

.summary-item:last-child {
    border-bottom: none;
}

.item-info {
    flex: 1;
}

.item-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 5px;
    line-height: 1.4;
}

.item-qty {
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 500;
}

.item-price {
    font-weight: 700;
    color: var(--primary-green);
    font-size: 15px;
    white-space: nowrap;
}

.summary-totals {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid var(--border-light);
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    font-size: 15px;
    color: var(--text-muted);
}

.total-row.discount {
    color: var(--success);
    font-weight: 600;
}

.total-row.grand-total {
    font-weight: 800;
    color: var(--primary-green);
    font-size: 20px;
    margin-top: 10px;
    padding-top: 15px;
    border-top: 2px solid var(--border-light);
}

.total-row.grand-total .amount {
    font-size: 24px;
}

.security-badge {
    margin-top: 25px;
    padding: 15px;
    background: linear-gradient(145deg, #f8faf9, #ffffff);
    border-radius: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    border: 1px solid var(--border-light);
}

.security-icon {
    width: 40px;
    height: 40px;
    background: var(--jaune);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-green);
    font-size: 18px;
}

.security-text h5 {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 3px;
}

.security-text p {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
}

/* ============================================
   PANIER VIDE & NOTIFICATIONS
   ============================================ */
.empty-checkout {
    text-align: center;
    padding: 80px 40px;
    background: white;
    border-radius: 30px;
    box-shadow: var(--shadow-soft);
    grid-column: 1 / -1;
}

.empty-checkout i {
    font-size: 80px;
    color: var(--border-light);
    margin-bottom: 30px;
}

.empty-checkout h2 {
    font-size: 28px;
    color: var(--text-dark);
    margin-bottom: 15px;
}

.empty-checkout p {
    font-size: 16px;
    color: var(--text-muted);
    margin-bottom: 30px;
}

.toast-notification {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: var(--secondary-green);
    color: white;
    padding: 15px 25px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    z-index: 3000;
    animation: slideInRight 0.3s ease;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.toast-notification.error {
    background: var(--rouge);
}

.toast-notification.warning {
    background: var(--warning);
    color: var(--text-dark);
}

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 1199px) {
    .checkout-grid {
        grid-template-columns: 1fr 350px;
    }
    
    .checkout-steps {
        gap: 40px;
    }
}

@media (max-width: 991px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    
    .order-summary {
        position: static;
        margin-top: 30px;
    }
    
    .checkout-steps {
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .checkout-steps::before {
        display: none;
    }
}

@media (max-width: 576px) {
    .checkout-page {
        padding: 30px 0;
    }
    
    .address-form {
        padding: 25px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-group.full-width {
        grid-column: span 1;
    }
    
    .checkout-actions {
        flex-direction: column-reverse;
        gap: 15px;
    }
    
    .btn-back, .btn-next {
        width: 100%;
        justify-content: center;
    }
    
    .autocomplete-results {
        max-height: 250px;
    }
}
</style>

<section class="checkout-page py-5">
    <div class="container">
        
        <!-- Étapes de commande -->
        <div class="checkout-steps mb-5">
            <div class="step-item active">
                <div class="step-number">1</div>
                <div class="step-label">Adresse</div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-label">Connexion</div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-label">Paiement</div>
            </div>
            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>

        <?php if (empty($cart_items)): ?>
            <!-- Panier vide -->
            <div class="empty-checkout text-center py-5">
                <div class="display-1 text-muted mb-4">
                    <i class="bi bi-cart-x"></i>
                </div>
                <h2 class="h3 mb-3">Votre panier est vide</h2>
                <p class="text-muted mb-4">Ajoutez des produits pour passer commande</p>
                <a href="<?= base_url('boutique') ?>" class="btn btn-success btn-lg">
                    <i class="bi bi-shop me-2"></i> Continuer les achats
                </a>
            </div>
        <?php else: ?>

        <div class="row g-4">
            <!-- Formulaire adresse - Colonne gauche -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                            <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3">
                                <i class="bi bi-geo-alt-fill text-warning fs-4"></i>
                            </div>
                            <div>
                                <h3 class="h5 mb-1">Adresse de livraison</h3>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-lock me-1"></i> Vos données sont sécurisées
                                </p>
                            </div>
                        </div>

                        <form id="checkoutForm" action="<?= base_url('Home/checkout/save_address') ?>" method="POST">
                           
                            
                            <div class="row g-3">
                                <!-- Prénom -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person text-success me-1"></i> Prénom <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-person"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               name="firstname" 
                                               id="firstname" 
                                               required 
                                               placeholder="Jean"
                                               value="<?= $this->session->userdata('checkout_address') ? $this->session->userdata('checkout_address')['firstname'] : '' ?>">
                                    </div>
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i> Le prénom est requis
                                    </div>
                                </div>
                                
                                <!-- Nom -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person-badge text-success me-1"></i> Nom <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-person-badge"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               name="lastname" 
                                               id="lastname" 
                                               required 
                                               placeholder="Dupont"
                                               value="<?= $this->session->userdata('checkout_address') ? $this->session->userdata('checkout_address')['lastname'] : '' ?>">
                                    </div>
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i> Le nom est requis
                                    </div>
                                </div>

                                <!-- PAYS AVEC AUTOCOMPLETE -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-globe text-success me-1"></i> Pays <span class="text-danger">*</span>
                                    </label>
                                    <div class="autocomplete-container">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="bi bi-globe"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="country_search" 
                                                   placeholder="Cherchez votre pays..." 
                                                   autocomplete="off"
                                                   value="<?= $this->session->userdata('checkout_address') ? $this->session->userdata('checkout_address')['country_name'] ?? '' : '' ?>"
                                                   required>
                                        </div>
                                        <input type="hidden" name="country_code" id="country_code" 
                                               value="<?= $this->session->userdata('checkout_address') ? $this->session->userdata('checkout_address')['country_code'] ?? '' : '' ?>">
                                        <input type="hidden" name="country_name" id="country_name" 
                                               value="<?= $this->session->userdata('checkout_address') ? $this->session->userdata('checkout_address')['country_name'] ?? '' : '' ?>">
                                        <input type="hidden" name="country_capital" id="country_capital" 
                                               value="<?= $this->session->userdata('checkout_address') ? $this->session->userdata('checkout_address')['country_capital'] ?? '' : '' ?>">
                                        <input type="hidden" name="country_phone_code" id="country_phone_code" 
                                               value="<?= $this->session->userdata('checkout_address') ? $this->session->userdata('checkout_address')['country_phone_code'] ?? '' : '' ?>">
                                        <div id="autocomplete_list" class="autocomplete-results"></div>
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i> Veuillez sélectionner un pays
                                        </div>
                                    </div>
                                </div>

                                <!-- Adresse -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-house-door text-success me-1"></i> Adresse <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-house-door"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               name="address" 
                                               id="address" 
                                               placeholder="123 rue de la Paix" 
                                               required
                                               value="<?= $this->session->userdata('checkout_address') ? $this->session->userdata('checkout_address')['address'] : '' ?>">
                                    </div>
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i> L'adresse est requise
                                    </div>
                                </div>

                                <!-- Ville et Code postal -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-building text-success me-1"></i> Ville <span class="text-danger">*</span>
                                    </label>
                                    <div class="autocomplete-container">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="bi bi-building"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="city" 
                                                   id="city" 
                                                   placeholder="Paris" 
                                                   required
                                                   autocomplete="off"
                                                   value="<?= $this->session->userdata('checkout_address') ? $this->session->userdata('checkout_address')['city'] : '' ?>">
                                        </div>
                                        <div id="city_autocomplete_list" class="autocomplete-results"></div>
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i> La ville est requise
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-mailbox text-success me-1"></i> Code postal <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-mailbox"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               name="zipcode" 
                                               id="zipcode" 
                                               placeholder="75000" 
                                               required
                                               value="<?= $this->session->userdata('checkout_address') ? $this->session->userdata('checkout_address')['zipcode'] : '' ?>">
                                    </div>
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i> Le code postal est requis
                                    </div>
                                </div>

                                <!-- Téléphone -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-telephone text-success me-1"></i> Téléphone <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-telephone"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="phone_code_display" 
                                               placeholder="+33" 
                                               readonly
                                               style="max-width: 100px; background-color: #f8f9fa;">
                                        <input type="tel" 
                                               class="form-control" 
                                               name="phone" 
                                               id="phone" 
                                               placeholder="6 12 34 56 78" 
                                               required
                                               value="<?= $this->session->userdata('checkout_address') ? preg_replace('/^\+\d+\s*/', '', $this->session->userdata('checkout_address')['phone']) : '' ?>">
                                    </div>
                                    <div class="invalid-feedback" id="phone-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i> Le numéro de téléphone est requis
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-envelope text-success me-1"></i> Email <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-envelope"></i>
                                        </span>
                                        <input type="email" 
                                               class="form-control" 
                                               name="email" 
                                               id="email" 
                                               placeholder="jean.dupont@email.com" 
                                               required
                                               value="<?= $this->session->userdata('checkout_address') ? $this->session->userdata('checkout_address')['email'] : '' ?>">
                                    </div>
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i> Email invalide
                                    </div>
                                </div>
                            </div>

                            <!-- Méthodes de livraison -->
                            <div class="mt-5 pt-4 border-top">
                                <h4 class="h6 fw-semibold mb-3">
                                    <i class="bi bi-truck text-success me-2"></i> Mode de livraison
                                </h4>
                                
                                <?php
                                $shipping_methods = [
                                    'standard' => ['label' => 'Livraison standard', 'price' => 5.90, 'desc' => 'Sous 2-3 jours ouvrés', 'icon' => 'bi-truck'],
                                    'express' => ['label' => 'Livraison express', 'price' => 12.90, 'desc' => 'Sous 24h (jours ouvrés)', 'icon' => 'bi-lightning'],
                                    'pointrelais' => ['label' => 'Point relais', 'price' => 3.90, 'desc' => 'Retrait en point relais sous 3-4 jours', 'icon' => 'bi-shop']
                                ];
                                
                                $saved_shipping = $this->session->userdata('checkout_address') ? $this->session->userdata('checkout_address')['shipping'] : 'standard';
                                
                                foreach ($shipping_methods as $key => $method): 
                                ?>
                                <div class="shipping-method card mb-2 <?= ($saved_shipping == $key) ? 'border-success' : '' ?>" 
                                     data-price="<?= $method['price'] ?>" 
                                     data-method="<?= $key ?>"
                                     style="cursor: pointer;">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="form-check me-3">
                                                <input type="radio" 
                                                       name="shipping" 
                                                       value="<?= $key ?>" 
                                                       class="form-check-input" 
                                                       id="shipping_<?= $key ?>"
                                                       <?= ($saved_shipping == $key) ? 'checked' : '' ?> 
                                                       onchange="selectShipping(this)">
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi <?= $method['icon'] ?> text-success me-2"></i>
                                                    <div>
                                                        <h5 class="h6 mb-1"><?= $method['label'] ?></h5>
                                                        <p class="small text-muted mb-0">
                                                            <i class="bi bi-check-circle-fill text-success me-1"></i> <?= $method['desc'] ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ms-3">
                                                <span class="fw-bold text-success"><?= number_format($method['price'], 2, ',', ' ') ?> €</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <a href="<?= base_url('panier') ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i> Retour au panier
                                </a>
                                <button type="submit" class="btn btn-success px-5" id="submitBtn">
                                    Continuer <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Résumé commande - Colonne droite -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="h6 fw-semibold mb-0">
                                <i class="bi bi-receipt text-success me-2"></i> Votre commande
                            </h4>
                            <a href="<?= base_url('panier') ?>" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-pencil me-1"></i> Modifier
                            </a>
                        </div>
                        
                        <!-- Articles -->
                        <div class="summary-items mb-4" style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($cart_items as $item): 
                                $total_ttc = $item['total_ligne_ttc'] ?? ($item['prix_unitaire_ht'] * $item['quantite'] * (1 + $item['taux_tva']/100));
                                $nom = $item['nom'] ?? 'Produit';
                                
                                // Déterminer l'emoji
                                $emoji = '📦';
                                $nom_lower = strtolower($nom);
                                if (strpos($nom_lower, 'masque') !== false) $emoji = '😷';
                                elseif (strpos($nom_lower, 'pizza') !== false) $emoji = '🍕';
                                elseif (strpos($nom_lower, 'artemisia') !== false) $emoji = '🌿';
                                elseif (strpos($nom_lower, 'moringa') !== false) $emoji = '🍃';
                                elseif (strpos($nom_lower, 'savon') !== false) $emoji = '🧼';
                                elseif (strpos($nom_lower, 'baobab') !== false) $emoji = '🥥';
                            ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <div class="fw-semibold small"><?= $emoji ?> <?= htmlspecialchars($nom) ?></div>
                                    <small class="text-muted">Quantité: <?= $item['quantite'] ?></small>
                                </div>
                                <span class="fw-semibold text-success"><?= number_format($total_ttc, 2, ',', ' ') ?> €</span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Totaux -->
                        <div class="bg-light p-3 rounded-3">
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Sous-total HT</span>
                                <span id="subtotalHT"><?= number_format($totals['total_ht'], 2, ',', ' ') ?> €</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">TVA</span>
                                <span id="totalTVA"><?= number_format($totals['total_tva'], 2, ',', ' ') ?> €</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-3">
                                <span class="text-muted">Livraison</span>
                                <span id="shippingCost"><?= number_format($shipping_ht * 1.055, 2, ',', ' ') ?> €</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold fs-5 pt-2 border-top">
                                <span>Total TTC</span>
                                <span class="text-success" id="grandTotal"><?= number_format($grand_total_ttc, 2, ',', ' ') ?> €</span>
                            </div>
                        </div>

                        <!-- Badge sécurité -->
                        <div class="d-flex align-items-center mt-4 p-3 bg-success bg-opacity-10 rounded-3">
                            <div class="bg-success text-white rounded-circle p-2 me-3">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <div>
                                <h5 class="h6 mb-1">Paiement sécurisé</h5>
                                <p class="small text-muted mb-0">Vos données sont protégées par SSL 256-bit</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* Styles personnalisés pour l'autocomplete */
.autocomplete-container {
    position: relative;
}

.autocomplete-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    max-height: 250px;
    overflow-y: auto;
    z-index: 1050;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    margin-top: 0.25rem;
    display: none;
}

.autocomplete-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: all 0.2s;
    border-bottom: 1px solid #f1f1f1;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item:hover,
.autocomplete-item:focus {
    background: #f8f9fa;
    outline: none;
}

.autocomplete-item i {
    color: #198754;
    font-size: 1rem;
    width: 1.25rem;
}

/* Scrollbar personnalisée */
.autocomplete-results::-webkit-scrollbar {
    width: 6px;
}

.autocomplete-results::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.autocomplete-results::-webkit-scrollbar-thumb {
    background: #198754;
    border-radius: 3px;
}

/* Validation styles */
.form-control.is-valid,
.form-select.is-valid {
    border-color: #198754;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

/* Shipping method hover */
.shipping-method {
    transition: all 0.2s;
}

.shipping-method:hover {
    border-color: #198754;
    background-color: #f8f9fa;
}

.shipping-method.border-success {
    background-color: #f0fff4;
}

/* Toast notification */
.toast-notification {
    position: fixed;
    bottom: 1rem;
    right: 1rem;
    padding: 1rem 1.5rem;
    background: white;
    border-left: 4px solid #198754;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    z-index: 1100;
    animation: slideInRight 0.3s ease;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.toast-notification.error {
    border-left-color: #dc3545;
}

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

/* Checkout steps */
.checkout-steps {
    display: flex;
    justify-content: center;
    gap: 2rem;
}

.step-item {
    text-align: center;
    position: relative;
}

.step-number {
    width: 2.5rem;
    height: 2.5rem;
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin: 0 auto 0.5rem;
    color: #6c757d;
    transition: all 0.2s;
}

.step-item.active .step-number {
    background: #198754;
    border-color: #198754;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
}

.step-item.active .step-label {
    color: #198754;
}

/* Responsive */
@media (max-width: 768px) {
    .checkout-steps {
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .step-item {
        width: calc(50% - 0.5rem);
    }
}
</style>

<script>
// ==========================================
// DONNÉES PHP VERS JAVASCRIPT
// ==========================================
const countries = <?= json_encode($pays ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

// Créer un dictionnaire des pays
const countriesMap = {};
countries.forEach(c => {
    if (c.pays) {
        countriesMap[c.pays.toLowerCase()] = c;
    }
});

// ==========================================
// CONFIGURATION
// ==========================================
const CONFIG = {
    baseUrl: '<?= base_url() ?>',
    shippingMethods: {
        standard: { price: 5.90, label: 'Livraison standard' },
        express: { price: 12.90, label: 'Livraison express' },
        pointrelais: { price: 3.90, label: 'Point relais' }
    }
};

let currentShipping = '<?= $this->session->userdata('checkout_address') ? $this->session->userdata('checkout_address')['shipping'] : 'standard' ?>';
let selectedCountry = null;

// ==========================================
// INITIALISATION
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    initAutocomplete();
    initCityAutocomplete();
    initShippingMethods();
    initFormValidation();
    restoreFromSession();
});

function restoreFromSession() {
    const countryName = document.getElementById('country_name')?.value;
    if (countryName && countriesMap[countryName.toLowerCase()]) {
        selectedCountry = countriesMap[countryName.toLowerCase()];
        updatePhoneCode(selectedCountry);
    }
}

// ==========================================
// AUTOCOMPLETE PAYS
// ==========================================
function initAutocomplete() {
    const searchInput = document.getElementById('country_search');
    const resultsList = document.getElementById('autocomplete_list');
    const countryCodeInput = document.getElementById('country_code');
    const countryNameInput = document.getElementById('country_name');
    const countryCapitalInput = document.getElementById('country_capital');
    const countryPhoneCodeInput = document.getElementById('country_phone_code');

    if (!searchInput || !resultsList) return;

    searchInput.addEventListener('input', function() {
        const val = this.value.toLowerCase().trim();
        resultsList.innerHTML = '';
        
        if (val.length < 2) { 
            resultsList.style.display = 'none'; 
            return; 
        }

        const filtered = countries.filter(c => 
            c.pays && c.pays.toLowerCase().includes(val)
        ).slice(0, 8);
        
        if (filtered.length > 0) {
            filtered.forEach(c => {
                const div = document.createElement('div');
                div.className = 'autocomplete-item';
                div.innerHTML = `<i class="bi bi-flag"></i> <strong>${escapeHtml(c.pays)}</strong>`;
                
                div.onclick = () => selectCountry(c, searchInput, countryCodeInput, countryNameInput, countryCapitalInput, countryPhoneCodeInput, resultsList);
                div.onkeypress = (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        selectCountry(c, searchInput, countryCodeInput, countryNameInput, countryCapitalInput, countryPhoneCodeInput, resultsList);
                    }
                };
                
                resultsList.appendChild(div);
            });
            resultsList.style.display = 'block';
        } else {
            resultsList.style.display = 'none';
        }
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsList.contains(e.target)) {
            resultsList.style.display = 'none';
        }
    });

    searchInput.addEventListener('blur', () => {
        setTimeout(() => {
            if (!searchInput.value.trim()) {
                searchInput.classList.add('is-invalid');
                clearCountryFields();
            }
        }, 200);
    });
}

function selectCountry(country, searchInput, codeInput, nameInput, capitalInput, phoneCodeInput, resultsList) {
    searchInput.value = country.pays;
    codeInput.value = country.ISO_3166_1_2_Letter_Code || country.id_country || country.id;
    nameInput.value = country.pays;
    capitalInput.value = country.Capital || '';
    phoneCodeInput.value = country.ITU_T_Telephone_Code || '';
    
    resultsList.style.display = 'none';
    searchInput.classList.remove('is-invalid');
    searchInput.classList.add('is-valid');
    
    selectedCountry = country;
    updatePhoneCode(country);
    suggestCapital(country.Capital);
    showNotification(`Pays sélectionné: ${country.pays}`);
}

function clearCountryFields() {
    document.getElementById('country_code').value = '';
    document.getElementById('country_name').value = '';
    document.getElementById('country_capital').value = '';
    document.getElementById('country_phone_code').value = '';
    document.getElementById('phone_code_display').value = '';
    selectedCountry = null;
}

// ==========================================
// AUTOCOMPLETE VILLE
// ==========================================
function initCityAutocomplete() {
    const cityInput = document.getElementById('city');
    const cityResultsList = document.getElementById('city_autocomplete_list');
    
    if (!cityInput || !cityResultsList) return;
    
    cityInput.addEventListener('input', function() {
        const val = this.value.toLowerCase().trim();
        cityResultsList.innerHTML = '';
        
        if (val.length < 2 || !selectedCountry?.Capital) { 
            cityResultsList.style.display = 'none'; 
            return; 
        }
        
        if (selectedCountry.Capital.toLowerCase().includes(val)) {
            const div = document.createElement('div');
            div.className = 'autocomplete-item';
            div.innerHTML = `<i class="bi bi-building"></i> <strong>${escapeHtml(selectedCountry.Capital)}</strong> <small class="text-muted">(Capitale)</small>`;
            
            div.onclick = () => {
                cityInput.value = selectedCountry.Capital;
                cityResultsList.style.display = 'none';
                cityInput.classList.add('is-valid');
                cityInput.classList.remove('is-invalid');
            };
            
            cityResultsList.appendChild(div);
            cityResultsList.style.display = 'block';
        }
    });
    
    document.addEventListener('click', (e) => {
        if (!cityInput.contains(e.target) && !cityResultsList.contains(e.target)) {
            cityResultsList.style.display = 'none';
        }
    });
}

function suggestCapital(capital) {
    if (capital) {
        const cityInput = document.getElementById('city');
        if (!cityInput.value.trim()) {
            cityInput.value = capital;
            cityInput.classList.add('is-valid');
        }
    }
}

// ==========================================
// GESTION TÉLÉPHONE
// ==========================================
function updatePhoneCode(country) {
    const phoneCodeDisplay = document.getElementById('phone_code_display');
    if (country?.ITU_T_Telephone_Code) {
        phoneCodeDisplay.value = country.ITU_T_Telephone_Code;
    }
}

// ==========================================
// GESTION LIVRAISON
// ==========================================
function initShippingMethods() {
    document.querySelectorAll('.shipping-method').forEach(method => {
        method.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            selectShipping(radio);
        });
    });
}

function selectShipping(radio) {
    document.querySelectorAll('.shipping-method').forEach(m => {
        m.classList.remove('border-success');
    });
    radio.closest('.shipping-method').classList.add('border-success');
    currentShipping = radio.value;
    updateShippingPrice();
}

function updateShippingPrice() {
    const price = CONFIG.shippingMethods[currentShipping].price;
    const shippingTTC = price * 1.055;
    document.getElementById('shippingCost').textContent = formatPrice(shippingTTC);
    
    const subtotalTTC = <?= $totals['total_ttc'] ?>;
    document.getElementById('grandTotal').textContent = formatPrice(subtotalTTC + shippingTTC);
}

function formatPrice(price) {
    return price.toFixed(2).replace('.', ',') + ' €';
}

// ==========================================
// VALIDATION FORMULAIRE
// ==========================================
function validateField(input) {
    if (!input.value.trim()) {
        input.classList.add('is-invalid');
        return false;
    }
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    return true;
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function initFormValidation() {
    const form = document.getElementById('checkoutForm');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        let isValid = true;
        
        // Validation des champs
        const fields = ['firstname', 'lastname', 'address', 'zipcode', 'city'];
        fields.forEach(id => {
            const input = document.getElementById(id);
            if (!validateField(input)) isValid = false;
        });
        
        // Validation email
        const email = document.getElementById('email');
        if (!email.value.trim() || !validateEmail(email.value)) {
            email.classList.add('is-invalid');
            isValid = false;
        }
        
        // Validation téléphone
        const phone = document.getElementById('phone');
        if (!phone.value.trim()) {
            phone.classList.add('is-invalid');
            isValid = false;
        }
        
        // Validation pays
        if (!document.getElementById('country_code').value) {
            document.getElementById('country_search').classList.add('is-invalid');
            isValid = false;
        }
        
        if (!isValid) {
            showNotification('Veuillez corriger les erreurs', 'error');
            return;
        }
        
        // Soumission
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Traitement...';
        
        try {
            const phoneCode = document.getElementById('phone_code_display').value;
            const fullPhone = phoneCode + ' ' + document.getElementById('phone').value;
            
            const formData = new FormData(form);
            formData.set('phone', fullPhone);
            
            const response = await fetch(CONFIG.baseUrl + 'checkout/save_address', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.href = CONFIG.baseUrl + 'checkout/payment';
            } else {
                showNotification(data.message || 'Erreur', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Continuer <i class="bi bi-arrow-right ms-2"></i>';
            }
        } catch (error) {
            showNotification('Erreur de connexion', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Continuer <i class="bi bi-arrow-right ms-2"></i>';
        }
    });
}

// ==========================================
// NOTIFICATIONS
// ==========================================
function showNotification(message, type = 'success') {
    const existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();
    
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}-fill fs-5"></i>
        <span>${escapeHtml(message)}</span>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>