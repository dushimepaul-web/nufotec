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
        .phone-code { width: 110px; flex-shrink: 0; }
        .phone-code select { width: 100%; padding: 16px 12px; cursor: pointer; }
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
        .error-border { border-color: #dc2626 !important; }
        .error-message { color: #dc2626; font-size: 12px; margin-top: 5px; display: block; }
        @media (max-width: 560px) {
            .phone-wrapper { flex-direction: column; }
            .phone-code { width: 100%; }
        }
        @media (max-width: 480px) { 
            .auth-card { padding: 30px 20px; } 
            .row-2cols { grid-template-columns: 1fr; gap: 0; } 
        }
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
        
        <div class="input-field" id="passwordField">
            <input type="password" name="password" id="loginPassword" placeholder="Mot de passe" required>
        </div>
        
        <!-- Mot de passe oublié - aligné à droite -->
        <div class="forgot-link-right">
            <a href="#" id="openForgotModal"><i class="fas fa-key"></i> Mot de passe oublié ?</a>
        </div>
        
        <button type="submit" class="btn-primary" id="submitBtn">Se connecter</button>
        
        <!-- Créer un compte - en dessous du bouton -->
        <div class="signup-link">
            <span class="signup-text">Pas encore de compte ? </span>
            <span id="switchToRegister" class="toggle-link">Créer un compte</span>
        </div>
    </form>

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
        
        <!-- Sélection du pays - International -->
        <div class="input-field">
            <label>Pays de résidence</label>
            <select name="pays_id" id="paysSelect" required>
                <option value="">-- Sélectionnez votre pays --</option>
                <?php if(isset($pays_list) && !empty($pays_list)): ?>
                    <?php foreach($pays_list as $p): 
                        $phone_code = ltrim($p['ITU_T_Telephone_Code'], '+');
                    ?>
                        <option value="<?= $p['id'] ?>" data-code="<?= $phone_code ?>">
                            <?= htmlspecialchars($p['pays']) ?> (+<?= htmlspecialchars($phone_code) ?>)
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="1" data-code="257">Burundi (+257)</option>
                    <option value="2" data-code="250">Rwanda (+250)</option>
                    <option value="3" data-code="256">Ouganda (+256)</option>
                    <option value="4" data-code="243">RDC (+243)</option>
                    <option value="5" data-code="254">Kenya (+254)</option>
                    <option value="6" data-code="33">France (+33)</option>
                    <option value="7" data-code="1">États-Unis (+1)</option>
                    <option value="8" data-code="44">Royaume-Uni (+44)</option>
                    <option value="9" data-code="49">Allemagne (+49)</option>
                    <option value="10" data-code="86">Chine (+86)</option>
                <?php endif; ?>
            </select>
        </div>
        
        <!-- Champ téléphone international - Version responsive même ligne -->
        <div class="input-field">
            <label>Numéro de téléphone</label>
            <div class="phone-wrapper-inline">
                <div class="phone-code-inline">
                    <select id="phoneCodeSelect">
                        <?php if(isset($pays_list) && !empty($pays_list)): ?>
                            <?php foreach($pays_list as $p): 
                                $phone_code = ltrim($p['ITU_T_Telephone_Code'], '+');
                            ?>
                                <option value="<?= $phone_code ?>" data-country-id="<?= $p['id'] ?>">
                                    +<?= $phone_code ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="257">+257</option>
                            <option value="250">+250</option>
                            <option value="256">+256</option>
                            <option value="243">+243</option>
                            <option value="254">+254</option>
                            <option value="33">+33</option>
                            <option value="1">+1</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="phone-number-inline">
                    <input type="tel" name="telephone" id="regPhone" placeholder="Numéro (ex: 68863945)">
                </div>
            </div>
            <small class="error-message" id="phoneError" style="display:none;"></small>
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
        <span id="switchToLogin" class="toggle-link">Se Connecter</span>
    </div>
</div>

<style>
    /* Conteneur pour le champ mot de passe et le lien oublié */
    .forgot-link-right {
        text-align: right;
        margin-top: -12px;
        margin-bottom: 20px;
    }

    .forgot-link-right a {
        color: #6c757d;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: color 0.2s;
    }

    .forgot-link-right a:hover {
        color: #0a66c2;
        text-decoration: underline;
    }

    /* Lien de création de compte */
    .signup-link {
        text-align: center;
        margin-top: 16px;
    }

    .signup-text {
        font-size: 14px;
        color: #6c757d;
    }

    /* Style pour garder les champs téléphone sur la même ligne */
    .phone-wrapper-inline {
        display: flex;
        gap: 10px;
        align-items: center;
        width: 100%;
    }
    
    .phone-code-inline {
        width: 100px;
        flex-shrink: 0;
    }
    
    .phone-code-inline select {
        width: 100%;
        padding: 16px 12px;
        font-size: 15px;
        font-family: 'Inter', sans-serif;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        background: #ffffff;
        cursor: pointer;
        outline: none;
        transition: all 0.2s;
        color: #1a1a2e;
    }
    
    .phone-code-inline select:focus {
        border-color: #0a66c2;
        box-shadow: 0 0 0 3px rgba(10, 102, 194, 0.1);
    }
    
    .phone-number-inline {
        flex: 1;
        min-width: 0;
    }
    
    .phone-number-inline input {
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
    
    .phone-number-inline input:focus {
        border-color: #0a66c2;
        box-shadow: 0 0 0 3px rgba(10, 102, 194, 0.1);
    }
    
    /* Responsive : garder sur la même ligne même sur mobile */
    @media (max-width: 480px) {
        .phone-wrapper-inline {
            flex-direction: row !important;
            flex-wrap: nowrap;
            gap: 8px;
        }
        
        .phone-code-inline {
            width: 85px;
            flex-shrink: 0;
        }
        
        .phone-code-inline select {
            padding: 14px 8px;
            font-size: 14px;
        }
        
        .phone-number-inline input {
            padding: 14px 12px;
            font-size: 14px;
        }
        
        .auth-card {
            padding: 30px 20px;
        }
        
        .row-2cols {
            grid-template-columns: 1fr;
            gap: 0;
        }
    }
    
    @media (max-width: 360px) {
        .phone-wrapper-inline {
            gap: 6px;
        }
        
        .phone-code-inline {
            width: 75px;
        }
        
        .phone-code-inline select {
            padding: 12px 6px;
            font-size: 13px;
        }
        
        .phone-number-inline input {
            padding: 12px 10px;
            font-size: 13px;
        }
    }
</style>



<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    // Injection automatique du jeton CSRF dans les requêtes AJAX POST
    (function() {
        var CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
        var CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';
        if (window.jQuery && jQuery.ajaxSetup) {
            jQuery.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    if ((settings.type || 'GET').toUpperCase() !== 'POST' || !CSRF_HASH) return;
                    if (xhr && xhr.setRequestHeader) xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_HASH);
                    var d = settings.data;
                    if (d instanceof FormData) {
                        if (!d.has(CSRF_NAME)) d.append(CSRF_NAME, CSRF_HASH);
                    } else if (d && typeof d === 'object') {
                        d[CSRF_NAME] = CSRF_HASH;
                    } else if (typeof d === 'string') {
                        settings.data = (d.length ? d + '&' : '') + encodeURIComponent(CSRF_NAME) + '=' + encodeURIComponent(CSRF_HASH);
                    } else {
                        settings.data = encodeURIComponent(CSRF_NAME) + '=' + encodeURIComponent(CSRF_HASH);
                    }
                }
            });
        }
    })();
