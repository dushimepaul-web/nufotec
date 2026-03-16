<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style type="text/css">
:root {
    --primary: #0f4c3a;
    --primary-light: #1a6b52;
    --primary-dark: #0a3326;
    --accent: #d4af37;
    --light: #f8f9fa;
    --dark: #212529;
    --gray: #6c757d;
    --success: #28a745;
    --error: #dc3545;
    --shadow: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.15);
    --radius: 12px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Container principal */
.auth-page {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, var(--primary-light) 100%);
    padding: 60px 20px;
    position: relative;
}

/* Effet de vague en arrière-plan */
.auth-page::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 50%, rgba(212, 175, 55, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(212, 175, 55, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

/* Info médecin sélectionné */
.selected-doctor-info {
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.15) 0%, rgba(15, 76, 58, 0.1) 100%);
    border: 2px solid var(--accent);
    border-radius: var(--radius);
    padding: 15px 20px;
    margin-bottom: 25px;
    display: none;
    align-items: center;
    gap: 15px;
    animation: slideDown 0.4s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.selected-doctor-info.show {
    display: flex;
}

.selected-doctor-info img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid white;
    box-shadow: var(--shadow);
}

.selected-doctor-info .doctor-details {
    flex: 1;
}

.selected-doctor-info .doctor-label {
    font-size: 12px;
    color: var(--gray);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.selected-doctor-info .doctor-name {
    font-weight: 700;
    color: var(--primary-dark);
    font-size: 16px;
}

.selected-doctor-info .doctor-action {
    font-size: 13px;
    color: var(--primary);
    margin-top: 2px;
}

/* Box principale */
.auth-wrapper {
    width: 100%;
    max-width: 460px;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    position: relative;
    z-index: 1;
}

/* Toggle navigation */
.auth-tabs {
    display: flex;
    background: #f1f3f4;
    border-bottom: 1px solid #e1e4e8;
}

.auth-tab {
    flex: 1;
    padding: 18px 24px;
    border: none;
    background: transparent;
    color: var(--gray);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    position: relative;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.auth-tab:hover {
    color: var(--primary);
    background: rgba(15, 76, 58, 0.05);
}

.auth-tab.active {
    color: var(--primary);
    background: white;
}

.auth-tab.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--accent);
}

/* Contenu des formulaires */
.auth-content {
    padding: 40px;
}

.auth-panel {
    display: none;
    animation: fadeIn 0.4s ease;
}

.auth-panel.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.auth-header {
    text-align: center;
    margin-bottom: 30px;
}

.auth-header h2 {
    color: var(--primary);
    font-size: 1.75rem;
    margin-bottom: 8px;
    font-weight: 700;
}

.auth-header p {
    color: var(--gray);
    font-size: 0.95rem;
}

/* Groupes d'inputs */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    color: var(--dark);
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 8px;
}

.input-box {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    font-size: 1.1rem;
    transition: var(--transition);
    pointer-events: none;
}

.form-input {
    width: 100%;
    padding: 14px 45px;
    border: 2px solid #e1e4e8;
    border-radius: var(--radius);
    font-size: 1rem;
    transition: var(--transition);
    background: white;
    color: var(--dark);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(15, 76, 58, 0.1);
}

.form-input:focus + .input-icon,
.input-box:focus-within .input-icon {
    color: var(--primary);
}

.form-input.error {
    border-color: var(--error);
    background-color: rgba(220, 53, 69, 0.02);
}

.toggle-pass {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: var(--gray);
    font-size: 1.1rem;
    transition: var(--transition);
    background: none;
    border: none;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
}

.toggle-pass:hover {
    color: var(--primary);
}

/* Messages d'erreur */
.error-text {
    color: var(--error);
    font-size: 0.85rem;
    margin-top: 6px;
    display: none;
    align-items: center;
    gap: 5px;
    font-weight: 500;
}

.error-text::before {
    content: '⚠️';
    font-size: 0.8rem;
}

