<?php 
include VIEWPATH.'includes/frontend/Header.php'; 
?>
<style type="text/css">
:root {
    --primary: #0f4c3a;
    --primary-light: #1a6b52;
    --primary-dark: #0a3326;
    --accent: #d4af37;
    --light: #f8f9fa;
    --dark: #212529;
    --gray: #6c757d;
    --gray-light: #e9ecef;
    --success: #28a745;
    --error: #dc3545;
    --warning: #ffc107;
    --shadow: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.15);
    --radius: 12px;
    --radius-sm: 8px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Container principal */
.auth-page {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, var(--primary-light) 100%);
    padding: clamp(20px, 5vw, 60px) clamp(15px, 4vw, 20px);
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

/* Box principale */
.auth-wrapper {
    width: 100%;
    max-width: 500px;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    position: relative;
    z-index: 1;
    animation: slideUp 0.5s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Toggle navigation */
.auth-tabs {
    display: flex;
    background: #f8f9fa;
    border-bottom: 2px solid var(--gray-light);
}

.auth-tab {
    flex: 1;
    padding: clamp(14px, 4vw, 18px) clamp(16px, 4vw, 24px);
    border: none;
    background: transparent;
    color: var(--gray);
    font-size: clamp(14px, 3.5vw, 16px);
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
    bottom: -2px;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--accent);
}

/* Contenu des formulaires */
.auth-content {
    padding: clamp(24px, 5vw, 40px);
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
    margin-bottom: clamp(24px, 5vw, 32px);
}

.auth-header h2 {
    color: var(--primary);
    font-size: clamp(22px, 5vw, 28px);
    margin-bottom: 8px;
    font-weight: 700;
}

.auth-header p {
    color: var(--gray);
    font-size: clamp(13px, 3.5vw, 15px);
}

/* Info médecin sélectionné */
.selected-doctor-info {
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.12) 0%, rgba(15, 76, 58, 0.08) 100%);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: var(--radius-sm);
    padding: clamp(12px, 3vw, 16px);
    margin-bottom: 24px;
    display: none;
    align-items: center;
    gap: 12px;
    animation: slideDown 0.4s ease;
}

.selected-doctor-info.show {
    display: flex;
}

@media (max-width: 480px) {
    .selected-doctor-info {
        flex-direction: column;
        text-align: center;
    }
}

.selected-doctor-info img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--accent);
    box-shadow: var(--shadow);
    flex-shrink: 0;
}

.selected-doctor-info .doctor-details {
    flex: 1;
}

.selected-doctor-info .doctor-label {
    font-size: 11px;
    color: var(--gray);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.selected-doctor-info .doctor-name {
    font-weight: 700;
    color: var(--primary-dark);
    font-size: clamp(14px, 4vw, 16px);
}

.selected-doctor-info .doctor-action {
    font-size: 12px;
    color: var(--primary);
    margin-top: 4px;
}

/* Groupes d'inputs */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    color: var(--dark);
    font-size: clamp(13px, 3.5vw, 14px);
    font-weight: 500;
    margin-bottom: 8px;
}

.input-box {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    font-size: clamp(16px, 4vw, 18px);
    transition: var(--transition);
    pointer-events: none;
    z-index: 2;
}

.form-input {
    width: 100%;
    padding: clamp(12px, 3.5vw, 14px) clamp(40px, 10vw, 45px);
    border: 2px solid var(--gray-light);
    border-radius: var(--radius-sm);
    font-size: clamp(14px, 3.5vw, 15px);
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

/* Champ téléphone avec préfixe + */
.phone-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.phone-prefix {
    position: absolute;
    left: 45px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    font-size: clamp(14px, 3.5vw, 15px);
    font-weight: 500;
    pointer-events: none;
    z-index: 2;
    background: white;
    padding-right: 4px;
}

.phone-number-input {
    width: 100%;
    padding: clamp(12px, 3.5vw, 14px) 12px clamp(12px, 3.5vw, 14px) 70px;
    border: 2px solid var(--gray-light);
    border-radius: var(--radius-sm);
    font-size: clamp(14px, 3.5vw, 15px);
    transition: var(--transition);
    background: white;
    color: var(--dark);
}

.phone-number-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(15, 76, 58, 0.1);
}

