<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nufotec · Créer mon mot de passe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .input-field input.error {
            border-color: #dc2626;
        }

        .error-message {
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
            display: none;
        }

        .error-message.show {
            display: block;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
            background: #004182;
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .info-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 32px;
            border: 1px solid #e9ecef;
        }

        .info-card p {
            margin: 8px 0;
            font-size: 14px;
            color: #5b6e8c;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .info-card i {
            width: 20px;
            color: #0a66c2;
            font-size: 16px;
        }

        .info-card strong {
            color: #1a1a2e;
        }

        .requirements {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 16px;
            margin: 20px 0;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #6c757d;
            margin: 8px 0;
        }

        .requirement i {
            width: 18px;
            font-size: 12px;
        }

        .requirement.valid {
            color: #16a34a;
        }

        .requirement.valid i {
            color: #16a34a;
        }

        .password-container {
            position: relative;
        }

        .password-container input {
            padding-right: 50px;
        }

        .toggle-password {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #adb5bd;
            font-size: 18px;
            transition: color 0.2s;
        }

        .toggle-password:hover {
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

        @media (max-width: 480px) {
            .auth-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="logo-area">
        <div class="logo-icon">
            <a href="<?= base_url() ?>" class="d-block">
        <img src="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'logo.png')) ?>" 
             alt="AGF Logo"
             class="img-fluid" 
             style="max-height: 80px; width: auto;">
    </a>
        </div>
        <h1>Nufotec Burundi</h1>
        <p>Plateforme d'investissement phytopharmaceutique</p>
    </div>

    <div class="welcome-title">Créez votre mot de passe</div>
    <div class="welcome-subtitle">
        <i class="fas fa-check-circle" style="color: #16a34a; margin-right: 5px;"></i> 
        Votre profil a été créé avec succès ! Définissez maintenant un mot de passe sécurisé.
    </div>

    <div class="info-card">
        <p><i class="fas fa-user-circle"></i> <strong>Nom :</strong> <?= htmlspecialchars($broker->full_name ?? '') ?></p>
        <p><i class="fas fa-building"></i> <strong>Entreprise :</strong> <?= htmlspecialchars($broker->firm_name ?? '') ?></p>
        <p><i class="fas fa-envelope"></i> <strong>Email :</strong> <?= htmlspecialchars($broker->email ?? '') ?></p>
    </div>

    <form id="passwordForm">
        <div class="input-field">
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Mot de passe" autocomplete="off">
                <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
            </div>
            <div class="error-message" id="error-password"></div>
        </div>

        <div class="input-field">
            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirmer le mot de passe" autocomplete="off">
                <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')"></i>
            </div>
            <div class="error-message" id="error-confirm_password"></div>
        </div>

        <div class="requirements">
            <div class="requirement" id="req-length">
                <i class="fas fa-circle"></i> Minimum 6 caractères
            </div>
            <div class="requirement" id="req-match">
                <i class="fas fa-circle"></i> Les mots de passe correspondent
            </div>
        </div>

        <button type="submit" class="btn-primary" id="submitBtn">
            <i class="fas fa-check-circle"></i> Créer mon mot de passe
        </button>
    </form>

    <div class="separator">
        <span>Besoin d'aide ?</span>
    </div>

    <div class="help-section">
        <i class="fas fa-question-circle"></i> Une question ? Contactez-nous au 
        <a href="tel:+25779000123">+257 79 00 01 23</a><br>
        ou consultez notre <a href="#">Centre d'aide</a>
    </div>
</div>

<script>
    const baseUrl = '<?= base_url() ?>';
    
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        const icon = input.nextElementSibling;
        
        if (input.getAttribute('type') === 'password') {
            input.setAttribute('type', 'text');
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.setAttribute('type', 'password');
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    // Validation en temps réel
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const reqLength = document.getElementById('req-length');
    const reqMatch = document.getElementById('req-match');
    const errorPassword = document.getElementById('error-password');
    const errorConfirm = document.getElementById('error-confirm_password');
    
    function validatePassword() {
        let isValid = true;
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        // Vérifier la longueur
        if (password.length >= 6) {
            reqLength.classList.add('valid');
            reqLength.innerHTML = '<i class="fas fa-check-circle"></i> Minimum 6 caractères';
        } else {
            reqLength.classList.remove('valid');
            reqLength.innerHTML = '<i class="fas fa-circle"></i> Minimum 6 caractères';
            isValid = false;
        }
        
        // Vérifier la correspondance
        if (password && password === confirm) {
            reqMatch.classList.add('valid');
            reqMatch.innerHTML = '<i class="fas fa-check-circle"></i> Les mots de passe correspondent';
            errorConfirm.classList.remove('show');
        } else {
            reqMatch.classList.remove('valid');
            reqMatch.innerHTML = '<i class="fas fa-circle"></i> Les mots de passe ne correspondent pas';
            if (confirm.length > 0) {
                isValid = false;
                errorConfirm.textContent = 'Les mots de passe ne correspondent pas';
                errorConfirm.classList.add('show');
            } else {
                errorConfirm.classList.remove('show');
            }
        }
        
        // Gestion des erreurs
        if (password.length < 6 && password.length > 0) {
            errorPassword.textContent = 'Le mot de passe doit contenir au moins 6 caractères';
            errorPassword.classList.add('show');
            passwordInput.classList.add('error');
            isValid = false;
        } else {
            errorPassword.classList.remove('show');
            passwordInput.classList.remove('error');
        }
        
        if (confirm.length > 0 && password !== confirm) {
            confirmInput.classList.add('error');
        } else {
            confirmInput.classList.remove('error');
        }
        
        return isValid && password.length >= 6;
    }
    
    passwordInput.addEventListener('input', validatePassword);
    confirmInput.addEventListener('input', validatePassword);
    
    // Soumission du formulaire
    document.getElementById('passwordForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validatePassword()) {
            Swal.fire({
                icon: 'error',
                title: 'Mot de passe invalide',
                text: 'Veuillez entrer un mot de passe valide (minimum 6 caractères) et vérifier la confirmation.',
                confirmButtonColor: '#0a66c2',
                confirmButtonText: '<i class="fas fa-check"></i> Compris'
            });
            return;
        }
        
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création en cours...';
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch(baseUrl + 'broker/save_password', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            const result = await response.json();
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Félicitations !',
                    text: result.message,
                    confirmButtonColor: '#0a66c2',
                    confirmButtonText: '<i class="fas fa-arrow-right"></i> Accéder au dashboard',
                    timer: 3000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = baseUrl + 'broker/dashboard';
                });
            } else {
                let errorMessage = result.message;
                if (result.errors) {
                    errorMessage = Object.values(result.errors).join('\n');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: errorMessage,
                    confirmButtonColor: '#0a66c2',
                    confirmButtonText: '<i class="fas fa-times"></i> Fermer'
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Erreur:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erreur réseau',
                text: 'Impossible de contacter le serveur. Veuillez réessayer.',
                confirmButtonColor: '#0a66c2',
                confirmButtonText: '<i class="fas fa-redo"></i> Réessayer'
            });
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
</script>
</body>
</html>