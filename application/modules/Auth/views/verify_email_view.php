<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification email - NUFOTEC</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .verify-card {
            max-width: 480px;
            width: 100%;
            background: white;
            border-radius: 16px;
            padding: 40px 32px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            text-align: center;
        }
        .logo-area {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo-area img {
            max-height: 60px;
            width: auto;
        }
        h1 {
            font-size: 24px;
            font-weight: 600;
            color: #1a2a3a;
            margin-bottom: 16px;
        }
        p {
            color: #6b7a8a;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .code-input {
            width: 100%;
            padding: 16px;
            font-size: 24px;
            text-align: center;
            letter-spacing: 8px;
            font-family: monospace;
            border: 2px solid #e0e7ed;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        .code-input:focus {
            outline: none;
            border-color: #0a66c2;
        }
        .btn-primary {
            width: 100%;
            background: #0a66c2;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            color: white;
            cursor: pointer;
            margin-bottom: 16px;
        }
        .btn-primary:hover { background: #004182; }
        .btn-link {
            background: none;
            border: none;
            color: #0a66c2;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="verify-card">
    <div class="logo-area">
        <img src="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'logo.png')) ?>" alt="Logo">
    </div>
    
    <h1>Vérification email</h1>
    <p>Un code de vérification a été envoyé à <strong><?= htmlspecialchars($email) ?></strong><br>Veuillez le saisir ci-dessous :</p>
    
    <input type="text" id="verificationCode" class="code-input" placeholder="000000" maxlength="6" autofocus>
    
    <button id="verifyBtn" class="btn-primary">Vérifier mon compte</button>
    <button id="resendBtn" class="btn-link">Renvoyer le code</button>
</div>

<script>
const baseUrl = '<?= rtrim(base_url(), '/') ?>';

document.getElementById('verifyBtn')?.addEventListener('click', function() {
    const code = document.getElementById('verificationCode').value.trim();
    
    if (!code || code.length !== 6) {
        Swal.fire('Erreur', 'Veuillez saisir un code à 6 chiffres', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Vérification...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    $.ajax({
        url: baseUrl + '/auth/verify_email_code',
        method: 'POST',
        data: { code: code },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                Swal.fire('Succès !', res.message, 'success').then(() => {
                    window.location.href = baseUrl + '/Auth';
                });
            } else {
                Swal.fire('Erreur', res.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Erreur', 'Erreur de connexion au serveur', 'error');
        }
    });
});

document.getElementById('resendBtn')?.addEventListener('click', function() {
    Swal.fire({
        title: 'Renvoi en cours...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    $.ajax({
        url: baseUrl + '/auth/resend_verification_code',
        method: 'POST',
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                Swal.fire('Succès', res.message, 'success');
            } else {
                Swal.fire('Erreur', res.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Erreur', 'Erreur lors du renvoi du code', 'error');
        }
    });
});
</script>
</body>
</html>