.phone-number-input.error {
    border-color: var(--error);
    background-color: rgba(220, 53, 69, 0.02);
}

.phone-hint {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    font-size: 11px;
    color: var(--gray);
    background: rgba(108, 117, 125, 0.05);
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    border-left: 3px solid var(--accent);
}

.phone-hint i {
    font-size: 12px;
    color: var(--accent);
}

.phone-hint .country-example {
    font-weight: 500;
    color: var(--primary);
    background: rgba(15, 76, 58, 0.1);
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
}

.toggle-pass {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: var(--gray);
    font-size: clamp(16px, 4vw, 18px);
    transition: var(--transition);
    background: none;
    border: none;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    z-index: 2;
}

.toggle-pass:hover {
    color: var(--primary);
}

/* Messages d'erreur */
.error-text {
    color: var(--error);
    font-size: clamp(11px, 3vw, 12px);
    margin-top: 6px;
    display: none;
    align-items: center;
    gap: 5px;
    font-weight: 500;
}

.error-text::before {
    content: '⚠️';
    font-size: 11px;
}

.error-text.show {
    display: flex;
    animation: shake 0.4s ease;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    75% { transform: translateX(4px); }
}

/* Message d'erreur global */
.global-error {
    background: rgba(220, 53, 69, 0.1);
    border: 1px solid var(--error);
    color: var(--error);
    padding: 12px 16px;
    border-radius: var(--radius-sm);
    margin-bottom: 20px;
    display: none;
    align-items: center;
    gap: 10px;
    font-size: clamp(13px, 3.5vw, 14px);
    font-weight: 500;
}

.global-error::before {
    content: '⚠️';
    font-size: 16px;
}

.global-error.show {
    display: flex;
    animation: shake 0.4s ease;
}

/* Message de succès */
.alert-success {
    background: rgba(40, 167, 69, 0.1);
    border: 1px solid var(--success);
    color: var(--success);
    padding: 12px 16px;
    border-radius: var(--radius-sm);
    margin-bottom: 20px;
    display: none;
    align-items: center;
    gap: 10px;
    font-size: clamp(13px, 3.5vw, 14px);
}

.alert-success::before {
    content: '✓';
    font-weight: bold;
    font-size: 18px;
}

.alert-success.show {
    display: flex;
}

/* Options (remember me, forgot password) */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}

.remember-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--gray);
    font-size: clamp(13px, 3.5vw, 14px);
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
    font-size: clamp(13px, 3.5vw, 14px);
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
    padding: clamp(14px, 4vw, 16px) 24px;
    border: none;
    border-radius: var(--radius-sm);
    font-size: clamp(14px, 3.5vw, 16px);
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

.btn-submit-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(15, 76, 58, 0.4);
}

