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
                    <li class="breadcrumb-item active" aria-current="page">Consultations</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-outline-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#consultation">
                <i class="bx bx-plus"></i> Nouvelle Consultation
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
                            <th>N° Consultation</th>
                            <th>Patient</th>
                            <th>Médecin</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Paiement</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $i = 1; foreach ($consultations as $value) { 
                        // Récupérer le nom du patient
                        $patient_nom = '-';
                        foreach ($patients as $pat) {
                            if ($pat['id'] == $value['patient_id']) {
                                $patient_nom = $pat['prenom'] . ' ' . $pat['nom'];
                                break;
                            }
                        }
                        
                        // Récupérer le nom du médecin
                        $medecin_nom = '-';
                        if ($value['medecin_id']) {
                            foreach ($medecins as $med) {
                                if ($med['id'] == $value['medecin_id']) {
                                    $medecin_nom = $med['prenom'] . ' ' . $med['nom'];
                                    break;
                                }
                            }
                        }
                        
                        // Badge type
                        $type_badges = [
                            'video' => 'primary',
                            'presentiel' => 'success',
                            'telephone' => 'info'
                        ];
                        
                        // Badges statut
                        $statut_badges = [
                            'en_attente' => 'warning',
                            'confirmee' => 'info',
                            'en_cours' => 'primary',
                            'terminee' => 'success',
                            'annulee' => 'danger',
                            'refusee' => 'dark'
                        ];
                        
                        // Labels statut
                        $statut_labels = [
                            'en_attente' => 'En attente',
                            'confirmee' => 'Confirmée',
                            'en_cours' => 'En cours',
                            'terminee' => 'Terminée',
                            'annulee' => 'Annulée',
                            'refusee' => 'Refusée'
                        ];
                        
                        // Badges paiement
                        $paiement_badges = [
                            'en_attente' => 'warning',
                            'paye' => 'success',
                            'echoue' => 'danger',
                            'rembourse' => 'info'
                        ];
                    ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            
                            <td>
                                <code><?= $value['numero_consultation'] ?></code>
                            </td>

                            <td><?= $patient_nom ?></td>

                            <td><?= $medecin_nom ?></td>

                            <td>
                                <span class="badge bg-<?= $type_badges[$value['type']] ?>">
                                    <i class="bx <?= $value['type'] == 'video' ? 'bx-video' : ($value['type'] == 'presentiel' ? 'bx-user' : 'bx-phone') ?>"></i>
                                    <?= ucfirst($value['type']) ?>
                                </span>
                            </td>

                            <td>
                                <?= date('d/m/Y H:i', strtotime($value['date_souhaitee'])) ?>
                                <br><small class="text-muted"><?= $value['duree_minutes'] ?> min</small>
                            </td>

                            <td>
                                <strong><?= number_format($value['prix_ttc'], 2, ',', ' ') ?> €</strong>
                            </td>

                            <td>
                                <a href="javascript:void()" data-bs-toggle="modal" data-bs-target="#statut_<?= $value['id'] ?>">
                                    <span class="badge bg-<?= $statut_badges[$value['statut']] ?>">
                                        <?= $statut_labels[$value['statut']] ?>
                                    </span>
                                </a>
                            </td>

                            <td>
                                <a href="javascript:void()" data-bs-toggle="modal" data-bs-target="#paiement_<?= $value['id'] ?>">
                                    <span class="badge bg-<?= $paiement_badges[$value['paiement_statut']] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $value['paiement_statut'])) ?>
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
                                        <h4 class="modal-title">Détail de la Consultation</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h5>Informations générales</h5>
                                                <table class="table table-sm table-borderless">
                                                    <tr><td>N° Consultation:</td><td><code><?= $value['numero_consultation'] ?></code></td></tr>
                                                    <tr><td>Type:</td><td><span class="badge bg-<?= $type_badges[$value['type']] ?>"><?= ucfirst($value['type']) ?></span></td></tr>
                                                    <tr><td>Patient:</td><td><?= $patient_nom ?></td></tr>
                                                    <tr><td>Médecin:</td><td><?= $medecin_nom ?></td></tr>
                                                    <tr><td>Statut:</td><td><span class="badge bg-<?= $statut_badges[$value['statut']] ?>"><?= $statut_labels[$value['statut']] ?></span></td></tr>
                                                    <tr><td>Room ID:</td><td><code><?= $value['room_id'] ?: '-' ?></code></td></tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Informations médicales</h5>
                                                <table class="table table-sm table-borderless">
                                                    <tr><td>Poids:</td><td><?= $value['poids'] ? $value['poids'] . ' kg' : '-' ?></td></tr>
                                                    <tr><td>Taille:</td><td><?= $value['taille'] ? $value['taille'] . ' cm' : '-' ?></td></tr>
                                                    <tr><td>Durée:</td><td><?= $value['duree_minutes'] ?> minutes</td></tr>
                                                    <tr><td>Prix:</td><td><strong><?= number_format($value['prix_ttc'], 2, ',', ' ') ?> €</strong></td></tr>
                                                    <tr><td>Paiement:</td><td><span class="badge bg-<?= $paiement_badges[$value['paiement_statut']] ?>"><?= ucfirst(str_replace('_', ' ', $value['paiement_statut'])) ?></span></td></tr>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Symptômes</h6>
                                                <p class="bg-light p-2 rounded"><?= nl2br($value['symptomes'] ?: 'Aucun symptôme enregistré') ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Examens demandés</h6>
                                                <p class="bg-light p-2 rounded"><?= nl2br($value['examens_demandes'] ?: 'Aucun examen demandé') ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <h6>Diagnostic</h6>
                                                <p class="bg-light p-2 rounded"><?= nl2br($value['diagnostic'] ?: 'Aucun diagnostic') ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Traitement</h6>
                                                <p class="bg-light p-2 rounded"><?= nl2br($value['traitement'] ?: 'Aucun traitement prescrit') ?></p>
                                            </div>
                                        </div>
                                        
                                        <?php if ($value['notes_medecin']): ?>
                                        <div class="mt-3">
                                            <h6>Notes du médecin</h6>
                                            <p class="bg-light p-2 rounded"><?= nl2br($value['notes_medecin']) ?></p>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($value['motif_annulation']): ?>
                                        <div class="mt-3">
                                            <h6 class="text-danger">Motif d'annulation</h6>
                                            <p class="bg-danger bg-opacity-10 p-2 rounded"><?= $value['motif_annulation'] ?></p>
                                        </div>
                                        <?php endif; ?>
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
                                        <h4 class="modal-title">Modifier la Consultation</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Consultations/Update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">

                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label">Patient <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="patient_id" required>
                                                        <?php foreach ($patients as $pat): ?>
                                                            <option value="<?= $pat['id'] ?>" <?= $value['patient_id'] == $pat['id'] ? 'selected' : '' ?>>
                                                                <?= $pat['prenom'] . ' ' . $pat['nom'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label">Médecin</label>
                                                    <select class="form-select" name="medecin_id">
                                                        <option value="">Non assigné</option>
                                                        <?php foreach ($medecins as $med): ?>
                                                            <option value="<?= $med['id'] ?>" <?= $value['medecin_id'] == $med['id'] ? 'selected' : '' ?>>
                                                                <?= $med['prenom'] . ' ' . $med['nom'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="mb-3 col-md-4">
                                                    <label class="form-label">Type</label>
                                                    <select class="form-select" name="type">
                                                        <option value="video" <?= $value['type'] == 'video' ? 'selected' : '' ?>>Vidéo</option>
                                                        <option value="presentiel" <?= $value['type'] == 'presentiel' ? 'selected' : '' ?>>Présentiel</option>
                                                        <option value="telephone" <?= $value['type'] == 'telephone' ? 'selected' : '' ?>>Téléphone</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3 col-md-4">
                                                    <label class="form-label">Date souhaitée <span class="text-danger">*</span></label>
                                                    <input type="datetime-local" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($value['date_souhaitee'])) ?>" name="date_souhaitee" required>
                                                </div>
                                                <div class="mb-3 col-md-4">
                                                    <label class="form-label">Durée (minutes)</label>
                                                    <input type="number" class="form-control" value="<?= $value['duree_minutes'] ?>" name="duree_minutes" min="15" max="120">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="mb-3 col-md-3">
                                                    <label class="form-label">Poids (kg)</label>
                                                    <input type="number" step="0.01" class="form-control" value="<?= $value['poids'] ?>" name="poids">
                                                </div>
                                                <div class="mb-3 col-md-3">
                                                    <label class="form-label">Taille (cm)</label>
                                                    <input type="number" step="0.01" class="form-control" value="<?= $value['taille'] ?>" name="taille">
                                                </div>
                                                <div class="mb-3 col-md-3">
                                                    <label class="form-label">Prix HT (€)</label>
                                                    <input type="number" step="0.01" class="form-control" value="<?= $value['prix_ht'] ?>" name="prix_ht">
                                                </div>
                                                <div class="mb-3 col-md-3">
                                                    <label class="form-label">TVA (%)</label>
                                                    <input type="number" step="0.01" class="form-control" value="<?= $value['tva'] ?>" name="tva">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Symptômes</label>
                                                <textarea class="form-control" name="symptomes" rows="3"><?= $value['symptomes'] ?></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Examens demandés</label>
                                                <textarea class="form-control" name="examens_demandes" rows="2"><?= $value['examens_demandes'] ?></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Diagnostic</label>
                                                <textarea class="form-control" name="diagnostic" rows="3"><?= $value['diagnostic'] ?></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Traitement</label>
                                                <textarea class="form-control" name="traitement" rows="3"><?= $value['traitement'] ?></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Notes du médecin</label>
                                                <textarea class="form-control" name="notes_medecin" rows="3"><?= $value['notes_medecin'] ?></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Motif d'annulation</label>
                                                <input type="text" class="form-control" value="<?= $value['motif_annulation'] ?>" name="motif_annulation">
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
                                        <p>Êtes-vous sûr de vouloir supprimer cette consultation ?</p>
                                        <div class="alert alert-warning">
                                            <strong>N°:</strong> <?= $value['numero_consultation'] ?><br>
                                            <strong>Patient:</strong> <?= $patient_nom ?><br>
                                            <strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($value['date_souhaitee'])) ?>
                                        </div>
                                    </div>

                                    <form action="<?= base_url('Consultations/Delete') ?>" method="POST">
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

                                    <form action="<?= base_url('Consultations/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="statut" value="<?= $value['statut'] ?>">

                                        <div class="modal-body">
                                            <p>Statut actuel: <span class="badge bg-<?= $statut_badges[$value['statut']] ?>"><?= $statut_labels[$value['statut']] ?></span></p>
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

                                    <form action="<?= base_url('Consultations/ChangePaiementStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="paiement_statut" value="<?= $value['paiement_statut'] ?>">

                                        <div class="modal-body">
                                            <p>Statut actuel: <span class="badge bg-<?= $paiement_badges[$value['paiement_statut']] ?>"><?= ucfirst(str_replace('_', ' ', $value['paiement_statut'])) ?></span></p>
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
                            <th>N° Consultation</th>
                            <th>Patient</th>
                            <th>Médecin</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Prix</th>
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

<!-- ========= MODAL NEW CONSULTATION ========= -->
<div class="modal fade" id="consultation" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Nouvelle Consultation</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Consultations/Create') ?>" method="POST">
                <div class="modal-body">

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select class="form-select" name="patient_id" required>
                                <option value="">Sélectionner un patient...</option>
                                <?php foreach ($patients as $pat): ?>
                                    <option value="<?= $pat['id'] ?>"><?= $pat['prenom'] . ' ' . $pat['nom'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Médecin</label>
                            <select class="form-select" name="medecin_id">
                                <option value="">Non assigné</option>
                                <?php foreach ($medecins as $med): ?>
                                    <option value="<?= $med['id'] ?>"><?= $med['prenom'] . ' ' . $med['nom'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Type de consultation</label>
                            <select class="form-select" name="type">
                                <option value="video" selected>Vidéo</option>
                                <option value="presentiel">Présentiel</option>
                                <option value="telephone">Téléphone</option>
                            </select>
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Date souhaitée <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="date_souhaitee" required>
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Durée (minutes)</label>
                            <input type="number" class="form-control" name="duree_minutes" value="30" min="15" max="120">
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-3">
                            <label class="form-label">Poids (kg)</label>
                            <input type="number" step="0.01" class="form-control" name="poids" placeholder="Ex: 70.5">
                        </div>
                        <div class="mb-3 col-md-3">
                            <label class="form-label">Taille (cm)</label>
                            <input type="number" step="0.01" class="form-control" name="taille" placeholder="Ex: 175">
                        </div>
                        <div class="mb-3 col-md-3">
                            <label class="form-label">Prix HT (€) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="prix_ht" required>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label class="form-label">TVA (%)</label>
                            <input type="number" step="0.01" class="form-control" name="tva" value="20.00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Symptômes</label>
                        <textarea class="form-control" name="symptomes" rows="3" placeholder="Décrivez les symptômes du patient..."></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-info">Créer la consultation</button>
                </div>

            </form>

        </div>
    </div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>