.error-text.show {
    display: flex;
    animation: shake 0.4s ease;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Message d'erreur global */
.global-error {
    background: rgba(220, 53, 69, 0.1);
    border: 1px solid var(--error);
    color: var(--error);
    padding: 12px 16px;
    border-radius: var(--radius);
    margin-bottom: 20px;
    display: none;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
    font-weight: 500;
}

.global-error::before {
    content: '⚠️';
    font-size: 1.1rem;
}

.global-error.show {
    display: flex;
    animation: shake 0.4s ease;
}

/* Options (remember me, forgot password) */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 10px;
}

.remember-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--gray);
    font-size: 0.9rem;
    cursor: pointer;
}

.remember-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
    cursor: pointer;
}

.forgot-link {
    color: var(--primary);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: var(--transition);
}

.forgot-link:hover {
    color: var(--accent);
    text-decoration: underline;
}

/* Bouton principal */
.btn-submit {
    width: 100%;
    padding: 16px 24px;
    border: none;
    border-radius: var(--radius);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-submit-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(15, 76, 58, 0.3);
}

.btn-submit-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(15, 76, 58, 0.4);
}

.btn-submit-primary:active {
    transform: translateY(0);
}

.btn-submit-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

/* Loader */
.spinner {
    display: none;
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.btn-submit.loading .spinner {
    display: block;
}

.btn-submit.loading .btn-text {
    opacity: 0.8;
}

/* Divider */
.divider {
    display: flex;
    align-items: center;
    margin: 28px 0;
    color: var(--gray);
    font-size: 0.9rem;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(to right, transparent, #dee2e6, transparent);
}

.divider span {
    padding: 0 16px;
    background: white;
}

/* Social login */
.social-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.social-btn {
    padding: 12px;
    border: 2px solid #e1e4e8;
    border-radius: var(--radius);
    background: white;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.9rem;
    color: var(--dark);
    font-weight: 500;
}

.social-btn:hover {
    border-color: var(--primary);
    background: rgba(15, 76, 58, 0.02);
    transform: translateY(-2px);
}

.social-icon {
    width: 20px;
    height: 20px;
}

/* Checkbox terms */
.terms-group {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 24px;
    font-size: 0.9rem;
    color: var(--gray);
    line-height: 1.5;
}

.terms-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
    margin-top: 2px;
    flex-shrink: 0;
}

.terms-group a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
}

.terms-group a:hover {
    text-decoration: underline;
    color: var(--accent);
}

/* Message de succès */
.alert-success {
    background: rgba(40, 167, 69, 0.1);
    border: 1px solid var(--success);
    color: var(--success);
    padding: 12px 16px;
    border-radius: var(--radius);
    margin-bottom: 20px;
    display: none;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
}

.alert-success::before {
    content: '✓';
    font-weight: bold;
    font-size: 1.1rem;
}

.alert-success.show {
    display: flex;
}

