<!doctype html>
<html lang="fr" data-bs-theme="light">
<head>
    <!-- Meta tags obligatoires -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Connexion à African Green Farmers - Plateforme agricole et médicale">
    <meta name="author" content="African Green Farmers">
    
    <!-- CSRF Token pour sécurité -->
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="<?= base_url('assets/frontend/img/logo/agf-favicon.png') ?>" type="image/png">
    
    <!-- Titre dynamique -->
    <title><?= $this->Model->get_setting('site_name', 'NUFOTEC') ?>-Connexion</title>
    
    <!-- Plugins CSS -->
    <link href="<?= base_url('assets/backend/plugins/simplebar/css/simplebar.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/backend/plugins/perfect-scrollbar/css/perfect-scrollbar.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/backend/plugins/metismenu/css/metisMenu.min.css') ?>" rel="stylesheet">
    
    <!-- Loader CSS -->
    <link href="<?= base_url('assets/backend/css/pace.min.css') ?>" rel="stylesheet">
    <script src="<?= base_url('assets/backend/js/pace.min.js') ?>"></script>
    
    <!-- Bootstrap CSS -->
    <link href="<?= base_url('assets/backend/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/backend/css/bootstrap-extended.css') ?>" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Thèmes -->
    <link href="<?= base_url('assets/backend/sass/app.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/backend/sass/dark-theme.css') ?>">
    <link href="<?= base_url('assets/backend/css/icons.css') ?>" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #29B6F6;
            --secondary-color: #FFFFFF;
            --dark-green: #2E7D32;
            --accent-color: #81C784;
            --red-star: #DC143C;
            --white: #FFFFFF;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--secondary-color);
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('<?= base_url('assets/images/pattern.png') ?>') repeat;
            opacity: 0.1;
            pointer-events: none;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
            overflow: hidden;
            position: relative;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--red-star));
        }

        .logo-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            background: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px rgba(41,182,246,0.2);
            border: 3px solid var(--primary-color);
        }

        .logo-wrapper img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(41,182,246,0.25);
        }

        .input-group-text {
            border: 2px solid #e0e0e0;
            border-left: none;
            border-radius: 0 12px 12px 0;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .input-group-text:hover {
            background: #f8f9fa;
            color: var(--primary-color);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(41,182,246,0.3);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34,139,34,0.3);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .forgot-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .forgot-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .register-link {
            color: var(--secondary-color);
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
        }

        .register-link:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background: rgba(220,20,60,0.1);
            color: var(--red-star);
            border-left: 4px solid var(--red-star);
        }

        .alert-success {
            background: rgba(34,139,34,0.1);
            color: var(--secondary-color);
            border-left: 4px solid var(--secondary-color);
        }

        .password-strength {
            height: 5px;
            border-radius: 5px;
            margin-top: 10px;
            transition: all 0.3s;
        }

        /* Loading spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
            
            .p-4 {
                padding: 1rem !important;
            }
        }
    </style>
</head>

<body>
    <!-- Loader principal -->
    <div id="global-loader" style="display: none;">
        <div class="loader-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>
    </div>

    <!-- Wrapper -->
    <div class="wrapper" style="margin-top: 12px;">
        <div class="section-authentication-signin d-flex align-items-center justify-content-center min-vh-100 py-5" style="margin-top: 12px;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-4 col-lg-5 col-md-6 col-sm-8">
                        <!-- Card de connexion -->
                        <div class="card mb-0 animate__animated animate__fadeIn">
                            <div class="card-body p-4">
                                <div class="p-3">
                                    <!-- Logo -->
                                    <div class="logo-wrapper d-flex justify-content-center align-items-center">
    <a href="<?= base_url() ?>" class="d-block">
        <img src="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'logo.png')) ?>" 
             alt="AGF Logo"
             class="img-fluid" 
             style="max-height: 80px; width: auto;">
    </a>
</div>
                                    
                                    <!-- Titre -->
                                    <div class="text-center mb-4">
                                        <h4 class="fw-bold">Bienvenue !</h4>
                                        <p class="text-muted mb-0"  style="color: var(--dark-green);">Connectez-vous à votre compte</p>
                                    </div>

                                    <!-- Messages flash -->
                                    <?php if($this->session->flashdata('sms')): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="bx bx-error-circle me-2"></i>
                                            <?= $this->session->flashdata('sms') ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>

                                    <?php if($this->session->flashdata('success')): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="bx bx-check-circle me-2"></i>
                                            <?= $this->session->flashdata('success') ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Formulaire de connexion -->
                                    <form action="<?= base_url('Admin/do_login') ?>" 
                                          method="POST" 
                                          class="row g-4 needs-validation" 
                                          id="loginForm"
                                          novalidate>
                                          
                                        <!-- CSRF Token -->
                                        <input type="hidden" 
                                               name="<?= $this->security->get_csrf_token_name() ?>" 
                                               value="<?= $csrf_token ?? $this->security->get_csrf_hash() ?>">

                                        <!-- Email -->
                                        <div class="col-12">
                                            <label for="email" class="form-label">
                                                <i class="bx bx-envelope me-1"></i>Adresse Email
                                            </label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email" 
                                                   name="email" 
                                                   placeholder="exemple@domaine.com" 
                                                   value="<?= set_value('email') ?>"
                                                   required 
                                                   autocomplete="email"
                                                   autofocus>
                                            <div class="invalid-feedback">
                                                Veuillez entrer une adresse email valide.
                                            </div>
                                        </div>

                                        <!-- Mot de passe -->
                                        <div class="col-12">
                                            <label for="password" class="form-label">
                                                <i class="bx bx-lock-alt me-1"></i>Mot de passe
                                            </label>
                                            <div class="input-group" id="show_hide_password">
                                                <input type="password" 
                                                       class="form-control border-end-0" 
                                                       id="password" 
                                                       name="password" 
                                                       placeholder="••••••••" 
                                                       required 
                                                       autocomplete="current-password"
                                                       minlength="6">
                                                <a href="javascript:;" class="input-group-text bg-transparent">
                                                    <i class='bx bx-hide'></i>
                                                </a>
                                                <div class="invalid-feedback">
                                                    Le mot de passe est requis.
                                                </div>
                                            </div>
                                            <!-- Indicateur de force du mot de passe (optionnel) -->
                                            <div class="password-strength" id="passwordStrength"></div>
                                        </div>

                                        <!-- Options -->
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="rememberMe" 
                                                       name="remember_me">
                                                <label class="form-check-label" for="rememberMe">
                                                    Se souvenir de moi
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 text-end">
                                            <a href="<?= base_url('Admin/forgot_password') ?>" 
                                               class="forgot-link">
                                                <i class="bx bx-help-circle me-1"></i>Mot de passe oublié ?
                                            </a>
                                        </div>

                                        <!-- Bouton de connexion -->
                                        <div class="col-12">
                                            <button type="submit" 
                                                    class="btn btn-primary w-100" 
                                                    id="loginBtn">
                                                <span class="btn-text">Se connecter</span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                            </button>
                                        </div>

                                        <!-- Lien d'inscription -->
                                        <div class="col-12">
                                            <div class="text-center mt-3">
                                                <p class="mb-0">
                                                    Pas encore de compte ? 
                                                    <a href="<?= base_url('Admin/register') ?>" 
                                                       class="register-link">
                                                        S'inscrire <i class="bx bx-right-arrow-alt"></i>
                                                    </a>
                                                </p>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Séparateur -->
                                    <div class="text-center mt-4">
                                        <p class="text-muted small mb-0">
                                            <i class="bx bx-shield-quarter me-1"></i>
                                            Connexion sécurisée
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer de la page de login -->
                        <div class="text-center mt-4 text-white">
                            <p class="small opacity-75">
                                &copy; <?= date('Y') ?> African Green Farmers. Tous droits réservés.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script src="<?= base_url('assets/backend/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/backend/js/bootstrap.bundle.min.js') ?>"></script>
    
    <!-- Plugins -->
    <script src="<?= base_url('assets/backend/plugins/simplebar/js/simplebar.min.js') ?>"></script>
    <script src="<?= base_url('assets/backend/plugins/metismenu/js/metisMenu.min.js') ?>"></script>
    <script src="<?= base_url('assets/backend/plugins/perfect-scrollbar/js/perfect-scrollbar.js') ?>"></script>
    
    <!-- App JS -->
    <script src="<?= base_url('assets/backend/js/app.js') ?>"></script>

    <script>
        $(document).ready(function() {
            // =========================================
            // 1. GESTION AFFICHAGE/CACHÉ MOT DE PASSE
            // =========================================
            $("#show_hide_password a").on('click', function(event) {
                event.preventDefault();
                var input = $(this).closest('.input-group').find('input');
                var icon = $(this).find('i');
                
                if (input.attr('type') == "password") {
                    input.attr('type', 'text');
                    icon.removeClass('bx-hide').addClass('bx-show');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('bx-show').addClass('bx-hide');
                }
            });

            // =========================================
            // 2. VALIDATION DU FORMULAIRE (Bootstrap)
            // =========================================
            var forms = document.querySelectorAll('.needs-validation');
            
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
                        // Afficher le loader
                        $('#loginBtn .btn-text').hide();
                        $('#loginBtn .spinner-border').removeClass('d-none');
                        $('#loginBtn').prop('disabled', true);
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });

            // =========================================
            // 3. INDICATEUR DE FORCE DU MOT DE PASSE
            // =========================================
            $('#password').on('keyup', function() {
                var password = $(this).val();
                var strength = getPasswordStrength(password);
                var strengthBar = $('#passwordStrength');
                
                strengthBar.css('width', strength.percentage + '%');
                
                if (strength.level === 'faible') {
                    strengthBar.css('background', 'linear-gradient(90deg, #dc3545, #ff6b6b)');
                } else if (strength.level === 'moyen') {
                    strengthBar.css('background', 'linear-gradient(90deg, #ffc107, #ffdb6b)');
                } else if (strength.level === 'fort') {
                    strengthBar.css('background', 'linear-gradient(90deg, #28a745, #6bc96b)');
                } else {
                    strengthBar.css('background', 'transparent');
                }
            });

            function getPasswordStrength(password) {
                var strength = 0;
                
                if (password.length > 0) strength += 10;
                if (password.length >= 6) strength += 10;
                if (password.length >= 8) strength += 20;
                if (password.match(/[a-z]+/)) strength += 10;
                if (password.match(/[A-Z]+/)) strength += 10;
                if (password.match(/[0-9]+/)) strength += 20;
                if (password.match(/[$@#&!]+/)) strength += 20;
                
                if (strength < 30) {
                    return { level: 'faible', percentage: strength };
                } else if (strength < 60) {
                    return { level: 'moyen', percentage: strength };
                } else {
                    return { level: 'fort', percentage: Math.min(strength, 100) };
                }
            }

            // =========================================
            // 4. GESTION DU "REMEMBER ME"
            // =========================================
            $('#rememberMe').on('change', function() {
                if ($(this).is(':checked')) {
                    localStorage.setItem('remember_email', $('#email').val());
                } else {
                    localStorage.removeItem('remember_email');
                }
            });

            // Récupérer l'email sauvegardé
            var savedEmail = localStorage.getItem('remember_email');
            if (savedEmail) {
                $('#email').val(savedEmail);
                $('#rememberMe').prop('checked', true);
            }

            // =========================================
            // 5. RÉINITIALISATION DU LOADER APRÈS SOUMISSION
            // =========================================
            $(window).on('load', function() {
                // Si le formulaire a été soumis et qu'il y a une erreur
                $('#loginBtn .btn-text').show();
                $('#loginBtn .spinner-border').addClass('d-none');
                $('#loginBtn').prop('disabled', false);
            });

            // =========================================
            // 6. PROTECTION CONTRE LA SOUMISSION MULTIPLE
            // =========================================
            $('#loginForm').on('submit', function() {
                if ($(this).data('submitted') === true) {
                    return false;
                }
                $(this).data('submitted', true);
            });

            // =========================================
            // 7. ANIMATION DES CHAMPS
            // =========================================
            $('.form-control').on('focus', function() {
                $(this).closest('.col-12').addClass('focused');
            }).on('blur', function() {
                $(this).closest('.col-12').removeClass('focused');
            });

            // =========================================
            // 8. NETTOYAGE DES ALERTS APRÈS 5 SECONDES
            // =========================================
            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        });

        // =========================================
        // 9. PRÉVENTION DU CLIC DROIT (OPTIONNEL)
        // =========================================
        document.addEventListener('contextmenu', function(e) {
            if (e.target.tagName === 'IMG') {
                e.preventDefault();
            }
        });

        // =========================================
        // 10. VERROUILLAGE APRÈS TENTATIVES (À ACTIVER SI BESOIN)
        // =========================================
        var loginAttempts = 0;
        $('#loginForm').on('submit', function() {
            loginAttempts++;
            if (loginAttempts >= 5) {
                $('#loginBtn').prop('disabled', true);
                $('#loginBtn').html('Trop de tentatives - Réessayez plus tard');
                setTimeout(function() {
                    loginAttempts = 0;
                    $('#loginBtn').prop('disabled', false);
                    $('#loginBtn').html('<span class="btn-text">Se connecter</span>');
                }, 900000); // 15 minutes
            }
        });
    </script>
</body>
</html>