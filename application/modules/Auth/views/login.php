<?php 
include VIEWPATH.'includes/frontend/Header.php'; 

// Récupération des messages flash
$login_error     = $this->session->flashdata('login_error');
$register_error  = $this->session->flashdata('register_error');
$register_success = $this->session->flashdata('register_success');
$login_success   = $this->session->flashdata('login_success');

// Récupération des anciennes saisies (POST) en cas d'erreur
$old_input = $this->session->flashdata('old_input');
if (!is_array($old_input)) $old_input = [];

// Onglet actif : par défaut 'login', sinon 'register' si paramètre GET register=1
$active_tab = isset($_GET['register']) ? 'register' : 'login';
?>

<!-- Le reste du style est identique, je ne le répète pas pour gagner de la place -->
<!-- (conservez tout le CSS inchangé) -->

<style>
:root {
    --primary: #0f4c3a;
    --primary-light: #1a6b52;
    --primary-dark: #0a3326;
    --accent: #d4af37;
    --accent-dark: #b8962e;
    --light: #f8f9fa;
    --dark: #212529;
    --gray: #6c757d;
    --gray-light: #e9ecef;
    --success: #28a745;
    --error: #dc3545;
    --warning: #ffc107;
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
    --shadow: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.15);
    --radius: 16px;
    --radius-sm: 10px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.auth-page {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, var(--primary-light) 100%);
    padding: clamp(20px, 5vw, 60px) clamp(15px, 4vw, 20px);
    position: relative;
}

.auth-page::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 50%, rgba(212, 175, 55, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(212, 175, 55, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

.auth-wrapper {
    width: 100%;
    max-width: 520px;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    position: relative;
    z-index: 1;
    animation: slideUp 0.4s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.auth-tabs {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid var(--gray-light);
}

.auth-tab {
    flex: 1;
    text-align: center;
    padding: 16px 20px;
    font-size: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--gray);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    position: relative;
    text-decoration: none;
    display: inline-block;
}

.auth-tab:hover {
    color: var(--primary);
    background: rgba(15, 76, 58, 0.04);
}

.auth-tab.active {
    color: var(--primary);
    background: white;
}

.auth-tab.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--accent);
    border-radius: 3px 3px 0 0;
}

.auth-content {
    padding: 32px 28px;
}

.auth-panel {
    display: none;
    animation: fadeIn 0.3s ease;
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
    margin-bottom: 28px;
}

.auth-header h2 {
    color: var(--primary);
    font-size: 1.8rem;
    margin-bottom: 8px;
    font-weight: 700;
}

.auth-header p {
    color: var(--gray);
    font-size: 0.9rem;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    color: var(--dark);
    font-size: 0.85rem;
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
    font-size: 1.1rem;
    transition: var(--transition);
    pointer-events: none;
    z-index: 2;
}

.form-input,
.phone-number-input {
    width: 100%;
    padding: 12px 16px 12px 44px;
    border: 2px solid var(--gray-light);
    border-radius: var(--radius-sm);
    font-size: 0.95rem;
    transition: var(--transition);
    background: white;
    color: var(--dark);
}

.form-input:focus,
.phone-number-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(15, 76, 58, 0.1);
}

.form-input:focus + .input-icon,
.input-box:focus-within .input-icon {
    color: var(--primary);
}

.phone-wrapper {
    position: relative;
}

.phone-prefix {
    position: absolute;
    left: 44px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    font-size: 0.95rem;
    font-weight: 500;
    pointer-events: none;
    z-index: 2;
    background: white;
    padding-right: 4px;
}

.phone-number-input {
    padding-left: 70px;
}

.phone-hint {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    font-size: 0.7rem;
    color: var(--gray);
    background: rgba(108, 117, 125, 0.05);
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    border-left: 3px solid var(--accent);
}

.phone-hint i {
    font-size: 0.8rem;
    color: var(--accent);
}

