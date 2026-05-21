<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Bot Whatsapp</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    	* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: #111b21;
    overflow: hidden;
}

.whatsapp-container {
    display: flex;
    height: 100vh;
    width: 100%;
    background: #0a0a0a;
}

/* ========== SIDEBAR ========== */
.sidebar {
    width: 380px;
    background: #202c33;
    border-right: 1px solid #2a3942;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 15px 20px;
    background: #202c33;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #2a3942;
}

.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #00a884;
    font-size: 20px;
    font-weight: 600;
}

.logo i {
    font-size: 28px;
}

.header-actions {
    display: flex;
    gap: 15px;
}

.btn-icon {
    color: #8696a0;
    font-size: 18px;
    transition: color 0.2s;
    text-decoration: none;
}

.btn-icon:hover {
    color: #00a884;
}

/* Statistiques mini */
.stats-mini {
    display: flex;
    padding: 15px 20px;
    gap: 15px;
    background: #182229;
    border-bottom: 1px solid #2a3942;
}

.stat-item {
    flex: 1;
    text-align: center;
    color: #8696a0;
}

.stat-item i {
    font-size: 20px;
    color: #00a884;
    display: block;
    margin-bottom: 5px;
}

.stat-item span {
    font-size: 18px;
    font-weight: 600;
    color: #e9edef;
    display: block;
}

.stat-item small {
    font-size: 10px;
}

/* Recherche */
.search-box {
    padding: 10px 15px;
    background: #202c33;
}

.search-box input {
    width: 100%;
    padding: 12px 15px 12px 40px;
    background: #111b21;
    border: none;
    border-radius: 8px;
    color: #e9edef;
    font-size: 14px;
}

.search-box i {
    position: absolute;
    margin: 14px 0 0 12px;
    color: #8696a0;
}

/* Liste des chats */
.chats-list {
    flex: 1;
    overflow-y: auto;
}

.chat-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid #2a3942;
}

.chat-item:hover {
    background: #2a3942;
}

.chat-item.active {
    background: #2a3942;
}

.avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #00a884;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.avatar i {
    font-size: 24px;
    color: white;
}

.chat-info {
    flex: 1;
}

.chat-name {
    font-weight: 500;
    color: #e9edef;
    margin-bottom: 4px;
}

.chat-last-message {
    font-size: 13px;
    color: #8696a0;
}

.chat-meta {
    text-align: right;
}

.participant-count {
    font-size: 11px;
    background: #00a884;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
}

.status {
    font-size: 10px;
    display: block;
    margin-top: 5px;
}

.status.active {
    color: #00a884;
}

.status.inactive {
    color: #8696a0;
}

/* ========== ZONE DE CHAT ========== */
.chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #0a0a0a;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path fill="%23111b21" d="M0 0h100v100H0z"/><path fill="%23131f29" d="M10 10h80v80H10z"/></svg>');
}

.chat-placeholder {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #8696a0;
}

.chat-placeholder i {
    font-size: 80px;
    margin-bottom: 20px;
}

.chat-header {
    padding: 15px 20px;
    background: #202c33;
    border-bottom: 1px solid #2a3942;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.chat-header-info .avatar {
    width: 40px;
    height: 40px;
}

.chat-header-info h3 {
    color: #e9edef;
    font-size: 16px;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}

.message {
    display: flex;
    margin-bottom: 15px;
}

.message.outgoing {
    justify-content: flex-end;
}

.message.incoming {
    justify-content: flex-start;
}

.message-bubble {
    max-width: 60%;
    padding: 8px 12px;
    border-radius: 8px;
    position: relative;
}

.message.outgoing .message-bubble {
    background: #005c4b;
    color: #e9edef;
}

.message.incoming .message-bubble {
    background: #202c33;
    color: #e9edef;
}

.message-time {
    font-size: 10px;
    color: #8696a0;
    margin-top: 4px;
    text-align: right;
}

.message-status {
    font-size: 12px;
    margin-left: 5px;
}

