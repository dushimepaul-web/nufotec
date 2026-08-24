<!-- =====================================================================
     CHATBOT WIDGET v2 - Include before </body> in your frontend views
     ===================================================================== -->
<style>
/* ===== BULLE ===== */
#nufotec-chat-bubble {
    position: fixed; bottom: 24px; right: 24px; width: 60px; height: 60px;
    border-radius: 50%; background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: #fff; display: flex; align-items: center; justify-content: center;
    cursor: pointer; box-shadow: 0 4px 20px rgba(13,110,253,.4); z-index: 9998;
    font-size: 24px; transition: transform .3s ease, box-shadow .3s ease;
    animation: nufotec-bubble-pulse 2s ease-in-out infinite;
}
#nufotec-chat-bubble:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 28px rgba(13,110,253,.55);
    animation: none;
}
#nufotec-chat-bubble.active { animation: none; }
@keyframes nufotec-bubble-pulse {
    0%, 100% { box-shadow: 0 4px 20px rgba(13,110,253,.4); }
    50% { box-shadow: 0 4px 28px rgba(13,110,253,.65); }
}
#nufotec-chat-bubble .badge-notification {
    position: absolute; top: -2px; right: -2px; width: 20px; height: 20px;
    border-radius: 50%; background: #dc3545; color: #fff; font-size: 11px;
    display: none; align-items: center; justify-content: center;
    font-weight: 700; border: 2px solid #fff;
}
#nufotec-chat-bubble .badge-notification.show { display: flex; }

/* ===== FENETRE ===== */
#nufotec-chat-window {
    position: fixed; bottom: 96px; right: 24px; width: 370px; max-width: 92vw;
    height: 520px; max-height: 80vh; background: #fff; border-radius: 16px;
    box-shadow: 0 12px 40px rgba(0,0,0,.22); display: flex; flex-direction: column;
    overflow: hidden; z-index: 9999; font-family: 'Segoe UI', Arial, sans-serif;
    opacity: 0; transform: translateY(20px) scale(.95);
    pointer-events: none; transition: opacity .3s ease, transform .3s ease;
}
#nufotec-chat-window.open {
    opacity: 1; transform: translateY(0) scale(1); pointer-events: auto;
}

/* ===== HEADER ===== */
#nufotec-chat-header {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: #fff; padding: 14px 16px; font-weight: 600;
    display: flex; justify-content: space-between; align-items: center;
    flex-shrink: 0;
}
#nufotec-chat-header .header-info { display: flex; align-items: center; gap: 10px; }
#nufotec-chat-header .header-avatar {
    width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center; font-size: 18px;
}
#nufotec-chat-header .header-text small {
    display: block; font-weight: normal; font-size: 11px; opacity: .85; margin-top: 2px;
}
#nufotec-chat-header .header-text small .status-dot {
    display: inline-block; width: 7px; height: 7px; border-radius: 50%;
    background: #198754; margin-right: 4px; vertical-align: middle;
    animation: nufotec-blink 1.5s ease-in-out infinite;
}
@keyframes nufotec-blink {
    0%, 100% { opacity: 1; } 50% { opacity: .4; }
}
#nufotec-chat-close {
    cursor: pointer; font-size: 20px; width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%; transition: background .2s;
}
#nufotec-chat-close:hover { background: rgba(255,255,255,.2); }

/* ===== BODY ===== */
#nufotec-chat-body {
    flex: 1; overflow-y: auto; padding: 16px; background: #f8f9fc;
    font-size: 14px; scroll-behavior: smooth;
}
#nufotec-chat-body::-webkit-scrollbar { width: 5px; }
#nufotec-chat-body::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }

