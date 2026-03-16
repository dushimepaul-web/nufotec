<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
// Helper pour les icônes des réseaux sociaux
$plateforme_icons = [
    'WhatsApp' => 'whatsapp',
    'Telegram' => 'telegram',
    'Facebook' => 'facebook',
    'Instagram' => 'instagram',
    'TikTok' => 'tiktok',
    'YouTube' => 'youtube',
    'Twitter' => 'twitter',
    'LinkedIn' => 'linkedin',
    'Discord' => 'discord',
    'Reddit' => 'reddit'
];

$plateforme_colors = [
    'WhatsApp' => '#25D366',
    'Telegram' => '#0088cc',
    'Facebook' => '#1877F2',
    'Instagram' => '#E4405F',
    'TikTok' => '#000000',
    'YouTube' => '#FF0000',
    'Twitter' => '#1DA1F2',
    'LinkedIn' => '#0A66C2',
    'Discord' => '#5865F2',
    'Reddit' => '#FF4500'
];
?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Statistiques</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Réseaux Sociaux</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_stat">
                <i class="bx bx-plus"></i> Nouvelle Statistique
            </a>
        </div>
    </div>

    <!-- Messages flash -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-check-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-error-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <?php 
        $total_participants = 0;
        $total_groupes = 0;
        if (!empty($statistiques)) {
            foreach ($statistiques as $stat) {
                $total_participants += $stat['nombre_participants'] ?? 0;
                $total_groupes += $stat['nombre_groupes'] ?? 0;
            }
        }
        ?>
        <div class="col-md-6 mb-2">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">Total Participants</h6>
                        <h3 class="mb-0"><?= number_format($total_participants, 0, ',', ' ') ?></h3>
                    </div>
                    <i class="bx bx-group fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-2">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">Total Groupes</h6>
                        <h3 class="mb-0"><?= number_format($total_groupes, 0, ',', ' ') ?></h3>
                    </div>
                    <i class="bx bx-chat fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-primary"><i class="bx bx-chart me-2"></i>Statistiques par Plateforme</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="statsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Plateforme</th>
                            <th width="15%">Groupes</th>
                            <th width="20%">Participants</th>
                            <th width="15%">Croissance</th>
                            <th width="12%">Date</th>
                            <th width="10%">Page</th>
                            <th width="8%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($statistiques)): $i = 1; foreach ($statistiques as $stat): 
                        // Utiliser l'opérateur ?? pour éviter les erreurs "undefined index"
                        $stat = array_merge([
                            'id_stat' => null,
                            'plateforme' => '',
                            'nom_groupe' => '',
                            'nombre_groupes' => 0,
                            'nombre_participants' => 0,
                            'croissance_mensuelle' => 0,
                            'date_mesure' => date('Y-m-d'),
                            'id_page_associee' => null
                        ], $stat);
                        
                        $icon = $plateforme_icons[$stat['plateforme']] ?? 'globe';
                        $color = $plateforme_colors[$stat['plateforme']] ?? '#6c757d';
                        $growth = floatval($stat['croissance_mensuelle'] ?? 0);
                        $growth_class = $growth >= 0 ? 'text-success' : 'text-danger';
                        $growth_icon = $growth >= 0 ? 'bx-trending-up' : 'bx-trending-down';
                        
                        // Trouver le titre de la page associée
                        $page_titre = '';
                        if (!empty($stat['id_page_associee']) && !empty($pages)) {
                            foreach ($pages as $page) {
                                if ($page['id_page'] == $stat['id_page_associee']) {
                                    $page_titre = $page['titre_page'] ?? '';
                                    break;
                                }
                            }
                        }
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="social-icon me-2" style="width: 40px; height: 40px; background: <?= $color ?>; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bx bxl-<?= $icon ?> text-white fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($stat['plateforme'] ?: 'N/A') ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($stat['nom_groupe'] ?? 'N/A') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info fs-6"><?= number_format($stat['nombre_groupes'] ?? 0, 0, ',', ' ') ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                        <?php 
                                        $max_participants = !empty($statistiques) ? max(array_column($statistiques, 'nombre_participants')) : 0;
                                        $percentage = $max_participants > 0 ? (($stat['nombre_participants'] ?? 0) / $max_participants) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                    <span class="fw-bold"><?= number_format($stat['nombre_participants'] ?? 0, 0, ',', ' ') ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="<?= $growth_class ?> fw-bold">
                                    <i class="bx <?= $growth_icon ?> me-1"></i>
                                    <?= $growth > 0 ? '+' : '' ?><?= number_format($growth, 1, ',', ' ') ?>%
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bx bx-calendar me-1"></i>
                                    <?= !empty($stat['date_mesure']) ? date('d/m/Y', strtotime($stat['date_mesure'])) : '-' ?>
                                </small>
                            </td>
                            <td>
                                <?php if (!empty($stat['id_page_associee']) && !empty($page_titre)): ?>
                                    <span class="badge bg-secondary text-decoration-none">
                                        <i class="bx bx-link-external me-1"></i><?= htmlspecialchars($page_titre) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted">Global</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit_stat_<?= $stat['id_stat'] ?>" title="Modifier">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delete_stat_<?= $stat['id_stat'] ?>" title="Supprimer">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bx bx-chart fs-1 mb-2"></i>
                                    <p>Aucune statistique trouvée</p>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#create_stat">
                                        <i class="bx bx-plus"></i> Ajouter une statistique
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

