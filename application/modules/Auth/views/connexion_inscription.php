<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nufotec · Connexion & Inscription</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .auth-card {
            max-width: 480px;
            width: 100%;
            background: white;
            padding: 40px 32px;
        }

        .logo-area {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-icon {
            background: #0a66c2;
            width: 56px;
            height: 56px;
            border-radius: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .logo-icon i {
            font-size: 30px;
            color: white;
        }

        .logo-area h1 {
            font-size: 32px;
            font-weight: 700;
            color: #0a2540;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .logo-area p {
            font-size: 15px;
            color: #6c757d;
            font-weight: 400;
        }

        .welcome-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 12px;
            text-align: center;
        }

        .welcome-subtitle {
            font-size: 14px;
            color: #6c757d;
            text-align: center;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .input-field {
            margin-bottom: 24px;
        }

        .input-field input {
            width: 100%;
            padding: 16px 18px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            background: #ffffff;
            transition: all 0.2s;
            outline: none;
            color: #1a1a2e;
        }

        .input-field input:focus {
            border-color: #0a66c2;
            box-shadow: 0 0 0 3px rgba(10, 102, 194, 0.1);
        }

        .input-field input::placeholder {
            color: #adb5bd;
            font-weight: 400;
        }

        .btn-primary {
            width: 100%;
            background: #0a66c2;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            color: white;
            cursor: pointer;
            transition: background 0.2s;
            font-family: 'Inter', sans-serif;
            margin-bottom: 24px;
        }

        .btn-primary:hover {
            background: #004182;
        }

        .btn-secondary {
            width: 100%;
            background: transparent;
            border: 1px solid #e0e0e0;
            padding: 14px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 14px;
            color: #5b6e8c;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 8px;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
            border-color: #0a66c2;
            color: #0a66c2;
        }

        .separator {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0;
            color: #adb5bd;
            font-size: 13px;
        }

        .separator::before,
        .separator::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e9ecef;
        }

        .separator span {
            margin: 0 12px;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 24px;
        }

        .social-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: #6c757d;
            font-size: 20px;
            text-decoration: none;
            border: 1px solid #e9ecef;
        }

        .social-icon:hover {
            background: #0a66c2;
            color: white;
            border-color: #0a66c2;
        }

        .legal-text {
            text-align: center;
            font-size: 12px;
            color: #8a99b0;
            margin: 24px 0 16px;
            line-height: 1.5;
        }

        .legal-text a {
            color: #0a66c2;
            text-decoration: none;
        }

        .legal-text a:hover {
            text-decoration: underline;
        }

        .help-section {
            text-align: center;
            font-size: 13px;
            color: #8a99b0;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
            margin-top: 8px;
        }

        .help-section a {
            color: #0a66c2;
            text-decoration: none;
        }

        .help-section a:hover {
            text-decoration: underline;
        }

        .toggle-link {
            color: #0a66c2;
            cursor: pointer;
            font-weight: 500;
        }

        .toggle-link:hover {
            text-decoration: underline;
        }

        .form-container {
            display: none;
        }

        .form-container.active-form {
            display: block;
        }

        .message-toast {
            background: #f8fafc;
            border-left: 4px solid #0a66c2;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 13px;
            font-weight: 500;
            display: none;
            align-items: center;
            gap: 12px;
        }

        .message-toast.error {
            border-left-color: #dc2626;
            background: #fef2f2;
            color: #991b1b;
        }

        .message-toast.success {
            border-left-color: #16a34a;
            background: #f0fdf4;
            color: #166534;
        }

        .message-toast.show {
            display: flex;
        }

        .forgot-link {
            text-align: center;
            margin-top: 16px;
        }

        .forgot-link a {
            color: #6c757d;
            text-decoration: none;
            font-size: 13px;
        }

        .forgot-link a:hover {
            color: #0a66c2;
            text-decoration: underline;
        }

        .additional-fields {
            margin-top: 20px;
            border-top: 1px solid #f0f0f0;
            padding-top: 20px;
        }

        .row-2cols {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .input-field label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #1f2a44;
            margin-bottom: 6px;
        }

        .input-field select {
            width: 100%;
            padding: 16px 18px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            background: #ffffff;
            cursor: pointer;
        }

        .input-field select:focus {
            border-color: #0a66c2;
            outline: none;
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 30px 20px;
            }
            .row-2cols {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
        
        .checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 16px 0;
        }
        
        .checkbox input {
            width: 18px;
            height: 18px;
        }
        
        .checkbox label {
            font-size: 13px;
            color: #6c757d;
        }
        
        .checkbox a {
            color: #0a66c2;
            text-decoration: none;
        }
        
        .checkbox a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="logo-area">
        <div class="logo-wrapper d-flex justify-content-center align-items-center">
    <a href="<?= base_url() ?>" class="d-block">
        <img src="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'logo.png')) ?>" 
             alt="AGF Logo"
             class="img-fluid" 
             style="max-height: 80px; width: auto;">
    </a>
        <h1>Nufotec Burundi</h1>
    </div>

    <div id="messageBox" class="message-toast"></div>

    <!-- FORMULAIRE CONNEXION -->
    <div id="loginPanel" class="form-container active-form">
        <div class="welcome-title">Bienvenue chez Nufotec</div>
        <div class="welcome-subtitle">
            Utilisez votre e-mail ou votre téléphone pour vous connecter ou créer un compte.
        </div>

        <form id="loginForm" method="POST" action="<?= base_url('Auth/login') ?>">
            <div class="input-field">
                <input type="text" name="email" id="loginIdentifier" placeholder="Adresse email ou numéro de téléphone" required>
            </div>
            <div class="input-field" id="passwordField" style="display: none;">
                <input type="password" name="password" id="loginPassword" placeholder="Mot de passe">
            </div>
            <button type="submit" class="btn-primary" id="submitBtn">Continuer</button>
        </form>

        <div class="forgot-link" id="forgotLink" style="display: none;">
            <a href="#" id="openForgotModal">Mot de passe oublié ?</a>
        </div>

        <div class="separator">
            <span>Ou connectez-vous avec :</span>
        </div>

        <div class="social-icons">
            <a href="#" class="social-icon"><i class="fab fa-google"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-apple"></i></a>
        </div>

        <div class="legal-text">
            En continuant, vous acceptez les <a href="#">conditions d'utilisation</a> de Nufotec<br>
            <a href="#">Termes et conditions</a>
        </div>

        <div class="help-section">
            <i class="fas fa-question-circle"></i> Besoin d'aide ? Visitez notre <a href="#">Centre d'aide</a><br>
            ou contactez-nous au <a href="tel:+25779000123">+257 79 00 01 23</a>
        </div>

        <div style="text-align: center; margin-top: 24px;">
            <span style="font-size: 14px; color: #6c757d;">Pas encore de compte ? </span>
            <span id="switchToRegister" class="toggle-link">Créer un compte</span>
        </div>
    </div>

    <!-- FORMULAIRE INSCRIPTION -->
    <div id="registerPanel" class="form-container">
        <div class="welcome-title">Créer un compte Nufotec Burundi</div>
        <div class="welcome-subtitle">
            Remplissez vos informations pour rejoindre notre communauté.
        </div>

        <form id="registerForm" method="POST" action="<?= base_url('Auth/register') ?>">
            <div class="row-2cols">
                <div class="input-field">
                    <label>Nom</label>
                    <input type="text" name="nom" id="regNom" placeholder="Dupont" required>
                </div>
                <div class="input-field">
                    <label>Prénom</label>
                    <input type="text" name="prenom" id="regPrenom" placeholder="Jean" required>
                </div>
            </div>
            <div class="input-field">
                <label>Adresse email</label>
                <input type="email" name="email" id="regEmail" placeholder="exemple@nufotec.com" required>
            </div>
            <div class="input-field">
                <label>Numéro de téléphone</label>
                <input type="tel" name="telephone" id="regPhone" placeholder="+257 XX XXX XXX">
            </div>
            <div class="input-field">
                <label>Type d'utilisateur</label>
                <select name="type_utilisateur" id="regType">
                    <option value="patient">Patient</option>
                    <option value="entreprise">Entreprise</option>
                    <option value="investisseur">Investisseur</option>
                    <option value="partenaire">Partenaire</option>
                    <option value="broker">Broker</option>
                </select>
            </div>
            <div class="input-field" id="entrepriseField" style="display: none;">
                <label>Nom de l'entreprise</label>
                <input type="text" name="nom_entreprise" id="regNomEntreprise" placeholder="Nom de votre entreprise">
            </div>
            <div class="input-field">
                <label>Mot de passe</label>
                <input type="password" name="password" id="regPassword" placeholder="8+ caractères, 1 majuscule, 1 chiffre" required>
            </div>
            <div class="input-field">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" id="regConfirmPwd" placeholder="Retapez votre mot de passe" required>
            </div>
            <div class="checkbox">
                <input type="checkbox" name="terms" id="termsCheckbox" required>
                <label>J'accepte les <a href="#">conditions générales</a></label>
            </div>
            <button type="submit" class="btn-primary"><i class="fas fa-user-plus"></i> Créer mon compte</button>
        </form>

        <div class="separator">
            <span>Ou inscrivez-vous avec :</span>
        </div>

        <div class="social-icons">
            <a href="#" class="social-icon"><i class="fab fa-google"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-apple"></i></a>
        </div>

        <div style="text-align: center; margin-top: 24px;">
            <span style="font-size: 14px; color: #6c757d;">Déjà inscrit ? </span>
            <span id="switchToLogin" class="toggle-link">Me connecter</span>
        </div>
    </div>
