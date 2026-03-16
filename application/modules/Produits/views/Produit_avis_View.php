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
                    <li class="breadcrumb-item active" aria-current="page">Avis Produits</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-outline-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#avis">
                <i class="bx bx-plus"></i> Nouvel Avis
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
                            <th>Produit</th>
                            <th>Utilisateur</th>
                            <th>Note</th>
                            <th>Titre</th>
                            <th>Commentaire</th>
                            <th>Validé</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $i = 1; foreach ($avis as $value) { 
                        // Récupérer le nom du produit
                        $produit_nom = '-';
                        foreach ($produits as $prod) {
                            if ($prod['id'] == $value['produit_id']) {
                                $produit_nom = $prod['nom'];
                                break;
                            }
                        }
                        
                        // Récupérer le nom de l'utilisateur
                        $user_nom = '-';
                        foreach ($users as $user) {
                            if ($user['id'] == $value['user_id']) {
                                $user_nom = $user['prenom'] . ' ' . $user['nom'];
                                break;
                            }
                        }
                        
                        // Générer les étoiles
                        $etoiles = '';
                        for ($j = 1; $j <= 5; $j++) {
                            if ($j <= $value['note']) {
                                $etoiles .= '<i class="bx bxs-star text-warning"></i>';
                            } else {
                                $etoiles .= '<i class="bx bx-star text-muted"></i>';
                            }
                        }
                    ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            
                            <td>
                                <strong><?= $produit_nom ?></strong>
                            </td>

                            <td><?= $user_nom ?></td>

                            <td>
                                <div class="text-nowrap">
                                    <?= $etoiles ?>
                                    <span class="badge bg-primary ms-1"><?= $value['note'] ?>/5</span>
                                </div>
                            </td>

                            <td><?= $value['titre'] ?: '-' ?></td>

                            <td><?= substr($value['commentaire'], 0, 50) ?>...</td>

                            <td>
                                <a href="javascript:void()" data-bs-toggle="modal" data-bs-target="#valide_<?= $value['id'] ?>">
                                    <?php if ($value['est_valide'] == 1): ?>
                                        <span class="badge bg-success"><i class="bx bx-check"></i> Validé</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><i class="bx bx-time"></i> En attente</span>
                                    <?php endif; ?>
                                </a>
                            </td>

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
                                        <h4 class="modal-title">Détail de l'Avis</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header bg-light">
                                                        <strong>Produit</strong>
                                                    </div>
                                                    <div class="card-body">
                                                        <?= $produit_nom ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header bg-light">
                                                        <strong>Utilisateur</strong>
                                                    </div>
                                                    <div class="card-body">
                                                        <?= $user_nom ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center mb-3">
                                            <h2 class="display-4"><?= $value['note'] ?>/5</h2>
                                            <div class="fs-3">
                                                <?= $etoiles ?>
                                            </div>
                                        </div>

                                        <?php if ($value['titre']): ?>
                                        <div class="mb-3">
                                            <h5><?= $value['titre'] ?></h5>
                                        </div>
                                        <?php endif; ?>

                                        <div class="mb-3">
                                            <h6>Commentaire:</h6>
                                            <p class="bg-light p-3 rounded"><?= nl2br($value['commentaire']) ?></p>
                                        </div>

                                        <div class="row">
                                            <?php if ($value['avantages']): ?>
                                            <div class="col-md-6">
                                                <h6 class="text-success"><i class="bx bx-plus-circle"></i> Avantages:</h6>
                                                <p class="bg-success bg-opacity-10 p-2 rounded"><?= nl2br($value['avantages']) ?></p>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($value['inconvenients']): ?>
                                            <div class="col-md-6">
                                                <h6 class="text-danger"><i class="bx bx-minus-circle"></i> Inconvénients:</h6>
                                                <p class="bg-danger bg-opacity-10 p-2 rounded"><?= nl2br($value['inconvenients']) ?></p>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <hr>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Statut:</strong>
                                                <?php if ($value['est_valide'] == 1): ?>
                                                    <span class="badge bg-success">Validé</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">En attente de validation</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <small class="text-muted">
                                                    Créé le: <?= date('d/m/Y H:i', strtotime($value['created_at'])) ?><br>
                                                    Modifié le: <?= date('d/m/Y H:i', strtotime($value['updated_at'])) ?>
                                                </small>
                                            </div>
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
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Modifier l'Avis</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Produit_avis/Update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">

                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label">Produit <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="produit_id" required>
                                                        <?php foreach ($produits as $prod): ?>
                                                            <option value="<?= $prod['id'] ?>" <?= $value['produit_id'] == $prod['id'] ? 'selected' : '' ?>>
                                                                <?= $prod['nom'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label">Utilisateur <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="user_id" required>
                                                        <?php foreach ($users as $user): ?>
                                                            <option value="<?= $user['id'] ?>" <?= $value['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                                                <?= $user['prenom'] . ' ' . $user['nom'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="mb-3 col-md-12">
                                                    <label class="form-label">Note <span class="text-danger">*</span></label>
                                                    <div class="rating-input">
                                                        <?php for ($j = 5; $j >= 1; $j--): ?>
                                                            <input type="radio" name="note" value="<?= $j ?>" id="star<?= $j ?>_<?= $value['id'] ?>" <?= $value['note'] == $j ? 'checked' : '' ?>>
                                                            <label for="star<?= $j ?>_<?= $value['id'] ?>"><i class="bx bxs-star"></i></label>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Titre</label>
                                                <input type="text" class="form-control" value="<?= $value['titre'] ?>" name="titre" placeholder="Résumé de l'avis">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Commentaire</label>
                                                <textarea class="form-control" name="commentaire" rows="4"><?= $value['commentaire'] ?></textarea>
                                            </div>

                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label">Avantages</label>
                                                    <textarea class="form-control" name="avantages" rows="3"><?= $value['avantages'] ?></textarea>
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label">Inconvénients</label>
                                                    <textarea class="form-control" name="inconvenients" rows="3"><?= $value['inconvenients'] ?></textarea>
                                                </div>
                                            </div>

                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="est_valide" value="1" <?= $value['est_valide'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Avis validé (visible publiquement)</label>
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
                                        <p>Êtes-vous sûr de vouloir supprimer cet avis ?</p>
                                        <div class="alert alert-light">
                                            <strong>Produit:</strong> <?= $produit_nom ?><br>
                                            <strong>Utilisateur:</strong> <?= $user_nom ?><br>
                                            <strong>Note:</strong> <?= $value['note'] ?>/5
                                        </div>
                                    </div>

                                    <form action="<?= base_url('Produit_avis/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-info">Supprimer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ========= MODAL VALIDATION ========= -->
                        <div class="modal fade" id="valide_<?= $value['id'] ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Changer la validation ?</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Produit_avis/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="est_valide" value="<?= $value['est_valide'] ?>">

                                        <div class="modal-body">
                                            <?php if ($value['est_valide'] == 1): ?>
                                                <p>Invalider cet avis ? Il ne sera plus visible publiquement.</p>
                                            <?php else: ?>
                                                <p>Valider cet avis ? Il deviendra visible publiquement.</p>
                                            <?php endif; ?>
                                            <div class="alert alert-light">
                                                <strong>Note:</strong> <?= $value['note'] ?>/5<br>
                                                <strong>Produit:</strong> <?= $produit_nom ?>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-<?= $value['est_valide'] == 1 ? 'warning' : 'success' ?>">
                                                <?= $value['est_valide'] == 1 ? 'Invalider' : 'Valider' ?>
                                            </button>
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
                            <th>Produit</th>
                            <th>Utilisateur</th>
                            <th>Note</th>
                            <th>Titre</th>
                            <th>Commentaire</th>
                            <th>Validé</th>
                            <th>Date</th>
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

<!-- ========= MODAL NEW AVIS ========= -->
<div class="modal fade" id="avis" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Nouvel Avis</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Produit_avis/Create') ?>" method="POST">
                <div class="modal-body">

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Produit <span class="text-danger">*</span></label>
                            <select class="form-select" name="produit_id" required>
                                <option value="">Sélectionner un produit...</option>
                                <?php foreach ($produits as $prod): ?>
                                    <option value="<?= $prod['id'] ?>"><?= $prod['nom'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Utilisateur <span class="text-danger">*</span></label>
                            <select class="form-select" name="user_id" required>
                                <option value="">Sélectionner un utilisateur...</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= $user['prenom'] . ' ' . $user['nom'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label class="form-label">Note <span class="text-danger">*</span></label>
                            <div class="rating-input">
                                <?php for ($j = 5; $j >= 1; $j--): ?>
                                    <input type="radio" name="note" value="<?= $j ?>" id="star<?= $j ?>_new" <?= $j == 5 ? 'checked' : '' ?>>
                                    <label for="star<?= $j ?>_new"><i class="bx bxs-star"></i></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Titre</label>
                        <input type="text" class="form-control" name="titre" placeholder="Résumé de l'avis">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Commentaire</label>
                        <textarea class="form-control" name="commentaire" rows="4"></textarea>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Avantages</label>
                            <textarea class="form-control" name="avantages" rows="3" placeholder="Points forts du produit..."></textarea>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Inconvénients</label>
                            <textarea class="form-control" name="inconvenients" rows="3" placeholder="Points faibles du produit..."></textarea>
                        </div>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="est_valide" value="1">
                        <label class="form-check-label">Valider immédiatement (visible publiquement)</label>
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

<style>
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}
.rating-input input {
    display: none;
}
.rating-input label {
    cursor: pointer;
    font-size: 2rem;
    color: #ddd;
    margin: 0 2px;
}
.rating-input input:checked ~ label,
.rating-input label:hover,
.rating-input label:hover ~ label {
    color: #ffc107;
}
</style>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>