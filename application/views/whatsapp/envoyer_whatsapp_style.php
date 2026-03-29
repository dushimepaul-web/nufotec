<?php defined('BASEPATH') OR exit('No direct script access allowed'); 

// Définir les variables par défaut si elles n'existent pas
if (!isset($resultat)) {
    $resultat = null;
}
if (!isset($message)) {
    $message = '';
}
if (!isset($type_envoi)) {
    $type_envoi = 'texte';
}
if (!isset($groupes_info)) {
    $groupes_info = array();
}
if (!isset($total_groupes)) {
    $total_groupes = 0;
}
if (!isset($groupes)) {
    $groupes = array();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp - Envoyer Message</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --whatsapp-teal: #128C7E;
            --whatsapp-light-teal: #25D366;
            --whatsapp-bg: #E5DDD5;
            --whatsapp-chat-bg: #DCF8C6;
            --whatsapp-header: #075E54;
        }
        
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            overflow: hidden;
        }
        
        .whatsapp-container {
            max-width: 1400px;
            height: 95vh;
            margin: 2vh auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            display: flex;
            overflow: hidden;
        }
        
        .sidebar-groupes {
            width: 350px;
            background: #f8f9fa;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            background: var(--whatsapp-header);
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .search-box {
            padding: 10px 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
        }
        
        .search-box input {
            border-radius: 20px;
            border: none;
            background: white;
            padding: 8px 15px;
            width: 100%;
        }
        
        .groupes-list {
            flex: 1;
            overflow-y: auto;
        }
        
        .groupe-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .groupe-item:hover {
            background: #f0f0f0;
        }
        
        .groupe-item.selected {
            background: #e3f2fd;
        }
        
        .groupe-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--whatsapp-teal);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .groupe-info {
            flex: 1;
            min-width: 0;
        }
        
        .groupe-nom {
            font-weight: 500;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .groupe-id {
            font-size: 0.8rem;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .groupe-checkbox {
            width: 20px;
            height: 20px;
            accent-color: var(--whatsapp-teal);
        }
        
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--whatsapp-bg);
        }
        
        .chat-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .selection-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .selection-count {
            background: var(--whatsapp-teal);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .preview-message {
            background: var(--whatsapp-chat-bg);
            padding: 15px;
            border-radius: 8px;
            max-width: 70%;
            margin-bottom: 20px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .file-preview {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--whatsapp-teal);
            margin-bottom: 15px;
            display: none;
        }
        
        .file-preview.active {
            display: block;
        }
        
        .chat-input-area {
            background: #f0f0f0;
            padding: 15px 20px;
        }
        
        .input-container {
            background: white;
            border-radius: 25px;
            padding: 10px 20px;
            display: flex;
            align-items: flex-end;
            gap: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .message-input {
            flex: 1;
            border: none;
            outline: none;
            resize: none;
            max-height: 120px;
            font-size: 1rem;
            padding: 5px;
        }
        
        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: transparent;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .btn-icon:hover {
            background: #f0f0f0;
            color: var(--whatsapp-teal);
        }
        
        .btn-send {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            background: var(--whatsapp-teal);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .btn-send:hover {
            background: var(--whatsapp-header);
        }
        
        .btn-send:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .btn-select-all {
            background: transparent;
            border: 1px solid white;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .type-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .type-btn {
            padding: 8px 20px;
            border-radius: 20px;
            border: 2px solid #ddd;
            background: white;
            cursor: pointer;
        }
        
        .type-btn.active {
            border-color: var(--whatsapp-teal);
            background: #e3f2fd;
            color: var(--whatsapp-teal);
        }
        
        .empty-state {
            text-align: center;
            color: #666;
            padding: 40px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        /* Résultat styles */
        .result-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .result-header {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .result-header.success {
            background: #d4edda;
            color: #155724;
        }
        
        .result-header.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--whatsapp-teal);
        }
        
        .details-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .groupe-result {
            padding: 12px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .groupe-result.success {
            border-left: 4px solid var(--whatsapp-light-teal);
        }
        
        .groupe-result.error {
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>

<!-- ✅ FORMULAIRE ENGLOBE TOUTE LA PAGE -->
<form action="<?php echo site_url('whatsapp/traiter_envoi'); ?>" method="post" enctype="multipart/form-data" id="envoiForm">

<div class="whatsapp-container">
	<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
<?php endif; ?>
    <!-- Sidebar Groupes -->
    <div class="sidebar-groupes">
        <div class="sidebar-header">
            <div>
                <h5 class="mb-0"><i class="bi bi-whatsapp me-2"></i>WhatsApp</h5>
                <small><?php echo $total_groupes; ?> groupes disponibles</small>
            </div>
            <button type="button" class="btn-select-all" onclick="toggleSelectAll()">
                <i class="bi bi-check-all me-1"></i><span id="selectAllText">Tous</span>
            </button>
        </div>
        
        <div class="search-box">
            <input type="text" class="form-control" id="searchGroupes" placeholder="Rechercher un groupe..." onkeyup="searchGroupes()">
        </div>
        
        <div class="groupes-list" id="groupesList">
            <?php foreach ($groupes as $groupe): ?>
            <div class="groupe-item" onclick="toggleGroupe(this)">
                <input type="checkbox" class="groupe-checkbox" name="groupes_ids[]" 
                       value="<?php echo htmlspecialchars($groupe['groupe_id']); ?>" 
                       onchange="updateSelection()">
                <div class="groupe-avatar">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="groupe-info">
                    <div class="groupe-nom"><?php echo htmlspecialchars($groupe['nom']); ?></div>
                    <div class="groupe-id"><?php echo substr($groupe['groupe_id'], 0, 30); ?>...</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Zone principale -->
    <div class="chat-area">
        
        <?php if ($resultat !== null): ?>
        <!-- AFFICHAGE DU RÉSULTAT -->
        <div class="chat-header">
            <div class="selection-info">
                <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                <div>
                    <h6 class="mb-0">Résultat de l'envoi</h6>
                </div>
            </div>
            <a href="<?php echo site_url('whatsapp/envoyer'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Nouvel envoi
            </a>
        </div>
        
        <div class="chat-messages">
            <?php 
            $stats = isset($resultat['response']) ? $resultat['response'] : $resultat;
            $total = isset($stats['total']) ? $stats['total'] : 0;
            $reussis = isset($stats['reussis']) ? $stats['reussis'] : 0;
            $echoues = isset($stats['echoues']) ? $stats['echoues'] : 0;
            $details = isset($stats['details']) ? $stats['details'] : array();
            $success = ($reussis > 0);
            ?>
            
            <div class="result-container">
                <div class="result-header <?php echo $success ? 'success' : 'error'; ?>">
                    <i class="bi bi-<?php echo $success ? 'check-circle-fill' : 'x-circle-fill'; ?> fs-1"></i>
                    <h4 class="mt-2"><?php echo $success ? 'Envoi terminé' : 'Échec de l\'envoi'; ?></h4>
                    <?php if (!$success && isset($resultat['error']) && $resultat['error']): ?>
                        <p class="mb-0 mt-2"><?php echo htmlspecialchars($resultat['error']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total; ?></div>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number text-success"><?php echo $reussis; ?></div>
                        <small class="text-muted">Succès</small>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number <?php echo ($echoues > 0) ? 'text-danger' : 'text-muted'; ?>"><?php echo $echoues; ?></div>
                        <small class="text-muted">Échecs</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6><i class="bi bi-chat-left-text me-2"></i>Message envoyé</h6>
                    <div class="p-3 bg-light rounded">
                        <span class="badge <?php echo ($type_envoi === 'fichier') ? 'bg-info' : 'bg-success'; ?> mb-2">
                            <?php echo ($type_envoi === 'fichier') ? 'Fichier' : 'Texte'; ?>
                        </span>
                        <p class="mb-0">
    <?php 
    $msg_str = is_array($message) ? implode(', ', $message) : (string)$message;
    echo nl2br(htmlspecialchars($msg_str));
    ?>
</p>
                    </div>
                </div>
                
                <?php if (!empty($details) && is_array($details)): ?>
                <h6><i class="bi bi-list-ul me-2"></i>Détails</h6>
                <div class="details-list">
                    <?php foreach ($details as $detail): 
                        $groupe_nom = 'Inconnu';
                        if (is_array($groupes_info) && !empty($groupes_info)) {
                            foreach ($groupes_info as $g) {
                                if (isset($g['groupe_id']) && $g['groupe_id'] === $detail['destinataire_id']) {
                                    $groupe_nom = $g['nom'];
                                    break;
                                }
                            }
                        }
                        $isSuccess = (isset($detail['statut']) && $detail['statut'] === 'succès');
                    ?>
                    <div class="groupe-result <?php echo $isSuccess ? 'success' : 'error'; ?>">
                        <i class="bi bi-<?php echo $isSuccess ? 'check-circle-fill text-success' : 'x-circle-fill text-danger'; ?>"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold"><?php echo htmlspecialchars($groupe_nom); ?></div>
                            <small class="text-muted"><?php echo isset($detail['destinataire_id']) ? substr($detail['destinataire_id'], 0, 30) : 'N/A'; ?>...</small>
                        </div>
                        <?php if (!$isSuccess && !empty($detail['erreur'])): ?>
    <small class="text-danger"><?php echo htmlspecialchars(is_array($detail['erreur']) ? json_encode($detail['erreur']) : (string)$detail['erreur']); ?></small>
<?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php else: ?>
        <!-- FORMULAIRE D'ENVOI -->
            
            <div class="chat-header">
                <div class="selection-info">
                    <i class="bi bi-chat-dots fs-4 text-muted"></i>
                    <div>
                        <h6 class="mb-0">Nouveau message</h6>
                        <small class="text-muted">
                            <span id="selectionCount" class="selection-count">0</span> groupe(s) sélectionné(s)
                        </small>
                    </div>
                </div>
                <a href="<?php echo site_url('whatsapp'); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
            
            <div class="chat-messages">
                <div class="type-selector">
                    <button type="button" class="type-btn active" onclick="setType('texte', this)" id="btnTexte">
                        <i class="bi bi-chat-text me-1"></i>Message
                    </button>
                    <button type="button" class="type-btn" onclick="setType('fichier', this)" id="btnFichier">
                        <i class="bi bi-paperclip me-1"></i>Fichier
                    </button>
                </div>
                
                <input type="hidden" name="type_envoi" id="typeEnvoi" value="texte">
                
                <div class="preview-message">
                    <div class="d-flex align-items-center gap-2 mb-2 text-muted">
                        <i class="bi bi-eye"></i>
                        <small>Aperçu du message</small>
                    </div>
                    <div id="messagePreview">Votre message apparaîtra ici...</div>
                </div>
                
                <div class="file-preview" id="filePreview">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-file-earmark fs-2 text-primary"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold" id="fileName">Aucun fichier</div>
                            <small class="text-muted" id="fileSize">-</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                
                <?php if (empty($groupes)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h5>Aucun groupe</h5>
                    <p>Synchronisez vos groupes WhatsApp pour commencer</p>
                    <a href="<?php echo site_url('whatsapp/synchroniser'); ?>" class="btn btn-success">
                        <i class="bi bi-arrow-repeat me-1"></i>Synchroniser
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="chat-input-area">
                <div class="input-container">
                    <input type="file" name="fichier" id="fileInput" style="display: none;" onchange="handleFileSelect(this)">
                    
                    <textarea name="message" class="message-input" id="messageInput" 
                              rows="1" placeholder="Votre message..." 
                              onkeyup="updatePreview()"></textarea>
                    
                    <button type="button" class="btn-icon" onclick="document.getElementById('fileInput').click()" title="Joindre fichier">
                        <i class="bi bi-paperclip fs-5"></i>
                    </button>
                    
                    <button type="submit" class="btn-send" id="btnSend" disabled>
                        <i class="bi bi-send-fill fs-5"></i>
                    </button>
                </div>
                
                <div class="mt-2 d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-shield-check me-1"></i>Envoi sécurisé via WhatsApp API
                    </small>
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted small me-2">Délai:</label>
                        <select name="delai" class="form-select form-select-sm" style="width: 100px;">
                            <option value="500">0.5s</option>
                            <option value="1000" selected>1s</option>
                            <option value="2000">2s</option>
                            <option value="3000">3s</option>
                        </select>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
        
    </div>
</div>

</form> <!-- ✅ FIN DU FORMULAIRE -->

<script>
function toggleGroupe(element) {
    const checkbox = element.querySelector('.groupe-checkbox');
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        element.classList.add('selected');
    } else {
        element.classList.remove('selected');
    }
    
    updateSelection();
}

function updateSelection() {
    const checkboxes = document.querySelectorAll('input[name="groupes_ids[]"]:checked');
    const count = checkboxes.length;
    
    document.getElementById('selectionCount').textContent = count;
    document.getElementById('btnSend').disabled = count === 0;
}

function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('input[name="groupes_ids[]"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
        const item = cb.closest('.groupe-item');
        if (!allChecked) {
            item.classList.add('selected');
        } else {
            item.classList.remove('selected');
        }
    });
    
    document.getElementById('selectAllText').textContent = allChecked ? 'Tous' : 'Aucun';
    updateSelection();
}

function setType(type, btn) {
    document.getElementById('typeEnvoi').value = type;
    
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    if (type === 'fichier') {
        document.getElementById('messageInput').placeholder = "Légende du fichier (optionnel)...";
        document.getElementById('fileInput').click();
    } else {
        document.getElementById('messageInput').placeholder = "Votre message...";
    }
}

function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = (file.size / 1024).toFixed(1) + ' KB';
        document.getElementById('filePreview').classList.add('active');
    }
}

function clearFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('filePreview').classList.remove('active');
    setType('texte', document.getElementById('btnTexte'));
}

function updatePreview() {
    const input = document.getElementById('messageInput');
    const preview = document.getElementById('messagePreview');
    const text = input.value.trim();
    
    preview.textContent = text || 'Votre message apparaîtra ici...';
    preview.style.fontStyle = text ? 'normal' : 'italic';
    preview.style.color = text ? '#333' : '#999';
}

function searchGroupes() {
    const term = document.getElementById('searchGroupes').value.toLowerCase();
    const items = document.querySelectorAll('.groupe-item');
    
    items.forEach(item => {
        const nom = item.querySelector('.groupe-nom').textContent.toLowerCase();
        item.style.display = nom.includes(term) ? '' : 'none';
    });
}

// Validation avant envoi
document.getElementById('envoiForm').addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('input[name="groupes_ids[]"]:checked');
    if (checkboxes.length === 0) {
        e.preventDefault();
        alert('Veuillez sélectionner au moins un groupe');
        return false;
    }
    
    const type = document.getElementById('typeEnvoi').value;
    const message = document.getElementById('messageInput').value.trim();
    const file = document.getElementById('fileInput').files[0];
    
    if (type === 'texte' && !message) {
        e.preventDefault();
        alert('Veuillez saisir un message');
        return false;
    }
    
    if (type === 'fichier' && !file) {
        e.preventDefault();
        alert('Veuillez sélectionner un fichier');
        return false;
    }
    
    document.getElementById('btnSend').disabled = true;
    document.getElementById('btnSend').innerHTML = '<i class="bi bi-hourglass-split fs-5"></i>';
});
</script>

</body>
</html>