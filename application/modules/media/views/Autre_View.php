<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Médias divers</div>
            <div class="ms-auto">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bx bx-upload"></i> Nouveau
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-bordered" id="itemsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Miniature</th>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= $item['id_media'] ?></td>
                            <td><?= $item['sous_type'] ?? $item['type'] ?></td>
                            <td>
                                <?php 
                                $thumb = base_url('assets/images/file-default.png');
                                if (!empty($item['miniature'])) {
                                    $thumb = (strpos($item['miniature'], 'http') === 0) ? $item['miniature'] : base_url($item['miniature']);
                                } elseif ($item['type'] == 'image' && !empty($item['fichier'])) {
                                    $thumb = base_url($item['fichier']);
                                }
                                ?>
                                <img src="<?= $thumb ?>" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td><?= htmlspecialchars($item['titre'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['categorie'] ?? '') ?></td>
                            <td>
                                <span class="badge bg-<?= $item['est_actif'] ? 'success' : 'secondary' ?>">
                                    <?= $item['est_actif'] ? 'Actif' : 'Inactif' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editModal<?= $item['id_media'] ?>">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $item['id_media'] ?>, '<?= addslashes($item['titre']) ?>')">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="editModal<?= $item['id_media'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="<?= base_url('autre/Update') ?>" method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Modifier</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $item['id_media'] ?>">
                                            <div class="mb-3">
                                                <label>Titre</label>
                                                <input type="text" name="titre" class="form-control" value="<?= htmlspecialchars($item['titre'] ?? '') ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Description</label>
                                                <textarea name="description" class="form-control"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label>Catégorie</label>
                                                <input type="text" name="categorie" class="form-control" value="<?= htmlspecialchars($item['categorie'] ?? '') ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label>Miniature</label>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <img src="<?= $thumb ?>" id="currentThumb<?= $item['id_media'] ?>" style="width: 100px;">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="file" class="form-control" accept="image/*" onchange="uploadEditThumbnail(<?= $item['id_media'] ?>, this.files[0])">
                                                        <input type="hidden" name="thumbnail" id="editThumb<?= $item['id_media'] ?>" value="<?= htmlspecialchars($item['miniature'] ?? '') ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="est_actif" value="1" <?= $item['est_actif'] ? 'checked' : '' ?>>
                                                        <label>Actif</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1" <?= $item['is_for_whatsapp'] ? 'checked' : '' ?>>
                                                        <label>WhatsApp</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="is_for_website" value="1" <?= $item['is_for_website'] ? 'checked' : '' ?>>
                                                        <label>Site Web</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('autre/Create') ?>" method="POST" id="uploadForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Nouveau média</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Titre *</label>
                                <input type="text" name="titre" class="form-control" required id="itemTitle">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Type *</label>
                                <select name="sous_type" class="form-control" id="itemType" required>
                                    <option value="">Sélectionner</option>
                                    <option value="link">Lien URL</option>
                                    <option value="photo">Photo / Image</option>
                                    <option value="book">Livre / PDF</option>
                                    <option value="texte">Texte</option>
                                    <option value="other">Autre fichier</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="linkField" style="display:none;">
                        <div class="mb-3">
                            <label>URL *</label>
                            <input type="url" name="lien" class="form-control" id="itemLink">
                        </div>
                    </div>

                    <div id="texteField" style="display:none;">
                        <div class="mb-3">
                            <label>Contenu texte</label>
                            <textarea name="contenu_texte" class="form-control" rows="5"></textarea>
                        </div>
                    </div>

                    <div id="fileField" style="display:none;">
                        <div class="mb-3">
                            <label>Fichier *</label>
                            <input type="file" id="fileInput" class="form-control">
                            <input type="hidden" name="uploaded_file_path" id="uploadedFilePath">
                            <div id="uploadProgress" class="mt-2" style="display:none;">
                                <div class="progress">
                                    <div class="progress-bar" id="progressBar" style="width:0%">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="thumbnailField" style="display:none;">
                        <div class="mb-3">
                            <label>Miniature (optionnel)</label>
                            <input type="file" class="form-control" accept="image/*" id="customThumbnail">
                            <input type="hidden" name="thumbnail" id="selectedThumbnail">
                            <div id="thumbPreview" class="mt-2" style="display:none;">
                                <img id="thumbImg" style="width: 100px;">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Catégorie</label>
                        <input type="text" name="categorie" class="form-control" list="catList">
                        <datalist id="catList">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="est_actif" value="1" checked>
                                <label>Actif</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1">
                                <label>WhatsApp</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked>
                                <label>Site Web</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('autre/Delete') ?>" method="POST">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Supprimer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Voulez-vous vraiment supprimer <strong id="deleteTitle"></strong> ?</p>
                    <input type="hidden" name="id" id="deleteId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#itemsTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' },
        order: [[0, 'desc']]
    });

    // Afficher/cacher les champs selon le type
    $('#itemType').on('change', function() {
        var type = $(this).val();
        $('#linkField').hide();
        $('#texteField').hide();
        $('#fileField').hide();
        $('#thumbnailField').hide();
        
        if (type == 'link') {
            $('#linkField').show();
            $('#submitBtn').prop('disabled', false);
        } else if (type == 'texte') {
            $('#texteField').show();
            $('#submitBtn').prop('disabled', false);
        } else if (type == 'photo' || type == 'book' || type == 'other') {
            $('#fileField').show();
            $('#thumbnailField').show();
            $('#submitBtn').prop('disabled', true);
        }
    });

    // Upload de fichier
    $('#fileInput').on('change', function(e) {
        var file = e.target.files[0];
        var type = $('#itemType').val();
        if (!file) return;
        
        var formData = new FormData();
        formData.append('file', file);
        formData.append('type', type);
        
        $('#uploadProgress').show();
        
        $.ajax({
            url: '<?= base_url('autre/uploadFile') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var percent = (e.loaded / e.total) * 100;
                        $('#progressBar').css('width', percent + '%').text(Math.round(percent) + '%');
                    }
                });
                return xhr;
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.success) {
                    $('#uploadedFilePath').val(data.file_path);
                    $('#submitBtn').prop('disabled', false);
                    toastr.success('Fichier uploadé');
                } else {
                    toastr.error(data.message);
                }
            },
            error: function() {
                toastr.error('Erreur upload');
            }
        });
    });

    // Upload miniature personnalisée
    $('#customThumbnail').on('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        
        var formData = new FormData();
        formData.append('thumbnail_file', file);
        
        $.ajax({
            url: '<?= base_url('autre/upload_image') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                var data = JSON.parse(response);
                if (data.success) {
                    $('#selectedThumbnail').val(data.file_path);
                    $('#thumbImg').attr('src', data.preview_url);
                    $('#thumbPreview').show();
                    toastr.success('Miniature uploadée');
                } else {
                    toastr.error(data.message);
                }
            }
        });
    });
});

function uploadEditThumbnail(id, file) {
    if (!file) return;
    var formData = new FormData();
    formData.append('thumbnail_file', file);
    
    $.ajax({
        url: '<?= base_url('autre/upload_image') ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            var data = JSON.parse(response);
            if (data.success) {
                $('#currentThumb' + id).attr('src', data.preview_url);
                $('#editThumb' + id).val(data.file_path);
                toastr.success('Miniature mise à jour');
            } else {
                toastr.error(data.message);
            }
        }
    });
}

function confirmDelete(id, title) {
    $('#deleteId').val(id);
    $('#deleteTitle').text(title);
    $('#deleteModal').modal('show');
}

toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right" };
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>