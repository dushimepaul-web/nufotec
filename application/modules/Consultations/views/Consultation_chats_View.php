<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

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

        <div class="card card-outline card-primary mb-0" style="height: calc(100vh - 200px);">
            <div class="card-body p-0 d-flex overflow-hidden">
                <div class="chat-sidebar d-flex flex-column flex-shrink-0 border-end" style="width: 350px;">
                    <div class="chat-sidebar-header p-3 border-bottom">
                        <h5 class="mb-0">
                            <i class="bx bx-chat me-2"></i>Mes conversations
                            <span class="badge text-bg-primary ms-2" id="total-unread">0</span>
                        </h5>
                    </div>

                    <div class="chat-search p-3 border-bottom">
                        <input type="text" id="search-chat" placeholder="Rechercher un patient..." onkeyup="searchUsers(this.value)" class="form-control rounded-pill">
                    </div>

                    <div class="chat-conversations list-group list-group-flush flex-grow-1 overflow-auto" id="conversations-list">
                        <?php if (!empty($conversations)): ?>
                            <?php foreach ($conversations as $conv): 
                                // Déterminer la classe du badge selon le statut
                                $badgeClass = '';
                                $badgeText = '';
                                if (!empty($conv['consultation_statut'])) {
                                    switch($conv['consultation_statut']) {
                                        case 'en_attente': $badgeClass = 'text-bg-warning'; $badgeText = 'En attente'; break;
                                        case 'confirmee': $badgeClass = 'text-bg-info'; $badgeText = 'Confirmée'; break;
                                        case 'en_cours': $badgeClass = 'text-bg-success'; $badgeText = 'En cours'; break;
                                        case 'terminee': $badgeClass = 'text-bg-secondary'; $badgeText = 'Terminée'; break;
                                        case 'annulee': $badgeClass = 'text-bg-danger'; $badgeText = 'Annulée'; break;
                                    }
                                }
                            ?>
                                <div class="conversation-item list-group-item list-group-item-action d-flex align-items-center p-3" 
                                     onclick="loadConversation(<?= $conv['user_id'] ?>, this)"
                                     data-id="<?= $conv['user_id'] ?>">
                                    <img src="<?= base_url(!empty($conv['photo']) ? 'attachments/Users/'.$conv['photo'] : 'assets/frontend/img/default-avatar.jpg') ?>" 
                                         class="conversation-avatar rounded-circle flex-shrink-0 me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                    <div class="conversation-info flex-grow-1" style="min-width: 0;">
                                        <div class="conversation-name d-flex justify-content-between align-items-center gap-2">
                                            <span class="fw-semibold text-truncate">
                                                <?= htmlspecialchars($conv['prenom'].' '.$conv['nom']) ?>
                                                <?php if ($badgeClass): ?>
                                                    <span class="consultation-badge badge <?= $badgeClass ?> ms-1"><?= $badgeText ?></span>
                                                <?php endif; ?>
                                            </span>
                                            <?php if ($conv['unread_count'] > 0): ?>
                                                <span class="unread-badge badge rounded-pill text-bg-primary flex-shrink-0" style="min-width: 20px;"><?= $conv['unread_count'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="conversation-last-message text-muted small text-truncate">
                                            <?= htmlspecialchars(substr($conv['last_message'] ?? '...', 0, 30)) ?>
                                        </div>
                                        <?php if (!empty($conv['prochaine_consultation'])): ?>
                                            <div class="conversation-time text-muted small">
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

                <div class="chat-main flex-grow-1 d-flex flex-column" id="chat-main">
                    <div class="chat-empty d-flex flex-column align-items-center justify-content-center flex-grow-1 text-muted">
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
                <div class="conversation-item list-group-item list-group-item-action d-flex align-items-center p-3" onclick="loadConversation(${conv.id}, this)">
                    <img src="<?= base_url('attachments/Users/') ?>${conv.photo || '<?= base_url("assets/frontend/img/default-avatar.jpg") ?>'}" class="conversation-avatar rounded-circle flex-shrink-0 me-3" style="width: 50px; height: 50px; object-fit: cover;">
                    <div class="conversation-info flex-grow-1" style="min-width: 0;">
                        <div class="conversation-name d-flex justify-content-between align-items-center gap-2">
                            <span class="fw-semibold text-truncate">${conv.prenom} ${conv.nom}</span>
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
            <div class="message-wrapper d-flex mb-3 ${msg.type === 'them' ? '' : 'justify-content-end'}">
                <div class="message-bubble ${msg.type === 'them' ? 'bg-white border rounded-4 rounded-top-start-0' : 'bg-primary text-white rounded-4 rounded-top-end-0'}" style="max-width: 60%;">
                    ${escapeHtml(msg.message)}
                    <div class="text-end small opacity-75">${time}</div>
                </div>
            </div>
        `;
    }).join('');
    
    $('#chat-main').html(`
        <div class="chat-header d-flex align-items-center justify-content-between p-3 border-bottom">
            <div class="chat-header-info d-flex align-items-center">
                <img src="<?= base_url('assets/frontend/img/default-avatar.jpg') ?>" class="chat-header-avatar rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;" id="chat-avatar">
                <div>
                    <div class="fw-bold" id="chat-name">Chargement...</div>
                    <small class="text-success">● En ligne</small>
                </div>
            </div>
        </div>
        <div class="chat-messages flex-grow-1 overflow-auto p-3" id="chat-messages" style="background: #f8f9fa;">${html}</div>
        <div class="chat-input-area p-3 border-top bg-white">
            <div class="chat-input-wrapper d-flex gap-2">
                <button type="button" class="btn btn-outline-primary rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center p-0" style="width: 45px; height: 45px;" onclick="$('#file-input').click()"><i class="bx bx-paperclip"></i></button>
                <input type="text" id="message-input" placeholder="Message..." class="form-control rounded-pill" onkeypress="if(event.key==='Enter') sendMessage()">
                <button type="button" class="btn btn-primary rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center p-0" style="width: 45px; height: 45px;" onclick="sendMessage()"><i class="bx bx-send"></i></button>
            </div>
        </div>
    `);
    
    $.getJSON('<?= base_url("Consultation_chats/getUserInfo/") ?>' + userId, function(info) {
        if (info) {
            $('#chat-name').text(info.prenom + ' ' + info.nom);
            $('#chat-avatar').attr('src', '<?= base_url('attachments/Users/') ?>' + (info.photo || '<?= base_url("assets/frontend/img/default-avatar.jpg") ?>'));
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
                <div class="message-wrapper d-flex mb-3 justify-content-end">
                    <div class="message-bubble bg-primary text-white rounded-4 rounded-top-end-0" style="max-width: 60%;">
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
                        <div class="message-wrapper d-flex mb-3">
                            <div class="message-bubble bg-white border rounded-4 rounded-top-start-0" style="max-width: 60%;">
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