/* Responsive */
@media (max-width: 576px) {
    .auth-page {
        padding: 20px 15px;
    }
    
    .auth-content {
        padding: 30px 20px;
    }
    
    .auth-header h2 {
        font-size: 1.5rem;
    }
    
    .social-buttons {
        grid-template-columns: 1fr;
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .selected-doctor-info {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<div class="auth-page">
    <div class="auth-wrapper">
        <!-- Toggle Tabs -->
        <div class="auth-tabs">
            <button class="auth-tab active" onclick="switchTab('login')" id="tab-login">
                Connexion
            </button>
            <button class="auth-tab" onclick="switchTab('register')" id="tab-register">
                Inscription
            </button>
        </div>

        <!-- Contenu -->
        <div class="auth-content">
            <!-- Panel Connexion -->
            <div class="auth-panel active" id="panel-login">
                <div class="auth-header">
                    <h2>Bienvenue</h2>
                    <p>Connectez-vous à votre compte</p>
                </div>

                <!-- Info médecin sélectionné -->
                <div class="selected-doctor-info" id="selectedDoctorInfo">
                    <img src="" alt="" id="selectedDoctorImg">
                    <div class="doctor-details">
                        <div class="doctor-label">Médecin sélectionné</div>
                        <div class="doctor-name" id="selectedDoctorName"></div>
                        <div class="doctor-action">Connectez-vous pour prendre rendez-vous</div>
                    </div>
                </div>

                <!-- Message d'erreur global -->
                <div class="global-error" id="login-global-error"></div>

                <!-- Message de succès -->
                <div class="alert-success" id="login-success">
                    Connexion réussie ! Redirection en cours...
                </div>

                <form id="form-login" onsubmit="return handleLogin(event)">
                    <div class="form-group">
                        <label class="form-label">Adresse email</label>
                        <div class="input-box">
                            <span class="input-icon"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-input" id="login-email" placeholder="votre@email.com" required>
                            <button type="button" class="toggle-pass" onclick="togglePassword('login-email', this)" style="display: none;"></button>
                        </div>
                        <span class="error-text" id="error-login-email">Veuillez entrer un email valide</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mot de passe</label>
                        <div class="input-box">
                            <span class="input-icon"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-input" id="login-password" placeholder="••••••••" required>
                            <button type="button" class="toggle-pass" onclick="togglePassword('login-password', this)"><i class="bi bi-eye"></i></button>
                        </div>
                        <span class="error-text" id="error-login-password">Mot de passe requis</span>
                    </div>

                    <div class="form-options">
                        <label class="remember-label">
                            <input type="checkbox" id="login-remember">
                            <span>Se souvenir de moi</span>
                        </label>
                        <a href="#" class="forgot-link">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="btn-submit btn-submit-primary" id="btn-login">
                        <span class="btn-text">Se connecter</span>
                        <span class="spinner"></span>
                    </button>
                </form>

                <div class="divider">
                    <span>Ou continuer avec</span>
                </div>

                <div class="social-buttons">
                    <button type="button" class="social-btn" onclick="socialLogin('google')">
                        <svg class="social-icon" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Google
                    </button>
                    <button type="button" class="social-btn" onclick="socialLogin('facebook')">
                        <svg class="social-icon" viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Facebook
                    </button>
                </div>
            </div>

            <!-- Panel Inscription -->
            <div class="auth-panel" id="panel-register">
                <div class="auth-header">
                    <h2>Créer un compte</h2>
                    <p>Rejoignez-nous en quelques secondes</p>
                </div>

                <!-- Message d'erreur global -->
                <div class="global-error" id="register-global-error"></div>

                <!-- Message de succès -->
                <div class="alert-success" id="register-success">
                    Compte créé avec succès ! Vous pouvez maintenant vous connecter.
                </div>

                <form id="form-register" onsubmit="return handleRegister(event)">
                    <div class="form-group">
                        <label class="form-label">Nom complet</label>
                        <div class="input-box">
                            <span class="input-icon"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-input" id="register-name" placeholder="Jean Dupont" required>
                        </div>
                        <span class="error-text" id="error-register-name">Nom requis (minimum 2 caractères)</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Adresse email</label>
                        <div class="input-box">
                            <span class="input-icon"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-input" id="register-email" placeholder="votre@email.com" required>
                        </div>
                        <span class="error-text" id="error-register-email">Veuillez entrer un email valide</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mot de passe</label>
                        <div class="input-box">
                            <span class="input-icon"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-input" id="register-password" placeholder="••••••••" required oninput="checkPasswordStrength()">
                            <button type="button" class="toggle-pass" onclick="togglePassword('register-password', this)"><i class="bi bi-eye"></i></button>
                        </div>
                        <span class="error-text" id="error-register-password">8 caractères min., 1 majuscule, 1 chiffre</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirmer le mot de passe</label>
                        <div class="input-box">
                            <span class="input-icon"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-input" id="register-confirm" placeholder="••••••••" required>
                            <button type="button" class="toggle-pass" onclick="togglePassword('register-confirm', this)"><i class="bi bi-eye"></i></button>
                        </div>
                        <span class="error-text" id="error-register-confirm">Les mots de passe ne correspondent pas</span>
                    </div>

                    <label class="terms-group">
                        <input type="checkbox" id="register-terms" required>
                        <span>J'accepte les <a href="#">conditions d'utilisation</a> et la <a href="#">politique de confidentialité</a></span>
                    </label>

                    <button type="submit" class="btn-submit btn-submit-primary" id="btn-register">
                        <span class="btn-text">Créer mon compte</span>
                        <span class="spinner"></span>
                    </button>
                </form>

                <div class="divider">
                    <span>Ou continuer avec</span>
                </div>

                <div class="social-buttons">
                    <button type="button" class="social-btn" onclick="socialLogin('google')">
                        <svg class="social-icon" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Google
                    </button>
                    <button type="button" class="social-btn" onclick="socialLogin('facebook')">
                        <svg class="social-icon" viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Facebook
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// CONFIGURATION
// ============================================
const BASE_URL = '<?php echo base_url(); ?>';

// ============================================
// GESTION MÉDECIN SÉLECTIONNÉ
// ============================================

/**
 * Affiche les informations du médecin sélectionné
 */
function showSelectedDoctor() {
    const selectedDoctor = sessionStorage.getItem('selected_doctor');
    if (!selectedDoctor) return;
    
    const doctor = JSON.parse(selectedDoctor);
    const THIRTY_MINUTES = 30 * 60 * 1000;
    
    // Vérifier expiration
    if (new Date().getTime() - doctor.timestamp > THIRTY_MINUTES) {
        sessionStorage.removeItem('selected_doctor');
        sessionStorage.removeItem('redirect_after_login');
        return;
    }
    
    // Afficher la bannière
    const infoDiv = document.getElementById('selectedDoctorInfo');
    const img = document.getElementById('selectedDoctorImg');
    const nameDiv = document.getElementById('selectedDoctorName');
    
    if (infoDiv && img && nameDiv) {
        img.src = doctor.photo || BASE_URL + 'assets/images/default-avatar.png';
        img.alt = doctor.name;
        nameDiv.textContent = doctor.name;
        infoDiv.classList.add('show');
    }
}

/**
 * Récupère l'URL de redirection après login
 */
function getRedirectUrl() {
    return sessionStorage.getItem('redirect_after_login') || null;
}

// ============================================
// NAVIGATION ONGLETS
// ============================================

function switchTab(mode) {
    const loginPanel = document.getElementById('panel-login');
    const registerPanel = document.getElementById('panel-register');
    const loginTab = document.getElementById('tab-login');
    const registerTab = document.getElementById('tab-register');
    
    if (!loginPanel || !registerPanel || !loginTab || !registerTab) return;
    
    // Cacher les erreurs globales lors du switch
    hideGlobalError('login');
    hideGlobalError('register');
    
    if (mode === 'login') {
        registerPanel.classList.remove('active');
        loginPanel.classList.add('active');
        loginTab.classList.add('active');
        registerTab.classList.remove('active');
    } else {
        loginPanel.classList.remove('active');
        registerPanel.classList.add('active');
        registerTab.classList.add('active');
        loginTab.classList.remove('active');
    }
    
    // Reset field errors
    document.querySelectorAll('.error-text').forEach(el => el.classList.remove('show'));
    document.querySelectorAll('.form-input').forEach(el => el.classList.remove('error'));
}

// ============================================
// GESTION ERREURS
// ============================================

/**
 * Affiche une erreur sous un champ spécifique
 */
function showFieldError(fieldId, message) {
    const errorEl = document.getElementById('error-' + fieldId);
    const input = document.getElementById(fieldId);
    
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.add('show');
    }
    if (input) {
        input.classList.add('error');
        input.addEventListener('input', function() {
            this.classList.remove('error');
            if (errorEl) errorEl.classList.remove('show');
        }, { once: true });
    }
}

/**
 * Affiche une erreur globale
 */
function showGlobalError(formType, message) {
    const errorDiv = document.getElementById(formType + '-global-error');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.classList.add('show');
    }
}

/**
 * Cache l'erreur globale
 */
function hideGlobalError(formType) {
    const errorDiv = document.getElementById(formType + '-global-error');
    if (errorDiv) {
        errorDiv.classList.remove('show');
        errorDiv.textContent = '';
    }
}

/**
 * Réinitialise toutes les erreurs d'un formulaire
 */
function resetErrors(formPrefix) {
    hideGlobalError(formPrefix);
    document.querySelectorAll('[id^="error-' + formPrefix + '"]').forEach(el => {
        el.classList.remove('show');
    });
    document.querySelectorAll('#form-' + formPrefix + ' .form-input').forEach(el => {
        el.classList.remove('error');
    });
}

// ============================================
// UTILITAIRES
// ============================================

function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input || !btn) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="bi bi-eye"></i>';
    }
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function checkPasswordStrength() {
    const password = document.getElementById('register-password')?.value || '';
    const errorEl = document.getElementById('error-register-password');
    const input = document.getElementById('register-password');
    
    if (!errorEl || !input) return false;
    
    const hasLength = password.length >= 8;
    const hasUpper = /[A-Z]/.test(password);
    const hasNumber = /\d/.test(password);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    if (!hasLength) {
        errorEl.textContent = 'Minimum 8 caractères requis';
        errorEl.classList.add('show');
        input.classList.add('error');
        return false;
    } else if (!hasUpper || !hasNumber) {
        errorEl.textContent = 'Doit contenir au moins une majuscule et un chiffre';
        errorEl.classList.add('show');
        input.classList.add('error');
        return false;
    } else {
        errorEl.classList.remove('show');
        input.classList.remove('error');
        return true;
    }
}

