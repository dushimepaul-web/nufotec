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
   CONTENEUR AUTHENTIFICATION
   ============================================ */
.auth-container {
    max-width: 800px;
    margin: 0 auto;
}

.auth-box {
    background: white;
    border-radius: 30px;
    padding: 40px;
    box-shadow: var(--shadow-soft);
    border: 1px solid var(--border-light);
}

/* ============================================
   UTILISATEUR CONNECTÉ (AFFICHAGE COMPTE)
   ============================================ */
.user-logged-in {
    text-align: center;
    padding: 20px 0;
}

.user-avatar {
    width: 100px;
    height: 100px;
    background: linear-gradient(145deg, var(--secondary-green), var(--primary-green));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    font-size: 50px;
    color: white;
    box-shadow: 0 10px 30px rgba(27, 123, 75, 0.3);
}

.user-name {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.user-email {
    font-size: 14px;
    color: var(--text-muted);
    margin-bottom: 30px;
}

.user-info-box {
    background: #f8faf9;
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
    text-align: left;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-light);
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: var(--text-dark);
}

.info-value {
    color: var(--text-muted);
}

.btn-continue-logged {
    width: 100%;
    padding: 18px;
    background: linear-gradient(145deg, var(--secondary-green), var(--primary-green));
    color: white;
    border: none;
    border-radius: 15px;
    font-size: 18px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: 0 10px 25px rgba(27, 123, 75, 0.3);
    text-decoration: none;
}

.btn-continue-logged:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(27, 123, 75, 0.4);
}

.btn-logout {
    margin-top: 15px;
    padding: 12px 30px;
    background: transparent;
    border: 2px solid var(--border-light);
    border-radius: 10px;
    color: var(--text-muted);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-logout:hover {
    border-color: var(--rouge);
    color: var(--rouge);
    background: #fff5f5;
}

/* ============================================
   FORMULAIRES (INVITÉ) - PAR DÉFAUT VISIBLE
   ============================================ */
.auth-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 2px solid var(--border-light);
    padding-bottom: 15px;
}

.auth-tab {
    flex: 1;
    padding: 15px;
    background: transparent;
    border: 2px solid transparent;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    color: var(--text-muted);
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.auth-tab:hover {
    color: var(--secondary-green);
    background: rgba(27, 123, 75, 0.05);
}

.auth-tab.active {
    background: var(--secondary-green);
    color: white;
    border-color: var(--secondary-green);
}

/* Par défaut, le formulaire de connexion est visible */
.auth-form {
    display: none; /* Caché par défaut, JS gère l'affichage */
}

.auth-form.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

/* Le formulaire de connexion est actif par défaut */
#loginForm {
    display: block; /* VISIBLE PAR DÉFAUT */
}

#loginForm.hidden {
    display: none;
}

#registerForm {
    display: none; /* Caché par défaut */
}

#registerForm.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
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
}

.form-group {
    margin-bottom: 20px;
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
}