.toggle-pass {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: var(--gray);
    font-size: 1.1rem;
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

.global-error,
.alert-success {
    padding: 12px 16px;
    border-radius: var(--radius-sm);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.85rem;
    font-weight: 500;
}

.global-error {
    background: rgba(220, 53, 69, 0.1);
    border: 1px solid var(--error);
    color: var(--error);
}

.global-error::before {
    content: '⚠️';
    font-size: 1rem;
}

.alert-success {
    background: rgba(40, 167, 69, 0.1);
    border: 1px solid var(--success);
    color: var(--success);
}

.alert-success::before {
    content: '✓';
    font-weight: bold;
    font-size: 1rem;
}

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
    font-size: 0.85rem;
    cursor: pointer;
}

.remember-label input {
    width: 16px;
    height: 16px;
    accent-color: var(--primary);
}

.forgot-link {
    color: var(--primary);
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    transition: var(--transition);
}

.forgot-link:hover {
    color: var(--accent);
    text-decoration: underline;
}

.btn-submit {
    width: 100%;
    padding: 14px 24px;
    border: none;
    border-radius: var(--radius-sm);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-submit-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    box-shadow: var(--shadow);
}

.btn-submit-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.divider {
    display: flex;
    align-items: center;
    margin: 28px 0;
    color: var(--gray);
    font-size: 0.8rem;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(to right, transparent, var(--gray-light), transparent);
}

.divider span {
    padding: 0 16px;
    background: white;
}

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
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px;
    border: 2px solid var(--gray-light);
    border-radius: var(--radius-sm);
    background: white;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--dark);
    transition: var(--transition);
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

.terms-group {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 24px;
    font-size: 0.8rem;
    color: var(--gray);
    line-height: 1.4;
}

