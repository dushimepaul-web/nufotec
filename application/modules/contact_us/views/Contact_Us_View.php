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

        <?php if ($this->session->flashdata('sms')): ?>
            <?= $this->session->flashdata('sms') ?>
        <?php endif; ?>

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
                            $is_unread = (isset($value['is_readed']) && $value['is_readed'] == 0);
                             $row_class = $is_unread ? 'unread-row' : 'read-row';
                              ?>
                            <tr class="<?= $row_class ?>" data-id="<?= $value['IdContact'] ?>">
                                <td><?=$i++;?></td>
                                <td><?=htmlspecialchars($value['FullName'])?></td>
                                <td><?=htmlspecialchars($value['Email'])?></td>
                                <td><?php $sujet = htmlspecialchars($value['Subject']); echo (mb_strlen($sujet) > 30) ? mb_substr($sujet, 0, 30).'...' : $sujet; ?></td>
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
                                <td><?= $value['Date_creation'] ? date('d/m/Y', strtotime($value['Date_creation'])) : '—' ?></td>
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

                    <?php if (!empty($contactus)): foreach ($contactus as $value): ?>
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
                                            <p class="form-control-plaintext border-bottom"><?=htmlspecialchars($value['FullName'])?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Email</label>
                                            <p class="form-control-plaintext border-bottom"><?=htmlspecialchars($value['Email'])?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Téléphone</label>
                                            <p class="form-control-plaintext border-bottom"><?=htmlspecialchars($value['PhoneNumber'])?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Date d'envoi</label>
                                            <p class="form-control-plaintext border-bottom"><?=htmlspecialchars($value['Date_creation'])?></p>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Sujet</label>
                                            <p class="form-control-plaintext border-bottom"><?=htmlspecialchars($value['Subject'])?></p>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Message</label>
                                            <div class="bg-light p-3 rounded shadow-sm" style="white-space: pre-wrap;"><?= htmlspecialchars($value['Message']) ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="window.location.reload()">Fermer</button>
                                    <?php $mailto_subject = rawurlencode('Re: ' . $value['Subject']); $mailto_body = rawurlencode('Bonjour ' . $value['FullName'] . ",\n\n"); ?>
                                    <a href="mailto:<?=htmlspecialchars($value['Email'])?>?subject=<?= $mailto_subject ?>&amp;body=<?= $mailto_body ?>" class="btn btn-primary">
                                        <i class="bx bx-envelope"></i> Répondre par Email
                                    </a>
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
                                <form action="<?=base_url('contact_us/Contact_Us/Update')?>" method="POST">
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                    <input type="hidden" name="IdContact" value="<?=htmlspecialchars($value['IdContact'])?>">
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Nom complet</label>
                                                <input type="text" class="form-control" value="<?=htmlspecialchars($value['FullName'])?>" name="FullName" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" value="<?=htmlspecialchars($value['Email'])?>" name="Email" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Objet</label>
                                                <input type="text" class="form-control" value="<?=htmlspecialchars($value['Subject'])?>" name="Subject" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Téléphone</label>
                                                <input type="text" class="form-control" value="<?=htmlspecialchars($value['PhoneNumber'])?>" name="PhoneNumber" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Message</label>
                                                <textarea class="form-control" rows="4" name="Message" required><?=htmlspecialchars($value['Message'])?></textarea>
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
                                <form action="<?=base_url('contact_us/Contact_Us/Delete')?>" method="POST">
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                    <div class="modal-body">
                                        <input type="hidden" name="IdContact" value="<?=htmlspecialchars($value['IdContact'])?>">
                                        <p>Voulez-vous vraiment supprimer le message de <strong><?=htmlspecialchars($value['FullName'])?></strong> ?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non</button>
                                        <button type="submit" class="btn btn-danger">Oui, Supprimer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>

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
            <form action="<?=base_url('contact_us/Contact_Us/Create')?>" method="POST">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
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

<script>
function markAsRead(id) {
    $.ajax({
        url: "<?= base_url('contact_us/Contact_Us/MarkAsRead/') ?>" + id,
        type: "GET",
        success: function(response) {
            if (response && response.status) {
                var row = $('tr[data-id="' + id + '"]');
                row.removeClass('unread-row').addClass('read-row');
                row.find('.badge')
                    .removeClass('bg-danger')
                    .addClass('bg-success')
                    .html('<i class="bx bx-check"></i> Lu');
            }
        },
        error: function(xhr) {
            console.error("Erreur lors de la mise à jour du statut.");
        }
    });
}
</script>

<?php include VIEWPATH.'includes/backend/Footer.php' ;?>