// ============================================
// CONNEXION
// ============================================

async function handleLogin(e) {
    e.preventDefault();
    
    const emailInput = document.getElementById('login-email');
    const passwordInput = document.getElementById('login-password');
    const rememberInput = document.getElementById('login-remember');
    const btn = document.getElementById('btn-login');
    
    if (!emailInput || !passwordInput || !btn) return false;
    
    const email = emailInput.value.trim();
    const password = passwordInput.value;
    const remember = rememberInput?.checked || false;
    
    // Reset erreurs
    resetErrors('login');
    
    // Validation
    let hasError = false;
    
    if (!email) {
        showFieldError('login-email', 'L\'email est requis');
        hasError = true;
    } else if (!isValidEmail(email)) {
        showFieldError('login-email', 'Veuillez entrer un email valide');
        hasError = true;
    }
    
    if (!password) {
        showFieldError('login-password', 'Le mot de passe est requis');
        hasError = true;
    }
    
    if (hasError) return false;
    
    // Loading
    btn.classList.add('loading');
    btn.disabled = true;
    
    try {
        const redirectUrl = getRedirectUrl();
        
        const response = await fetch(BASE_URL + 'Auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                email: email,
                password: password,
                remember: remember,
                redirect_url: redirectUrl || ''
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Succès
            const successDiv = document.getElementById('login-success');
            if (successDiv) successDiv.classList.add('show');
            
            // Sauvegarder email si remember
            if (remember) {
                localStorage.setItem('user_email', email);
            }
            
            // Nettoyer sessionStorage
            sessionStorage.removeItem('selected_doctor');
            sessionStorage.removeItem('redirect_after_login');
            
            // Redirection
            setTimeout(() => {
                window.location.href = data.redirect || BASE_URL + 'Consultations/PatientForm';
            }, 1000);
            
        } else {
            // Erreur serveur
            const errorMsg = data.message || 'Erreur de connexion';
            
            // Déterminer où afficher l'erreur
            if (errorMsg.toLowerCase().includes('email') || errorMsg.toLowerCase().includes('utilisateur')) {
                showFieldError('login-email', errorMsg);
            } else if (errorMsg.toLowerCase().includes('mot de passe') || errorMsg.toLowerCase().includes('password')) {
                showFieldError('login-password', errorMsg);
            } else {
                showGlobalError('login', errorMsg);
            }
        }
        
    } catch (err) {
        console.error('Erreur:', err);
        showGlobalError('login', 'Erreur de connexion au serveur. Veuillez réessayer.');
    } finally {
        btn.classList.remove('loading');
        btn.disabled = false;
    }
    
    return false;
}

