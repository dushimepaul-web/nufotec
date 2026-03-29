<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'envoi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-warning">
                        <h4 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Confirmation d'envoi
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <i class="fas fa-broadcast-tower"></i>
                            <strong>Vous êtes sur le point d'envoyer un message à TOUS les groupes !</strong>
                        </div>
                        
                        <h5>Récapitulatif :</h5>
                        <ul class="list-group mb-3">
                            <li class="list-group-item">
                                <strong>Nombre de groupes :</strong> 
                                <span class="badge bg-primary"><?= $total_groupes ?></span>
                            </li>
                            <li class="list-group-item">
                                <strong>Message :</strong><br>
                                <div class="alert alert-light mt-2"><?= nl2br(htmlspecialchars($message)) ?></div>
                            </li>
                        </ul>
                        
                        <div class="list-group mb-3">
                            <div class="list-group-item active">
                                <i class="fas fa-users"></i> Groupes concernés :
                            </div>
                            <?php foreach($groupes as $groupe): ?>
                            <div class="list-group-item">
                                <i class="fab fa-whatsapp text-success"></i>
                                <?= htmlspecialchars($groupe['nom']) ?>
                                <small class="text-muted">(<?= $groupe['groupe_id'] ?>)</small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <form method="post" action="<?= site_url('whatsapp/envoyer_a_tous') ?>">
                            <input type="hidden" name="message" value="<?= htmlspecialchars($message) ?>">
                            <input type="hidden" name="confirm" value="1">
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="<?= site_url('whatsapp/liste_groupes') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-paper-plane"></i> Confirmer l'envoi à <?= $total_groupes ?> groupes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>