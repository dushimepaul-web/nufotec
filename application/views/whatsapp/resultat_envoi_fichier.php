<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat envoi fichier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-<?= $resultat['reussis'] == $resultat['total'] ? 'success' : 'warning' ?> text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-chart-line"></i> Résultat de l'envoi
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Statistiques -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="alert alert-primary text-center">
                                    <h2><?= $resultat['total'] ?></h2>
                                    <p>Groupes</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-success text-center">
                                    <h2><?= $resultat['reussis'] ?></h2>
                                    <p>Succès</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-danger text-center">
                                    <h2><?= $resultat['echoues'] ?></h2>
                                    <p>Échecs</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Fichier envoyé -->
                        <div class="alert alert-info">
                            <i class="fas fa-paperclip"></i>
                            <strong>Fichier :</strong> <?= htmlspecialchars($fichier_nom) ?><br>
                            <strong>Type :</strong> <?= htmlspecialchars($fichier_type) ?><br>
                            <?php if($caption): ?>
                            <strong>Légende :</strong> <?= nl2br(htmlspecialchars($caption)) ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Détails -->
                        <h5>Détails par groupe</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Groupe</th><th>Statut</th><th>Réponse</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($resultat['details'] as $detail): 
                                        $groupe = null;
                                        foreach($groupes_info as $g) {
                                            if($g['groupe_id'] == $detail['groupe_id']) {
                                                $groupe = $g;
                                                break;
                                            }
                                        }
                                    ?>
                                    <tr class="<?= $detail['statut'] == 'succès' ? 'table-success' : 'table-danger' ?>">
                                        <td>
                                            <i class="fab fa-whatsapp"></i>
                                            <?= $groupe ? htmlspecialchars($groupe['nom']) : $detail['groupe_id'] ?>
                                        </td>
                                        <td>
                                            <?php if($detail['statut'] == 'succès'): ?>
                                                <span class="badge bg-success">✓ Succès</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">✗ Échec</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= isset($detail['reponse']['error']) ? htmlspecialchars($detail['reponse']['error']) : 'OK' ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= site_url('whatsapp/envoyer_fichier') ?>" class="btn btn-primary">
                                <i class="fas fa-paperclip"></i> Nouvel envoi
                            </a>
                            <a href="<?= site_url('whatsapp/liste_groupes') ?>" class="btn btn-secondary">
                                <i class="fas fa-home"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>