.message-status.sent { color: #8696a0; }
.message-status.delivered { color: #8696a0; }
.message-status.read { color: #53bdeb; }
.message-status.failed { color: #ff3b30; }

.chat-input-area {
    padding: 15px 20px;
    background: #202c33;
    display: flex;
    gap: 10px;
    align-items: center;
}

.chat-input-area input {
    flex: 1;
    padding: 12px;
    background: #111b21;
    border: none;
    border-radius: 8px;
    color: #e9edef;
}

.chat-input-area button {
    background: #00a884;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    color: white;
    cursor: pointer;
}

/* ========== PANEL LATÉRAL ========== */
.info-panel {
    width: 320px;
    background: #202c33;
    border-left: 1px solid #2a3942;
    display: flex;
    flex-direction: column;
}

.info-header {
    padding: 15px 20px;
    border-bottom: 1px solid #2a3942;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-header h3 {
    color: #e9edef;
}

.close-info {
    background: none;
    border: none;
    color: #8696a0;
    cursor: pointer;
}

.info-content {
    padding: 20px;
    color: #e9edef;
}

/* ========== MODALS ========== */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: #202c33;
    border-radius: 12px;
    width: 500px;
    max-width: 90%;
    padding: 20px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #2a3942;
}

.modal-body {
    margin-bottom: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* ========== SCROLLBAR ========== */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #202c33;
}

::-webkit-scrollbar-thumb {
    background: #2a3942;
    border-radius: 3px;
}
    </style>
</head>
<body>
    <div class="whatsapp-container">
        <!-- SIDEBAR (LISTE DES CHATS) -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp Bot</span>
                </div>
                <div class="header-actions">
                    <a href="<?= site_url('Whatsapp/broadcast') ?>" class="btn-icon" title="Nouvelle diffusion">
                        <i class="fas fa-bullhorn"></i>
                    </a>
                    <a href="<?= site_url('Whatsapp/settings') ?>" class="btn-icon" title="Paramètres">
                        <i class="fas fa-cog"></i>
                    </a>
                    <a href="<?= site_url('Whatsapp/logout') ?>" class="btn-icon" title="Déconnexion">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
            
            <!-- STATISTIQUES RAPIDES -->
            <div class="stats-mini">
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <span><?= $stats['total_participants'] ?></span>
                    <small>Participants</small>
                </div>
                <div class="stat-item">
                    <i class="fas fa-comment"></i>
                    <span><?= $stats['messages_today'] ?></span>
                    <small>Aujourd'hui</small>
                </div>
                <div class="stat-item">
                    <i class="fas fa-clock"></i>
                    <span><?= $stats['pending_messages'] ?></span>
                    <small>En attente</small>
                </div>
            </div>
            
            <!-- RECHERCHE -->
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchChat" placeholder="Rechercher une conversation...">
            </div>
            
            <!-- LISTE DES CONVERSATIONS -->
            <div class="chats-list" id="chatsList">
                <?php foreach ($groups as $group): ?>
                <div class="chat-item" data-chat-id="<?= $group->groupe_id ?>" data-chat-type="group">
                    <div class="avatar">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="chat-info">
                        <div class="chat-name"><?= htmlspecialchars($group->nom ?? 'Groupe sans nom') ?></div>
                        <div class="chat-last-message">
                            <?= $group->message_count ?? 0 ?> messages
                        </div>
                    </div>
                    <div class="chat-meta">
                        <span class="participant-count"><?= $group->participant_count ?? 0 ?></span>
                        <?php if ($group->actif): ?>
                            <span class="status active">Actif</span>
                        <?php else: ?>
                            <span class="status inactive">Inactif</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- ZONE DE CHAT PRINCIPALE -->
        <div class="chat-area" id="chatArea">
            <div class="chat-placeholder">
                <i class="fab fa-whatsapp"></i>
                <h3>Sélectionnez une conversation</h3>
                <p>Choisissez un groupe ou une conversation privée pour commencer</p>
            </div>
        </div>
        
        <!-- PANEL LATÉRAL DROIT (INFOS) -->
        <div class="info-panel" id="infoPanel" style="display: none;">
            <div class="info-header">
                <h3>Informations</h3>
                <button class="close-info" onclick="closeInfoPanel()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="info-content" id="infoContent">
                <!-- Contenu dynamique -->
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
    <script>
    	$(document).ready(function() {
    // Socket.io pour les messages en temps réel
    const socket = io('https://nufotec.com:3000');
    
    let currentChat = null;
    let currentType = null;
    
    // ========== CHARGER UNE CONVERSATION ==========
    function loadChat(chatId, chatType) {
        currentChat = chatId;
        currentType = chatType;
        
        $.ajax({
            url: base_url + 'Whatsapp/chat/' + chatId + '/' + chatType,
            type: 'GET',
            success: function(response) {
                $('#chatArea').html(response);
                scrollToBottom();
                
                // Marquer comme lu
                $.post(base_url + 'Whatsapp/mark_as_read/' + chatId + '/' + chatType);
                
                // Activer l'élément dans la liste
                $('.chat-item').removeClass('active');
                $(`.chat-item[data-chat-id="${chatId}"]`).addClass('active');
            }
        });
    }
    
    // ========== ENVOYER UN MESSAGE ==========
    function sendMessage() {
        const message = $('#messageInput').val();
        if (!message.trim()) return;
        
        $.ajax({
            url: base_url + 'Whatsapp/send_message',
            type: 'POST',
            data: {
                chat_id: currentChat,
                type: currentType,
                message: message
            },
            success: function(response) {
                if (response.success) {
                    $('#messageInput').val('');
                    addMessageToChat({
                        message: message,
                        direction: 'outgoing',
                        created_at: new Date().toISOString(),
                        status: 'sent'
                    });
                    scrollToBottom();
                } else {
                    alert('Erreur: ' + response.error);
                }
            }
        });
    }
    
    // ========== AJOUTER UN MESSAGE DANS LE CHAT ==========
    function addMessageToChat(message) {
        const messageHtml = `
            <div class="message ${message.direction}">
                <div class="message-bubble">
                    ${escapeHtml(message.message)}
                    <div class="message-time">
                        ${new Date(message.created_at).toLocaleTimeString()}
                        <span class="message-status ${message.status}">
                            ${getStatusIcon(message.status)}
                        </span>
                    </div>
                </div>
            </div>
        `;
        $('.chat-messages').append(messageHtml);
    }
    
    function getStatusIcon(status) {
        const icons = {
            sent: '✓',
            delivered: '✓✓',
            read: '✓✓',
            failed: '✗'
        };
        return icons[status] || '';
    }
    
    // ========== CHARGER LES INFOS D'UN GROUPE ==========
    function loadGroupInfo(groupId) {
        $.ajax({
            url: base_url + 'Whatsapp/group_info/' + groupId,
            type: 'GET',
            success: function(data) {
                $('#infoContent').html(data);
                $('#infoPanel').show();
            }
        });
    }
    
    // ========== GESTION DES PARTICIPANTS ==========
    function blockParticipant(participantId) {
        if (confirm('Bloquer ce participant ?')) {
            $.post(base_url + 'Whatsapp/block_participant/' + participantId, function() {
                location.reload();
            });
        }
    }
    
    function unblockParticipant(participantId) {
        if (confirm('Débloquer ce participant ?')) {
            $.post(base_url + 'Whatsapp/unblock_participant/' + participantId, function() {
                location.reload();
            });
        }
    }
    
    // ========== GESTION DES GROUPES ==========
    function toggleGroup(groupId, actif) {
        $.post(base_url + 'Whatsapp/toggle_group/' + groupId, {actif: actif}, function() {
            location.reload();
        });
    }
    
    function deleteGroup(groupId) {
        if (confirm('Supprimer définitivement ce groupe ?')) {
            $.post(base_url + 'Whatsapp/delete_group/' + groupId, function() {
                location.reload();
            });
        }
    }
    
    // ========== DIFFUSION ==========
    function sendBroadcast() {
        const formData = new FormData($('#broadcastForm')[0]);
        
        $.ajax({
            url: base_url + 'Whatsapp/send_broadcast',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Diffusion ajoutée à la file d\'attente');
                    $('#broadcastModal').hide();
                } else {
                    alert('Erreur: ' + response.error);
                }
            }
        });
    }
    
    // ========== UTILITAIRES ==========
    function scrollToBottom() {
        const messages = $('.chat-messages');
        messages.scrollTop(messages[0].scrollHeight);
    }
    
    function escapeHtml(text) {
        return text.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    // ========== SOCKET.IO (REALTIME) ==========
    socket.on('new_message', function(data) {
        if (data.chat_id === currentChat) {
            addMessageToChat(data);
        } else {
            // Mettre à jour la liste des chats (nouveau message)
            updateChatList(data);
        }
    });
    
    socket.on('message_status', function(data) {
        $(`.message[data-id="${data.message_id}"] .message-status`)
            .removeClass('sent delivered read')
            .addClass(data.status);
    });
    
    // ========== EVENT HANDLERS ==========
    $(document).on('click', '.chat-item', function() {
        const chatId = $(this).data('chat-id');
        const chatType = $(this).data('chat-type');
        loadChat(chatId, chatType);
    });
    
    $(document).on('click', '.group-info-btn', function() {
        const groupId = $(this).data('group-id');
        loadGroupInfo(groupId);
    });
    
    $(document).on('click', '.block-participant', function() {
        blockParticipant($(this).data('id'));
    });
    
    $(document).on('click', '.unblock-participant', function() {
        unblockParticipant($(this).data('id'));
    });
    
    $('#searchChat').on('keyup', function() {
        const search = $(this).val().toLowerCase();
        $('.chat-item').each(function() {
            const name = $(this).find('.chat-name').text().toLowerCase();
            $(this).toggle(name.includes(search));
        });
    });
    
    $('#messageInput').on('keypress', function(e) {
        if (e.which === 13) sendMessage();
    });
    
    // ========== INITIALISATION ==========
    function init() {
        // Charger le premier chat
        const firstChat = $('.chat-item').first();
        if (firstChat.length) {
            loadChat(firstChat.data('chat-id'), firstChat.data('chat-type'));
        }
        
        // Rafraîchir les statistiques toutes les 30 secondes
        setInterval(function() {
            $.get(base_url + 'Whatsapp/get_stats', function(data) {
                $('.stat-item span').each(function(i) {
                    $(this).text(data[i]);
                });
            });
        }, 30000);
    }
    
    init();
});

function closeInfoPanel() {
    $('#infoPanel').hide();
}
    </script>
</body>
</html>