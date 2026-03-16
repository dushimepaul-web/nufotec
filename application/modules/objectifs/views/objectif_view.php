<?php include VIEWPATH.'includes/backend/Header.php' ;?>
<?php include VIEWPATH.'includes/backend/Sidebar.php' ;?>
<?php include VIEWPATH.'includes/backend/Topheader.php' ;?>

<!--start page wrapper -->
<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-Objectif pe-3">Admin</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">les objectifs</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-outline-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#objectifs">Nouvel Objectif</a>
        </div>
    </div>

    <hr/>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered table-hover" style="width:100%">
                    <thead>
                        <tr class= "text-info">
                            <th>number</th>
                            <th>objectif</th>
                            <th>Détail</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $i=1; foreach ($objectifs as $value) { ?>
                        <tr>
                            <td><?=$i++;?></td>
                            <td><?=$value['Objectif']?></td>
                            <td><?=substr($value['Details'], 0, 20)?>...</td>
                            <td>
                               
                             <div class="dropdown mt-1">
                                    <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">Options</button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item text-info" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?=$value['id_objectif']?>">Modifier</a>
                                        <a class="dropdown-item text-info" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#detail_<?=$value['id_objectif']?>">Detail</a>
                                        <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#delete_<?=$value['id_objectif']?>">Supprimer</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                      
                        <!-- Modal: Modifier -->
                        <div class="modal fade" id="update_<?=$value['id_objectif']?>" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="<?=base_url('Objectifs/Update')?>" method="POST" enctype="multipart/form-data">
                                        <div class="modal-header">
                                            <h4 class="modal-objectif">Modifier</h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                         <div class="mb-3 col-md-6">
                                                <label class="form-label">objectif</label>
                                                <input type="text" class="form-control" name="Objectif" value="<?=$value['id_objectif']?>" required>
                                       <input type="hidden" name="id_objectif" value="<?=$value['id_objectif']?>">
                                        <div class="modal-body row">
                                                </div>
                                            <div class="form-floating">
                                                <textarea class="form-control" name="Details" id="floatingTextarea" style="height: 100px;" required><?=$value['Details']?></textarea>
                                                <label for="floatingTextarea">Détail</label>
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

                        <!-- Modal: Supprimer -->
                        <div class="modal fade" id="delete_<?=$value['id_objectif']?>" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="<?=base_url('Objectifs/delete')?>" method="POST" enctype="multipart/form-data">
                                        <div class="modal-header">
                                            <h4 class="modal-objectif">Voulez-vous vraiment supprimer ?</h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <input type="hidden" name="id_objectif" value="<?=$value['id_objectif']?>">
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                                            <button type="submit" class="btn btn-info">Supprimer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        					<!--modal pour l'affichage du detail -->
								
<div class="modal fade" id="detail_<?=$value['id_objectif']?>" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-backdrop="static">
<div class="modal-dialog modal-fullscreen">
<div class="modal-content">
<div class="modal-header">
    <h4 class="modal-title" id="myLargeModalLabel">Détail</h4>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
</div>
<div class="modal-body">
  <?=$value['Details']?>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button> 
</div>   
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
								

                    <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>number</th>
                            <th>objectif</th>
                            <th>Détail</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>


    <hr/>
      <!-- Modal: Créer un nouvel objectif -->
                 <div class="modal fade" id="objectifs" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                         <form action="<?=base_url('Objectifs/Create')?>" method="POST" enctype="multipart/form-data">
                            <div class="modal-header">
                                 <h4 class="modal-Objectif">Nouvel objectif</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                         </div>
                  <div class="modal-body row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Objectif</label>
                        <input type="text" class="form-control" name="Objectif" placeholder="Entrez le titre de l'Objectif" required>
                    </div>
                
                          <div class="form-floating">
                               <textarea class="form-control" name="Details" style="height: 100px;" required></textarea>
                             <label>Détail</label>
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

</div>



<?php include VIEWPATH.'includes/backend/Footer.php'; ?>
