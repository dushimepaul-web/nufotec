<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
<style>
        .reset-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .reset-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .reset-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--primary-dark);
            font-size: 36px;
        }

        .reset-title {
            color: var(--primary-dark);
            font-size: 24px;
            font-weight: 700;
        }

        .reset-subtitle {
            color: #6c757d;
            font-size: 14px;
            margin-top: 10px;
        }

        .email-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(15, 76, 58, 0.1);
            color: var(--primary);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .password-strength {
            height: 4px;
            background: #dee2e6;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }

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

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
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

        .btn-reset:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 76, 58, 0.3);
        }

        .btn-reset:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .alert {
            display: none;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .alert.show {
            display: block;
        }

        .requirements {
            list-style: none;
            padding: 0;
            margin: 10px 0;
            font-size: 12px;
            color: #6c757d;
        }

        .requirements li {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 5px;
        }

        .requirements li.valid {
            color: #28a745;
        }

        .requirements li i {
            font-size: 14px;
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
    </style>
</head>
<body>

    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <div class="reset-icon">
                    <i class="bi bi-key"></i>
                </div>
                <h1 class="reset-title">Nouveau mot de passe</h1>
                <p class="reset-subtitle">Créez un mot de passe sécurisé pour votre compte</p>
                <div class="email-badge">
                    <i class="bi bi-envelope"></i>
                    <?= htmlspecialchars($email) ?>
                </div>
            </div>

            <div class="alert alert-danger" id="errorAlert"></div>

            <form id="resetForm" onsubmit="handleResetSubmit(event)">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="form-floating position-relative">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Nouveau mot de passe" required minlength="8">
                    <label for="password">Nouveau mot de passe</label>
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <div class="password-strength">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>

                <ul class="requirements" id="requirements">
                    <li id="req-length"><i class="bi bi-circle"></i> Au moins 8 caractères</li>
                    <li id="req-upper"><i class="bi bi-circle"></i> Une majuscule</li>
                    <li id="req-lower"><i class="bi bi-circle"></i> Une minuscule</li>
                    <li id="req-number"><i class="bi bi-circle"></i> Un chiffre</li>
                </ul>

                <div class="form-floating position-relative">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Confirmer le mot de passe" required>
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <button type="submit" class="btn btn-reset" id="submitBtn">
                    <span class="spinner-border spinner-border-sm"></span>
                    <span class="btn-text">Réinitialiser le mot de passe</span>
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        
        passwordInput.addEventListener('input', function() {
            const val = this.value;
            let strength = 0;
            
            // Vérifier les critères
            document.getElementById('req-length').classList.toggle('valid', val.length >= 8);
            document.getElementById('req-length').querySelector('i').className = val.length >= 8 ? 'bi bi-check-circle-fill' : 'bi bi-circle';
            
            document.getElementById('req-upper').classList.toggle('valid', /[A-Z]/.test(val));
            document.getElementById('req-upper').querySelector('i').className = /[A-Z]/.test(val) ? 'bi bi-check-circle-fill' : 'bi bi-circle';
            
            document.getElementById('req-lower').classList.toggle('valid', /[a-z]/.test(val));
            document.getElementById('req-lower').querySelector('i').className = /[a-z]/.test(val) ? 'bi bi-check-circle-fill' : 'bi bi-circle';
            
            document.getElementById('req-number').classList.toggle('valid', /\d/.test(val));
            document.getElementById('req-number').querySelector('i').className = /\d/.test(val) ? 'bi bi-check-circle-fill' : 'bi bi-circle';
            
            // Calculer force
            if (val.length >= 8) strength++;
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) strength++;
            if (/\d/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;
            
            // Afficher barre
            strengthBar.className = 'password-strength-bar';
            if (val.length > 0) {
                if (strength <= 1) strengthBar.classList.add('strength-weak');
                else if (strength === 2) strengthBar.classList.add('strength-medium');
                else strengthBar.classList.add('strength-strong');
            }
        });

        function togglePassword(fieldId) {
            const input = document.getElementById(fieldId);
            const button = input.nextElementSibling.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        async function handleResetSubmit(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            document.getElementById('errorAlert').classList.remove('show');
            
            if (password !== confirm) {
                document.getElementById('errorAlert').textContent = 'Les mots de passe ne correspondent pas';
                document.getElementById('errorAlert').classList.add('show');
                return;
            }
            
            btn.classList.add('loading');
            btn.disabled = true;
            
            try {
                const formData = new FormData(e.target);
                
                const response = await fetch('<?= base_url("Auth/update_password") ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirection vers page de succès
                    window.location.href = '<?= base_url("Auth/reset_success") ?>';
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
    </script>

