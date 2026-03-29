<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoyer un fichier - WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #075E54 0%, #128C7E 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .card { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border: none; }
        .card-header { background: linear-gradient(135deg, #075E54 0%, #128C7E 100%); color: white; border-radius: 15px 15px 0 0 !important; padding: 20px; }
        .drop-zone {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        .drop-zone:hover, .drop-zone.dragover {
            border-color: #25D366;
            background: #e8f5e9;
        }
        .file-info {
            background: #e8f5e9;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }
        .group-list { max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 10px; padding: 10px; }
        .form-check { padding: 8px; border-bottom: 1px solid #eee; }
        .form-check:hover { background: #f8f9fa; }
        .selected-count { background: #25D366; color: white; padding: 5px 12px; border-radius: 20px; font-size: 14px; }
        .file-type-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin: 2px;
        }
        .type-image { background: #4caf50; color: white; }
        .type-document { background: #2196f3; color: white; }
        .type-video { background: #ff9800; color: white; }
        .type-audio { background: #9c27b0; color: white; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card shadow">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-paperclip"></i> Envoyer un fichier
                        </h4>
                        <p class="mb-0 mt-2 small">Images, PDF, vidéos, audios (max 16MB)</p>
                    </div>
                    <div class="card-body">
                        
                        <?php if($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?= site_url('whatsapp/traiter_envoi_fichier') ?>" enctype="multipart/form-data" id="formEnvoi">
                            
                            <!-- Sélection des groupes -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-users"></i> Groupes destinataires
                                    <span class="selected-count" id="selectedCount">0 sélectionné(s)</span>
                                </label>
                                
                                <div class="select-all mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label fw-bold" for="selectAll">
                                            <i class="fas fa-check-double"></i> Sélectionner tous
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="group-list">
                                    <?php foreach($groupes as $groupe): ?>
                                    <div class="form-check">
                                        <input class="form-check-input group-checkbox" type="checkbox" 
                                               name="groupes_ids[]" value="<?= $groupe['groupe_id'] ?>" 
                                               id="groupe_<?= $groupe['id'] ?>">
                                        <label class="form-check-label" for="groupe_<?= $groupe['id'] ?>">
                                            <i class="fab fa-whatsapp text-success"></i>
                                            <strong><?= htmlspecialchars($groupe['nom']) ?></strong>
                                            <small class="text-muted">(<?= $groupe['groupe_id'] ?>)</small>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Zone de drop pour le fichier -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-file"></i> Fichier à envoyer
                                </label>
                                <div class="drop-zone" id="dropZone">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <p class="mb-0">Glissez-déposez un fichier ici ou cliquez pour sélectionner</p>
                                    <small class="text-muted">Formats acceptés : JPG, PNG, GIF, PDF, MP4, MP3, DOC, XLS (max 16MB)</small>
                                    <input type="file" name="fichier" id="fichier" class="d-none" accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,video/*,audio/*">
                                </div>
                                <div id="fileInfo" class="file-info" style="display: none;">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-alt fa-2x me-3"></i>
                                        <div class="flex-grow-1">
                                            <strong id="fileName"></strong><br>
                                            <small id="fileSize" class="text-muted"></small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger" id="removeFile">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Légende -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-comment"></i> Légende (optionnelle)
                                </label>
                                <textarea name="caption" class="form-control" rows="3" 
                                          placeholder="Ajoutez une légende à votre fichier..."></textarea>
                            </div>
                            
                            <!-- Types acceptés -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Formats acceptés :</label>
                                <div>
                                    <span class="file-type-badge type-image"><i class="fas fa-image"></i> JPG, PNG, GIF, WEBP</span>
                                    <span class="file-type-badge type-document"><i class="fas fa-file-pdf"></i> PDF, DOC, DOCX, XLS, PPT</span>
                                    <span class="file-type-badge type-video"><i class="fas fa-video"></i> MP4, AVI, MOV</span>
                                    <span class="file-type-badge type-audio"><i class="fas fa-music"></i> MP3, WAV, OGG</span>
                                </div>
                                <small class="text-muted">Taille maximale : 16MB par fichier</small>
                            </div>
                            
                            <!-- Boutons -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="<?= site_url('whatsapp/liste_groupes') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </a>
                                <button type="submit" class="btn btn-success" id="btnEnvoyer">
                                    <i class="fas fa-paper-plane"></i> Envoyer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Sélection des groupes
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.group-checkbox');
        const selectedCount = document.getElementById('selectedCount');
        
        function updateSelectedCount() {
            const checked = document.querySelectorAll('.group-checkbox:checked');
            selectedCount.textContent = checked.length + ' sélectionné(s)';
        }
        
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateSelectedCount();
            });
        }
        
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });
        
        // Drag & drop pour les fichiers
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fichier');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const removeFile = document.getElementById('removeFile');
        
        dropZone.addEventListener('click', () => fileInput.click());
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                handleFile(files[0]);
            }
        });
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) {
                handleFile(e.target.files[0]);
            }
        });
        
        function handleFile(file) {
            const maxSize = 16 * 1024 * 1024; // 16MB
            if (file.size > maxSize) {
                alert('Le fichier est trop volumineux (max 16MB)');
                fileInput.value = '';
                return;
            }
            
            fileName.textContent = file.name;
            fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            fileInfo.style.display = 'block';
            dropZone.style.display = 'none';
        }
        
        if (removeFile) {
            removeFile.addEventListener('click', () => {
                fileInput.value = '';
                fileInfo.style.display = 'none';
                dropZone.style.display = 'block';
            });
        }
        
        updateSelectedCount();
    </script>
</body>
</html>