.form-control:focus {
    border-color: var(--secondary-green);
    outline: none;
    box-shadow: 0 0 0 4px rgba(27, 123, 75, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

/* Checkbox se souvenir */
.remember-me {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 25px;
    cursor: pointer;
}

.remember-me input[type="checkbox"] {
    width: 20px;
    height: 20px;
    accent-color: var(--secondary-green);
    cursor: pointer;
}

.remember-me span {
    font-size: 14px;
    color: var(--text-dark);
}

/* Mot de passe oublié */
.forgot-password {
    text-align: right;
    margin-bottom: 25px;
}

.forgot-password a {
    font-size: 14px;
    color: var(--secondary-green);
    text-decoration: none;
    font-weight: 600;
}

.forgot-password a:hover {
    text-decoration: underline;
}

/* Boutons */
.btn-submit {
    width: 100%;
    padding: 16px;
    background: linear-gradient(145deg, var(--secondary-green), var(--primary-green));
    color: white;
    border: none;
    border-radius: 15px;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: 0 10px 25px rgba(27, 123, 75, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(27, 123, 75, 0.4);
}

.btn-guest {
    width: 100%;
    padding: 16px;
    background: transparent;
    border: 2px solid var(--border-light);
    border-radius: 15px;
    font-size: 16px;
    font-weight: 600;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    transition: var(--transition);
    margin-top: 15px;
    text-decoration: none;
}

.btn-guest:hover {
    border-color: var(--secondary-green);
    color: var(--secondary-green);
    background: rgba(27, 123, 75, 0.05);
}

/* Séparateur */
.divider {
    display: flex;
    align-items: center;
    margin: 30px 0;
    color: var(--text-muted);
    font-size: 14px;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border-light);
}

.divider span {
    padding: 0 15px;
}

/* Sécurité */
.security-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid var(--border-light);
    font-size: 14px;
    color: var(--text-muted);
}

.security-note i {
    color: var(--secondary-green);
}

/* ============================================
   RÉSUMÉ COMMANDE (COLONNE DROITE)
   ============================================ */
.order-summary {
    background: white;
    border-radius: 30px;
    padding: 30px;
    box-shadow: var(--shadow-soft);
    border: 1px solid var(--border-light);
    margin-top: 30px;
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

.summary-items {
    max-height: 200px;
    overflow-y: auto;
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-light);
    gap: 15px;
}

.item-info {
    flex: 1;
}

.item-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 3px;
}

.item-qty {
    font-size: 12px;
    color: var(--text-muted);
}

.item-price {
    font-weight: 700;
    color: var(--primary-green);
    font-size: 14px;
}

.summary-totals {
    padding-top: 15px;
    border-top: 2px solid var(--border-light);
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
    color: var(--text-muted);
}