</script>
<script>
    const baseUrl = '<?= rtrim(base_url(), '/') ?>';
    let isLoading = false;

    // Fonctions SweetAlert
    function showSuccess(message) {
        Swal.fire({ icon: 'success', title: 'Succès !', text: message, confirmButtonColor: '#0a66c2', timer: 3000 });
    }

    function showError(message) {
        Swal.fire({ icon: 'error', title: 'Erreur', text: message, confirmButtonColor: '#0a66c2' });
    }

    // Basculer entre formulaires
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

    // Synchronisation Pays ↔ Code Téléphone
    const paysSelect = document.getElementById('paysSelect');
    const phoneCodeSelect = document.getElementById('phoneCodeSelect');

    // Quand on change le pays dans la liste déroulante principale
    paysSelect?.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const code = selectedOption.getAttribute('data-code');
        const countryId = this.value;
        
        if (code) {
            // Mettre à jour le sélecteur de code téléphone
            for(let i = 0; i < phoneCodeSelect.options.length; i++) {
                if(phoneCodeSelect.options[i].value === code) {
                    phoneCodeSelect.selectedIndex = i;
                    break;
                }
            }
        }
    });

    // Quand on change le code téléphone directement
    phoneCodeSelect?.addEventListener('change', function() {
        const selectedCode = this.value;
        // Chercher et sélectionner le pays correspondant
        for(let i = 0; i < paysSelect.options.length; i++) {
            const optionCode = paysSelect.options[i].getAttribute('data-code');
            if(optionCode === selectedCode) {
                paysSelect.selectedIndex = i;
                break;
            }
        }
    });

    // Validation du numéro de téléphone en temps réel
    const regPhone = document.getElementById('regPhone');
    const phoneError = document.getElementById('phoneError');

    function validatePhoneNumber() {
        const phone = regPhone.value.trim();
        const phoneRegex = /^[0-9]{7,15}$/;
        
        if (phone && !phoneRegex.test(phone)) {
            phoneError.textContent = 'Le numéro doit contenir uniquement des chiffres (7 à 15 chiffres)';
            phoneError.style.display = 'block';
            regPhone.classList.add('error-border');
            return false;
        } else {
            phoneError.style.display = 'none';
            regPhone.classList.remove('error-border');
            return true;
        }
    }

    regPhone?.addEventListener('input', validatePhoneNumber);
    regPhone?.addEventListener('blur', validatePhoneNumber);

    // Validation inscription
    document.getElementById('registerForm')?.addEventListener('submit', function(e) {
        const pwd = document.getElementById('regPassword').value;
        const confirm = document.getElementById('regConfirmPwd').value;
        const phone = regPhone.value.trim();
        const phoneCode = phoneCodeSelect.value;
        
        // Construire le numéro complet pour l'envoi
        if (phone) {
            const fullPhone = '+' + phoneCode + phone;
            // Créer un champ caché pour envoyer le numéro complet
            let hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'telephone';
            hiddenInput.value = fullPhone;
            this.appendChild(hiddenInput);
            // Désactiver le champ original pour qu'il ne soit pas envoyé
            regPhone.disabled = true;
        }
        
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
        if (phone && !/^[0-9]{7,15}$/.test(phone)) {
            e.preventDefault();
            showError('Format de téléphone invalide. Utilisez uniquement des chiffres.');
            return false;
        }
        
        return true;
    });

    // MODALE RÉINITIALISATION (vérification d'identité - sans OTP)
    async function showResetPasswordModal() {
        const { value: formValues } = await Swal.fire({
            title: 'Réinitialisation du mot de passe',
            html: `<p style="font-size:13px; color:#6c757d; margin-bottom:16px;">
                       Saisissez les informations que vous avez utilisées lors de la création de votre compte.<br>
                       <strong>Email</strong>, <strong>numéro de téléphone</strong>, <strong>nom</strong> et <strong>prénom</strong>.
                   </p>
                   <input type="email" id="swal-email" class="swal2-input" placeholder="Adresse email" style="margin-bottom:10px;">
                   <input type="tel" id="swal-phone" class="swal2-input" placeholder="Numéro de téléphone" style="margin-bottom:10px;">
                   <input type="text" id="swal-nom" class="swal2-input" placeholder="Nom" style="margin-bottom:10px;">
                   <input type="text" id="swal-prenom" class="swal2-input" placeholder="Prénom">`,
            showCancelButton: true,
            confirmButtonColor: '#0a66c2',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Vérifier',
            cancelButtonText: 'Annuler',
            focusConfirm: false,
            didOpen: () => {
                setTimeout(() => document.getElementById('swal-email')?.focus(), 100);
            },
            preConfirm: () => {
                const email = document.getElementById('swal-email')?.value?.trim();
                const phone = document.getElementById('swal-phone')?.value?.trim();
                const nom = document.getElementById('swal-nom')?.value?.trim();
                const prenom = document.getElementById('swal-prenom')?.value?.trim();

                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    Swal.showValidationMessage('Veuillez saisir une adresse email valide');
                    return false;
                }
                if (!phone) {
                    Swal.showValidationMessage('Veuillez saisir votre numéro de téléphone');
                    return false;
                }
                if (!nom || nom.length < 2) {
                    Swal.showValidationMessage('Veuillez saisir votre nom');
                    return false;
                }
                if (!prenom || prenom.length < 2) {
                    Swal.showValidationMessage('Veuillez saisir votre prénom');
                    return false;
                }
                return { email, phone, nom, prenom };
            }
        });

        if (formValues) {
            Swal.fire({ title: 'Vérification en cours...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: baseUrl + '/auth/verify_identity',
                method: 'POST',
                data: {
                    email: formValues.email,
                    telephone: formValues.phone,
                    nom: formValues.nom,
                    prenom: formValues.prenom
                },
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
                error: function() { Swal.close(); showError('Erreur de connexion au serveur.'); }
            });
        }
    }

    function showNewPasswordModal() {
        Swal.fire({
            title: 'Nouveau mot de passe',
            html: `<input type="password" id="swal-password" class="swal2-input" placeholder="Nouveau mot de passe" style="margin-bottom:10px;">
                   <input type="password" id="swal-confirm" class="swal2-input" placeholder="Confirmer le mot de passe">
                   <p style="font-size:12px; color:#6c757d;">Minimum 8 caractères, 1 majuscule et 1 chiffre</p>`,
            showCancelButton: true,
            confirmButtonColor: '#0a66c2',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Changer le mot de passe',
            cancelButtonText: 'Annuler',
            preConfirm: () => {
                const pwd = document.getElementById('swal-password').value;
                const confirm = document.getElementById('swal-confirm').value;
                if (!pwd || pwd.length < 8) {
                    Swal.showValidationMessage('8 caractères minimum');
                    return false;
                }
                if (!/[A-Z]/.test(pwd) || !/[0-9]/.test(pwd)) {
                    Swal.showValidationMessage('Une majuscule et un chiffre requis');
                    return false;
                }
                if (pwd !== confirm) {
                    Swal.showValidationMessage('Les mots de passe ne correspondent pas');
                    return false;
                }
                return pwd;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Modification...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: baseUrl + '/auth/reset_password',
                    method: 'POST',
                    data: { password: result.value, confirm_password: result.value },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({ icon: 'success', title: 'Succès !', text: res.message, confirmButtonColor: '#0a66c2' })
                                .then(() => switchToLogin());
                        } else { Swal.close(); showError(res.message); }
                    },
                    error: function() { Swal.close(); showError('Erreur lors de la modification.'); }
                });
            }
        });
    }

    document.getElementById('openForgotModal')?.addEventListener('click', (e) => { e.preventDefault(); showResetPasswordModal(); });

    // Messages flash
    <?php if($this->session->flashdata('login_error')): ?>
        (function() { showError('<?= addslashes($this->session->flashdata('login_error')) ?>'); 
        $.ajax({ url: baseUrl + '/auth/clear_flash', method: 'POST', data: { key: 'login_error' }, async: false }); })();
    <?php endif; ?>
    <?php if($this->session->flashdata('register_error')): ?>
        (function() { showError('<?= addslashes($this->session->flashdata('register_error')) ?>'); 
        $.ajax({ url: baseUrl + '/auth/clear_flash', method: 'POST', data: { key: 'register_error' }, async: false }); })();
    <?php endif; ?>
    <?php if($this->session->flashdata('register_success')): ?>
        (function() { 
            showSuccess('<?= addslashes($this->session->flashdata('register_success')) ?>'); 
            $.ajax({ url: baseUrl + '/auth/clear_flash', method: 'POST', data: { key: 'register_success' }, async: false });
            setTimeout(() => switchToLogin(), 2000);
        })();
    <?php endif; ?>
</script>
</body>
</html>