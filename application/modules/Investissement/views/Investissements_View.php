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
                    <li class="breadcrumb-item active" aria-current="page">Investissements</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-outline-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#investissement">
                <i class="bx bx-plus"></i> Nouvel Investissement
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
                            <th>N° Investissement</th>
                            <th>Investisseur</th>
                            <th>Projet</th>
                            <th>Montant</th>
                            <th>Parts</th>
                            <th>Rendement</th>
                            <th>Statut</th>
                            <th>Paiement</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $i = 1; foreach ($investissements as $value) { 
                        // Récupérer le nom de l'investisseur
                        $investisseur_nom = '-';
                        foreach ($investisseurs as $inv) {
                            if ($inv['id'] == $value['investisseur_id']) {
                                $investisseur_nom = $inv['prenom'] . ' ' . $inv['nom'];
                                break;
                            }
                        }
                        
                        // Récupérer le nom du projet
                        $projet_nom = '-';
                        foreach ($projets as $proj) {
                            if ($proj['id'] == $value['projet_id']) {
                                $projet_nom = $proj['titre'];
                                break;
                            }
                        }
                        
                        // Badges statut
                        $statut_badges = [
                            'actif' => 'success',
                            'termine' => 'info',
                            'rembourse' => 'primary',
                            'annule' => 'danger'
                        ];
                        
                        // Badges paiement
                        $paiement_badges = [
                            'en_attente' => 'warning',
                            'paye' => 'success',
                            'echoue' => 'danger'
                        ];
                    ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            
                            <td>
                                <code><?= $value['numero_investissement'] ?></code>
                                <?php if ($value['type'] == 'premium'): ?>
                                    <span class="badge bg-warning">PREMIUM</span>
                                <?php endif; ?>
                            </td>

                            <td><?= $investisseur_nom ?></td>

                            <td><?= substr($projet_nom, 0, 30) ?>...</td>

                            <td>
                                <strong><?= number_format($value['montant'], 0, ',', ' ') ?> €</strong>
                            </td>

                            <td><?= $value['nombre_parts'] ?></td>

                            <td><?= $value['rendement_annuel'] ?>%</td>

                            <td>
                                <a href="javascript:void()" data-bs-toggle="modal" data-bs-target="#statut_<?= $value['id'] ?>">
                                    <span class="badge bg-<?= $statut_badges[$value['statut']] ?>">
                                        <?= $value['statut'] ?>
                                    </span>
                                </a>
                            </td>

                            <td>
                                <a href="javascript:void()" data-bs-toggle="modal" data-bs-target="#paiement_<?= $value['id'] ?>">
                                    <span class="badge bg-<?= $paiement_badges[$value['statut_paiement']] ?>">
                                        <?= str_replace('_', ' ', $value['statut_paiement']) ?>
                                    </span>
                                </a>
                            </td>

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
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Détail de l'Investissement</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h5>Informations générales</h5>
                                                <table class="table table-sm table-borderless">
                                                    <tr><td>N° Investissement:</td><td><code><?= $value['numero_investissement'] ?></code></td></tr>
                                                    <tr><td>Type:</td><td><span class="badge bg-<?= $value['type'] == 'premium' ? 'warning' : 'secondary' ?>"><?= $value['type'] ?></span></td></tr>
                                                    <tr><td>Investisseur:</td><td><?= $investisseur_nom ?></td></tr>
                                                    <tr><td>Projet:</td><td><?= $projet_nom ?></td></tr>
                                                    <tr><td>Statut:</td><td><span class="badge bg-<?= $statut_badges[$value['statut']] ?>"><?= $value['statut'] ?></span></td></tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Informations financières</h5>
                                                <table class="table table-sm table-borderless">
                                                    <tr><td>Montant investi:</td><td><strong class="text-primary"><?= number_format($value['montant'], 0, ',', ' ') ?> €</strong></td></tr>
                                                    <tr><td>Nombre de parts:</td><td><?= $value['nombre_parts'] ?></td></tr>
                                                    <tr><td>Rendement annuel:</td><td><?= $value['rendement_annuel'] ?>%</td></tr>
                                                    <tr><td>Durée:</td><td><?= $value['duree_mois'] ?> mois</td></tr>
                                                    <tr><td>Rendement prévisionnel:</td><td><strong class="text-success"><?= number_format($value['rendement_previsionnel_total'], 0, ',', ' ') ?> €</strong></td></tr>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Dates</h6>
                                                <table class="table table-sm">
                                                    <tr><td>Date investissement:</td><td><?= date('d/m/Y H:i', strtotime($value['date_investissement'])) ?></td></tr>
                                                    <tr><td>Date échéance:</td><td><?= date('d/m/Y H:i', strtotime($value['date_echeance'])) ?></td></tr>
                                                    <tr><td>Créé le:</td><td><?= date('d/m/Y H:i', strtotime($value['created_at'])) ?></td></tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Paiement</h6>
                                                <table class="table table-sm">
                                                    <tr><td>Mode:</td><td><?= $value['mode_paiement'] ?: '-' ?></td></tr>
                                                    <tr><td>Statut:</td><td><span class="badge bg-<?= $paiement_badges[$value['statut_paiement']] ?>"><?= $value['statut_paiement'] ?></span></td></tr>
                                                    <tr><td>Date paiement:</td><td><?= $value['date_paiement'] ? date('d/m/Y H:i', strtotime($value['date_paiement'])) : '-' ?></td></tr>
                                                    <tr><td>Transaction ID:</td><td><code><?= $value['transaction_id'] ?: '-' ?></code></td></tr>
                                                </table>
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
                                        <h4 class="modal-title">Modifier l'Investissement</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Investissements/Update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">

                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label">Investisseur <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="investisseur_id" required>
                                                        <?php foreach ($investisseurs as $inv): ?>
                                                            <option value="<?= $inv['id'] ?>" <?= $value['investisseur_id'] == $inv['id'] ? 'selected' : '' ?>>
                                                                <?= $inv['prenom'] . ' ' . $inv['nom'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label">Projet <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="projet_id" required>
                                                        <?php foreach ($projets as $proj): ?>
                                                            <option value="<?= $proj['id'] ?>" <?= $value['projet_id'] == $proj['id'] ? 'selected' : '' ?>>
                                                                <?= $proj['titre'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="mb-3 col-md-3">
                                                    <label class="form-label">Montant (€) <span class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" class="form-control" value="<?= $value['montant'] ?>" name="montant" required>
                                                </div>
                                                <div class="mb-3 col-md-3">
                                                    <label class="form-label">Nombre de parts</label>
                                                    <input type="number" step="0.01" class="form-control" value="<?= $value['nombre_parts'] ?>" name="nombre_parts">
                                                </div>
                                                <div class="mb-3 col-md-3">
                                                    <label class="form-label">Rendement annuel (%)</label>
                                                    <input type="number" step="0.01" class="form-control" value="<?= $value['rendement_annuel'] ?>" name="rendement_annuel">
                                                </div>
                                                <div class="mb-3 col-md-3">
                                                    <label class="form-label">Durée (mois)</label>
                                                    <input type="number" class="form-control" value="<?= $value['duree_mois'] ?>" name="duree_mois">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="mb-3 col-md-4">
                                                    <label class="form-label">Type</label>
                                                    <select class="form-select" name="type">
                                                        <option value="classique" <?= $value['type'] == 'classique' ? 'selected' : '' ?>>Classique</option>
                                                        <option value="premium" <?= $value['type'] == 'premium' ? 'selected' : '' ?>>Premium</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3 col-md-4">
                                                    <label class="form-label">Date investissement</label>
                                                    <input type="datetime-local" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($value['date_investissement'])) ?>" name="date_investissement">
                                                </div>
                                                <div class="mb-3 col-md-4">
                                                    <label class="form-label">Mode de paiement</label>
                                                    <input type="text" class="form-control" value="<?= $value['mode_paiement'] ?>" name="mode_paiement">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">ID Transaction</label>
                                                <input type="text" class="form-control" value="<?= $value['transaction_id'] ?>" name="transaction_id">
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
                                        <p>Êtes-vous sûr de vouloir supprimer cet investissement ?</p>
                                        <div class="alert alert-warning">
                                            <strong>N°:</strong> <?= $value['numero_investissement'] ?><br>
                                            <strong>Montant:</strong> <?= number_format($value['montant'], 0, ',', ' ') ?> €<br>
                                            <strong>Investisseur:</strong> <?= $investisseur_nom ?>
                                        </div>
                                    </div>

                                    <form action="<?= base_url('Investissements/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-info">Supprimer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ========= MODAL STATUT ========= -->
                        <div class="modal fade" id="statut_<?= $value['id'] ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Changer le statut</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Investissements/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="statut" value="<?= $value['statut'] ?>">

                                        <div class="modal-body">
                                            <p>Statut actuel: <span class="badge bg-<?= $statut_badges[$value['statut']] ?>"><?= $value['statut'] ?></span></p>
                                            <p>Cliquez pour passer au statut suivant.</p>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">Changer le statut</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ========= MODAL PAIEMENT ========= -->
                        <div class="modal fade" id="paiement_<?= $value['id'] ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Changer le statut de paiement</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Investissements/ChangePaiementStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="statut_paiement" value="<?= $value['statut_paiement'] ?>">

                                        <div class="modal-body">
                                            <p>Statut actuel: <span class="badge bg-<?= $paiement_badges[$value['statut_paiement']] ?>"><?= $value['statut_paiement'] ?></span></p>
                                            <p>Cliquez pour passer au statut suivant.</p>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">Changer le statut</button>
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
                            <th>N° Investissement</th>
                            <th>Investisseur</th>
                            <th>Projet</th>
                            <th>Montant</th>
                            <th>Parts</th>
                            <th>Rendement</th>
                            <th>Statut</th>
                            <th>Paiement</th>
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

<!-- ========= MODAL NEW INVESTISSEMENT ========= -->
<div class="modal fade" id="investissement" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Nouvel Investissement</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Investissements/Create') ?>" method="POST">
                <div class="modal-body">

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Investisseur <span class="text-danger">*</span></label>
                            <select class="form-select" name="investisseur_id" required>
                                <option value="">Sélectionner...</option>
                                <?php foreach ($investisseurs as $inv): ?>
                                    <option value="<?= $inv['id'] ?>"><?= $inv['prenom'] . ' ' . $inv['nom'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Projet <span class="text-danger">*</span></label>
                            <select class="form-select" name="projet_id" required>
                                <option value="">Sélectionner...</option>
                                <?php foreach ($projets as $proj): ?>
                                    <option value="<?= $proj['id'] ?>"><?= $proj['titre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-3">
                            <label class="form-label">Montant (€) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="montant" required>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label class="form-label">Nombre de parts</label>
                            <input type="number" step="0.01" class="form-control" name="nombre_parts" value="1">
                        </div>
                        <div class="mb-3 col-md-3">
                            <label class="form-label">Rendement annuel (%)</label>
                            <input type="number" step="0.01" class="form-control" name="rendement_annuel" value="10.00">
                        </div>
                        <div class="mb-3 col-md-3">
                            <label class="form-label">Durée (mois)</label>
                            <input type="number" class="form-control" name="duree_mois" value="12">
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type">
                                <option value="classique">Classique</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Date investissement <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="date_investissement" value="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Mode de paiement</label>
                            <input type="text" class="form-control" name="mode_paiement" placeholder="Ex: Virement, Carte, etc.">
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Statut paiement</label>
                            <select class="form-select" name="statut_paiement">
                                <option value="en_attente">En attente</option>
                                <option value="paye">Payé</option>
                                <option value="echoue">Échoué</option>
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">ID Transaction</label>
                            <input type="text" class="form-control" name="transaction_id" placeholder="Référence de transaction">
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-info">Créer l'investissement</button>
                </div>

            </form>

        </div>
    </div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>