<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nufotec · Connexion & Inscription</title>
    <link rel="icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .auth-card { max-width: 480px; width: 100%; background: white; padding: 40px 32px; }
        .logo-area { text-align: center; margin-bottom: 40px; }
        .logo-area h1 { font-size: 32px; font-weight: 700; color: #0a2540; letter-spacing: -0.5px; margin-bottom: 8px; }
        .welcome-title { font-size: 20px; font-weight: 600; color: #1a1a2e; margin-bottom: 12px; text-align: center; }
        .welcome-subtitle { font-size: 14px; color: #6c757d; text-align: center; margin-bottom: 32px; line-height: 1.5; }
        .input-field { margin-bottom: 24px; }
        .input-field input, .input-field select {
            width: 100%; padding: 16px 18px; font-size: 15px; font-family: 'Inter', sans-serif;
            border: 1px solid #e0e0e0; border-radius: 12px; background: #ffffff;
            transition: all 0.2s; outline: none; color: #1a1a2e;
        }
        .input-field select { cursor: pointer; }
        .input-field input:focus, .input-field select:focus { border-color: #0a66c2; box-shadow: 0 0 0 3px rgba(10, 102, 194, 0.1); }
        .phone-wrapper { display: flex; gap: 10px; align-items: center; }
        .phone-code { width: 100px; flex-shrink: 0; }
        .phone-number { flex: 1; }
        .phone-number input { width: 100%; }
        .btn-primary {
            width: 100%; background: #0a66c2; border: none; padding: 16px; border-radius: 12px;
            font-weight: 600; font-size: 16px; color: white; cursor: pointer; transition: background 0.2s;
            font-family: 'Inter', sans-serif; margin-bottom: 24px;
        }
        .btn-primary:hover { background: #004182; }
        .btn-primary:disabled { background: #ccc; cursor: not-allowed; }
        .btn-secondary {
            width: 100%; background: transparent; border: 1px solid #e0e0e0; padding: 14px; border-radius: 12px;
            font-weight: 500; font-size: 14px; color: #5b6e8c; cursor: pointer; transition: all 0.2s;
            font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-secondary:hover { background: #f8f9fa; border-color: #0a66c2; color: #0a66c2; }
        .separator { display: flex; align-items: center; text-align: center; margin: 24px 0; color: #adb5bd; font-size: 13px; }
        .separator::before, .separator::after { content: ''; flex: 1; border-bottom: 1px solid #e9ecef; }
        .separator span { margin: 0 12px; }
        .social-icons { display: flex; justify-content: center; gap: 20px; margin-bottom: 24px; }
        .social-icon {
            width: 48px; height: 48px; border-radius: 50%; background: #f8f9fa; display: flex;
            align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;
            color: #6c757d; font-size: 20px; text-decoration: none; border: 1px solid #e9ecef;
        }
        .social-icon:hover { background: #0a66c2; color: white; border-color: #0a66c2; }
        .legal-text { text-align: center; font-size: 12px; color: #8a99b0; margin: 24px 0 16px; line-height: 1.5; }
        .legal-text a { color: #0a66c2; text-decoration: none; }
        .toggle-link { color: #0a66c2; cursor: pointer; font-weight: 500; }
        .toggle-link:hover { text-decoration: underline; }
        .form-container { display: none; }
        .form-container.active-form { display: block; }
        .forgot-link { text-align: center; margin-top: 16px; margin-bottom: 24px; }
        .forgot-link a { color: #6c757d; text-decoration: none; font-size: 14px; font-weight: 500; }
        .forgot-link a:hover { color: #0a66c2; text-decoration: underline; }
        .row-2cols { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .input-field label { display: block; font-size: 13px; font-weight: 500; color: #1f2a44; margin-bottom: 6px; }
        .checkbox { display: flex; align-items: center; gap: 10px; margin: 16px 0; }
        .checkbox input { width: 18px; height: 18px; }
        .checkbox label { font-size: 13px; color: #6c757d; }
        .reset-link { text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #f0f0f0; cursor: pointer; color: #8a99b0; }
        @media (max-width: 480px) { .auth-card { padding: 30px 20px; } .row-2cols { grid-template-columns: 1fr; gap: 0; } .phone-wrapper { flex-direction: column; } .phone-code { width: 100%; } }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="logo-area">
        <div class="logo-wrapper" style="text-align:center;">
            <a href="<?= base_url() ?>">
                <img src="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'logo.png')) ?>" 
                     alt="Nufotec Logo" style="max-height: 80px; width: auto;">
            </a>
        </div>
        <h1>Nufotec Burundi</h1>
    </div>

    <!-- FORMULAIRE CONNEXION -->
    <div id="loginPanel" class="form-container active-form">
        <div class="welcome-title">Bienvenue chez Nufotec</div>
        <div class="welcome-subtitle">Utilisez votre e-mail ou votre téléphone pour vous connecter.</div>

        <form id="loginForm" method="POST" action="<?= base_url('auth/login') ?>">
            <div class="input-field">
                <input type="text" name="email" id="loginIdentifier" placeholder="Adresse email ou numéro de téléphone" required>
            </div>
            <div class="input-field" id="passwordField" style="display: none;">
                <input type="password" name="password" id="loginPassword" placeholder="Mot de passe">
            </div>
            <button type="submit" class="btn-primary" id="submitBtn">Continuer</button>
        </form>

        <div class="forgot-link" id="forgotLink" style="display: none;">
            <a href="#" id="openForgotModal"><i class="fas fa-key"></i> Mot de passe oublié ?</a>
        </div>

        <div class="separator"><span>Ou connectez-vous avec :</span></div>
        <div class="social-icons">
            <a href="#" class="social-icon"><i class="fab fa-google"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-apple"></i></a>
        </div>

        <div class="legal-text">
            En continuant, vous acceptez les <a href="#">conditions d'utilisation</a> de Nufotec<br>
            <a href="#">Termes et conditions</a>
        </div>

        <div style="text-align: center; margin-top: 24px;">
            <span style="font-size: 14px; color: #6c757d;">Pas encore de compte ? </span>
            <span id="switchToRegister" class="toggle-link">Créer un compte</span>
        </div>
    </div>

    <!-- FORMULAIRE INSCRIPTION -->
    <div id="registerPanel" class="form-container">
        <div class="welcome-title">Créer un compte Nufotec Burundi</div>
        <div class="welcome-subtitle">Remplissez vos informations pour rejoindre notre communauté.</div>

        <form id="registerForm" method="POST" action="<?= base_url('auth/register') ?>">
            <div class="row-2cols">
                <div class="input-field">
                    <label>Nom</label>
                    <input type="text" name="nom" id="regNom" required>
                </div>
                <div class="input-field">
                    <label>Prénom</label>
                    <input type="text" name="prenom" id="regPrenom" required>
                </div>
            </div>
            <div class="input-field">
                <label>Adresse email</label>
                <input type="email" name="email" id="regEmail" required>
            </div>
            
            <div class="input-field">
                <label>Pays</label>
                <select name="pays_id" id="paysSelect">
                    <option value="">-- Sélectionnez votre pays --</option>
                    <?php if(isset($pays_list) && !empty($pays_list)): ?>
                        <?php foreach($pays_list as $p): ?>
                            <option value="<?= $p['id'] ?>" data-code="<?= $p['ITU_T_Telephone_Code'] ?>">
                                <?= htmlspecialchars($p['pays']) ?> (+<?= htmlspecialchars($p['ITU_T_Telephone_Code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="1" data-code="257">Burundi (+257)</option>
                        <option value="2" data-code="250">Rwanda (+250)</option>
                        <option value="3" data-code="256">Ouganda (+256)</option>
                        <option value="4" data-code="243">RDC (+243)</option>
                        <option value="5" data-code="254">Kenya (+254)</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="input-field">
                <label>Numéro de téléphone</label>
                <div class="phone-wrapper">
                    <div class="phone-code">
                        <input type="text" id="phoneCode" value="+257" readonly style="background:#f5f5f5;">
                    </div>
                    <div class="phone-number">
                        <input type="tel" name="telephone" id="regPhone" placeholder="XX XXX XXX" value="">
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="type_utilisateur" value="patient">
            <div class="input-field">
                <label>Mot de passe</label>
                <input type="password" name="password" id="regPassword" placeholder="8+ caractères, 1 majuscule, 1 chiffre" required>
            </div>
            <div class="input-field">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" id="regConfirmPwd" required>
            </div>
            <div class="checkbox">
                <input type="checkbox" name="terms" id="termsCheckbox" required>
                <label>J'accepte les <a href="#">conditions générales</a></label>
            </div>
            <button type="submit" class="btn-primary"><i class="fas fa-user-plus"></i> Créer mon compte</button>
        </form>

        <div class="separator"><span>Ou inscrivez-vous avec :</span></div>
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    const baseUrl = '<?= rtrim(base_url(), '/') ?>';
    let resetEmail = '';
    let isLoading = false;

    function showSweetAlert(icon, title, message) {
        Swal.fire({
            icon: icon,
            title: title,
            text: message,
            confirmButtonColor: '#0a66c2',
            confirmButtonText: 'OK'
        });
    }

    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Succès !',
            text: message,
            confirmButtonColor: '#0a66c2',
            timer: 3000,
            showConfirmButton: true
        });
    }

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: message,
            confirmButtonColor: '#0a66c2'
        });
    }

    function showLoading(message) {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    function switchToLogin() {
        document.getElementById('loginPanel').classList.add('active-form');
        document.getElementById('registerPanel').classList.remove('active-form');
        const url = new URL(window.location.href);
        url.searchParams.delete('register');
        window.history.pushState({}, '', url);
    }

    function switchToRegister() {
        document.getElementById('loginPanel').classList.remove('active-form');
        document.getElementById('registerPanel').classList.add('active-form');
        const url = new URL(window.location.href);
        url.searchParams.set('register', '1');
        window.history.pushState({}, '', url);
    }

    document.getElementById('switchToRegister')?.addEventListener('click', (e) => { e.preventDefault(); switchToRegister(); });
    document.getElementById('switchToLogin')?.addEventListener('click', (e) => { e.preventDefault(); switchToLogin(); });

    // Connexion progressive
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

    // Auto-complétion du code téléphone
    const paysSelect = document.getElementById('paysSelect');
    const phoneCode = document.getElementById('phoneCode');
    
    paysSelect?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    let code = selectedOption.getAttribute('data-code');
    if (code) {
        // Si le code contient déjà un +, ne pas en ajouter un autre
        if (code.startsWith('+')) {
            phoneCode.value = code;
        } else {
            phoneCode.value = '+' + code;
        }
    } else {
        phoneCode.value = '+257';
    }
});

    // Validation inscription
    document.getElementById('registerForm')?.addEventListener('submit', function(e) {
        const pwd = document.getElementById('regPassword').value;
        const confirm = document.getElementById('regConfirmPwd').value;
        
        if (pwd !== confirm) { 
            e.preventDefault(); 
            showError('Les mots de passe ne correspondent pas.');
            return false; 
        }
        if (pwd.length < 8) { 
            e.preventDefault(); 
            showError('Le mot de passe doit contenir au moins 8 caractères.');
            return false; 
        }
        if (!/[A-Z]/.test(pwd) || !/[0-9]/.test(pwd)) { 
            e.preventDefault(); 
            showError('Le mot de passe doit contenir au moins une majuscule et un chiffre.');
            return false; 
        }
        return true;
    });

    // MODALE RÉINITIALISATION AVEC SWEETALERT
    async function showResetPasswordModal() {
        const { value: email } = await Swal.fire({
            title: 'Réinitialisation du mot de passe',
            text: 'Entrez votre adresse email pour recevoir un code de réinitialisation',
            input: 'email',
            inputPlaceholder: 'exemple@nufotec.com',
            showCancelButton: true,
            confirmButtonColor: '#0a66c2',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Envoyer le code',
            cancelButtonText: 'Annuler',
            preConfirm: (email) => {
                if (!email) {
                    Swal.showValidationMessage('Veuillez saisir votre adresse email');
                    return false;
                }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    Swal.showValidationMessage('Adresse email invalide');
                    return false;
                }
                return email;
            }
        });

        if (email) {
            // Envoyer la demande de code
            Swal.fire({
                title: 'Envoi en cours...',
                text: 'Veuillez patienter',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: baseUrl + '/auth/send_reset_code',
                method: 'POST',
                data: { email: email },
                dataType: 'json',
                timeout: 30000,
                success: function(res) {
                    if (res.success) {
                        resetEmail = email;
                        Swal.close();
                        showVerifyCodeModal(email);
                    } else {
                        Swal.close();
                        showError(res.message);
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    showError('Erreur de connexion au serveur. Veuillez réessayer.');
                }
            });
        }
    }

    function showVerifyCodeModal(email) {
        Swal.fire({
            title: 'Code de vérification',
            html: `
                <p>Code envoyé à <strong>${email}</strong></p>
                <input type="text" id="swal-code" class="swal2-input" placeholder="Code à 6 chiffres" maxlength="6" style="text-align:center; font-size:24px; letter-spacing:5px;">
            `,
            showCancelButton: true,
            confirmButtonColor: '#0a66c2',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Vérifier le code',
            cancelButtonText: 'Annuler',
            showDenyButton: true,
            denyButtonText: 'Renvoyer le code',
            denyButtonColor: '#28a745',
            preConfirm: () => {
                const code = document.getElementById('swal-code').value;
                if (!code || code.length !== 6) {
                    Swal.showValidationMessage('Veuillez saisir le code à 6 chiffres');
                    return false;
                }
                if (!/^\d{6}$/.test(code)) {
                    Swal.showValidationMessage('Le code doit contenir 6 chiffres');
                    return false;
                }
                return code;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const code = result.value;
                verifyCode(code);
            } else if (result.isDenied) {
                resendCode(email);
            }
        });
    }

    function verifyCode(code) {
        Swal.fire({
            title: 'Vérification...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: baseUrl + '/auth/verify_reset_code',
            method: 'POST',
            data: { code: code },
            dataType: 'json',
            timeout: 30000,
            success: function(res) {
                if (res.success) {
                    Swal.close();
                    showNewPasswordModal();
                } else {
                    Swal.close();
                    showError(res.message);
                }
            },
            error: function() {
                Swal.close();
                showError('Erreur de vérification. Veuillez réessayer.');
            }
        });
    }

    function resendCode(email) {
        Swal.fire({
            title: 'Renvoi en cours...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: baseUrl + '/auth/resend_otp',
            method: 'POST',
            data: { email: email },
            dataType: 'json',
            timeout: 30000,
            success: function(res) {
                if (res.success) {
                    Swal.close();
                    showSuccess('Un nouveau code a été envoyé à votre adresse email.');
                    showVerifyCodeModal(email);
                } else {
                    Swal.close();
                    showError(res.message);
                }
            },
            error: function() {
                Swal.close();
                showError('Erreur lors du renvoi du code.');
            }
        });
    }

    function showNewPasswordModal() {
        Swal.fire({
            title: 'Nouveau mot de passe',
            html: `
                <input type="password" id="swal-password" class="swal2-input" placeholder="Nouveau mot de passe" style="margin-bottom:10px;">
                <input type="password" id="swal-confirm" class="swal2-input" placeholder="Confirmer le mot de passe">
                <p style="font-size:12px; color:#6c757d; margin-top:10px;">Minimum 8 caractères, 1 majuscule et 1 chiffre</p>
            `,
            showCancelButton: true,
            confirmButtonColor: '#0a66c2',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Changer le mot de passe',
            cancelButtonText: 'Annuler',
            preConfirm: () => {
                const password = document.getElementById('swal-password').value;
                const confirm = document.getElementById('swal-confirm').value;
                
                if (!password || password.length < 8) {
                    Swal.showValidationMessage('Le mot de passe doit contenir au moins 8 caractères');
                    return false;
                }
                if (!/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                    Swal.showValidationMessage('Le mot de passe doit contenir une majuscule et un chiffre');
                    return false;
                }
                if (password !== confirm) {
                    Swal.showValidationMessage('Les mots de passe ne correspondent pas');
                    return false;
                }
                return password;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const newPassword = result.value;
                
                Swal.fire({
                    title: 'Modification en cours...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: baseUrl + '/auth/reset_password',
                    method: 'POST',
                    data: { password: newPassword, confirm_password: newPassword },
                    dataType: 'json',
                    timeout: 30000,
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Succès !',
                                text: res.message,
                                confirmButtonColor: '#0a66c2',
                                timer: 2000,
                                showConfirmButton: true
                            }).then(() => {
                                switchToLogin();
                            });
                        } else {
                            Swal.close();
                            showError(res.message);
                        }
                    },
                    error: function() {
                        Swal.close();
                        showError('Erreur lors de la modification du mot de passe.');
                    }
                });
            }
        });
    }

    document.getElementById('openForgotModal')?.addEventListener('click', (e) => {
        e.preventDefault();
        showResetPasswordModal();
    });

   // Flash messages avec SweetAlert - Version corrigée
<?php if($this->session->flashdata('login_error')): ?>
    showError('<?= addslashes($this->session->flashdata('login_error')) ?>');
<?php endif; ?>
<?php if($this->session->flashdata('register_error')): ?>
    showError('<?= addslashes($this->session->flashdata('register_error')) ?>');
<?php endif; ?>
<?php if($this->session->flashdata('register_success')): ?>
    showSuccess('<?= addslashes($this->session->flashdata('register_success')) ?>');
    // Ne pas rediriger automatiquement, laisser l'utilisateur choisir
    // setTimeout(function() { switchToLogin(); }, 2000);
<?php endif; ?>
</script>
</body>
</html>