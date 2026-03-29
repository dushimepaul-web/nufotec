<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultat de l'envoi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #075E54; color: white; }
    </style>
</head>
<body>
    <h1>Résultat de l'envoi</h1>
    
    <h3>Statistiques</h3>
    <ul>
        <li>Total groupes: <?= $resultat['total'] ?? 0 ?></li>
        <li>Succès: <?= $resultat['reussis'] ?? 0 ?></li>
        <li>Échecs: <?= $resultat['echoues'] ?? 0 ?></li>
    </ul>
    
    <h3>Message envoyé</h3>
    <p><?= nl2br(htmlspecialchars($message)) ?></p>
    
    <h3>Détails</h3>
    <table>
        <thead>
            <tr><th>Groupe</th><th>Statut</th><th>Code</th></tr>
        </thead>
        <tbody>
            <?php if(isset($resultat['details'])): ?>
                <?php foreach($resultat['details'] as $detail): ?>
                <tr class="<?= $detail['statut'] == 'succès' ? 'success' : 'error' ?>">
                    <td><?= htmlspecialchars($detail['groupe_id']) ?></td>
                    <td><?= $detail['statut'] ?></td>
                    <td><?= $detail['reponse']['status_code'] ?? 'N/A' ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <p><a href="<?= site_url('whatsapp/liste_groupes') ?>">Retour à la liste</a></p>
</body>
</html>