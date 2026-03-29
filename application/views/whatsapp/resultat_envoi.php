<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultat - WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --whatsapp-teal: #128C7E;
            --whatsapp-light-teal: #25D366;
        }
        
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .result-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .result-header {
            background: var(--whatsapp-teal);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .success-icon {
            font-size: 4rem;
            color: var(--whatsapp-light-teal);
        }
        
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--whatsapp-teal);
        }
        
        .details-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .groupe-result {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .groupe-result.success {
            border-left: 4px solid var(--whatsapp-light-teal);
        }
        
        .groupe-result.error {
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>

<div class="result-container">
    <?php 
    $stats = isset($resultat['response']) ? $resultat['response'] : $resultat;
    $total = $stats['total'] ?? 0;
    $reussis = $stats['reussis'] ?? 0;
    $echoues = $stats['echoues'] ?? 0;
    $success = ($reussis > 0);
    ?>
    
    <div class="result-header">
        <?php if ($success): ?>
            <i class="bi bi-check-circle-fill success-icon"></i>
            <h2 class="mt-3">Envoi réussi !</h2>
            <p>Votre message a été envoyé avec succès</p>
        <?php else: ?>
            <i class="bi bi-x-circle-fill error-icon"></i>
            <h2 class="mt-3">Échec de l'envoi</h2>
            <p>Des erreurs sont survenues lors de l'envoi</p>
        <?php endif; ?>
    </div>
    
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-number"><?= $total ?></div>
            <small class="text-muted">Total</small>
        </div>
        <div class="stat-item">
            <div class="stat-number text-success"><?= $reussis ?></div>
            <small class="text-muted">Succès</small>
        </div>
        <div class="stat-item">
            <div class="stat-number <?= $echoues > 0 ? 'text-danger' : 'text-muted' ?>"><?= $echoues ?></div>
            <small class="text-muted">Échecs</small>
        </div>
    </div>
    
    <div class="p-4">
        <h5><i class="bi bi-chat-left-text me-2"></i>Message</h5>
        <div class="p-3 bg-light rounded">
            <?php if (isset($type_envoi) && $type_envoi === 'fichier'): ?>
                <span class="badge bg-info mb-2">Fichier</span>
            <?php else: ?>
                <span class="badge bg-success mb-2">Texte</span>
            <?php endif; ?>
            <p class="mb-0"><?= nl2br(htmlspecialchars($message)) ?></p>
        </div>
    </div>
    
    <?php if (!empty($stats['details'])): ?>
    <div class="px-4 pb-4">
        <h5 class="mb-3"><i class="bi bi-list-ul me-2"></i>Détails par groupe</h5>
        <div class="details-list border rounded">
            <?php foreach ($stats['details'] as $detail): 
                $groupe_nom = 'Inconnu';
                foreach ($groupes_info as $g) {
                    if ($g['groupe_id'] === $detail['destinataire_id']) {
                        $groupe_nom = $g['nom'];
                        break;
                    }
                }
                $isSuccess = ($detail['statut'] === 'succès');
            ?>
            <div class="groupe-result <?= $isSuccess ? 'success' : 'error' ?>">
                <i class="bi bi-<?= $isSuccess ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?> fs-4"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold"><?= htmlspecialchars($groupe_nom) ?></div>
                    <small class="text-muted"><?= substr($detail['destinataire_id'], 0, 30) ?>...</small>
                </div>
                <?php if (!$isSuccess && !empty($detail['erreur'])): ?>
                    <small class="text-danger"><?= htmlspecialchars($detail['erreur']) ?></small>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="p-4 text-center border-top">
        <a href="<?= site_url('whatsapp/envoyer') ?>" class="btn btn-success btn-lg me-2">
            <i class="bi bi-send me-2"></i>Nouvel envoi
        </a>
        <a href="<?= site_url('whatsapp/groupes') ?>" class="btn btn-outline-secondary btn-lg">
            <i class="bi bi-people me-2"></i>Groupes
        </a>
    </div>
</div>

</body>
</html>