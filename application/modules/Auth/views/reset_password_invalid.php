<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
<style type="text/css">

        .error-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
            text-align: center;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .error-icon {
            width: 100px;
            height: 100px;
            background: #f8d7da;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: #dc3545;
            font-size: 50px;
        }

        .error-title {
            color: var(--primary-dark);
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .error-text {
            color: #6c757d;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .btn-retry {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 30px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-retry:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 76, 58, 0.3);
            color: white;
        }

        .help-text {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #dee2e6;
            font-size: 13px;
            color: #6c757d;
        }

        .help-text a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            
            <h1 class="error-title">Lien expiré ou invalide</h1>
            
            <p class="error-text">
                <?= isset($error) ? $error : 'Ce lien de réinitialisation a expiré ou a déjà été utilisé. Pour des raisons de sécurité, les liens de réinitialisation sont valables pendant 1 heure uniquement.' ?>
            </p>

            <a href="<?= base_url('Auth/forgot_password') ?>" class="btn-retry">
                <i class="bi bi-arrow-repeat"></i>
                Faire une nouvelle demande
            </a>

            <div class="help-text">
                Besoin d'aide ? <a href="<?= base_url('Home/Contact') ?>">Contactez-nous</a>
            </div>
        </div>
    </div>