.total-row.grand-total {
    font-weight: 800;
    color: var(--primary-green);
    font-size: 18px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid var(--border-light);
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
@media (max-width: 768px) {
    .checkout-steps {
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .checkout-steps::before {
        display: none;
    }
    
    .auth-box {
        padding: 25px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .auth-tab {
        font-size: 14px;
        padding: 12px;
    }
}
</style>

<section class="checkout-page">
    <div class="container">
        
        <!-- Étapes de commande -->
        <div class="checkout-steps">
            <div class="step-item completed">
                <div class="step-number">✓</div>
                <div class="step-label">Adresse</div>
            </div>
            <div class="step-item active">
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

        <div class="auth-container">
            <div class="auth-box" id="authBox">
                
                <!-- PAR DÉFAUT: Formulaire de connexion visible immédiatement -->
                <div id="guestForms">
                    <div class="auth-tabs">
                        <button class="auth-tab active" onclick="switchTab('login')" id="tab-login">
                            <i class="bi bi-box-arrow-in-right"></i> Connexion
                        </button>
                        <button class="auth-tab" onclick="switchTab('register')" id="tab-register">
                            <i class="bi bi-person-plus"></i> Inscription
                        </button>
                    </div>
                    
                    <!-- FORMULAIRE CONNEXION - VISIBLE PAR DÉFAUT -->
                    <div class="auth-form" id="loginForm">
                        <div class="form-title">
                            <i class="bi bi-person"></i>
                            Déjà client
                        </div>
                        
                        <form onsubmit="handleLogin(event)">
                            <div class="form-group">
                                <label class="form-label">Email <span class="required">*</span></label>
                                <input type="email" class="form-control" name="email" required placeholder="votre@email.com" value="">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Mot de passe <span class="required">*</span></label>
                                <input type="password" class="form-control" name="password" required placeholder="••••••••">
                            </div>
                            
                            <div class="forgot-password">
                                <a href="<?= base_url('auth/forgot-password') ?>">Mot de passe oublié ?</a>
                            </div>
                            
                            <label class="remember-me">
                                <input type="checkbox" name="remember">
                                <span>Se souvenir de moi</span>
                            </label>
                            
                            <button type="submit" class="btn-submit">
                                <i class="bi bi-box-arrow-in-right"></i> Se connecter
                            </button>
                        </form>
                        
                        <div class="divider"><span>ou</span></div>
                        
                        <a href="<?= base_url('checkout/payment') ?>" class="btn-guest">
                            Continuer sans compte <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    
                    <!-- FORMULAIRE INSCRIPTION - CACHÉ PAR DÉFAUT -->
                    <div class="auth-form" id="registerForm" style="display: none;">
                        <div class="form-title">
                            <i class="bi bi-person-plus"></i>
                            Nouveau client
                        </div>
                        
                        <form onsubmit="handleRegister(event)">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Prénom <span class="required">*</span></label>
                                    <input type="text" class="form-control" name="firstname" required placeholder="Jean">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nom <span class="required">*</span></label>
                                    <input type="text" class="form-control" name="lastname" required placeholder="Dupont">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Email <span class="required">*</span></label>
                                <input type="email" class="form-control" name="email" required placeholder="jean.dupont@email.com">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Téléphone <span class="required">*</span></label>
                                <input type="tel" class="form-control" name="phone" required placeholder="+33 6 12 34 56 78">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Mot de passe <span class="required">*</span></label>
                                    <input type="password" class="form-control" name="password" required placeholder="8 caractères min" minlength="8">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Confirmation <span class="required">*</span></label>
                                    <input type="password" class="form-control" name="password_confirm" required placeholder="Répétez le mot de passe">
                                </div>
                            </div>
                            
                            <label class="remember-me">
                                <input type="checkbox" name="accept_terms" required>
                                <span>J'accepte les <a href="<?= base_url('cgv') ?>" style="color: var(--secondary-green);">conditions générales</a></span>
                            </label>
                            
                            <button type="submit" class="btn-submit">
                                <i class="bi bi-person-check"></i> Créer mon compte
                            </button>
                        </form>
                        
                        <div class="divider"><span>ou</span></div>
                        
                        <a href="<?= base_url('checkout/payment') ?>" class="btn-guest">
                            Continuer sans compte <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="security-note">
                        <i class="bi bi-shield-check"></i>
                        <span>Vos données sont protégées par SSL 256-bit</span>
                    </div>
                </div>
                
                <!-- UTILISATEUR CONNECTÉ (caché par défaut, affiché par JS si connecté) -->
                <div id="loggedInView" style="display: none;">
                    <div class="user-logged-in">
                        <div class="user-avatar">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                        <div class="user-name" id="userName">Utilisateur</div>
                        <div class="user-email" id="userEmail">email@example.com</div>
                        
                        <div class="user-info-box">
                            <div class="info-row">
                                <span class="info-label">Email</span>
                                <span class="info-value" id="infoEmail">email@example.com</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Téléphone</span>
                                <span class="info-value" id="infoPhone">Non renseigné</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Compte créé le</span>
                                <span class="info-value">12/01/2024</span>
                            </div>
                        </div>
                        
                        <a href="<?= base_url('checkout/payment') ?>" class="btn-continue-logged">
                            Continuer vers le paiement <i class="bi bi-arrow-right"></i>
                        </a>
                        
                        <button class="btn-logout" onclick="logout()">
                            <i class="bi bi-box-arrow-right"></i> Se déconnecter
                        </button>
                    </div>
                </div>
                
            </div>
        </div>

        <!-- Résumé commande -->
        <div class="order-summary">
            <div class="summary-header">
                <div class="summary-title">
                    <i class="bi bi-receipt"></i>
                    Résumé
                </div>
                <a href="<?= base_url('panier') ?>" style="color: var(--secondary-green); font-size: 14px; text-decoration: none;">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
            </div>
            
            <div class="summary-items" id="summaryItems">
                <!-- Injecté par JS -->
            </div>

            <div class="summary-totals">
                <div class="total-row grand-total">
                    <span>Total</span>
                    <span id="summaryTotal">0,00 €</span>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// ==========================================
// CONFIGURATION
// ==========================================
const CONFIG = {
    tvaRate: 0.055
};

// ==========================================
// SIMULATION UTILISATEUR (À REMPLACER PAR PHP)
// ==========================================
// En production, utilisez : <?php echo json_encode($this->session->userdata('user')); ?>
const currentUser = null; // null si non connecté, ou objet user si connecté

// Exemple avec utilisateur connecté (décommentez pour tester) :
/*
const currentUser = {
    id: 123,
    firstname: 'Jean',
    lastname: 'Dupont',
    email: 'jean.dupont@email.com',
    phone: '+33 6 12 34 56 78'
};
*/

// ==========================================
// CHARGEMENT DU PANIER
// ==========================================
let cart = JSON.parse(localStorage.getItem('agf_cart')) || [];

function loadCart() {
    const container = document.getElementById('summaryItems');
    const totalElement = document.getElementById('summaryTotal');
    
    if (cart.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: var(--text-muted);">Panier vide</p>';
        totalElement.textContent = '0,00 €';
        return;
    }
    
    let total = 0;
    
    container.innerHTML = cart.map(item => {
        const itemTotal = item.price * item.qty * (1 + CONFIG.tvaRate);
        total += itemTotal;
        
        let emoji = '📦';
        if (item.name.toLowerCase().includes('masque')) emoji = '😷';
        if (item.name.toLowerCase().includes('pizza')) emoji = '🍕';
        if (item.name.toLowerCase().includes('artemisia')) emoji = '🌿';
        if (item.name.toLowerCase().includes('moringa')) emoji = '🍃';
        
        return `
            <div class="summary-item">
                <div class="item-info">
                    <div class="item-name">${emoji} ${item.name}</div>
                    <div class="item-qty">Qté: ${item.qty}</div>
                </div>
                <div class="item-price">${itemTotal.toFixed(2).replace('.', ',')} €</div>
            </div>
        `;
    }).join('');
    
    totalElement.textContent = total.toFixed(2).replace('.', ',') + ' €';
}

// ==========================================
// GESTION DES ONGLETS (Connexion/Inscription)
// ==========================================
function switchTab(tab) {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const tabLogin = document.getElementById('tab-login');
    const tabRegister = document.getElementById('tab-register');
    
    if (tab === 'login') {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
        tabLogin.classList.add('active');
        tabRegister.classList.remove('active');
    } else {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
        tabLogin.classList.remove('active');
        tabRegister.classList.add('active');
    }
}

// ==========================================
// GESTION DES FORMULAIRES
// ==========================================
function handleLogin(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    // Simulation connexion (remplacer par AJAX en production)
    showNotification('Connexion réussie ! Redirection...');
    
    setTimeout(() => {
        window.location.href = '<?= base_url("checkout/payment") ?>';
    }, 1500);
}

function handleRegister(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    // Vérifier que les mots de passe correspondent
    if (formData.get('password') !== formData.get('password_confirm')) {
        showNotification('Les mots de passe ne correspondent pas', 'error');
        return;
    }
    
    // Simulation inscription (remplacer par AJAX en production)
    showNotification('Compte créé avec succès ! Redirection...');
    
    setTimeout(() => {
        window.location.href = '<?= base_url("checkout/payment") ?>';
    }, 1500);
}

function logout() {
    if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
        showNotification('Déconnexion réussie');
        setTimeout(() => {
            window.location.href = '<?= base_url("auth/logout") ?>';
        }, 1000);
    }
}

// ==========================================
// VÉRIFICATION UTILISATEUR CONNECTÉ
// ==========================================
function checkUserSession() {
    if (currentUser) {
        // Utilisateur connecté - afficher le profil
        document.getElementById('guestForms').style.display = 'none';
        document.getElementById('loggedInView').style.display = 'block';
        
        // Mettre à jour les infos
        document.getElementById('userName').textContent = currentUser.firstname + ' ' + currentUser.lastname;
        document.getElementById('userEmail').textContent = currentUser.email;
        document.getElementById('infoEmail').textContent = currentUser.email;
        document.getElementById('infoPhone').textContent = currentUser.phone || 'Non renseigné';
    }
    // Sinon, garder l'affichage par défaut (formulaire de connexion)
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
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}-fill"></i>
        ${message}
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ==========================================
// INITIALISATION
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    loadCart();
    checkUserSession();
});
</script>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>