.btn-submit-primary:active:not(:disabled) {
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
    font-size: 13px;
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

@media (max-width: 480px) {
    .social-buttons {
        grid-template-columns: 1fr;
    }
}

.social-btn {
    padding: 12px;
    border: 2px solid var(--gray-light);
    border-radius: var(--radius-sm);
    background: white;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: clamp(13px, 3.5vw, 14px);
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
    font-size: clamp(13px, 3.5vw, 14px);
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

/* Indicateur de force du mot de passe */
.password-strength {
    margin-top: 8px;
    display: flex;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
}

.strength-bar {
    flex: 1;
    height: 4px;
    background: var(--gray-light);
    border-radius: 2px;
    overflow: hidden;
}

.strength-bar-fill {
    width: 0%;
    height: 100%;
    transition: width 0.3s ease, background 0.3s ease;
}

.strength-text {
    font-size: 11px;
    color: var(--gray);
}

.strength-weak .strength-bar-fill { width: 25%; background: var(--error); }
.strength-medium .strength-bar-fill { width: 50%; background: var(--warning); }
.strength-good .strength-bar-fill { width: 75%; background: #17a2b8; }
.strength-strong .strength-bar-fill { width: 100%; background: var(--success); }

/* Responsive */
@media (max-width: 576px) {
    .auth-content {
        padding: 24px 20px;
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .phone-number-input {
        padding-left: 65px;
    }
    
    .phone-prefix {
        left: 42px;
    }
}

@media (max-width: 380px) {
    .auth-tab {
        padding: 12px;
        font-size: 12px;
    }
    
    .auth-header h2 {
        font-size: 20px;
    }
    
    .phone-number-input {
        padding-left: 60px;
    }
    
    .phone-prefix {
        left: 40px;
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
                        <a href="#" class="forgot-link" onclick="forgotPassword(event)">Mot de passe oublié ?</a>
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
                        <label class="form-label">Nom & Prenom</label>
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
                        <label class="form-label">Numéro de téléphone</label>
                        <div class="phone-wrapper">
                            <span class="input-icon"><i class="bi bi-phone"></i></span>
                            <span class="phone-prefix">+</span>
                            <input type="tel" class="phone-number-input" id="register-phone" placeholder="257 XX XX XX XX" required>
                        </div>
                        <div class="phone-hint">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>Commencez par <strong class="country-example">+</strong> suivi du code pays et du numéro</span>
                        </div>
                        <span class="error-text" id="error-register-phone">Veuillez entrer un numéro de téléphone valide (ex: +257XXXXXXXXX)</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mot de passe</label>
                        <div class="input-box">
                            <span class="input-icon"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-input" id="register-password" placeholder="••••••••" required oninput="checkPasswordStrength()">
                            <button type="button" class="toggle-pass" onclick="togglePassword('register-password', this)"><i class="bi bi-eye"></i></button>
                        </div>
                        <div class="password-strength" id="password-strength">
                            <div class="strength-bar">
                                <div class="strength-bar-fill"></div>
                            </div>
                            <span class="strength-text">Force du mot de passe</span>
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

function showSelectedDoctor() {
    const selectedDoctor = sessionStorage.getItem('selected_doctor');
    if (!selectedDoctor) return;
    
    try {
        const doctor = JSON.parse(selectedDoctor);
        const THIRTY_MINUTES = 30 * 60 * 1000;
        
        if (new Date().getTime() - doctor.timestamp > THIRTY_MINUTES) {
            sessionStorage.removeItem('selected_doctor');
            sessionStorage.removeItem('redirect_after_login');
            return;
        }
        
        const infoDiv = document.getElementById('selectedDoctorInfo');
        const img = document.getElementById('selectedDoctorImg');
        const nameDiv = document.getElementById('selectedDoctorName');
        
        if (infoDiv && img && nameDiv) {
            img.src = doctor.photo || BASE_URL + 'assets/images/default-avatar.png';
            img.alt = doctor.name;
            nameDiv.textContent = doctor.name;
            infoDiv.classList.add('show');
        }
    } catch(e) {
        console.error('Erreur:', e);
    }
}

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
    
    hideGlobalError('login');
    hideGlobalError('register');
    
    // Réinitialiser les messages de succès
    document.getElementById('login-success')?.classList.remove('show');
    document.getElementById('register-success')?.classList.remove('show');
    
    if (mode === 'login') {
        registerPanel.classList.remove('active');
        loginPanel.classList.add('active');
        loginTab.classList.add('active');
        registerTab.classList.remove('active');
        // Focus sur le premier champ
        setTimeout(() => document.getElementById('login-email')?.focus(), 100);
    } else {
        loginPanel.classList.remove('active');
        registerPanel.classList.add('active');
        registerTab.classList.add('active');
        loginTab.classList.remove('active');
        // Focus sur le premier champ
        setTimeout(() => document.getElementById('register-name')?.focus(), 100);
    }
    
    document.querySelectorAll('.error-text').forEach(el => el.classList.remove('show'));
    document.querySelectorAll('.form-input, .phone-number-input').forEach(el => el.classList.remove('error'));
}

// ============================================
// GESTION ERREURS
// ============================================

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

function showGlobalError(formType, message) {
    const errorDiv = document.getElementById(formType + '-global-error');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.classList.add('show');
        // Auto-hide après 5 secondes
        setTimeout(() => {
            errorDiv.classList.remove('show');
        }, 5000);
    }
}

function hideGlobalError(formType) {
    const errorDiv = document.getElementById(formType + '-global-error');
    if (errorDiv) {
        errorDiv.classList.remove('show');
        errorDiv.textContent = '';
    }
}

function resetErrors(formPrefix) {
    hideGlobalError(formPrefix);
    document.querySelectorAll('[id^="error-' + formPrefix + '"]').forEach(el => {
        el.classList.remove('show');
    });
    document.querySelectorAll('#form-' + formPrefix + ' .form-input, #form-' + formPrefix + ' .phone-number-input').forEach(el => {
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

function isValidPhone(phone) {
    // Format international: commence par + suivi de 8 à 15 chiffres
    return /^\+\d{8,15}$/.test(phone.replace(/[\s\-]/g, ''));
}

function formatPhoneNumber(input) {
    let value = input.value;
    // Supprimer tous les caractères non numériques sauf le +
    value = value.replace(/[^\d+]/g, '');
    // Si ne commence pas par +, ajouter +
    if (value.length > 0 && !value.startsWith('+')) {
        value = '+' + value;
    }
    input.value = value;
}

function checkPasswordStrength() {
    const password = document.getElementById('register-password')?.value || '';
    const strengthDiv = document.getElementById('password-strength');
    const errorEl = document.getElementById('error-register-password');
    const input = document.getElementById('register-password');
    
    if (!strengthDiv || !errorEl || !input) return false;
    
    const hasLength = password.length >= 8;
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /\d/.test(password);
    
    let strength = 0;
    if (hasLength) strength++;
    if (hasUpper) strength++;
    if (hasLower) strength++;
    if (hasNumber) strength++;
    
    strengthDiv.classList.remove('strength-weak', 'strength-medium', 'strength-good', 'strength-strong');
    
    if (password.length === 0) {
        strengthDiv.classList.remove('strength-weak', 'strength-medium', 'strength-good', 'strength-strong');
        errorEl.classList.remove('show');
        input.classList.remove('error');
        return false;
    } else if (strength <= 2) {
        strengthDiv.classList.add('strength-weak');
        strengthDiv.querySelector('.strength-text').textContent = 'Faible';
        errorEl.textContent = '8 caractères min., 1 majuscule, 1 chiffre';
        errorEl.classList.add('show');
        input.classList.add('error');
        return false;
    } else if (strength === 3) {
        strengthDiv.classList.add('strength-medium');
        strengthDiv.querySelector('.strength-text').textContent = 'Moyen';
        errorEl.classList.remove('show');
        input.classList.remove('error');
        return true;
    } else {
        strengthDiv.classList.add('strength-strong');
        strengthDiv.querySelector('.strength-text').textContent = 'Fort';
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
    
    resetErrors('login');
    
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
    
    btn.classList.add('loading');
    btn.disabled = true;
    btn.querySelector('.btn-text').textContent = 'Connexion en cours...';
    
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
                remember: remember ? '1' : '0',
                redirect: redirectUrl || ''
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const successDiv = document.getElementById('login-success');
            if (successDiv) {
                successDiv.textContent = data.message || 'Connexion réussie ! Redirection...';
                successDiv.classList.add('show');
            }
            
            if (remember) {
                localStorage.setItem('user_email', email);
            } else {
                localStorage.removeItem('user_email');
            }
            
            // Nettoyer les données temporaires
            sessionStorage.removeItem('selected_doctor');
            sessionStorage.removeItem('redirect_after_login');
            
            setTimeout(() => {
                window.location.href = data.redirect || BASE_URL;
            }, 1000);
            
        } else {
            const errorMsg = data.message || 'Email ou mot de passe incorrect';
            showGlobalError('login', errorMsg);
            // Réinitialiser le champ mot de passe
            passwordInput.value = '';
            passwordInput.focus();
        }
        
    } catch (err) {
        console.error('Erreur:', err);
        showGlobalError('login', 'Erreur de connexion au serveur. Veuillez réessayer.');
    } finally {
        btn.classList.remove('loading');
        btn.disabled = false;
        btn.querySelector('.btn-text').textContent = 'Se connecter';
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
    const phoneInput = document.getElementById('register-phone');
    const passwordInput = document.getElementById('register-password');
    const confirmInput = document.getElementById('register-confirm');
    const termsInput = document.getElementById('register-terms');
    const btn = document.getElementById('btn-register');
    
    if (!nameInput || !emailInput || !phoneInput || !passwordInput || !confirmInput || !btn) return false;
    
    const name = nameInput.value.trim();
    const email = emailInput.value.trim();
    let phone = phoneInput.value.trim();
    const password = passwordInput.value;
    const confirm = confirmInput.value;
    const terms = termsInput?.checked || false;
    
    resetErrors('register');
    
    let hasError = false;
    
    // Validation du nom
    if (!name || name.length < 2) {
        showFieldError('register-name', 'Le nom doit contenir au moins 2 caractères');
        hasError = true;
    }
    
    // Validation de l'email
    if (!email) {
        showFieldError('register-email', 'L\'email est requis');
        hasError = true;
    } else if (!isValidEmail(email)) {
        showFieldError('register-email', 'Veuillez entrer un email valide');
        hasError = true;
    }
    
    // Validation du téléphone
    phone = phone.replace(/\s/g, '');
    if (!phone) {
        showFieldError('register-phone', 'Le numéro de téléphone est requis');
        hasError = true;
    } else if (!isValidPhone(phone)) {
        showFieldError('register-phone', 'Format invalide. Utilisez le format international: +257XXXXXXXXX');
        hasError = true;
    }
    
    // Validation du mot de passe
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
    
    // Validation de la confirmation
    if (password !== confirm) {
        showFieldError('register-confirm', 'Les mots de passe ne correspondent pas');
        hasError = true;
    }
    
    // Validation des conditions
    if (!terms) {
        showGlobalError('register', 'Veuillez accepter les conditions d\'utilisation');
        hasError = true;
    }
    
    if (hasError) return false;
    
    btn.classList.add('loading');
    btn.disabled = true;
    btn.querySelector('.btn-text').textContent = 'Création en cours...';
    
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
                phone: phone,
                password: password,
                confirm_password: confirm
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const successDiv = document.getElementById('register-success');
            if (successDiv) {
                successDiv.textContent = data.message || 'Compte créé avec succès ! Redirection vers la connexion...';
                successDiv.classList.add('show');
            }
            
            // Réinitialiser le formulaire
            document.getElementById('form-register').reset();
            document.getElementById('password-strength')?.classList.remove('strength-weak', 'strength-medium', 'strength-good', 'strength-strong');
            
            setTimeout(() => {
                // Basculer vers l'onglet connexion
                switchTab('login');
                // Pré-remplir l'email
                const loginEmail = document.getElementById('login-email');
                if (loginEmail && data.email) {
                    loginEmail.value = data.email;
                }
                // Cacher le message de succès
                if (successDiv) successDiv.classList.remove('show');
            }, 2000);
            
        } else {
            const errorMsg = data.message || 'Erreur lors de l\'inscription';
            
            if (errorMsg.toLowerCase().includes('email')) {
                showFieldError('register-email', errorMsg);
            } else if (errorMsg.toLowerCase().includes('téléphone') || errorMsg.toLowerCase().includes('phone')) {
                showFieldError('register-phone', errorMsg);
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
        btn.querySelector('.btn-text').textContent = 'Créer mon compte';
    }
    
    return false;
}

// ============================================
// AUTRES FONCTIONS
// ============================================

function forgotPassword(event) {
    event.preventDefault();
    const email = document.getElementById('login-email')?.value;
    if (email && isValidEmail(email)) {
        window.location.href = BASE_URL + 'Auth/forgot_password?email=' + encodeURIComponent(email);
    } else {
        showGlobalError('login', 'Veuillez entrer votre email pour recevoir un lien de réinitialisation');
        document.getElementById('login-email')?.focus();
    }
}

function socialLogin(provider) {
    const redirectUrl = getRedirectUrl();
    if (redirectUrl) {
        sessionStorage.setItem('oauth_redirect', redirectUrl);
    }
    
    const btn = document.querySelector(`.social-btn[onclick*="${provider}"]`);
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Redirection...';
    }
    
    if (provider === 'google') {
        window.location.href = BASE_URL + 'Auth/google_login';
    } else if (provider === 'facebook') {
        window.location.href = BASE_URL + 'Auth/facebook_login';
    }
}

// ============================================
// INITIALISATION
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Afficher le médecin sélectionné
    showSelectedDoctor();
    
    // Récupérer l'email sauvegardé
    const savedEmail = localStorage.getItem('user_email');
    if (savedEmail) {
        const loginEmail = document.getElementById('login-email');
        const rememberMe = document.getElementById('login-remember');
        if (loginEmail) loginEmail.value = savedEmail;
        if (rememberMe) rememberMe.checked = true;
    }
    
    // Focus sur le premier champ du panel actif
    const activePanel = document.querySelector('.auth-panel.active');
    if (activePanel) {
        const firstInput = activePanel.querySelector('input:not([type="hidden"])');
        if (firstInput) firstInput.focus();
    }
    
    // Gestion des événements clavier
    document.getElementById('form-login')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleLogin(e);
        }
    });
    
    document.getElementById('form-register')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && e.target.type !== 'textarea' && e.target.type !== 'checkbox') {
            e.preventDefault();
            handleRegister(e);
        }
    });
    
    // Formatage du téléphone en temps réel
    const phoneInput = document.getElementById('register-phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            formatPhoneNumber(this);
            // Validation en temps réel
            const cleanValue = this.value.replace(/\s/g, '');
            const errorEl = document.getElementById('error-register-phone');
            if (cleanValue && !isValidPhone(cleanValue)) {
                this.classList.add('error');
                if (errorEl) errorEl.classList.add('show');
            } else {
                this.classList.remove('error');
                if (errorEl) errorEl.classList.remove('show');
            }
        });
    }
    
    // Validation du mot de passe en temps réel
    const passwordInput = document.getElementById('register-password');
    if (passwordInput) {
        passwordInput.addEventListener('input', checkPasswordStrength);
    }
    
    // Confirmation du mot de passe en temps réel
    const confirmInput = document.getElementById('register-confirm');
    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            const password = document.getElementById('register-password')?.value || '';
            const confirm = this.value;
            const errorEl = document.getElementById('error-register-confirm');
            
            if (confirm && password !== confirm) {
                this.classList.add('error');
                if (errorEl) errorEl.classList.add('show');
            } else {
                this.classList.remove('error');
                if (errorEl) errorEl.classList.remove('show');
            }
        });
    }
    
    // Nettoyer les messages d'erreur lors de la saisie
    document.querySelectorAll('#form-login input, #form-register input').forEach(input => {
        input.addEventListener('input', function() {
            const errorId = 'error-' + this.id;
            const errorEl = document.getElementById(errorId);
            if (errorEl) errorEl.classList.remove('show');
            this.classList.remove('error');
        });
    });
});
</script>
