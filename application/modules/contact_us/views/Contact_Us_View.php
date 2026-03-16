<?php include VIEWPATH.'includes/backend/Header.php' ;?>
<?php include VIEWPATH.'includes/backend/Sidebar.php' ;?>
<?php include VIEWPATH.'includes/backend/Topheader.php' ;?>

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Admin</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Contactez-nous</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a class="btn btn-outline-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#contactus">Nouveau</a>
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
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Sujet</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; foreach ($contactus as $value): 
                            $is_unread = ($value['is_readed'] == 0);
                             $row_class = $is_unread ? 'unread-row' : 'read-row';
                              ?>
                            <tr class="<?= $row_class ?>">
                                <td><?=$i++;?></td>
                                <td><?=$value['FullName']?></td>
                                <td><?=$value['Email']?></td>
                                <td><?=substr($value['Subject'], 0, 30)?>...</td>
                                <td class="text-center">
                                  <?php if($is_unread): ?>
                                      <span class="badge bg-danger px-3 py-2">
                                          <i class="bx bx-envelope"></i> Non lu
                                      </span>
                                       <?php else: ?>
                                      <span class="badge bg-success px-3 py-2">
                                       <i class="bx bx-check"></i> Lu
                                      </span>
                                  <?php endif; ?>
                              </td>
                                <td><?=date('d/m/Y', strtotime($value['Date_creation']))?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-bs-toggle="dropdown">Options</button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item text-primary" href="javascript:void(0)" onclick="markAsRead(<?= $value['IdContact'] ?>)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['IdContact'] ?>">
                                            <i class="bx bx-show"></i> Voir Détails
                                        </a>
                                        <a class="dropdown-item text-info" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?=$value['IdContact']?>">
                                            <i class="bx bx-edit"></i> Modifier
                                        </a>
                                        <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?=$value['IdContact']?>">
                                            <i class="bx bx-trash"></i> Supprimer
                                        </a>
                                    </div> 
                                </td>
                            </tr>

                            <div class="modal fade" id="view_<?=$value['IdContact']?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content border-top border-0 border-4 border-info">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Détails du message</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="window.location.reload()"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Nom complet</label>
                                                    <p class="form-control-plaintext border-bottom"><?=$value['FullName']?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Email</label>
                                                    <p class="form-control-plaintext border-bottom"><?=$value['Email']?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Téléphone</label>
                                                    <p class="form-control-plaintext border-bottom"><?=$value['PhoneNumber']?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Date d'envoi</label>
                                                    <p class="form-control-plaintext border-bottom"><?=$value['Date_creation']?></p>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Sujet</label>
                                                    <p class="form-control-plaintext border-bottom"><?=$value['Subject']?></p>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Message</label>
                                                    <div class="bg-light p-3 rounded shadow-sm" style="white-space: pre-wrap;"><?= htmlspecialchars($value['Message']) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="window.location.reload()">Fermer</button>
                                            <a href="mailto:<?=$value['Email']?>" class="btn btn-primary">Répondre par Email</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="update_<?=$value['IdContact']?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-info">
                                            <h5 class="modal-title text-white">Modifier Contact</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?=base_url('Contact_Us/Update')?>" method="POST">
                                            <input type="hidden" name="IdContact" value="<?=$value['IdContact']?>">
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Nom complet</label>
                                                        <input type="text" class="form-control" value="<?=$value['FullName']?>" name="FullName" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" class="form-control" value="<?=$value['Email']?>" name="Email" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Objet</label>
                                                        <input type="text" class="form-control" value="<?=$value['Subject']?>" name="Subject" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Téléphone</label>
                                                        <input type="text" class="form-control" value="<?=$value['PhoneNumber']?>" name="PhoneNumber" required>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Message</label>
                                                        <textarea class="form-control" rows="4" name="Message" required><?=$value['Message']?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-info">Mettre à jour</button>  
                                            </div>                   
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="delete_<?=$value['IdContact']?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger">
                                            <h5 class="modal-title text-white">Confirmation</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?=base_url('Contact_Us/Delete')?>" method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="IdContact" value="<?=$value['IdContact']?>">
                                                <p>Voulez-vous vraiment supprimer le message de <strong><?=$value['FullName']?></strong> ?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non</button>
                                                <button type="submit" class="btn btn-danger">Oui, Supprimer</button>  
                                            </div>                   
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        	 <tr>
                                <th>#</th>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Sujet</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="contactus" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?=base_url('Contact_Us/Create')?>" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom complet</label>
                            <input type="text" class="form-control" name="FullName" placeholder="Nom complet" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="Email" placeholder="Email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Objet</label>
                            <input type="text" class="form-control" name="Subject" placeholder="Objet" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" name="PhoneNumber" placeholder="Téléphone" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="Message" rows="4" placeholder="Votre message..." required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="Date_creation" value="<?=date('Y-m-d')?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>  
                </div>                   
            </form>
        </div>
    </div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php' ;?>

<script>
function markAsRead(id) {
    $.ajax({
        url: "<?= base_url('Contact_Us/MarkAsRead/') ?>" + id,
        type: "GET",
        success: function(response) {
            console.log("Statut mis à jour en base de données.");
        }
    });
}
</script>