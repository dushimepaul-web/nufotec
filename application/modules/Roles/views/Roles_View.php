<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<!--start page wrapper -->
<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Admin</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Rôles</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-outline-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#role">
                <i class="bx bx-plus"></i> Nouveau Rôle
            </a>
        </div>
    </div>
    <!--end breadcrumb-->

    <hr/>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Créé le</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $i = 1; foreach ($roles as $value) { ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            
                            <td>
                                <strong><?= $value['nom'] ?></strong>
                            </td>

                            <td>
                                <code><?= $value['slug'] ?></code>
                            </td>

                            <td><?= substr($value['description'], 0, 50) ?>...</td>


                            <td><?= date('d/m/Y', strtotime($value['created_at'])) ?></td>

                            <td>
                                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Options
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item text-primary" href="javascript:void()" data-bs-toggle="modal" data-bs-target="#detail_<?= $value['id'] ?>">
                                        <i class="bx bx-show"></i> Voir détail
                                    </a>
                                    <a class="dropdown-item text-info" href="javascript:void()" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id'] ?>">
                                        <i class="bx bx-edit"></i> Modifier
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="javascript:void()" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id'] ?>">
                                        <i class="bx bx-trash"></i> Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- ========= MODAL DETAIL ========= -->
                        <div class="modal fade" id="detail_<?= $value['id'] ?>">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Détail du Rôle</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row mb-3">
                                            <div class="col-md-4"><strong>Nom:</strong></div>
                                            <div class="col-md-8"><?= $value['nom'] ?></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4"><strong>Slug:</strong></div>
                                            <div class="col-md-8"><code><?= $value['slug'] ?></code></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4"><strong>Niveau d'accès:</strong></div>
                                            <div class="col-md-8">
                                                <span class="badge bg-<?= $value['niveau'] >= 90 ? 'danger' : ($value['niveau'] >= 50 ? 'warning' : 'info') ?>">
                                                    <?= $value['niveau'] ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4"><strong>Description:</strong></div>
                                            <div class="col-md-8"><?= nl2br($value['description']) ?></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4"><strong>Créé le:</strong></div>
                                            <div class="col-md-8"><?= date('d/m/Y H:i:s', strtotime($value['created_at'])) ?></div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========= MODAL UPDATE ========= -->
                        <div class="modal fade" id="update_<?= $value['id'] ?>" data-bs-backdrop="static">
                            <div class="modal-dialog modal-ms">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Modifier le Rôle</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Roles/Update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">

                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="mb-3">
                                                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" value="<?= $value['nom'] ?>" name="nom" required>
                                                </div>

                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label">Slug <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" value="<?= $value['slug'] ?>" name="slug" required>
                                                    <small class="text-muted">Identifiant unique (pas d'espaces, minuscules)</small>
                                                </div>
                                            </div>

                                            <div class="form-floating">
                                                <textarea class="form-control" name="description" style="height:120px;"><?= $value['description'] ?></textarea>
                                                <label>Description</label>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                                            <button type="submit" class="btn btn-info">Enregistrer</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>

                        <!-- ========= MODAL DELETE ========= -->
                        <div class="modal fade" id="delete_<?= $value['id'] ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Voulez-vous supprimer ?</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <p>Êtes-vous sûr de vouloir supprimer le rôle <strong><?= $value['nom'] ?></strong> ?</p>
                                        <p class="text-danger"><small>Attention: Cette action est impossible si des utilisateurs utilisent ce rôle.</small></p>
                                    </div>

                                    <form action="<?= base_url('Roles/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-info">Supprimer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php } ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Créé le</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>

                </table>
            </div>
        </div>
    </div>

    <hr/>

</div>
</div>

<!-- ========= MODAL NEW ROLE ========= -->
<div class="modal fade" id="role" data-bs-backdrop="static">
    <div class="modal-dialog modal-ms">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Nouveau Rôle</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Roles/Create') ?>" method="POST">
                <div class="modal-body">

                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>

                    </div>

                    <div class="form-floating">
                        <textarea class="form-control" name="description" style="height:120px;"></textarea>
                        <label>Description</label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-info">Enregistrer</button>
                </div>

            </form>

        </div>
    </div>


<?php include VIEWPATH.'includes/backend/Footer.php'; ?>