<!-- Modal Création -->
<div class="modal fade" id="create_stat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('Statistiques_reseaux/Create') ?>" method="post">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouvelle Statistique</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Plateforme <span class="text-danger">*</span></label>
                            <select name="plateforme" class="form-select" required>
                                <option value="">Choisir...</option>
                                <?php foreach ($plateforme_icons as $plat => $icon): ?>
                                    <option value="<?= $plat ?>"><?= $plat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nom du Groupe</label>
                            <input type="text" name="nom_groupe" class="form-control" placeholder="Ex: Groupe Principal">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre de Groupes <span class="text-danger">*</span></label>
                            <input type="number" name="nombre_groupes" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Participants <span class="text-danger">*</span></label>
                            <input type="number" name="nombre_participants" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Croissance Mensuelle (%)</label>
                            <input type="number" name="croissance_mensuelle" class="form-control" step="0.1" placeholder="Ex: 15.5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de mesure <span class="text-danger">*</span></label>
                            <input type="date" name="date_mesure" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Page Associée</label>
                            <select name="id_page_associee" class="form-select">
                                <option value="">Aucune (Global)</option>
                                <?php if (!empty($pages)): foreach ($pages as $page): ?>
                                    <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page'] ?? '') ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-save me-1"></i>Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals Édition -->
<?php if (!empty($statistiques)): foreach ($statistiques as $stat): 
    $stat = array_merge([
        'id_stat' => null,
        'plateforme' => '',
        'nom_groupe' => '',
        'nombre_groupes' => 0,
        'nombre_participants' => 0,
        'croissance_mensuelle' => 0,
        'date_mesure' => date('Y-m-d'),
        'id_page_associee' => null
    ], $stat);
?>
<div class="modal fade" id="edit_stat_<?= $stat['id_stat'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('Statistiques_reseaux/Update') ?>" method="post">
                <input type="hidden" name="id" value="<?= $stat['id_stat'] ?>">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier <?= htmlspecialchars($stat['plateforme']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Plateforme <span class="text-danger">*</span></label>
                            <select name="plateforme" class="form-select" required>
                                <?php foreach ($plateforme_icons as $plat => $icon): ?>
                                    <option value="<?= $plat ?>" <?= $stat['plateforme'] == $plat ? 'selected' : '' ?>><?= $plat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nom du Groupe</label>
                            <input type="text" name="nom_groupe" class="form-control" value="<?= htmlspecialchars($stat['nom_groupe'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre de Groupes <span class="text-danger">*</span></label>
                            <input type="number" name="nombre_groupes" class="form-control" min="0" value="<?= $stat['nombre_groupes'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Participants <span class="text-danger">*</span></label>
                            <input type="number" name="nombre_participants" class="form-control" min="0" value="<?= $stat['nombre_participants'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Croissance Mensuelle (%)</label>
                            <input type="number" name="croissance_mensuelle" class="form-control" step="0.1" value="<?= $stat['croissance_mensuelle'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de mesure <span class="text-danger">*</span></label>
                            <input type="date" name="date_mesure" class="form-control" value="<?= $stat['date_mesure'] ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Page Associée</label>
                            <select name="id_page_associee" class="form-select">
                                <option value="">Aucune (Global)</option>
                                <?php if (!empty($pages)): foreach ($pages as $page): ?>
                                    <option value="<?= $page['id_page'] ?>" <?= ($stat['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>><?= htmlspecialchars($page['titre_page'] ?? '') ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning"><i class="bx bx-save me-1"></i>Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Suppression -->
<div class="modal fade" id="delete_stat_<?= $stat['id_stat'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form action="<?= base_url('Statistiques_reseaux/Delete') ?>" method="post">
                <input type="hidden" name="id" value="<?= $stat['id_stat'] ?>">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bx bx-error-circle text-danger fs-1 mb-3"></i>
                    <p>Êtes-vous sûr de vouloir supprimer les statistiques <strong><?= htmlspecialchars($stat['plateforme']) ?></strong> ?</p>
                    <p class="text-muted small">Cette action est irréversible.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger"><i class="bx bx-trash me-1"></i>Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; endif; ?>

<script>
$(document).ready(function() {
    $('#statsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'asc']],
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [7] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>