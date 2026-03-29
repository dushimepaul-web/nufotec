<!DOCTYPE html>
<html>
<head>
    <title>Envoyer - WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .type-selector { cursor: pointer; transition: all 0.3s; }
        .type-selector:hover { transform: translateY(-2px); }
        .type-selector.active { border-color: #25D366 !important; background: #e8f5e9; }
        .preview-zone { min-height: 200px; border: 2px dashed #ddd; border-radius: 8px; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">📤 Envoyer un message WhatsApp</h2>
    
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>
    
    <form action="<?= site_url('whatsapp/traiter_envoi') ?>" method="post" enctype="multipart/form-data" id="envoiForm">
        
        <!-- Sélection du type -->
        <div class="card mb-4">
            <div class="card-header">Type d'envoi</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card type-selector active" onclick="selectType('texte')" id="type-texte">
                            <div class="card-body text-center">
                                <h4>💬</h4>
                                <strong>Message texte</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card type-selector" onclick="selectType('fichier')" id="type-fichier">
                            <div class="card-body text-center">
                                <h4>📎</h4>
                                <strong>Fichier / Média</strong>
                                <small class="d-block text-muted">PDF, Word, Images, Vidéos, Audio</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card type-selector" onclick="selectType('url')" id="type-url">
                            <div class="card-body text-center">
                                <h4>🔗</h4>
                                <strong>URL externe</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="type_envoi" id="type_envoi" value="texte">
            </div>
        </div>
        
        <!-- Sélection des groupes -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Groupes destinataires (<?= $total_groupes ?> disponibles)</span>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">Tous</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectNone()">Aucun</button>
                </div>
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                <div class="row">
                    <?php foreach ($groupes as $g): ?>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="groupes_ids[]" 
                                   value="<?= htmlspecialchars($g['groupe_id']) ?>" id="g<?= $g['id'] ?>">
                            <label class="form-check-label" for="g<?= $g['id'] ?>">
                                <?= htmlspecialchars($g['nom']) ?>
                                <small class="text-muted d-block"><?= substr($g['groupe_id'], 0, 20) ?>...</small>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Zone message / fichier -->
        <div class="card mb-4">
            <div class="card-header">Contenu</div>
            <div class="card-body">
                
                <!-- Zone texte (toujours visible) -->
                <div class="mb-3">
                    <label class="form-label">Message / Légende</label>
                    <textarea name="message" class="form-control" rows="4" 
                              placeholder="Votre message..."></textarea>
                </div>
                
                <!-- Zone fichier -->
                <div id="zone-fichier" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Fichier (max 16MB)</label>
                        <input type="file" name="fichier" class="form-control" id="fileInput" 
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.mp4,.avi,.mov,.mp3,.wav,.ogg">
                        <div class="form-text">
                            Formats supportés: PDF, Word, Excel, PowerPoint, Images, Vidéos, Audio
                        </div>
                    </div>
                    <div id="filePreview" class="preview-zone d-flex align-items-center justify-content-center text-muted">
                        <span>Aperçu du fichier sélectionné</span>
                    </div>
                </div>
                
                <!-- Zone URL -->
                <div id="zone-url" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">URL du média</label>
                        <input type="url" name="media_url" class="form-control" 
                               placeholder="https://exemple.com/fichier.pdf">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type de média</label>
                        <select name="media_type" class="form-select">
                            <option value="document">Document</option>
                            <option value="image">Image</option>
                            <option value="video">Vidéo</option>
                            <option value="audio">Audio</option>
                        </select>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Options avancées -->
        <div class="card mb-4">
            <div class="card-header">Options</div>
            <div class="card-body row">
                <div class="col-md-6">
                    <label class="form-label">Délai entre envois (ms)</label>
                    <input type="number" name="delai" class="form-control" value="1000" min="500" max="5000" step="100">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Temps d'écriture simulé (ms)</label>
                    <input type="number" name="typing_time" class="form-control" value="0" min="0" max="3000">
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-success btn-lg w-100">
            🚀 Envoyer
        </button>
        
    </form>
</div>

<script>
function selectType(type) {
    // Mettre à jour les classes
    document.querySelectorAll('.type-selector').forEach(el => el.classList.remove('active'));
    document.getElementById('type-' + type).classList.add('active');
    document.getElementById('type_envoi').value = type;
    
    // Afficher/masquer les zones
    document.getElementById('zone-fichier').classList.toggle('d-none', type !== 'fichier');
    document.getElementById('zone-url').classList.toggle('d-none', type !== 'url');
    
    // Validation du required
    document.getElementById('fileInput').required = (type === 'fichier');
}

function selectAll() {
    document.querySelectorAll('input[name="groupes_ids[]"]').forEach(cb => cb.checked = true);
}

function selectNone() {
    document.querySelectorAll('input[name="groupes_ids[]"]').forEach(cb => cb.checked = false);
}

// Preview du fichier
document.getElementById('fileInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('filePreview');
    
    if (file) {
        const size = (file.size / 1024 / 1024).toFixed(2);
        const icon = getFileIcon(file.type);
        preview.innerHTML = `
            <div class="text-center">
                <div style="font-size: 48px">${icon}</div>
                <strong>${file.name}</strong>
                <div class="text-muted">${size} MB</div>
                <div class="badge bg-info">${file.type || 'Inconnu'}</div>
            </div>
        `;
    }
});

function getFileIcon(mimeType) {
    if (mimeType?.startsWith('image/')) return '🖼️';
    if (mimeType?.startsWith('video/')) return '🎬';
    if (mimeType?.startsWith('audio/')) return '🎵';
    if (mimeType?.includes('pdf')) return '📄';
    return '📎';
}
</script>

</body>
</html>