<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
function get_youtube_embed_url($url) {
    if (empty($url)) return '';
    $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_-]+)\??/i';
    $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))([a-zA-Z0-9_-]+)/i';
    if (preg_match($longUrlRegex, $url, $matches)) {
        if (!empty($matches[3])) {
            return 'https://www.youtube.com/embed/' . $matches[3];
        }
    }
    if (preg_match($shortUrlRegex, $url, $matches)) {
        if (!empty($matches[1])) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
    }
    return $url;
}
?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Contenu</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Témoignages Vidéo YouTube</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_temoignage">
                <i class="bx bx-plus"></i> Nouveau Témoignage Vidéo
            </a>
        </div>
    </div>

    <!-- Messages flash -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-check-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-error-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="row mb-4">
        <?php 
        $total_approuves = 0;
        $total_en_attente = 0;
        foreach ($temoignages as $t) {
            if ($t['est_approuve']) $total_approuves++;
            else $total_en_attente++;
        }
        ?>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">Total Vidéos</h6>
                        <h3 class="mb-0"><?= count($temoignages) ?></h3>
                    </div>
                    <i class="bx bx-video fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">Approuvés</h6>
                        <h3 class="mb-0"><?= $total_approuves ?></h3>
                    </div>
                    <i class="bx bx-check-circle fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">En Attente</h6>
                        <h3 class="mb-0"><?= $total_en_attente ?></h3>
                    </div>
                    <i class="bx bx-time fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-primary"><i class="bx bx-video me-2"></i>Liste des Témoignages Vidéo</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="temoignagesTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Miniature</th>
                            <th width="40%">Titre YouTube</th>
                            <th width="15%">Statut</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($temoignages)): $i = 1; foreach ($temoignages as $value): 
                        $miniature_path = !empty($value['miniature']) ? $value['miniature'] : 'assets/frontend/img/default-avatar.jpg';
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            
                            <td>
                                <img src="<?= $miniature_path ?>" 
                                     class="rounded border"
                                     style="width:70px; height:50px; object-fit:cover;"
                                     onerror="this.src='<?= base_url('assets/frontend/img/default-avatar.jpg') ?>'"
                                     alt="Miniature">
                            </td>

                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($value['titre'] ?? '') ?></strong>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_temoignage'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['est_approuve']) && $value['est_approuve'] == 1): ?>
                                        <span class="badge bg-success">Approuvé</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    <?php endif; ?>
                                </a>
                            </td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_temoignage'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_temoignage'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_temoignage'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id_temoignage'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-video me-2"></i>Détails du Témoignage Vidéo</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <h4 class="mb-3 text-dark fw-bold"><?= htmlspecialchars($value['titre'] ?? '') ?></h4>

                                        <?php if (!empty($value['video_url'])): ?>
                                            <div class="ratio ratio-16x9 mb-3">
                                                <iframe src="<?= get_youtube_embed_url($value['video_url']) ?>" title="YouTube video" allowfullscreen></iframe>
                                            </div>
                                        <?php endif; ?>

                                        <div class="text-center border-top pt-3">
                                            <small class="text-muted d-block">Statut</small>
                                            <span><?= !empty($value['est_approuve']) ? '<span class="badge bg-success">Approuvé</span>' : '<span class="badge bg-warning text-dark">En attente</span>' ?></span>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id_temoignage'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-md modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le Témoignage Vidéo</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Temoignages/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_temoignage" value="<?= $value['id_temoignage'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Lien de la Vidéo (YouTube) <span class="text-danger">*</span></label>
                                                    <input type="url" class="form-control" name="video_url" value="<?= htmlspecialchars($value['video_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=..." required>
                                                    <small class="text-muted">Le titre et la miniature officiels seront actualisés automatiquement depuis YouTube.</small>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Miniature personnalisée (Optionnel)</label>
                                                    <input type="file" class="form-control" name="miniature" accept="image/*">
                                                    <?php if (!empty($value['miniature'])): ?>
                                                        <small class="text-muted">Actuelle: <?= $value['miniature'] ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" name="est_approuve" id="est_approuve_<?= $value['id_temoignage'] ?>" value="1" <?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="est_approuve_<?= $value['id_temoignage'] ?>">Approuvé (visible sur le site)</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bx bx-save me-2"></i>Enregistrer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $value['id_temoignage'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer le témoignage vidéo.</p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Temoignages/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_temoignage'] ?>">
                                        <div class="modal-footer bg-light justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bx bx-trash me-2"></i>Supprimer définitivement
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL STATUS -->
                        <div class="modal fade" id="status_<?= $value['id_temoignage'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? '<i class="bx bx-hide me-2"></i>Mettre en attente' : '<i class="bx bx-check-circle me-2"></i>Approuver' ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? 'mettre en attente' : 'approuver' ?></strong> ce témoignage vidéo ?</p>
                                    </div>
                                    <form action="<?= base_url('Temoignages/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_temoignage'] ?>">
                                        <input type="hidden" name="est_approuve" value="<?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? 'btn-warning' : 'btn-success' ?>">
                                                <?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? 'Mettre en attente' : 'Approuver' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bx bx-video-off text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun témoignage vidéo trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- MODAL CREATE -->
<div class="modal fade" id="create_temoignage" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouveau Témoignage Vidéo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Temoignages/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Lien de la Vidéo (YouTube) <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="video_url" placeholder="https://www.youtube.com/watch?v=..." required>
                            <small class="text-muted">Le titre et la miniature officiels seront récupérés automatiquement depuis YouTube.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Miniature personnalisée (Optionnel)</label>
                            <input type="file" class="form-control" name="miniature" accept="image/*">
                            <small class="text-muted">Si vide, la miniature officielle YouTube sera utilisée.</small>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="est_approuve" id="create_est_approuve" value="1">
                                <label class="form-check-label" for="create_est_approuve">Approuvé immédiatement (visible sur le site)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#temoignagesTable').DataTable({
        language: {
            url: '<?= base_url("assets/backend/plugins/datatable/js/fr-FR.json") ?>'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [1, 3, 4] }
        ]
    });
    
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>
