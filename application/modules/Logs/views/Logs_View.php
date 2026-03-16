<?php include VIEWPATH.'includes/backend/Header.php' ;?>
<?php include VIEWPATH.'includes/backend/Sidebar.php' ;?>
<?php include VIEWPATH.'includes/backend/Topheader.php' ;?>

<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Système</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Logs d'activité</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a class="btn btn-outline-danger" href="javascript:;" data-bs-toggle="modal" data-bs-target="#clearLogs">Vider les logs</a>
            </div>
        </div>
        <hr/>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Utilisateur</th>
                                <th>Action</th>
                                <th>Niveau</th>
                                <th>IP</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php $i=1; foreach ($logs as $value) { 
                            // Détermination de la couleur selon le niveau
                            $badge_class = "bg-info";
                            if($value['niveau'] == 'warning') $badge_class = "bg-warning text-dark";
                            if($value['niveau'] == 'error') $badge_class = "bg-danger";
                            if($value['niveau'] == 'critical') $badge_class = "bg-dark text-white";
                        ?>
                            <tr>
                                <td><?=$i++;?></td>
                                <td><?=date('d/m/Y H:i', strtotime($value['created_at']))?></td>
                                <td><?=$value['user_id'] ? "ID: ".$value['user_id'] : '<span class="text-muted">Anonyme</span>'?></td>
                                <td><strong><?=$value['action']?></strong></td>
                                <td><span class="badge <?=$badge_class?>"><?=$value['niveau']?></span></td>
                                <td><?=$value['ip_address']?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detail_<?=$value['id']?>">
                                        <i class="bx bx-show"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete_<?=$value['id']?>">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="detail_<?=$value['id']?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Détails du Log #<?=$value['id']?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <table class="table table-bordered">
                                                <tr><th>Description</th><td><?=$value['description']?></td></tr>
                                                <tr><th>URL</th><td><code><?=$value['url']?></code></td></tr>
                                                <tr><th>Méthode</th><td><span class="badge bg-secondary"><?=strtoupper($value['method'])?></span></td></tr>
                                                <tr><th>User Agent</th><td><small><?=$value['user_agent']?></small></td></tr>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="delete_<?=$value['id']?>">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="<?=base_url('Logs/Delete')?>" method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Supprimer ce log ?</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <input type="hidden" name="id" value="<?=$value['id']?>">
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="clearLogs">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?=base_url('Logs/ClearAll')?>" method="POST">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Vider tout l'historique ?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Êtes-vous sûr de vouloir supprimer <strong>tous les logs</strong> ? Cette action est irréversible.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Tout effacer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php' ;?>