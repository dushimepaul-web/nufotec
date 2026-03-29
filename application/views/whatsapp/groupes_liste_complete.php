<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des groupes WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .group-id { font-family: monospace; font-size: 12px; background: #f8f9fa; padding: 2px 5px; border-radius: 3px; }
        .table-hover tbody tr:hover { background-color: #f5f5f5; cursor: pointer; }
        .badge-active { background-color: #28a745; }
        .badge-inactive { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fab fa-whatsapp"></i> Groupes WhatsApp
                            <span class="badge bg-light text-dark float-end">Total: <?= $total ?> groupes</span>
                        </h4>
                    </div>
                    <div class="card-body">
                        
                        <?php if($this->session->flashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Actions -->
                        <div class="mb-3">
                            <a href="<?= site_url('whatsapp/synchroniser') ?>" class="btn btn-primary">
                                <i class="fas fa-sync-alt"></i> Synchroniser depuis WhatsApp
                            </a>
                            <a href="<?= site_url('whatsapp/envoyer_par_nom') ?>" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Envoyer un message
                            </a>
                            <button onclick="copierTousIds()" class="btn btn-secondary">
                                <i class="fas fa-copy"></i> Copier tous les IDs
                            </button>
                        </div>
                        
                        <!-- Tableau des groupes -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="50">#</th>
                                        <th width="200">Nom du groupe</th>
                                        <th>ID WhatsApp (à copier)</th>
                                        <th width="300">Description</th>
                                        <th width="100">Statut</th>
                                        <th width="100">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($groupes)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> Aucun groupe trouvé. 
                                            <a href="<?= site_url('whatsapp/synchroniser') ?>">Synchronisez depuis WhatsApp</a>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach($groupes as $index => $groupe): ?>
                                        <tr onclick="selectionnerGroupe('<?= $groupe['groupe_id'] ?>', '<?= addslashes($groupe['nom']) ?>')">
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <strong>
                                                    <i class="fab fa-whatsapp text-success"></i>
                                                    <?= htmlspecialchars($groupe['nom']) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <code class="group-id" id="group-id-<?= $index ?>">
                                                    <?= htmlspecialchars($groupe['groupe_id']) ?>
                                                </code>
                                                <button onclick="event.stopPropagation(); copierId('<?= $groupe['groupe_id'] ?>')" 
                                                        class="btn btn-sm btn-outline-secondary ms-2">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </td>
                                            <td><?= htmlspecialchars($groupe['description']) ?></td>
                                            <td>
                                                <span class="badge bg-success">Actif</span>
                                            </td>
                                            <td>
                                                <button onclick="event.stopPropagation(); envoyerTest('<?= $groupe['groupe_id'] ?>', '<?= addslashes($groupe['nom']) ?>')" 
                                                        class="btn btn-sm btn-warning">
                                                    <i class="fas fa-comment"></i> Test
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Zone de copie des IDs -->
                        <div class="alert alert-info mt-3">
                            <strong><i class="fas fa-info-circle"></i> IDs à utiliser dans votre code :</strong>
                            <textarea id="all-ids" class="form-control mt-2 font-monospace" rows="5" readonly><?php 
                                foreach($groupes as $groupe) {
                                    echo $groupe['groupe_id'] . " => " . $groupe['nom'] . "\n";
                                }
                            ?></textarea>
                            <button onclick="copierTexte()" class="btn btn-sm btn-primary mt-2">
                                <i class="fas fa-copy"></i> Copier la liste
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function copierId(id) {
            navigator.clipboard.writeText(id).then(function() {
                alert('ID copié : ' + id);
            }).catch(function() {
                prompt('Copiez manuellement :', id);
            });
        }
        
        function copierTousIds() {
            let ids = [];
            <?php foreach($groupes as $groupe): ?>
                ids.push('<?= $groupe['groupe_id'] ?>');
            <?php endforeach; ?>
            
            let texte = ids.join('\n');
            navigator.clipboard.writeText(texte).then(function() {
                alert(ids.length + ' IDs copiés !');
            });
        }
        
        function copierTexte() {
            let texte = document.getElementById('all-ids').value;
            navigator.clipboard.writeText(texte).then(function() {
                alert('Liste copiée !');
            });
        }
        
        function envoyerTest(groupeId, groupeNom) {
            let message = prompt(`Message test pour le groupe "${groupeNom}" :`, "Test de connexion WhatsApp");
            if (message) {
                window.location.href = "<?= site_url('whatsapp/envoyer_test') ?>?groupe_id=" + encodeURIComponent(groupeId) + "&message=" + encodeURIComponent(message);
            }
        }
        
        function selectionnerGroupe(id, nom) {
            console.log("Groupe sélectionné:", nom, id);
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>