.terms-group input {
    width: 16px;
    height: 16px;
    margin-top: 2px;
    accent-color: var(--primary);
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

@media (max-width: 576px) {
    .auth-content {
        padding: 24px 20px;
    }
    .auth-header h2 {
        font-size: 1.5rem;
    }
}
</style>




<div class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-tabs">
            <button class="auth-tab <?= $active_tab === 'login' ? 'active' : '' ?>" data-tab="login">Connexion</button>
            <button class="auth-tab <?= $active_tab === 'register' ? 'active' : '' ?>" data-tab="register">Inscription</button>
        </div>

        <div class="auth-content">
            <!-- Panel Connexion -->
            <div id="panel-login" class="auth-panel <?= $active_tab === 'login' ? 'active' : '' ?>">
                <div class="auth-header">
                    <h2>Bienvenue</h2>
                    <p>Connectez-vous à votre compte</p>
                </div>

                <?php if ($login_error): ?>
                    <div class="global-error"><?= htmlspecialchars($login_error) ?></div>
                <?php endif; ?>
                <?php if ($login_success): ?>
                    <div class="alert-success"><?= htmlspecialchars($login_success) ?></div>
                <?php endif; ?>

                <form action="<?= base_url('Auth/login') ?>" method="POST">
                    <div class="form-group">
                        <label class="form-label">Adresse email</label>
                        <div class="input-box">
                            <span class="input-icon"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-input" 
                                   placeholder="exemple@domaine.com" 
                                   value="<?= htmlspecialchars($old_input['email'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mot de passe</label>
                        <div class="input-box">
                            <span class="input-icon"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                            <button type="button" class="toggle-pass" onclick="togglePassword(this)"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-label">
                            <input type="checkbox" name="remember" value="1" <?= isset($old_input['remember']) ? 'checked' : '' ?>>
                            <span>Se souvenir de moi</span>
                        </label>
                        <a href="<?= base_url('Auth/forgot_password') ?>" class="forgot-link">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="btn-submit btn-submit-primary">Se connecter</button>
                </form>

                <!-- Divider et social buttons (inchangés) -->
                <div class="divider"><span>Ou continuer avec</span></div>
                <div class="social-buttons">
                    <a href="<?= base_url('Auth/google') ?>" class="social-btn">...</a>
                    <a href="<?= base_url('Auth/facebook') ?>" class="social-btn">...</a>
                </div>
            </div>

            <!-- Panel Inscription -->
            <div id="panel-register" class="auth-panel <?= $active_tab === 'register' ? 'active' : '' ?>">
                <div class="auth-header">
                    <h2>Créer un compte</h2>
                    <p>Rejoignez-nous en quelques secondes</p>
                </div>

                <?php if ($register_error): ?>
                    <div class="global-error"><?= htmlspecialchars($register_error) ?></div>
                <?php endif; ?>
                <?php if ($register_success): ?>
                    <div class="alert-success"><?= htmlspecialchars($register_success) ?></div>
                <?php endif; ?>

                <form action="<?= base_url('Auth/register') ?>" method="POST">
                    <div class="form-group">
                        <label class="form-label">Nom & Prénom</label>
                        <div class="input-box">
                            <span class="input-icon"><i class="bi bi-person"></i></span>
                            <input type="text" name="fullname" class="form-input" 
                                   placeholder="Jean Dupont" 
                                   value="<?= htmlspecialchars($old_input['fullname'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Adresse email</label>
                        <div class="input-box">
                            <span class="input-icon"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-input" 
                                   placeholder="exemple@domaine.com" 
                                   value="<?= htmlspecialchars($old_input['email'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Numéro de téléphone</label>
                        <div class="phone-wrapper">
                            <span class="input-icon"><i class="bi bi-phone"></i></span>
                            <span class="phone-prefix">+</span>
                            <input type="tel" name="phone" class="phone-number-input" 
                                   placeholder="257 XX XX XX XX" 
                                   value="<?= htmlspecialchars($old_input['phone'] ?? '') ?>" required>
                        </div>
                        <div class="phone-hint">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>Commencez par <strong>+</strong> suivi du code pays (ex: +257XXXXXXXXX)</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mot de passe</label>
                        <div class="input-box">
                            <span class="input-icon"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                            <button type="button" class="toggle-pass" onclick="togglePassword(this)"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirmer le mot de passe</label>
                        <div class="input-box">
                            <span class="input-icon"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="confirm_password" class="form-input" placeholder="••••••••" required>
                            <button type="button" class="toggle-pass" onclick="togglePassword(this)"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>

                    <label class="terms-group">
                        <input type="checkbox" name="terms" required <?= isset($old_input['terms']) ? 'checked' : '' ?>>
                        <span>J'accepte les <a href="#">conditions d'utilisation</a> et la <a href="#">politique de confidentialité</a></span>
                    </label>

                    <button type="submit" class="btn-submit btn-submit-primary">Créer mon compte</button>
                </form>

                <div class="divider"><span>Ou continuer avec</span></div>
                <div class="social-buttons">
                    <a href="<?= base_url('Auth/google') ?>" class="social-btn">...</a>
                    <a href="<?= base_url('Auth/facebook') ?>" class="social-btn">...</a>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
// Basculement des onglets sans rechargement de page
document.querySelectorAll('.auth-tab').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        const tabName = this.getAttribute('data-tab');
        // Mettre à jour les classes actives
        document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.auth-panel').forEach(panel => panel.classList.remove('active'));
        document.getElementById(`panel-${tabName}`).classList.add('active');
        // Mettre à jour l'URL sans rechargement (optionnel)
        const url = new URL(window.location.href);
        if (tabName === 'register') {
            url.searchParams.set('register', '1');
        } else {
            url.searchParams.delete('register');
        }
        window.history.pushState({}, '', url);
    });
});

// Fonction pour afficher/masquer le mot de passe
function togglePassword(btn) {
    const input = btn.parentElement.querySelector('input');
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="bi bi-eye"></i>';
    }
}
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>