/* ===== MESSAGES ===== */
.nufotec-msg { margin-bottom: 12px; display: flex; gap: 8px; animation: nufotec-msg-in .25s ease; }
@keyframes nufotec-msg-in {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
.nufotec-msg .msg-avatar {
    width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; margin-top: 2px;
}
.nufotec-msg.bot .msg-avatar, .nufotec-msg.agent .msg-avatar {
    background: #e9ecef; color: #495057;
}
.nufotec-msg .msg-content { max-width: 75%; }
.nufotec-msg .bubble {
    padding: 10px 14px; border-radius: 16px; line-height: 1.45;
    word-wrap: break-word;
}
.nufotec-msg .msg-time {
    font-size: 10px; color: #999; margin-top: 3px; padding: 0 4px;
}
.nufotec-msg.user { justify-content: flex-end; }
.nufotec-msg.user .bubble {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7); color: #fff;
    border-bottom-right-radius: 4px;
}
.nufotec-msg.user .msg-time { text-align: right; }
.nufotec-msg.bot .bubble {
    background: #fff; color: #212529; border-bottom-left-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.nufotec-msg.agent .bubble {
    background: #d1e7dd; color: #0f5132; border-bottom-left-radius: 4px;
}
.nufotec-msg.system .bubble {
    background: #fff3cd; color: #664d03; font-style: italic; font-size: 12px;
    border-bottom-left-radius: 4px; text-align: center; width: 100%;
    max-width: 100%;
}

/* ===== TYPING INDICATOR ===== */
#nufotec-typing {
    display: none; padding: 0 16px 8px; font-size: 12px; color: #6c757d;
    align-items: center; gap: 6px;
}
#nufotec-typing.show { display: flex; }
#nufotec-typing .typing-dots { display: flex; gap: 3px; }
#nufotec-typing .typing-dots span {
    width: 6px; height: 6px; border-radius: 50%; background: #6c757d;
    animation: nufotec-typing-bounce 1.2s ease-in-out infinite;
}
#nufotec-typing .typing-dots span:nth-child(2) { animation-delay: .15s; }
#nufotec-typing .typing-dots span:nth-child(3) { animation-delay: .3s; }
@keyframes nufotec-typing-bounce {
    0%, 60%, 100% { transform: translateY(0); opacity: .4; }
    30% { transform: translateY(-4px); opacity: 1; }
}

/* ===== HUMAN BUTTON ===== */
#nufotec-human-btn {
    font-size: 12px; text-align: center; padding: 8px; color: #0d6efd;
    cursor: pointer; background: #fff; border-top: 1px solid #e9ecef;
    transition: background .2s, color .2s; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
#nufotec-human-btn:hover { background: #f0f4ff; }
#nufotec-human-btn.disabled {
    color: #6c757d; cursor: default; pointer-events: none;
}

/* ===== FOOTER ===== */
#nufotec-chat-footer {
    padding: 10px 12px; border-top: 1px solid #e9ecef; background: #fff;
    flex-shrink: 0;
}
#nufotec-chat-footer form { display: flex; gap: 8px; align-items: center; }
#nufotec-chat-input {
    flex: 1; border: 1px solid #dee2e6; border-radius: 22px;
    padding: 10px 16px; font-size: 14px; outline: none;
    transition: border-color .2s, box-shadow .2s;
}
#nufotec-chat-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13,110,253,.12);
}
#nufotec-chat-input:disabled { background: #f8f9fa; cursor: not-allowed; }
#nufotec-chat-footer button.send {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7); color: #fff;
    border: none; border-radius: 50%; width: 38px; height: 38px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: transform .2s, box-shadow .2s; flex-shrink: 0;
}
#nufotec-chat-footer button.send:hover {
    transform: scale(1.08);
    box-shadow: 0 2px 10px rgba(13,110,253,.35);
}
#nufotec-chat-footer button.send:disabled {
    opacity: .5; cursor: not-allowed; transform: none; box-shadow: none;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 480px) {
    #nufotec-chat-window {
        bottom: 0; right: 0; width: 100%; height: 100%;
        max-width: 100%; max-height: 100%; border-radius: 0;
    }
    #nufotec-chat-bubble { bottom: 16px; right: 16px; }
}
</style>

<!-- BULLE -->
<div id="nufotec-chat-bubble" title="Discuter avec nous">
    <i class="bi bi-chat-dots-fill"></i>
    <span class="badge-notification" id="nufotec-badge">0</span>
</div>

