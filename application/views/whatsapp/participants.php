<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants - <?= htmlspecialchars($group['group_name'] ?? 'Groupe') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Style WhatsApp */
        :root {
            --whatsapp-green: #25D366;
            --whatsapp-dark: #128C7E;
            --whatsapp-bg: #E5DDD5;
            --chat-bg: #DCF8C6;
            --chat-incoming: #FFFFFF;
        }
        
        body {
            background: var(--whatsapp-bg);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
        }
        
        .whatsapp-header {
            background: linear-gradient(180deg, #075E54 0%, #128C7E 100%);
            color: white;
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .whatsapp-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            min-height: calc(100vh - 70px);
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .chat-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .chat-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
            position: relative;
        }
        
        .chat-item:hover {
            background: #f5f5f5;
        }
        
        .chat-item.selected {
            background: #e3f2fd;
        }
        
        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .avatar.creator {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .avatar.admin {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .chat-info {
            flex: 1;
            min-width: 0;
        }
        
        .chat-name {
            font-weight: 600;
            font-size: 16px;
            color: #111;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .chat-meta {
            color: #667781;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .role-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 500;
        }
        
        .role-creator {
            background: #ffebee;
            color: #c62828;
        }
        
        .role-admin {
            background: #fff3e0;
            color: #ef6c00;
        }
        
        .role-member {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .chat-actions {
            display: flex;
            gap: 5px;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .chat-item:hover .chat-actions {
            opacity: 1;
        }
        
        .btn-whatsapp-action {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-whatsapp-action:hover {
            transform: scale(1.1);
        }
        
        .btn-whatsapp {
            background: var(--whatsapp-green);
            color: white;
        }
        
        .btn-call {
            background: #2196F3;
            color: white;
        }
        
        .btn-copy {
            background: #9C27B0;
            color: white;
        }
        
        .selection-bar {
            background: white;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            display: none;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 70px;
            z-index: 999;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .selection-bar.active {
            display: flex;
        }
        
        .checkbox-wrapper {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .chat-item:hover .checkbox-wrapper,
        .chat-item.selected .checkbox-wrapper {
            opacity: 1;
        }
        
        .chat-item.with-checkbox {
            padding-left: 55px;
        }
        
        .floating-compose {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--whatsapp-green);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            z-index: 1000;
        }
        
        .floating-compose:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.5);
        }
        
        .bulk-actions {
            position: fixed;
            bottom: 100px;
            right: 30px;
            display: none;
            flex-direction: column;
            gap: 10px;
            z-index: 1000;
        }
        
        .bulk-actions.active {
            display: flex;
        }
        
        .bulk-btn {
            padding: 12px 20px;
            border-radius: 25px;
            border: none;
            color: white;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s;
        }
        
        .bulk-btn:hover {
            transform: translateX(-5px);
        }
        
        .bulk-whatsapp {
            background: var(--whatsapp-green);
        }
        
        .bulk-sms {
            background: #2196F3;
        }
        
        .search-bar {
            background: #f6f6f6;
            padding: 10px 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 15px;
            border-radius: 20px;
            border: none;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .alert-whatsapp {
            border-radius: 0;
            border: none;
            margin: 0;
        }
        
        .stats-bar {
            background: white;
            padding: 10px 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: #667781;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Modal style WhatsApp */
        .modal-whatsapp .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-whatsapp .modal-header {
            background: linear-gradient(180deg, #075E54 0%, #128C7E 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .message-preview {
            background: var(--chat-bg);
            padding: 15px;
            border-radius: 15px 15px 15px 5px;
            margin: 15px 0;
            position: relative;
        }
        
        .message-preview::before {
            content: '';
            position: absolute;
            left: -10px;
            top: 0;
            width: 0;
            height: 0;
            border-top: 15px solid var(--chat-bg);
            border-left: 15px solid transparent;
        }
    </style>
</head>
<body>

<!-- Header WhatsApp -->
<div class="whatsapp-header">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= site_url('whatsapp/groupes') ?>" class="text-white text-decoration-none">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <div>
                <h5 class="mb-0 fw-bold"><?= htmlspecialchars($group['group_name'] ?? 'Groupe') ?></h5>
                <small><?= count($participants) ?> participants</small>
            </div>
        </div>
        <div class="d-flex gap-3">
            <button class="btn btn-link text-white p-0" onclick="toggleSelectionMode()" title="Sélection multiple">
                <i class="bi bi-check2-square fs-5"></i>
            </button>
            <button class="btn btn-link text-white p-0" onclick="document.getElementById('searchInput').focus()" title="Rechercher">
                <i class="bi bi-search fs-5"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-link text-white p-0" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical fs-5"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= site_url('whatsapp/sync_participants/' . urlencode($group['group_id'] ?? '')) ?>">
                        <i class="bi bi-arrow-clockwise me-2"></i>Actualiser
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exporterCSV()">
                        <i class="bi bi-download me-2"></i>Exporter CSV
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= site_url('whatsapp/envoyer?groupe=' . urlencode($group['group_id'] ?? '')) ?>">
                        <i class="bi bi-send me-2"></i>Message au groupe
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Barre de sélection (apparaît en mode sélection) -->
<div class="selection-bar" id="selectionBar">
    <div class="d-flex align-items-center gap-3">
        <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
        <span id="selectionCount">0 sélectionné(s)</span>
    </div>
    <button class="btn btn-link text-danger p-0" onclick="clearSelection()">
        <i class="bi bi-x-lg"></i> Annuler
    </button>
</div>

<!-- Alertes -->
<?php if (isset($from_cache) && $from_cache): ?>
    <div class="alert alert-warning alert-whatsapp alert-dismissible fade show mb-0">
        <i class="bi bi-clock-history"></i> Données en cache. 
        <a href="<?= site_url('whatsapp/sync_participants/' . urlencode($group['group_id'] ?? '')) ?>" class="alert-link">
            Actualiser
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php elseif (isset($sync_stats)): ?>
    <div class="alert alert-success alert-whatsapp alert-dismissible fade show mb-0">
        <i class="bi bi-check-circle"></i> 
        Synchronisé: <?= $sync_stats['inserted'] ?> nouveaux, <?= $sync_stats['updated'] ?> mis à jour
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-item">
        <i class="bi bi-people-fill text-success"></i>
        <span><?= count($participants) ?> total</span>
    </div>
    <div class="stat-item">
        <i class="bi bi-shield-fill text-warning"></i>
        <span><?= count(array_filter($participants, fn($p) => !empty($p['is_admin']))) ?> admins</span>
    </div>
    <div class="stat-item">
        <i class="bi bi-star-fill text-danger"></i>
        <span><?= count(array_filter($participants, fn($p) => !empty($p['is_creator']))) ?> créateur(s)</span>
    </div>
</div>

<!-- Recherche -->
<div class="search-bar">
    <input type="text" class="search-input" id="searchInput" placeholder="🔍 Rechercher un participant..." 
           onkeyup="filterParticipants()">
</div>

<!-- Liste des participants style WhatsApp -->
<div class="whatsapp-container">
    <ul class="chat-list" id="participantsList">
        <?php if (empty($participants)): ?>
            <li class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                <p>Aucun participant trouvé</p>
            </li>
        <?php else: ?>
            <?php foreach ($participants as $i => $p): 
                $initial = strtoupper(substr($p['profile_name'] ?? $p['phone'], 0, 1));
                $role_class = !empty($p['is_creator']) ? 'creator' : (!empty($p['is_admin']) ? 'admin' : '');
                $role_badge = !empty($p['is_creator']) ? 'role-creator' : (!empty($p['is_admin']) ? 'role-admin' : 'role-member');
                $role_text = !empty($p['is_creator']) ? 'Créateur' : (!empty($p['is_admin']) ? 'Admin' : 'Membre');
                $phone_clean = str_replace(['+', ' ', '-', '@s.whatsapp.net'], '', $p['number_formatted'] ?? $p['phone']);
            ?>
            <li class="chat-item" id="participant-<?= $i ?>" data-phone="<?= $phone_clean ?>" 
                onclick="toggleParticipantSelection(<?= $i ?>)">
                
                <!-- Checkbox (visible en mode sélection) -->
                <div class="checkbox-wrapper">
                    <input type="checkbox" class="form-check-input participant-check" 
                           id="check-<?= $i ?>" value="<?= $phone_clean ?>" 
                           data-name="<?= htmlspecialchars($p['profile_name'] ?? $p['phone']) ?>"
                           onclick="event.stopPropagation()">
                </div>
                
                <!-- Avatar -->
                <div class="avatar <?= $role_class ?>"><?= $initial ?></div>
                
                <!-- Info -->
                <div class="chat-info">
                    <div class="chat-name">
                        <?= htmlspecialchars($p['profile_name'] ?? $p['number_formatted'] ?? $p['phone']) ?>
                        <span class="role-badge <?= $role_badge ?>"><?= $role_text ?></span>
                    </div>
                    <div class="chat-meta">
                        <code><?= htmlspecialchars($p['phone']) ?></code>
                        <?php if (!empty($p->synced_at)): ?>
                            <span>• <?= date('d/m/Y', strtotime($p->synced_at)) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="chat-actions">
                    <button class="btn-whatsapp-action btn-copy" onclick="event.stopPropagation(); copierNumero('<?= $p['phone'] ?>')" title="Copier">
                        <i class="bi bi-clipboard"></i>
                    </button>
                    <a href="tel:<?= $p['number_formatted'] ?? $p['phone'] ?>" class="btn-whatsapp-action btn-call" onclick="event.stopPropagation()" title="Appeler">
                        <i class="bi bi-telephone"></i>
                    </a>
                    <a href="https://wa.me/<?= $phone_clean ?>" target="_blank" class="btn-whatsapp-action btn-whatsapp" onclick="event.stopPropagation()" title="WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<!-- Bouton flottant composer -->
<button class="floating-compose" onclick="openComposeModal()" title="Nouveau message">
    <i class="bi bi-chat-dots-fill"></i>
</button>

<!-- Boutons d'action groupée (apparaissent quand sélection actif) -->
<div class="bulk-actions" id="bulkActions">
    <button class="bulk-btn bulk-whatsapp" onclick="sendBulkWhatsApp()" title="Envoyer WhatsApp aux sélectionnés">
        <i class="bi bi-whatsapp"></i> WhatsApp (<span id="bulkCount">0</span>)
    </button>
    <button class="bulk-btn bulk-sms" onclick="sendBulkSMS()" title="Envoyer SMS aux sélectionnés">
        <i class="bi bi-chat-text"></i> SMS
    </button>
</div>

<!-- Modal Composer Message -->
<div class="modal fade modal-whatsapp" id="composeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-chat-dots me-2"></i>Nouveau message</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Destinataires</label>
                    <div class="d-flex gap-2 mb-2">
                        <button class="btn btn-sm btn-outline-success" onclick="selectAllParticipants()">
                            <i class="bi bi-check-all"></i> Tous les participants
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="selectAdminsOnly()">
                            <i class="bi bi-shield"></i> Admins uniquement
                        </button>
                    </div>
                    <select class="form-select" id="recipientsSelect" multiple size="5">
                        <?php foreach ($participants as $p): 
                            $phone_clean = str_replace(['+', ' ', '-'], '', $p['number_formatted'] ?? $p['phone']);
                            $label = ($p['profile_name'] ?? $p['number_formatted'] ?? $p['phone']) . ' (' . $role_text . ')';
                        ?>
                            <option value="<?= $phone_clean ?>" data-role="<?= $p['rank'] ?? 'member' ?>">
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted"><span id="recipientCount">0</span> destinataire(s) sélectionné(s)</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Message</label>
                    <textarea class="form-control" id="messageText" rows="4" placeholder="Tapez votre message..."></textarea>
                </div>
                
                <div class="message-preview" id="messagePreview" style="display: none;">
                    <small class="text-muted">Aperçu</small>
                    <p class="mb-0" id="previewText"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="sendMessageToAll()">
                    <i class="bi bi-send-fill me-2"></i>Envoyer à tous
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let selectionMode = false;
let selectedParticipants = new Set();

// Activer/désactiver le mode sélection
function toggleSelectionMode() {
    selectionMode = !selectionMode;
    document.body.classList.toggle('selection-active', selectionMode);
    
    const items = document.querySelectorAll('.chat-item');
    items.forEach(item => {
        if (selectionMode) {
            item.classList.add('with-checkbox');
        } else {
            item.classList.remove('with-checkbox', 'selected');
            item.querySelector('.participant-check').checked = false;
        }
    });
    
    document.getElementById('selectionBar').classList.toggle('active', selectionMode);
    updateSelectionCount();
}

// Sélectionner/désélectionner un participant
function toggleParticipantSelection(index) {
    if (!selectionMode) return;
    
    const item = document.getElementById('participant-' + index);
    const checkbox = document.getElementById('check-' + index);
    const phone = checkbox.value;
    
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        item.classList.add('selected');
        selectedParticipants.add(phone);
    } else {
        item.classList.remove('selected');
        selectedParticipants.delete(phone);
    }
    
    updateSelectionCount();
}

// Mettre à jour le compteur de sélection
function updateSelectionCount() {
    const count = selectedParticipants.size;
    document.getElementById('selectionCount').textContent = count + ' sélectionné(s)';
    document.getElementById('bulkCount').textContent = count;
    document.getElementById('bulkActions').classList.toggle('active', count > 0);
}

// Tout sélectionner/désélectionner
function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.participant-check');
    const selectAll = document.getElementById('selectAll').checked;
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll;
        const phone = cb.value;
        const item = document.getElementById('participant-' + cb.id.split('-')[1]);
        
        if (selectAll) {
            selectedParticipants.add(phone);
            item.classList.add('selected');
        } else {
            selectedParticipants.delete(phone);
            item.classList.remove('selected');
        }
    });
    
    updateSelectionCount();
}

// Vider la sélection
function clearSelection() {
    selectedParticipants.clear();
    document.querySelectorAll('.participant-check').forEach(cb => cb.checked = false);
    document.querySelectorAll('.chat-item').forEach(item => item.classList.remove('selected'));
    document.getElementById('selectAll').checked = false;
    toggleSelectionMode();
}

// Envoi WhatsApp groupé
function sendBulkWhatsApp() {
    const phones = Array.from(selectedParticipants);
    if (phones.length === 0) return;
    
    // Ouvrir WhatsApp Web avec le premier numéro
    window.open('https://wa.me/' + phones[0], '_blank');
    
    // Afficher les autres à envoyer
    if (phones.length > 1) {
        alert('Envoyez le message au premier contact, puis cliquez OK pour le suivant.\nRestants: ' + (phones.length - 1));
        // On pourrait implémenter une file d'attente ici
    }
}

// Envoi SMS groupé
function sendBulkSMS() {
    const phones = Array.from(selectedParticipants);
    if (phones.length === 0) return;
    
    const smsLink = 'sms:' + phones.join(',');
    window.location.href = smsLink;
}

// Ouvrir le modal de composition
function openComposeModal() {
    const modal = new bootstrap.Modal(document.getElementById('composeModal'));
    modal.show();
    updateRecipientCount();
}

// Sélectionner tous les participants dans le modal
function selectAllParticipants() {
    const select = document.getElementById('recipientsSelect');
    for (let option of select.options) {
        option.selected = true;
    }
    updateRecipientCount();
}

// Sélectionner uniquement les admins
function selectAdminsOnly() {
    const select = document.getElementById('recipientsSelect');
    for (let option of select.options) {
        option.selected = (option.dataset.role === 'admin' || option.dataset.role === 'creator');
    }
    updateRecipientCount();
}

// Mettre à jour le compteur de destinataires
function updateRecipientCount() {
    const select = document.getElementById('recipientsSelect');
    const count = Array.from(select.selectedOptions).length;
    document.getElementById('recipientCount').textContent = count;
}

// Envoyer message à tous (via l'API du site)
function sendMessageToAll() {
    const select = document.getElementById('recipientsSelect');
    const message = document.getElementById('messageText').value;
    const recipients = Array.from(select.selectedOptions).map(o => o.value);
    
    if (recipients.length === 0) {
        alert('Veuillez sélectionner au moins un destinataire');
        return;
    }
    
    if (!message.trim()) {
        alert('Veuillez saisir un message');
        return;
    }
    
    // Redirection vers la page d'envoi avec les paramètres
    const url = '<?= site_url('whatsapp/envoyer') ?>?' + 
                'phones=' + encodeURIComponent(recipients.join(',')) + 
                '&message=' + encodeURIComponent(message) +
                '&type=private';
    
    window.location.href = url;
}

// Filtrer les participants
function filterParticipants() {
    const term = document.getElementById('searchInput').value.toLowerCase();
    const items = document.querySelectorAll('.chat-item');
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(term) ? '' : 'none';
    });
}

// Copier un numéro
function copierNumero(numero) {
    navigator.clipboard.writeText(numero).then(() => {
        // Toast notification
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-50 start-50 translate-middle bg-dark text-white px-4 py-2 rounded';
        toast.style.zIndex = '9999';
        toast.innerHTML = '<i class="bi bi-check-circle me-2"></i>Numéro copié';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    });
}

// Exporter CSV
function exporterCSV() {
    const rows = document.querySelectorAll('.chat-item');
    let csv = 'Nom,Numéro,Rôle\n';
    
    rows.forEach(row => {
        const name = row.querySelector('.chat-name').textContent.trim().replace(/Créateur|Admin|Membre/, '').trim();
        const phone = row.querySelector('code').textContent;
        const role = row.querySelector('.role-badge').textContent;
        csv += `"${name}","${phone}","${role}"\n`;
    });
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'participants_<?= htmlspecialchars($group['group_id'] ?? 'groupe') ?>.csv';
    link.click();
}

// Écouteur pour le compteur de destinataires
document.getElementById('recipientsSelect')?.addEventListener('change', updateRecipientCount);

// Aperçu du message
document.getElementById('messageText')?.addEventListener('input', function() {
    const preview = document.getElementById('messagePreview');
    const text = document.getElementById('previewText');
    if (this.value.trim()) {
        preview.style.display = 'block';
        text.textContent = this.value;
    } else {
        preview.style.display = 'none';
    }
});
</script>

</body>
</html>