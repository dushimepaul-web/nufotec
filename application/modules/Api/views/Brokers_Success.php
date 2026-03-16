<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription réussie | AGF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #0B4F2E;
            --secondary-green: #1B7B4B;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            padding: 3rem;
            max-width: 500px;
            text-align: center;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: #dcfce7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 3rem;
            color: var(--primary-green);
            animation: scaleIn 0.5s ease;
        }
        @keyframes scaleIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .btn-home {
            background: var(--primary-green);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            margin-top: 2rem;
            transition: all 0.3s;
        }
        .btn-home:hover {
            background: var(--secondary-green);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(11, 79, 46, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="success-card">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="mb-3" style="color: var(--primary-green);">Inscription réussie !</h2>
                    <p class="text-muted mb-4">
                        Votre inscription en tant que broker partenaire a bien été enregistrée.<br>
                        Un email de confirmation vous a été envoyé.
                    </p>
                    <p class="mb-4">
                        Notre équipe va étudier votre dossier et vous contactera dans les plus brefs délais.
                    </p>
                    <a href="<?= base_url() ?>" class="btn-home">
                        <i class="fas fa-home me-2"></i>Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>