</div>

<!-- MODALE RÉINITIALISATION -->
<div id="forgotModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: white; max-width: 450px; width: 90%; border-radius: 24px; padding: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="font-size: 22px; font-weight: 700;">Réinitialisation</h3>
            <i class="fas fa-times" id="closeModalBtn" style="font-size: 22px; cursor: pointer; color: #8a99b0;"></i>
        </div>
        <div id="step1Container">
            <p style="margin-bottom: 20px; color: #6c757d;">Entrez votre email, nous vous enverrons un code.</p>
            <div class="input-field">
                <input type="email" id="resetEmail" placeholder="exemple@nufotec.com">
            </div>
            <button id="sendCodeBtn" class="btn-primary">Envoyer le code</button>
        </div>
        <div id="step2Container" style="display: none;">
            <p style="margin-bottom: 20px;">Code envoyé à <strong id="displayEmail"></strong></p>
            <div class="input-field">
                <input type="text" id="verificationCode" maxlength="6" placeholder="Code 6 chiffres">
            </div>
            <button id="verifyCodeBtn" class="btn-primary">Vérifier</button>
            <button id="resendCodeBtn" class="btn-secondary" style="margin-top:12px;">Renvoyer</button>
        </div>
        <div id="step3Container" style="display: none;">
            <p style="margin-bottom: 20px;">Nouveau mot de passe</p>
            <div class="input-field">
                <input type="password" id="newPassword" placeholder="Minimum 8 caractères">
            </div>
            <div class="input-field">
                <input type="password" id="confirmNewPassword" placeholder="Confirmation">
            </div>
            <button id="resetPasswordBtn" class="btn-primary">Changer</button>
        </div>
        <div class="back-link" id="cancelReset" style="text-align:center; margin-top:20px; cursor:pointer; color:#8a99b0;">Annuler</div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    (function() {
        const loginPanel = document.getElementById('loginPanel');
        const registerPanel = document.getElementById('registerPanel');
        const msgBox = document.getElementById('messageBox');
        const modal = document.getElementById('forgotModal');
        
        // Vérifier si on doit afficher le formulaire d'inscription
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('register') === '1') {
            switchToRegister();
        }

        // Flash messages
        <?php if($this->session->flashdata('login_error')): ?>
            showMessage('<?= addslashes($this->session->flashdata('login_error')) ?>', 'error');
        <?php endif; ?>
        <?php if($this->session->flashdata('register_error')): ?>
            showMessage('<?= addslashes($this->session->flashdata('register_error')) ?>', 'error');
        <?php endif; ?>
        <?php if($this->session->flashdata('register_success')): ?>
            showMessage('<?= addslashes($this->session->flashdata('register_success')) ?>', 'success');
            // Après un succès, on bascule vers la connexion
            setTimeout(function() {
                switchToLogin();
            }, 2000);
        <?php endif; ?>

        function showMessage(text, type = 'error') {
            const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
            msgBox.innerHTML = `<i class="fas ${icon}"></i> ${text}`;
            msgBox.className = `message-toast ${type} show`;
            setTimeout(() => msgBox.classList.remove('show'), 5000);
        }

        function switchToLogin() {
            loginPanel.classList.add('active-form');
            registerPanel.classList.remove('active-form');
            clearMessage();
            // Mettre à jour l'URL sans recharger
            const url = new URL(window.location.href);
            url.searchParams.delete('register');
            window.history.pushState({}, '', url);
        }

        function switchToRegister() {
            loginPanel.classList.remove('active-form');
            registerPanel.classList.add('active-form');
            clearMessage();
            toggleEntrepriseField();
            // Mettre à jour l'URL
            const url = new URL(window.location.href);
            url.searchParams.set('register', '1');
            window.history.pushState({}, '', url);
        }

        document.getElementById('switchToRegister')?.addEventListener('click', (e) => {
            e.preventDefault();
            switchToRegister();
        });
        
        document.getElementById('switchToLogin')?.addEventListener('click', (e) => {
            e.preventDefault();
            switchToLogin();
        });

        // Connexion progressive (style Jumia)
        const loginIdentifier = document.getElementById('loginIdentifier');
        const loginPassword = document.getElementById('loginPassword');
        const passwordField = document.getElementById('passwordField');
        const submitBtn = document.getElementById('submitBtn');
        const forgotLink = document.getElementById('forgotLink');

        loginIdentifier?.addEventListener('input', function() {
            if (this.value.trim().length > 0) {
                passwordField.style.display = 'block';
                loginPassword.setAttribute('required', 'required');
                submitBtn.innerHTML = 'Se connecter';
                forgotLink.style.display = 'block';
            } else {
                passwordField.style.display = 'none';
                loginPassword.removeAttribute('required');
                submitBtn.innerHTML = 'Continuer';
                forgotLink.style.display = 'none';
            }
        });

        // Entreprise field toggle
        const typeSelect = document.getElementById('regType');
        const entrepriseField = document.getElementById('entrepriseField');
        function toggleEntrepriseField() {
            if (typeSelect && typeSelect.value === 'entreprise') {
                entrepriseField.style.display = 'block';
            } else {
                entrepriseField.style.display = 'none';
            }
        }
        typeSelect?.addEventListener('change', toggleEntrepriseField);

        // Validation inscription
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            const pwd = document.getElementById('regPassword').value;
            const confirm = document.getElementById('regConfirmPwd').value;
            if (pwd !== confirm) {
                e.preventDefault();
                showMessage('Les mots de passe ne correspondent pas.', 'error');
                return false;
            }
            if (pwd.length < 8) {
                e.preventDefault();
                showMessage('8+ caractères requis.', 'error');
                return false;
            }
            if (!/[A-Z]/.test(pwd) || !/[0-9]/.test(pwd)) {
                e.preventDefault();
                showMessage('Majuscule et chiffre requis.', 'error');
                return false;
            }
            return true;
        });

        // Modale réinitialisation
        let resetEmail = '', resetCode = '', codeExpire = null;
        const step1 = document.getElementById('step1Container');
        const step2 = document.getElementById('step2Container');
        const step3 = document.getElementById('step3Container');

        document.getElementById('openForgotModal')?.addEventListener('click', (e) => {
            e.preventDefault();
            modal.style.display = 'flex';
            step1.style.display = 'block';
            step2.style.display = 'none';
            step3.style.display = 'none';
            document.getElementById('resetEmail').value = '';
            document.getElementById('verificationCode').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmNewPassword').value = '';
        });

        document.getElementById('closeModalBtn')?.addEventListener('click', () => modal.style.display = 'none');
        document.getElementById('cancelReset')?.addEventListener('click', () => modal.style.display = 'none');

        document.getElementById('sendCodeBtn')?.addEventListener('click', function() {
            const email = document.getElementById('resetEmail').value.trim();
            if (!email) { showMessage('Email requis', 'error'); return; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showMessage('Email invalide', 'error'); return; }
            
            $.ajax({
                url: '<?= base_url("Auth/send_reset_code") ?>',
                method: 'POST',
                data: { email: email },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        resetEmail = email;
                        resetCode = res.code;
                        codeExpire = Date.now() + 10 * 60 * 1000;
                        document.getElementById('displayEmail').innerText = email;
                        step1.style.display = 'none';
                        step2.style.display = 'block';
                        showMessage('Code envoyé à votre email', 'success');
                    } else {
                        showMessage(res.message, 'error');
                    }
                },
                error: function() {
                    showMessage('Erreur de connexion', 'error');
                }
            });
        });

        document.getElementById('resendCodeBtn')?.addEventListener('click', function() {
            if (!resetEmail) return;
            $.ajax({
                url: '<?= base_url("Auth/send_reset_code") ?>',
                method: 'POST',
                data: { email: resetEmail },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        resetCode = res.code;
                        codeExpire = Date.now() + 10 * 60 * 1000;
                        showMessage('Nouveau code envoyé', 'success');
                    } else {
                        showMessage(res.message, 'error');
                    }
                }
            });
        });

        document.getElementById('verifyCodeBtn')?.addEventListener('click', function() {
            const code = document.getElementById('verificationCode').value.trim();
            if (!code) { showMessage('Code requis', 'error'); return; }
            if (Date.now() > codeExpire) { showMessage('Code expiré', 'error'); return; }
            if (code !== resetCode) { showMessage('Code incorrect', 'error'); return; }
            step2.style.display = 'none';
            step3.style.display = 'block';
        });

        document.getElementById('resetPasswordBtn')?.addEventListener('click', function() {
            const newPwd = document.getElementById('newPassword').value;
            const confirmPwd = document.getElementById('confirmNewPassword').value;
            if (!newPwd || newPwd.length < 8) { showMessage('8+ caractères requis', 'error'); return; }
            if (!/[A-Z]/.test(newPwd) || !/[0-9]/.test(newPwd)) { showMessage('Majuscule et chiffre requis', 'error'); return; }
            if (newPwd !== confirmPwd) { showMessage('Confirmation différente', 'error'); return; }
            
            $.ajax({
                url: '<?= base_url("Auth/reset_password") ?>',
                method: 'POST',
                data: { email: resetEmail, password: newPwd },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        showMessage(res.message, 'success');
                        modal.style.display = 'none';
                        setTimeout(function() {
                            switchToLogin();
                        }, 1500);
                    } else {
                        showMessage(res.message, 'error');
                    }
                }
            });
        });

        function clearMessage() {
            msgBox.classList.remove('show');
        }
        
        // Fermer la modale en cliquant en dehors
        modal?.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    })();
</script>
</body>
</html>