<!-- FENETRE -->
<div id="nufotec-chat-window">
    <div id="nufotec-chat-header">
        <div class="header-info">
            <div class="header-avatar"><i class="bi bi-robot"></i></div>
            <div class="header-text">
                <?php echo $this->Model->get_setting('chatbot_site_name', 'Assistant'); ?>
                <small id="nufotec-status">
                    <span class="status-dot"></span> En ligne
                </small>
            </div>
        </div>
        <span id="nufotec-chat-close" title="Fermer"><i class="bi bi-x-lg"></i></span>
    </div>
    <div id="nufotec-chat-body"></div>
    <div id="nufotec-typing">
        <div class="typing-dots"><span></span><span></span><span></span></div>
        L'assistant écrit...
    </div>
    <div id="nufotec-human-btn">
        <i class="bi bi-person-raised-hand"></i> Parler à un agent humain
    </div>
    <div id="nufotec-chat-footer">
        <form id="nufotec-chat-form">
            <input type="text" id="nufotec-chat-input" placeholder="Écrivez votre message..." autocomplete="off" required aria-label="Message">
            <button type="submit" class="send" aria-label="Envoyer"><i class="bi bi-send-fill"></i></button>
        </form>
    </div>
</div>

<script>
(function () {
    var BASE_URL  = "<?php echo site_url('chatbot/'); ?>";
    var GREETING  = "<?php echo addslashes($this->Model->get_setting('chatbot_greeting', 'Bonjour ! Comment puis-je vous aider ?')); ?>";
    var SITE_NAME = "<?php echo addslashes($this->Model->get_setting('chatbot_site_name', 'Assistant')); ?>";
    var conversationId = null;
    var lastMessageId = 0;
    var pollTimer = null;
    var unreadCount = 0;
    var isSending = false;

    var bubble    = document.getElementById('nufotec-chat-bubble');
    var win       = document.getElementById('nufotec-chat-window');
    var body      = document.getElementById('nufotec-chat-body');
    var form      = document.getElementById('nufotec-chat-form');
    var input     = document.getElementById('nufotec-chat-input');
    var humanBtn  = document.getElementById('nufotec-human-btn');
    var statusEl  = document.getElementById('nufotec-status');
    var badge     = document.getElementById('nufotec-badge');
    var typingEl  = document.getElementById('nufotec-typing');
    var closeBtn  = document.getElementById('nufotec-chat-close');

    /* ===== OUVERTURE / FERMETURE ===== */
    bubble.addEventListener('click', function () {
        win.classList.toggle('open');
        bubble.classList.toggle('active', win.classList.contains('open'));
        if (win.classList.contains('open')) {
            unreadCount = 0;
            updateBadge();
            if (!conversationId) startConversation();
            input.focus();
        }
    });
    closeBtn.addEventListener('click', function () {
        win.classList.remove('open');
        bubble.classList.remove('active');
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && win.classList.contains('open')) {
            win.classList.remove('open');
            bubble.classList.remove('active');
        }
    });

    /* ===== NOTIFICATION BADGE ===== */
    function updateBadge() {
        badge.textContent = unreadCount;
        badge.classList.toggle('show', unreadCount > 0 && !win.classList.contains('open'));
    }
    function notifyNewMessage() {
        if (!win.classList.contains('open')) {
            unreadCount++;
            updateBadge();
            document.title = '(' + unreadCount + ') ' + document.title.replace(/^\(\d+\)\s*/, '');
        }
    }

    /* ===== HORODATAGE ===== */
    function timeAgo(dateStr) {
        var now = new Date();
        var d   = new Date(dateStr.replace(' ', 'T') + (dateStr.indexOf('+') === -1 ? 'Z' : ''));
        var sec = Math.floor((now - d) / 1000);
        if (sec < 60)   return "à l'instant";
        if (sec < 3600)  return 'il y a ' + Math.floor(sec / 60) + ' min';
        if (sec < 86400) return 'il y a ' + Math.floor(sec / 3600) + ' h';
        return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
    }

    /* ===== AVATARS ===== */
    function getAvatar(sender) {
        if (sender === 'bot')   return '<i class="bi bi-robot"></i>';
        if (sender === 'agent') return '<i class="bi bi-person"></i>';
        return '';
    }

    /* ===== MESSAGES ===== */
    function appendMessage(sender, text, time) {
        var div = document.createElement('div');
        div.className = 'nufotec-msg ' + sender;

        var avatarHtml = '';
        if (sender === 'bot' || sender === 'agent') {
            avatarHtml = '<div class="msg-avatar">' + getAvatar(sender) + '</div>';
        }

        var timeStr = '';
        if (time) timeStr = '<div class="msg-time">' + timeAgo(time) + '</div>';

        div.innerHTML = avatarHtml +
            '<div class="msg-content">' +
                '<div class="bubble">' + escapeHtml(text) + '</div>' +
                timeStr +
            '</div>';

        body.appendChild(div);
        body.scrollTop = body.scrollHeight;
    }

    function escapeHtml(t) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(t));
        return d.innerHTML;
    }

    /* ===== TYPING INDICATOR ===== */
    function showTyping() { typingEl.classList.add('show'); body.scrollTop = body.scrollHeight; }
    function hideTyping() { typingEl.classList.remove('show'); }

    /* ===== STATUS ===== */
    function updateStatus(status) {
        var labels = {
            bot: '<span class="status-dot"></span> En ligne (IA)',
            pending_human: '<span class="status-dot" style="background:#ffc107"></span> En attente...',
            human: '<span class="status-dot" style="background:#198754"></span> Agent en ligne',
            closed: '<span class="status-dot" style="background:#6c757d"></span> Terminé'
        };
        statusEl.innerHTML = labels[status] || '<span class="status-dot"></span> En ligne';

        if (status === 'closed' || status === 'human' || status === 'pending_human') {
            humanBtn.classList.add('disabled');
        } else {
            humanBtn.classList.remove('disabled');
        }
    }

    /* ===== DEMARRER CONVERSATION ===== */
    function startConversation() {
        fetch(BASE_URL + 'start', { method: 'POST', credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) return;
                conversationId = data.conversation_id;
                (data.messages || []).forEach(function (m) {
                    appendMessage(m.sender, m.message, m.created_at);
                    lastMessageId = m.id;
                });
                if (data.messages.length === 0) {
                    appendMessage('bot', GREETING);
                }
                updateStatus(data.status);
                startPolling();
            });
    }

    /* ===== ENVOI DE MESSAGE ===== */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = input.value.trim();
        if (!text || !conversationId || isSending) return;

        isSending = true;
        appendMessage('user', text);
        input.value = '';
        input.disabled = true;

        showTyping();

        var fd = new FormData();
        fd.append('conversation_id', conversationId);
        fd.append('message', text);

        fetch(BASE_URL + 'send', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                hideTyping();
                isSending = false;
                input.disabled = false;
                input.focus();

                if (data.success && data.reply) {
                    appendMessage('bot', data.reply);
                }

                // Add WhatsApp button if link is provided
                if (data.whatsapp_link) {
                    var div = document.createElement('div');
                    div.className = 'msg msg-bot';
                    div.innerHTML = '<div class="msg-content">' +
                        '<a href="' + data.whatsapp_link + '" target="_blank" ' +
                        'style="display:inline-block;background:#25D366;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;margin-top:5px;">' +
                        '<i class="bi bi-whatsapp"></i> Envoyer via WhatsApp</a></div>';
                    body.appendChild(div);
                    body.scrollTop = body.scrollHeight;
                }

                if (data.escalation_suggested) {
                    updateStatus('pending_human');
                }
            })
            .catch(function () {
                hideTyping();
                isSending = false;
                input.disabled = false;
            });
    });

    /* ===== DEMANDER UN AGENT ===== */
    humanBtn.addEventListener('click', function () {
        if (!conversationId || humanBtn.classList.contains('disabled')) return;
        var fd = new FormData();
        fd.append('conversation_id', conversationId);
        fd.append('reason', 'Demande explicite via le bouton');

        fetch(BASE_URL + 'request_human', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                appendMessage('system', data.message || 'Transfert demandé.');
                updateStatus('pending_human');
            });
    });

    /* ===== POLLING ===== */
    function startPolling() {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(function () {
            if (!conversationId) return;
            fetch(BASE_URL + 'history/' + conversationId + '?after_id=' + lastMessageId, { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) return;
                    (data.messages || []).forEach(function (m) {
                        // Only poll for agent/system messages (bot replies come from /send response)
                        if (m.sender === 'agent' || m.sender === 'system') {
                            appendMessage(m.sender, m.message, m.created_at);
                            notifyNewMessage();
                        }
                        lastMessageId = m.id;
                    });
                    if (data.status) updateStatus(data.status);
                });
        }, 3000);
    }
})();
</script>
