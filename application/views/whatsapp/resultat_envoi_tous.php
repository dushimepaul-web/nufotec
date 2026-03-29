<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat de l'envoi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .success-row { background-color: #d4edda; }
        .error-row { background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 mx-auto">
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
                                <div class="card text-white bg-primary">
                                    <div class="card-body text-center">
                                        <h2><?= $resultat['total'] ?></h2>
                                        <p class="mb-0">Groupes total</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-success">
                                    <div class="card-body text-center">
                                        <h2><?= $resultat['reussis'] ?></h2>
                                        <p class="mb-0">Envois réussis</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-danger">
                                    <div class="card-body text-center">
                                        <h2><?= $resultat['echoues'] ?></h2>
                                        <p class="mb-0">Envois échoués</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Message envoyé -->
                        <div class="alert alert-info">
                            <strong><i class="fas fa-comment"></i> Message envoyé :</strong><br>
                            <?= nl2br(htmlspecialchars($message)) ?>
                        </div>
                        
                        <!-- Détails par groupe -->
                        <h5><i class="fas fa-list"></i> Détails par groupe :</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Groupe</th>
                                        <th>ID WhatsApp</th>
                                        <th>Statut</th>
                                        <th>Code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($resultat['details'] as $index => $detail): 
                                        $groupe = null;
                                        foreach($groupes as $g) {
                                            if ($g['groupe_id'] == $detail['groupe_id']) {
                                                $groupe = $g;
                                                break;
                                            }
                                        }
                                    ?>
                                    <tr class="<?= $detail['statut'] == 'succès' ? 'success-row' : 'error-row' ?>">
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <i class="fab fa-whatsapp text-success"></i>
                                            <?= $groupe ? htmlspecialchars($groupe['nom']) : $detail['groupe_id'] ?>
                                        </td>
                                        <td><code><?= htmlspecialchars($detail['groupe_id']) ?></code></td>
                                        <td>
                                            <?php if($detail['statut'] == 'succès'): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Succès
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle"></i> Échec
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $detail['reponse']['status_code'] ?? 'N/A' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="<?= site_url('whatsapp/liste_groupes') ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </a>
                            <a href="<?= site_url('whatsapp/envoyer_par_nom') ?>" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Nouvel envoi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>