// ============================================
// INSCRIPTION
// ============================================

async function handleRegister(e) {
    e.preventDefault();
    
    const nameInput = document.getElementById('register-name');
    const emailInput = document.getElementById('register-email');
    const passwordInput = document.getElementById('register-password');
    const confirmInput = document.getElementById('register-confirm');
    const termsInput = document.getElementById('register-terms');
    const btn = document.getElementById('btn-register');
    
    if (!nameInput || !emailInput || !passwordInput || !confirmInput || !btn) return false;
    
    const name = nameInput.value.trim();
    const email = emailInput.value.trim();
    const password = passwordInput.value;
    const confirm = confirmInput.value;
    const terms = termsInput?.checked || false;
    
    // Reset erreurs
    resetErrors('register');
    
    // Validation
    let hasError = false;
    
    if (!name || name.length < 2) {
        showFieldError('register-name', 'Le nom doit contenir au moins 2 caractères');
        hasError = true;
    }
    
    if (!email) {
        showFieldError('register-email', 'L\'email est requis');
        hasError = true;
    } else if (!isValidEmail(email)) {
        showFieldError('register-email', 'Veuillez entrer un email valide');
        hasError = true;
    }
    
    if (!password) {
        showFieldError('register-password', 'Le mot de passe est requis');
        hasError = true;
    } else if (password.length < 8) {
        showFieldError('register-password', 'Minimum 8 caractères requis');
        hasError = true;
    } else if (!/[A-Z]/.test(password) || !/\d/.test(password)) {
        showFieldError('register-password', 'Doit contenir une majuscule et un chiffre');
        hasError = true;
    }
    
    if (password !== confirm) {
        showFieldError('register-confirm', 'Les mots de passe ne correspondent pas');
        hasError = true;
    }
    
    if (!terms) {
        showGlobalError('register', 'Veuillez accepter les conditions d\'utilisation');
        hasError = true;
    }
    
    if (hasError) return false;
    
    // Loading
    btn.classList.add('loading');
    btn.disabled = true;
    
    try {
        const response = await fetch(BASE_URL + 'Auth/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                fullname: name,
                email: email,
                password: password,
                confirm_password: confirm
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Succès
            const successDiv = document.getElementById('register-success');
            if (successDiv) successDiv.classList.add('show');
            
            // Reset formulaire
            const form = document.getElementById('form-register');
            if (form) form.reset();
            
            // Switch vers login après 2s
            setTimeout(() => {
                switchTab('login');
                const loginEmail = document.getElementById('login-email');
                if (loginEmail) loginEmail.value = data.email || email;
                
                if (successDiv) successDiv.classList.remove('show');
            }, 2000);
            
        } else {
            // Erreur serveur
            const errorMsg = data.message || 'Erreur lors de l\'inscription';
            
            if (errorMsg.toLowerCase().includes('email')) {
                showFieldError('register-email', errorMsg);
            } else if (errorMsg.toLowerCase().includes('mot de passe') || errorMsg.toLowerCase().includes('password')) {
                showFieldError('register-password', errorMsg);
            } else {
                showGlobalError('register', errorMsg);
            }
        }
        
    } catch (err) {
        console.error('Erreur:', err);
        showGlobalError('register', 'Erreur de connexion au serveur. Veuillez réessayer.');
    } finally {
        btn.classList.remove('loading');
        btn.disabled = false;
    }
    
    return false;
}

