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
        }

        .success-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-hover);
            padding: 4rem;
            text-align: center;
            max-width: 600px;
            width: 90%;
            position: relative;
            overflow: hidden;
        }

        .success-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-green), var(--accent-green), var(--light-green));
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--accent-green) 0%, var(--light-green) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease;
        }

        .success-icon i {
            font-size: 4rem;
            color: white;
        }

        @keyframes scaleIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }

        .status-timeline {
            margin: 2rem 0;
            padding: 0;
            list-style: none;
        }

        .status-timeline li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: var(--text-muted);
        }

        .status-timeline li.active {
            color: var(--primary-green);
            font-weight: 600;
        }

        .status-timeline li.completed {
            color: var(--accent-green);
        }

        .status-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--bg-light);
            border: 3px solid var(--border-light);
            margin-right: 1rem;
            position: relative;
        }

        .status-timeline li.active .status-dot {
            background: var(--jaune);
            border-color: var(--jaune);
            animation: pulse 2s infinite;
        }

        .status-timeline li.completed .status-dot {
            background: var(--accent-green);
            border-color: var(--accent-green);
        }

        .status-timeline li.completed .status-dot::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 0.7rem;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 215, 0, 0.4); }
            50% { box-shadow: 0 0 0 10px rgba(255, 215, 0, 0); }
        }

        .btn-home {
            background: linear-gradient(135deg, var(--secondary-green) 0%, var(--accent-green) 100%);
            color: white;
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(39, 174, 96, 0.3);
            color: white;
        }
    </style>
</head>
<body>

    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1 class="h2 mb-3" style="color: var(--primary-green);">Inscription Réussie !</h1>
        
        <p class="text-muted mb-4">
            Votre candidature a été enregistrée avec succès. Notre équipe va examiner votre profil sous 48h ouvrées.
        </p>

        <ul class="status-timeline text-start">
            <li class="completed">
                <div class="status-dot"></div>
                <span>Formulaire soumis</span>
            </li>
            <li class="active">
                <div class="status-dot"></div>
                <span>Vérification du profil en cours</span>
            </li>
            <li>
                <div class="status-dot"></div>
                <span>Validation et activation</span>
            </li>
            <li>
                <div class="status-dot"></div>
                <span>Accès au tableau de bord</span>
            </li>
        </ul>

        <div class="alert alert-info bg-light border-0">
            <i class="fas fa-envelope me-2" style="color: var(--bleu);"></i>
            Un email de confirmation vous a été envoyé.
        </div>

        <a href="<?= base_url() ?>" class="btn-home">
            <i class="fas fa-home me-2"></i>Retour à l'accueil
        </a>
        
        <div class="mt-4">
            <a href="<?= base_url('eoi_register/status') ?>" style="color: var(--secondary-green); text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-search me-1"></i>Vérifier mon statut plus tard
            </a>
        </div>
    </div>

</body>
</html>