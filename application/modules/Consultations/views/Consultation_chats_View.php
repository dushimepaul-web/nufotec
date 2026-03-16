<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<style>
    .chat-container { height: calc(100vh - 200px); display: flex; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .chat-sidebar { width: 350px; background: #f8f9fa; border-right: 1px solid #e9ecef; display: flex; flex-direction: column; }
    .chat-sidebar-header { padding: 20px; border-bottom: 1px solid #e9ecef; }
    .chat-search { padding: 10px 20px; border-bottom: 1px solid #e9ecef; }
    .chat-search input { width: 100%; padding: 10px 15px; border: 1px solid #dee2e6; border-radius: 25px; font-size: 14px; }
    .chat-conversations { flex: 1; overflow-y: auto; }
    .conversation-item { padding: 15px 20px; display: flex; align-items: center; cursor: pointer; border-bottom: 1px solid #e9ecef; transition: all 0.3s; }
    .conversation-item:hover, .conversation-item.active { background: #e3f2fd; }
    .conversation-item.active { border-left: 4px solid #0d6efd; }
    .conversation-avatar { width: 50px; height: 50px; border-radius: 50%; margin-right: 15px; object-fit: cover; }
    .conversation-info { flex: 1; min-width: 0; }
    .conversation-name { font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
    .conversation-time { font-size: 11px; color: #6c757d; }
    .conversation-last-message { font-size: 12px; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .unread-badge { background: #0d6efd; color: white; border-radius: 50%; min-width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; }
    
    /* Badges de statut consultation */
    .consultation-badge { font-size: 10px; padding: 2px 8px; border-radius: 12px; margin-left: 5px; }
    .badge-en-attente { background: #ffc107; color: #000; }
    .badge-confirmee { background: #17a2b8; color: #fff; }
    .badge-en-cours { background: #28a745; color: #fff; }
    .badge-terminee { background: #6c757d; color: #fff; }
    .badge-annulee { background: #dc3545; color: #fff; }
    
    .chat-main { flex: 1; display: flex; flex-direction: column; }
    .chat-header { padding: 20px; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; justify-content: space-between; }
    .chat-header-info { display: flex; align-items: center; }
    .chat-header-avatar { width: 50px; height: 50px; border-radius: 50%; margin-right: 15px; }
    .chat-messages { flex: 1; overflow-y: auto; padding: 20px; background: #f8f9fa; }
    .message-wrapper { display: flex; margin-bottom: 20px; }
    .message-wrapper.them { justify-content: flex-start; }
    .message-wrapper.me { justify-content: flex-end; }
    .message-bubble { max-width: 60%; padding: 12px 16px; border-radius: 18px; }
    .them .message-bubble { background: #fff; border: 1px solid #e9ecef; border-top-left-radius: 4px; }
    .me .message-bubble { background: #0d6efd; color: white; border-top-right-radius: 4px; }
    .chat-input-area { padding: 20px; border-top: 1px solid #e9ecef; background: #fff; }
    .chat-input-wrapper { display: flex; gap: 10px; }
    .chat-input-wrapper input { flex: 1; padding: 10px 20px; border: 1px solid #dee2e6; border-radius: 25px; }
    .chat-input-wrapper button { width: 45px; height: 45px; border-radius: 50%; border: none; background: #0d6efd; color: white; }
    .chat-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #6c757d; }
</style>

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb mb-3">
            <div class="breadcrumb-title pe-3">Communication</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active">Chat en direct</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="chat-container">
            <div class="chat-sidebar">
                <div class="chat-sidebar-header">
                    <h5 class="mb-0">
                        <i class="bx bx-chat me-2"></i>Mes conversations
                        <span class="badge bg-primary ms-2" id="total-unread">0</span>
                    </h5>
                </div>
                
                <div class="chat-search">
                    <input type="text" id="search-chat" placeholder="Rechercher un patient..." onkeyup="searchUsers(this.value)">
                </div>
                
                <div class="chat-conversations" id="conversations-list">
                    <?php if (!empty($conversations)): ?>
                        <?php foreach ($conversations as $conv): 
                            // Déterminer la classe du badge selon le statut
                            $badgeClass = '';
                            $badgeText = '';
                            if (!empty($conv['consultation_statut'])) {
                                switch($conv['consultation_statut']) {
                                    case 'en_attente': $badgeClass = 'badge-en-attente'; $badgeText = 'En attente'; break;
                                    case 'confirmee': $badgeClass = 'badge-confirmee'; $badgeText = 'Confirmée'; break;
                                    case 'en_cours': $badgeClass = 'badge-en-cours'; $badgeText = 'En cours'; break;
                                    case 'terminee': $badgeClass = 'badge-terminee'; $badgeText = 'Terminée'; break;
                                    case 'annulee': $badgeClass = 'badge-annulee'; $badgeText = 'Annulée'; break;
                                }
                            }
                        ?>
                            <div class="conversation-item <?= $conv['unread_count'] > 0 ? 'unread' : '' ?>" 
                                 onclick="loadConversation(<?= $conv['user_id'] ?>, this)"
                                 data-id="<?= $conv['user_id'] ?>">
                                <img src="<?= base_url(!empty($conv['photo']) ? 'attachments/Users/'.$conv['photo'] : 'attachments/Users/default-avatar.png') ?>" 
                                     class="conversation-avatar">
                                <div class="conversation-info">
                                    <div class="conversation-name">
                                        <span>
                                            <?= htmlspecialchars($conv['prenom'].' '.$conv['nom']) ?>
                                            <?php if ($badgeClass): ?>
                                                <span class="consultation-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <?php if ($conv['unread_count'] > 0): ?>
                                            <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="conversation-last-message">
                                        <?= htmlspecialchars(substr($conv['last_message'] ?? '...', 0, 30)) ?>
                                    </div>
                                    <?php if (!empty($conv['prochaine_consultation'])): ?>
                                        <div class="conversation-time">
                                            <i class="bx bx-calendar"></i> 
                                            <?= date('d/m/Y H:i', strtotime($conv['prochaine_consultation'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bx bx-user-x fs-1 text-muted"></i>
                            <p class="text-muted mt-3">Aucun patient trouvé</p>
                            <small class="text-muted">Les patients apparaîtront ici après avoir pris rendez-vous</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="chat-main" id="chat-main">
                <div class="chat-empty">
                    <i class="bx bx-message-square-dots fs-1"></i>
                    <h5>Sélectionnez un patient</h5>
                    <p class="text-muted">Choisissez un patient dans la liste pour consulter vos échanges</p>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="file" id="file-input" style="display: none;" onchange="uploadFile(this)">

<script>
let currentUserId = null;
let messageCheckInterval = null;

$(document).ready(function() {
    setInterval(checkNewMessages, 3000);
    setInterval(updateUnreadCount, 5000);
    updateUnreadCount();
});

function searchUsers(query) {
    if (query.length < 2) return;
    
    $.getJSON('<?= base_url("Consultation_chats/searchUsers") ?>?q=' + encodeURIComponent(query), function(data) {
        let html = '';
        data.forEach(function(conv) {
            html += `
                <div class="conversation-item" onclick="loadConversation(${conv.id}, this)">
                    <img src="<?= base_url('attachments/Users/') ?>${conv.photo || 'default-avatar.png'}" class="conversation-avatar">
                    <div class="conversation-info">
                        <div class="conversation-name">
                            <span>${conv.prenom} ${conv.nom}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#conversations-list').html(html);
    });
}

function loadConversation(userId, element) {
    $('.conversation-item').removeClass('active');
    $(element).addClass('active');
    
    currentUserId = userId;
    
    loadMessages(userId);
    markAsRead(userId);
    
    if (messageCheckInterval) clearInterval(messageCheckInterval);
    messageCheckInterval = setInterval(() => checkNewMessagesForUser(userId), 2000);
}

function loadMessages(userId) {
    $.getJSON('<?= base_url("Consultation_chats/getMessages/") ?>' + userId, function(messages) {
        displayMessages(messages, userId);
    }).fail(function(xhr) {
        if (xhr.status === 403) {
            alert('Vous n\'êtes pas autorisé à communiquer avec ce patient');
        }
    });
}

function displayMessages(messages, userId) {
    let html = messages.map(msg => {
        const time = new Date(msg.created_at).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
        return `
            <div class="message-wrapper ${msg.type}">
                <div class="message-bubble">
                    ${escapeHtml(msg.message)}
                    <div class="text-end small opacity-75">${time}</div>
                </div>
            </div>
        `;
    }).join('');
    
    $('#chat-main').html(`
        <div class="chat-header">
            <div class="chat-header-info">
                <img src="<?= base_url('attachments/Users/default-avatar.png') ?>" class="chat-header-avatar" id="chat-avatar">
                <div>
                    <div class="fw-bold" id="chat-name">Chargement...</div>
                    <small class="text-success">● En ligne</small>
                </div>
            </div>
        </div>
        <div class="chat-messages" id="chat-messages">${html}</div>
        <div class="chat-input-area">
            <div class="chat-input-wrapper">
                <button onclick="$('#file-input').click()"><i class="bx bx-paperclip"></i></button>
                <input type="text" id="message-input" placeholder="Message..." onkeypress="if(event.key==='Enter') sendMessage()">
                <button onclick="sendMessage()"><i class="bx bx-send"></i></button>
            </div>
        </div>
    `);
    
    $.getJSON('<?= base_url("Consultation_chats/getUserInfo/") ?>' + userId, function(info) {
        if (info) {
            $('#chat-name').text(info.prenom + ' ' + info.nom);
            $('#chat-avatar').attr('src', '<?= base_url('attachments/Users/') ?>' + (info.photo || 'default-avatar.png'));
        }
    });
    
    scrollToBottom();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function sendMessage() {
    const message = $('#message-input').val().trim();
    if (!message || !currentUserId) return;
    
    $.post('<?= base_url("Consultation_chats/sendMessage") ?>', {
        receiver_id: currentUserId,
        message: message
    }, function(response) {
        if (response.success) {
            $('#message-input').val('');
            $('#chat-messages').append(`
                <div class="message-wrapper me">
                    <div class="message-bubble">
                        ${escapeHtml(message)}
                        <div class="text-end small opacity-75">${new Date().toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'})}</div>
                    </div>
                </div>
            `);
            scrollToBottom();
        } else {
            alert(response.error || 'Erreur d\'envoi');
        }
    }, 'json');
}

function uploadFile(input) {
    if (!input.files[0] || !currentUserId) return;
    
    const formData = new FormData();
    formData.append('file', input.files[0]);
    formData.append('receiver_id', currentUserId);
    
    $.ajax({
        url: '<?= base_url("Consultation_chats/uploadFile") ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) loadMessages(currentUserId);
        }
    });
}

function checkNewMessages() {
    if (currentUserId) checkNewMessagesForUser(currentUserId);
    updateUnreadCount();
}

function checkNewMessagesForUser(userId) {
    $.getJSON('<?= base_url("Consultation_chats/getMessages/") ?>' + userId, function(messages) {
        const current = $('#chat-messages .message-wrapper').length;
        if (messages.length > current) {
            for (let i = current; i < messages.length; i++) {
                if (messages[i].type === 'them') {
                    const time = new Date(messages[i].created_at).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
                    $('#chat-messages').append(`
                        <div class="message-wrapper them">
                            <div class="message-bubble">
                                ${escapeHtml(messages[i].message)}
                                <div class="text-end small opacity-75">${time}</div>
                            </div>
                        </div>
                    `);
                }
            }
            scrollToBottom();
        }
    });
}

function markAsRead(userId) {
    $.get('<?= base_url("Consultation_chats/markAsRead/") ?>' + userId);
    $(`.conversation-item[data-id="${userId}"] .unread-badge`).remove();
}

function updateUnreadCount() {
    $.getJSON('<?= base_url("Consultation_chats/getUnreadCount") ?>', function(data) {
        $('#total-unread').text(data.unread || 0);
    });
}

function scrollToBottom() {
    const div = document.getElementById('chat-messages');
    if (div) div.scrollTop = div.scrollHeight;
}
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>