// ============================================
// CONNEXION SOCIALE
// ============================================

function socialLogin(provider) {
    // Sauvegarder la redirection pour après OAuth
    const redirectUrl = getRedirectUrl();
    if (redirectUrl) {
        sessionStorage.setItem('oauth_redirect', redirectUrl);
    }
    
    if (provider === 'google') {
        window.location.href = BASE_URL + 'Auth/google_login';
    } else if (provider === 'facebook') {
        window.location.href = BASE_URL + 'Auth/facebook_login';
    } else {
        showGlobalError('login', 'Connexion ' + provider + ' - Intégration à venir');
    }
}

// ============================================
// INITIALISATION
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Afficher médecin sélectionné si existe
    showSelectedDoctor();
    
    // Pré-remplir email si remember me
    const savedEmail = localStorage.getItem('user_email');
    if (savedEmail) {
        const loginEmail = document.getElementById('login-email');
        const rememberMe = document.getElementById('login-remember');
        if (loginEmail) loginEmail.value = savedEmail;
        if (rememberMe) rememberMe.checked = true;
    }
    
    // Auto-focus sur le premier champ du formulaire actif
    const activePanel = document.querySelector('.auth-panel.active');
    if (activePanel) {
        const firstInput = activePanel.querySelector('input[type="email"], input[type="text"]');
        if (firstInput) firstInput.focus();
    }
    
    // Gestion touche Entrée sur les formulaires
    document.getElementById('form-login')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleLogin(e);
        }
    });
    
    document.getElementById('form-register')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && e.target.type !== 'textarea') {
            e.preventDefault();
            handleRegister(e);
        }
    });
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>