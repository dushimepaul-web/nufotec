<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | AGF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-green: #0B4F2E;
            --secondary-green: #1B7B4B;
            --accent-green: #27ae60;
            --light-green: #2ecc71;
            --jaune: #FFD700;
            --orange: #FF6B35;
            --rouge: #E74C3C;
            --bleu: #3498DB;
            --bg-light: #f8faf9;
            --shadow-hover: 0 15px 35px rgba(11, 79, 46, 0.15);
            --radius: 16px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-light) 0%, #e8f5e9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .status-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-hover);
            padding: 3rem;
            width: 100%;
            max-width: 600px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 1rem 1rem 1rem 3rem;
            border-radius: 50px;
            border: 2px solid var(--border-light);
            font-size: 1rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .status-result {
            margin-top: 2rem;
            padding: 2rem;
            border-radius: var(--radius);
            text-align: center;
        }

        .status-pending {
            background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
            border: 2px solid var(--jaune);
        }

        .status-approved {
            background: linear-gradient(135deg, #e8f5e9 0%, #d4edda 100%);
            border: 2px solid var(--accent-green);
        }

        .status-rejected {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 2px solid var(--rouge);
        }

        .status-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2.5rem;
        }

        .status-pending .status-icon {
            background: var(--jaune);
            color: #856404;
        }

        .status-approved .status-icon {
            background: var(--accent-green);
            color: white;
        }

        .status-rejected .status-icon {
            background: var(--rouge);
            color: white;
        }

        .progress-custom {
            height: 8px;
            border-radius: 10px;
            background: var(--bg-light);
            margin: 2rem 0;
            overflow: hidden;
        }

        .progress-bar-custom {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>

    <div class="status-card">
        <div class="text-center mb-4">
            <i class="fas fa-search fa-3x mb-3" style="color: var(--secondary-green);"></i>
            <h2 style="color: var(--primary-green);">Vérifier mon statut</h2>
            <p class="text-muted">Entrez votre email pour consulter l'état de votre candidature</p>
        </div>

        <form action="<?= base_url('eoi_register/status') ?>" method="post">
            <div class="search-box">
                <i class="fas fa-envelope"></i>
                <input type="email" name="check_email" class="form-control" placeholder="votre@email.com" required>
            </div>
            <button type="submit" class="btn w-100 mt-3 py-3" style="background: var(--secondary-green); color: white; border-radius: 50px; font-weight: 600;">
                <i class="fas fa-search me-2"></i>Vérifier mon statut
            </button>
        </form>

        <?php if (isset($not_found) && $not_found): ?>
            <div class="alert alert-warning mt-4 rounded-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Aucune candidature trouvée avec cet email. Vérifiez l'adresse ou <a href="<?= base_url('eoi_register') ?>" style="color: var(--primary-green); font-weight: 600;">inscrivez-vous</a>.
            </div>
        <?php endif; ?>

        <?php if (isset($intermediary)): ?>
            <div class="status-result status-<?= $intermediary->is_authorized ? 'approved' : 'pending' ?>">
                <div class="status-icon">
                    <i class="fas <?= $intermediary->is_authorized ? 'fa-check' : 'fa-clock' ?>"></i>
                </div>
                <h4 class="mb-2"><?= $intermediary->full_name ?></h4>
                <p class="text-muted mb-3"><?= $intermediary->email ?></p>
                
                <?php if ($intermediary->is_authorized): ?>
                    <span class="badge bg-success mb-3" style="font-size: 1rem; padding: 0.5rem 1rem;">
                        <i class="fas fa-check-circle me-1"></i>Compte approuvé
                    </span>
                    <p class="mb-0">Votre compte est actif. Vous pouvez maintenant accéder à votre espace.</p>
                    <a href="#" class="btn mt-3" style="background: var(--accent-green); color: white; border-radius: 50px; padding: 0.75rem 2rem;">
                        Accéder à mon espace
                    </a>
                <?php else: ?>
                    <span class="badge bg-warning text-dark mb-3" style="font-size: 1rem; padding: 0.5rem 1rem;">
                        <i class="fas fa-hourglass-half me-1"></i>En attente de validation
                    </span>
                    <p class="mb-0">Votre candidature est en cours d'examen. Vous serez notifié par email dès que votre compte sera approuvé.</p>
                    
                    <div class="progress-custom">
                        <div class="progress-bar-custom bg-warning" style="width: 60%;"></div>
                    </div>
                    <small class="text-muted">Étape 2 sur 3 : Vérification du profil</small>
                <?php endif; ?>

                <div class="mt-4 pt-3 border-top">
                    <small class="text-muted">
                        <i class="far fa-calendar-alt me-1"></i>
                        Inscrit le <?= date('d/m/Y', strtotime($intermediary->submitted_at)) ?>
                    </small>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="<?= base_url('eoi_register') ?>" style="color: var(--secondary-green); text-decoration: none;">
                <i class="fas fa-arrow-left me-1"></i>Retour au formulaire d'inscription
            </a>
        </div>
    </div>

</body>
</html>