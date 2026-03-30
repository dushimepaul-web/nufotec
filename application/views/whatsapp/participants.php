<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants - <?= htmlspecialchars($group['group_name'] ?? 'Groupe') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .container-fluid { padding: 20px; }
        .badge { font-size: 0.85em; }
        code { font-size: 0.9em; }
    </style>
</head>
<body>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-people-fill"></i> 
            Participants: <?= htmlspecialchars($group['group_name'] ?? 'Groupe inconnu') ?>
        </h2>
        <a href="<?= site_url('whatsapp/groupes') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour aux groupes
        </a>
    </div>
    
    <!-- Alertes -->
    <?php if (isset($from_cache) && $from_cache): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="bi bi-clock-history"></i> 
            <strong>Cache actif:</strong> Données en cache (API indisponible). 
            <a href="<?= site_url('whatsapp/sync_participants/' . urlencode($group['group_id'] ?? '')) ?>" class="alert-link">
                Réessayer la synchronisation
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (isset($sync_stats)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> 
            <strong>Synchronisé:</strong> 
            <?= $sync_stats['inserted'] ?> nouveaux, 
            <?= $sync_stats['updated'] ?> mis à jour, 
            <?= $sync_stats['deleted'] ?> supprimés
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Barre d'outils -->
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <span class="badge bg-primary fs-6">
                <i class="bi bi-person"></i> <?= count($participants) ?> participants
            </span>
            <div class="btn-group">
                <a href="<?= site_url('whatsapp/sync_participants/' . urlencode($group['group_id'] ?? '')) ?>" 
                   class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise"></i> Actualiser
                </a>
                <a href="https://wa.me/?text=Message%20au%20groupe" 
                   target="_blank" 
                   class="btn btn-success">
                    <i class="bi bi-whatsapp"></i> Message groupé
                </a>
                <button class="btn btn-outline-secondary" onclick="exporterCSV()">
                    <i class="bi bi-download"></i> Exporter
                </button>
            </div>
        </div>
    </div>
    
    <!-- Tableau des participants -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0" id="tableParticipants">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Numéro</th>
                            <th>Rôle</th>
                            <th>Dernière sync</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($participants)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p>Aucun participant trouvé</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($participants as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <code class="d-block"><?= htmlspecialchars($p['number_formatted'] ?? $p['phone']) ?></code>
                                    <small class="text-muted"><?= htmlspecialchars($p['phone']) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($p['is_creator'])): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-star-fill"></i> Créateur
                                        </span>
                                    <?php elseif (!empty($p['is_admin'])): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-shield-fill"></i> Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Membre</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($p->synced_at)): ?>
                                        <small><?= date('d/m/Y H:i', strtotime($p->synced_at)) ?></small>
                                    <?php elseif (!empty($p['synced_at'])): ?>
                                        <small><?= date('d/m/Y H:i', strtotime($p['synced_at'])) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                        $phone_clean = str_replace(['+', ' ', '-'], '', $p['number_formatted'] ?? $p['phone']);
                                    ?>
                                    <div class="btn-group btn-group-sm">
                                        <a href="https://wa.me/<?= $phone_clean ?>" 
                                           target="_blank" 
                                           class="btn btn-success" 
                                           title="Contacter sur WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                        <button class="btn btn-info" 
                                                onclick="copierNumero('<?= htmlspecialchars($p['phone']) ?>')" 
                                                title="Copier le numéro">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                        <a href="tel:<?= $p['number_formatted'] ?? $p['phone'] ?>" 
                                           class="btn btn-primary" 
                                           title="Appeler">
                                            <i class="bi bi-telephone"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Info footer -->
    <div class="mt-3 text-muted small">
        <i class="bi bi-info-circle"></i> 
        ID Groupe: <code><?= htmlspecialchars($group['group_id'] ?? 'N/A') ?></code>
        <?php if (!empty($group['group_description'])): ?>
            | Description: <?= htmlspecialchars($group['group_description']) ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/**
 * Copier le numéro dans le presse-papiers
 */
function copierNumero(numero) {
    navigator.clipboard.writeText(numero).then(() => {
        // Toast notification au lieu d'alert
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show bg-success text-white">
                <div class="toast-body">
                    <i class="bi bi-check-circle"></i> Numéro copié: ${numero}
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }).catch(err => {
        console.error('Erreur copie:', err);
        alert('Erreur lors de la copie');
    });
}

/**
 * Exporter les participants en CSV
 */
function exporterCSV() {
    const table = document.getElementById('tableParticipants');
    const rows = table.querySelectorAll('tbody tr');
    
    let csv = 'Numéro,Rôle\n';
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 3) {
            const numero = cells[1].querySelector('code')?.textContent?.trim() || '';
            const role = cells[2].querySelector('.badge')?.textContent?.trim() || '';
            csv += `"${numero}","${role}"\n`;
        }
    });
    
    // Télécharger
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'participants_<?= htmlspecialchars($group['group_id'] ?? 'groupe') ?>.csv';
    link.click();
}
</script>

</body>
</html>