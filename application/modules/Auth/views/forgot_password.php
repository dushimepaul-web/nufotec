<style type="text/css">

        section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, var(--primary-light) 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .forgot-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .forgot-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }

        .forgot-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .forgot-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--accent);
            font-size: 36px;
        }

        .forgot-title {
            color: var(--primary-dark);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .forgot-subtitle {
            color: #6c757d;
            font-size: 14px;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-floating input {
            border: 2px solid #dee2e6;
            border-radius: 12px;
            height: 56px;
        }

        .form-floating input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(15, 76, 58, 0.1);
        }

        .btn-reset {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 76, 58, 0.3);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .alert {
            display: none;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .alert.show {
            display: block;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .spinner-border {
            width: 18px;
            height: 18px;
            display: none;
        }

        .btn-reset.loading .spinner-border {
            display: inline-block;
        }

        .btn-reset.loading .btn-text {
            display: none;
        }

        /* Étape 2: Email envoyé */
        .success-step {
            display: none;
            text-align: center;
        }

        .success-step.show {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: #d4edda;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #155724;
            font-size: 48px;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .email-preview {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            font-family: monospace;
            color: var(--primary);
        }
    </style>


<section>
    <div class="forgot-container">
        <div class="forgot-card">
            
            <!-- Étape 1: Formulaire -->
            <div id="step1" class="form-step">
                <div class="forgot-header">
                    <div class="forgot-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h1 class="forgot-title">Mot de passe oublié ?</h1>
                    <p class="forgot-subtitle">
                        Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                    </p>
                </div>

                <div class="alert alert-success" id="successAlert"></div>
                <div class="alert alert-danger" id="errorAlert"></div>

                <form id="forgotForm" onsubmit="handleForgotSubmit(event)">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" placeholder="nom@exemple.com" required>
                        <label for="email">Adresse email</label>
                    </div>

                    <button type="submit" class="btn btn-reset" id="submitBtn">
                        <span class="spinner-border spinner-border-sm"></span>
                        <span class="btn-text">Envoyer le lien de réinitialisation</span>
                    </button>
                </form>

                <div class="back-link">
                    <a href="<?= base_url('Auth/login') ?>">
                        <i class="bi bi-arrow-left"></i> Retour à la connexion
                    </a>
                </div>
            </div>

            <!-- Étape 2: Confirmation envoyée -->
            <div id="step2" class="success-step">
                <div class="success-icon">
                    <i class="bi bi-envelope-check"></i>
                </div>
                <h2 class="forgot-title">Email envoyé !</h2>
                <p class="forgot-subtitle">
                    Nous avons envoyé un lien de réinitialisation à :
                </p>
                <div class="email-preview" id="emailDisplay"></div>
                <p class="forgot-subtitle">
                    Ce lien expirera dans <strong>1 heure</strong>.<br>
                    Vérifiez votre dossier spam si vous ne le voyez pas.
                </p>
                <button class="btn btn-reset mt-3" onclick="resendEmail()">
                    <span class="btn-text">Renvoyer l'email</span>
                </button>
                <div class="back-link mt-3">
                    <a href="<?= base_url('Auth/login') ?>">Retour à la connexion</a>
                </div>
            </div>

        </div>
    </div>
</section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function handleForgotSubmit(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            const email = document.getElementById('email').value;
            
            // Reset alerts
            document.querySelectorAll('.alert').forEach(a => a.classList.remove('show'));
            
            btn.classList.add('loading');
            btn.disabled = true;
            
            try {
                const formData = new FormData();
                formData.append('email', email);
                
                const response = await fetch('<?= base_url("Auth/request_reset") ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Afficher étape 2
                    document.getElementById('emailDisplay').textContent = email;
                    document.getElementById('step1').style.display = 'none';
                    document.getElementById('step2').classList.add('show');
                } else {
                    document.getElementById('errorAlert').textContent = data.message;
                    document.getElementById('errorAlert').classList.add('show');
                }
            } catch (error) {
                document.getElementById('errorAlert').textContent = 'Erreur de connexion. Réessayez.';
                document.getElementById('errorAlert').classList.add('show');
            } finally {
                btn.classList.remove('loading');
                btn.disabled = false;
            }
        }

        function resendEmail() {
            // Simplement re-soumettre le formulaire caché
            const email = document.getElementById('emailDisplay').textContent;
            document.getElementById('email').value = email;
            handleForgotSubmit(new Event('submit'));
        }
    </script>

