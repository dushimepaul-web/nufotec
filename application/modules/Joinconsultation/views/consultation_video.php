<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Consultation avec <?= htmlspecialchars(
        (is_object($other_user) ? ($other_user->prenom ?? '') : ($other_user['prenom'] ?? '')) . ' ' .
        (is_object($other_user) ? ($other_user->nom ?? '') : ($other_user['nom'] ?? ''))
    ) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        /* Styles conservés... */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #111b21; height: 100vh; overflow: hidden; color: #e9edef; }
        #app { display: flex; flex-direction: column; height: 100vh; }
        .header { background: #202c33; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #2a3942; }
        .user-info { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; background: #2a3942; }
        .user-name { font-weight: 600; font-size: 1.1rem; }
        .user-status { font-size: 0.8rem; color: #8696a0; }
        .header-actions { display: flex; gap: 15px; }
        .header-actions button { background: none; border: none; color: #aebac1; font-size: 1.2rem; cursor: pointer; padding: 8px; border-radius: 50%; transition: background 0.2s; }
        .header-actions button:hover { background: #2a3942; color: #fff; }
        .main { flex: 1; display: flex; overflow: hidden; }
        .video-area { flex: 2; background: #0b141a; display: flex; flex-direction: column; padding: 10px; position: relative; }
        .video-container { position: relative; flex: 1; background: #1f2c33; border-radius: 12px; overflow: hidden; }
        .remote-video { width: 100%; height: 100%; object-fit: cover; background: #000; }
        .local-video { position: absolute; bottom: 20px; right: 20px; width: 150px; height: 100px; border-radius: 8px; object-fit: cover; border: 2px solid #00a884; background: #2a3942; z-index: 10; }
        .call-controls { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 15px; background: #202c33cc; padding: 10px 20px; border-radius: 40px; backdrop-filter: blur(10px); z-index: 20; }
        .call-controls button { background: none; border: none; color: white; font-size: 1.2rem; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
        .call-controls button.active { background: #00a884; }
        .call-controls button:not(.active) { background: #374045; }
        .call-controls button.danger { background: #dc3c3c !important; }
        .call-controls button:hover { transform: scale(1.1); }
        .chat-area { width: 350px; background: #202c33; border-left: 1px solid #2a3942; display: flex; flex-direction: column; transition: transform 0.3s; }
        .chat-header { padding: 15px; background: #2a3942; display: flex; justify-content: space-between; align-items: center; }
        .chat-header span { font-weight: 600; }
        .chat-close { display: none; background: none; border: none; color: #aebac1; font-size: 1.2rem; cursor: pointer; }
        .chat-messages { flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
        .message { display: flex; gap: 8px; max-width: 80%; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .message.own { align-self: flex-end; flex-direction: row-reverse; }
        .message .avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; align-self: flex-end; }
        .message .bubble { background: #2a3942; padding: 8px 12px; border-radius: 16px; word-break: break-word; position: relative; max-width: 100%; }
        .message.own .bubble { background: #005c4b; color: white; border-bottom-right-radius: 4px; }
        .message.other .bubble { border-bottom-left-radius: 4px; }
        .message .sender { font-size: 0.75rem; font-weight: 600; margin-bottom: 2px; color: #00a884; }
        .message .text { font-size: 0.9rem; line-height: 1.4; }
        .message .time { font-size: 0.65rem; opacity: 0.7; text-align: right; margin-top: 4px; }
        .message .file { display: flex; align-items: center; gap: 8px; background: rgba(0,0,0,0.2); padding: 8px; border-radius: 8px; margin-top: 4px; }
        .message .file i { font-size: 1.5rem; color: #aebac1; }
        .message .file a { color: white; text-decoration: none; font-size: 0.9rem; word-break: break-all; }
        .message .file a:hover { text-decoration: underline; }
        .chat-footer { padding: 10px; background: #2a3942; display: flex; gap: 10px; align-items: center; }
        .chat-footer input[type="text"] { flex: 1; padding: 10px 15px; border: none; border-radius: 24px; background: #202c33; color: white; outline: none; font-size: 0.95rem; }
        .chat-footer input[type="text"]::placeholder { color: #8696a0; }
        .chat-footer button { background: none; border: none; color: #00a884; font-size: 1.5rem; cursor: pointer; padding: 8px; border-radius: 50%; transition: background 0.2s; }
        .chat-footer button:hover { background: #202c33; }
        .chat-footer label { color: #aebac1; font-size: 1.3rem; cursor: pointer; padding: 8px; border-radius: 50%; transition: background 0.2s; }
        .chat-footer label:hover { background: #202c33; color: #fff; }
        #file-input { display: none; }
        
        /* Overlay d'attente */
        #waiting-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(11, 20, 26, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            transition: opacity 0.3s;
        }
        .waiting-content {
            text-align: center;
            color: white;
            padding: 40px;
        }
        .spinner {
            border: 4px solid rgba(255,255,255,0.1);
            border-top: 4px solid #00a884;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .waiting-content h3 { font-size: 1.5rem; margin-bottom: 10px; font-weight: 500; }
        .waiting-content p { color: #8696a0; font-size: 1rem; }
        
        /* Toast notifications */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2500;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast {
            background: #333;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s;
            min-width: 250px;
        }
        .toast.info { background: #2196F3; }
        .toast.success { background: #00a884; }
        .toast.error { background: #F44336; }
        .toast.warning { background: #FF9800; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        
        /* Responsive */
        @media (max-width: 768px) {
            .chat-area { position: fixed; top: 0; right: 0; bottom: 0; width: 100%; z-index: 1000; transform: translateX(100%); border-left: none; }
            .chat-area.chat-visible { transform: translateX(0); }
            .chat-close { display: block; }
            .local-video { width: 100px; height: 75px; bottom: 100px; }
            .call-controls { bottom: 20px; padding: 8px 15px; gap: 10px; }
            .call-controls button { width: 40px; height: 40px; font-size: 1rem; }
            .header-actions button { font-size: 1rem; }
            .user-name { font-size: 1rem; }
        }
        
        /* État hors ligne */
        .offline-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #8696a0;
            display: none;
        }
        .offline-indicator i { font-size: 4rem; margin-bottom: 15px; display: block; }
        .offline-indicator.show { display: block; }
    </style>
</head>
<body>
<?php
// Fonction utilitaire sécurisée
function getProp($data, $prop, $default = '') {
    if (is_object($data)) return $data->$prop ?? $default;
    if (is_array($data)) return $data[$prop] ?? $default;
    return $default;
}

// Données pour l'autre participant avec fallback sécurisé
$other_prenom = getProp($other_user, 'prenom', '');
$other_nom = getProp($other_user, 'nom', '');
$other_photo = getProp($other_user, 'photo', '');
$other_id = getProp($other_user, 'id', '');

// Avatar autre participant
if (!empty($other_photo) && $other_photo !== 'default-avatar.png' && $other_photo !== 'default.png') {
    $other_avatar_url = base_url('attachments/Users/' . $other_photo);
} else {
    $initials = strtoupper(substr($other_prenom, 0, 1) . substr($other_nom, 0, 1));
    if (empty($initials)) $initials = 'U';
    $other_avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&size=128&background=random&color=fff&bold=true';
}

// Avatar utilisateur courant avec vérification
$current_prenom = $current_user['prenom'] ?? '';
$current_nom = $current_user['nom'] ?? '';
$current_photo = $current_user['photo'] ?? '';
$current_id = $current_user['user_id'] ?? $current_user['id'] ?? '';

if (!empty($current_photo) && $current_photo !== 'default-avatar.png' && $current_photo !== 'default.png') {
    $current_avatar_url = base_url('attachments/Users/' . $current_photo);
} else {
    $initials_cur = strtoupper(substr($current_prenom, 0, 1) . substr($current_nom, 0, 1));
    if (empty($initials_cur)) $initials_cur = 'U';
    $current_avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($initials_cur) . '&size=128&background=random&color=fff&bold=true';
}

// Message d'attente selon le rôle
$waitingMessage = ($current_role === 'patient') ? 'En attente du médecin...' : 'En attente du patient...';
$otherRoleLabel = ($current_role === 'patient') ? 'Dr. ' : '';
?>

<div id="app">
    <div class="header">
        <div class="user-info">
            <img src="<?= htmlspecialchars($other_avatar_url) ?>" class="avatar" alt="Avatar" onerror="this.src='https://ui-avatars.com/api/?name=U&size=128&background=random&color=fff'">
            <div>
                <div class="user-name"><?= htmlspecialchars($otherRoleLabel . $other_prenom . ' ' . $other_nom) ?></div>
                <div class="user-status" id="other-status">Hors ligne</div>
            </div>
        </div>
        <div class="header-actions">
            <button id="toggle-chat" title="Ouvrir le chat"><i class="fas fa-comment"></i></button>
            <button id="leave-call" class="danger" title="Quitter l'appel"><i class="fas fa-phone-slash"></i></button>
        </div>
    </div>

    <div class="main">
        <div class="video-area">
            <div class="video-container">
                <video id="remote-video" class="remote-video" autoplay playsinline poster="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Crect width='100' height='100' fill='%231f2c33'/%3E%3C/svg%3E"></video>
                <div class="offline-indicator" id="offline-indicator">
                    <i class="fas fa-user-slash"></i>
                    <p>Participant déconnecté</p>
                </div>
                <video id="local-video" class="local-video" autoplay muted playsinline></video>
                <div class="call-controls">
                    <button id="mute-audio" class="active" title="Activer/Désactiver micro"><i class="fas fa-microphone"></i></button>
                    <button id="mute-video" class="active" title="Activer/Désactiver caméra"><i class="fas fa-video"></i></button>
                </div>
            </div>
        </div>

        <div class="chat-area" id="chat-area">
            <div class="chat-header">
                <span><i class="fas fa-comment"></i> Chat</span>
                <button id="chat-close" class="chat-close"><i class="fas fa-times"></i></button>
            </div>
            <div id="chat-messages" class="chat-messages"></div>
            <div class="chat-footer">
                <label for="file-input" title="Joindre un fichier"><i class="fas fa-paperclip"></i></label>
                <input type="file" id="file-input" multiple accept="image/*,.pdf,.doc,.docx,.txt">
                <input type="text" id="chat-input" placeholder="Écrivez un message..." maxlength="500">
                <button id="chat-send" title="Envoyer"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Overlay d'attente -->
<div id="waiting-overlay">
    <div class="waiting-content">
        <div class="spinner"></div>
        <h3><?= htmlspecialchars($waitingMessage) ?></h3>
        <p>Veuillez patienter, la consultation va démarrer...</p>
    </div>
</div>

<!-- Conteneur de notifications toast -->
<div id="toast-container"></div>

<!-- ============================================ -->
<!-- ⭐ VERSION SIMPLIFIÉE -->
<!-- ============================================ -->

<!-- Socket.IO CDN (sans integrity) -->
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>

<!-- Variables globales -->
<script>
// Passage des données PHP à JavaScript
window.roomId = <?= json_encode($room_id ?? null) ?>;
window.currentUser = <?= json_encode($current_user ?? ['id' => 'me', 'name' => 'Moi']) ?>;
window.otherUser = <?= json_encode($other_user ?? ['id' => 'other', 'name' => 'Autre']) ?>;
window.currentRole = <?= json_encode($current_role ?? 'participant') ?>;

// Configuration
window.SOCKET_CONFIG = {
    url: window.location.origin,
    path: '/socket/socket.io'
};

// Debug
console.log('✅ Variables chargées:', {
    roomId: window.roomId,
    currentUser: window.currentUser,
    otherUser: window.otherUser
});

// Test serveur
fetch('/socket/health')
    .then(r => r.json())
    .then(d => console.log('✅ Serveur OK:', d))
    .catch(e => console.warn('⚠️ Serveur indisponible'));
</script>

<!-- Consultation JS -->
<script src="<?= base_url('assets/js/consultation.js?v=' . time()) ?>"></script>

</body>
</html>