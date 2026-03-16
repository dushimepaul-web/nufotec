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
        /* Vos styles existants (conservés) */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #111b21; height: 100vh; overflow: hidden; color: #e9edef; }
        #app { display: flex; flex-direction: column; height: 100vh; }
        .header { background: #202c33; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #2a3942; }
        .user-info { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; background: #2a3942; }
        .user-name { font-weight: 600; font-size: 1.1rem; }
        .user-status { font-size: 0.8rem; color: #8696a0; }
        .header-actions { display: flex; gap: 15px; }
        .header-actions button { background: none; border: none; color: #aebac1; font-size: 1.2rem; cursor: pointer; }
        .main { flex: 1; display: flex; overflow: hidden; }
        .video-area { flex: 2; background: #0b141a; display: flex; flex-direction: column; padding: 10px; }
        .video-container { position: relative; flex: 1; background: #1f2c33; border-radius: 12px; overflow: hidden; }
        .remote-video { width: 100%; height: 100%; object-fit: cover; }
        .local-video { position: absolute; bottom: 20px; right: 20px; width: 150px; height: 100px; border-radius: 8px; object-fit: cover; border: 2px solid #00a884; background: #2a3942; }
        .call-controls { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 15px; background: #202c33cc; padding: 10px 20px; border-radius: 40px; backdrop-filter: blur(10px); }
        .call-controls button { background: none; border: none; color: white; font-size: 1.2rem; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; transition: background 0.2s; }
        .call-controls button.active { background: #00a884; }
        .call-controls button.danger { background: #dc3c3c; }
        .call-controls button:hover { background: #2a3942; }
        .chat-area { width: 350px; background: #202c33; border-left: 1px solid #2a3942; display: flex; flex-direction: column; transition: transform 0.3s; }
        .chat-header { padding: 15px; background: #2a3942; display: flex; justify-content: space-between; align-items: center; }
        .chat-header span { font-weight: 600; }
        .chat-close { display: none; background: none; border: none; color: #aebac1; font-size: 1.2rem; cursor: pointer; }
        .chat-messages { flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
        .message { display: flex; gap: 8px; max-width: 80%; }
        .message.own { align-self: flex-end; flex-direction: row-reverse; }
        .message .avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; align-self: flex-end; }
        .message .bubble { background: #2a3942; padding: 8px 12px; border-radius: 16px; word-break: break-word; position: relative; }
        .message.own .bubble { background: #005c4b; color: white; border-bottom-right-radius: 4px; }
        .message.other .bubble { border-bottom-left-radius: 4px; }
        .message .sender { font-size: 0.75rem; font-weight: 600; margin-bottom: 2px; color: #00a884; }
        .message .text { font-size: 0.9rem; }
        .message .time { font-size: 0.65rem; opacity: 0.6; text-align: right; margin-top: 2px; }
        .message .file { display: flex; align-items: center; gap: 8px; background: #1f2c33; padding: 8px; border-radius: 12px; margin-top: 4px; }
        .message .file i { font-size: 1.5rem; }
        .chat-footer { padding: 10px; background: #2a3942; display: flex; gap: 10px; align-items: center; }
        .chat-footer input[type="text"] { flex: 1; padding: 10px 15px; border: none; border-radius: 24px; background: #202c33; color: white; outline: none; }
        .chat-footer button { background: none; border: none; color: #00a884; font-size: 1.5rem; cursor: pointer; }
        #file-input { display: none; }
        @media (max-width: 768px) {
            .chat-area { position: fixed; top: 0; right: 0; bottom: 0; width: 100%; z-index: 1000; transform: translateX(100%); border-left: none; }
            .chat-area.chat-visible { transform: translateX(0); }
            .chat-close { display: block; }
            .local-video { width: 100px; height: 70px; }
        }

        /* Nouveaux styles pour overlay d'attente et notifications */
        #waiting-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            transition: opacity 0.3s;
        }
        .waiting-content {
            text-align: center;
            color: white;
        }
        .spinner {
            border: 6px solid rgba(255,255,255,0.2);
            border-top: 6px solid #00a884;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2500;
        }
        .toast {
            background: #333;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s;
        }
        .toast.info { background: #2196F3; }
        .toast.success { background: #4CAF50; }
        .toast.error { background: #F44336; }
        .toast.warning { background: #FF9800; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
</head>
<body>
<?php
// Fonction utilitaire (inchangée)
function getProp($data, $prop, $default = '') {
    if (is_object($data)) return $data->$prop ?? $default;
    if (is_array($data)) return $data[$prop] ?? $default;
    return $default;
}

// Données pour l'autre participant
$other_prenom = getProp($other_user, 'prenom', '');
$other_nom = getProp($other_user, 'nom', '');
$other_photo = getProp($other_user, 'photo', '');

// Avatar autre participant
if (!empty($other_photo) && $other_photo !== 'default-avatar.png') {
    $other_avatar_url = base_url('attachments/Users/' . $other_photo);
} else {
    $initials = strtoupper(substr($other_prenom, 0, 1) . substr($other_nom, 0, 1));
    if (empty($initials)) $initials = 'U';
    $other_avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&size=128&background=random&color=fff&bold=true';
}

// Avatar utilisateur courant
$current_prenom = $current_user['prenom'] ?? '';
$current_nom = $current_user['nom'] ?? '';
$current_photo = $current_user['photo'] ?? '';
if (!empty($current_photo) && $current_photo !== 'default-avatar.png') {
    $current_avatar_url = base_url('attachments/Users/' . $current_photo);
} else {
    $initials_cur = strtoupper(substr($current_prenom, 0, 1) . substr($current_nom, 0, 1));
    if (empty($initials_cur)) $initials_cur = 'U';
    $current_avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($initials_cur) . '&size=128&background=random&color=fff&bold=true';
}

// Message d'attente selon le rôle
$waitingMessage = ($current_role === 'patient') ? 'En attente du médecin...' : 'En attente du patient...';
?>

<div id="app">
    <div class="header">
        <div class="user-info">
            <img src="<?= htmlspecialchars($other_avatar_url) ?>" class="avatar" alt="">
            <div>
                <div class="user-name">Dr. <?= htmlspecialchars($other_prenom . ' ' . $other_nom) ?></div>
                <div class="user-status" id="other-status">Hors ligne</div>
            </div>
        </div>
        <div class="header-actions">
            <button id="toggle-chat"><i class="fas fa-comment"></i></button>
            <button id="leave-call"><i class="fas fa-phone-slash"></i></button>
        </div>
    </div>

    <div class="main">
        <div class="video-area">
            <div class="video-container">
                <video id="remote-video" class="remote-video" autoplay playsinline></video>
                <video id="local-video" class="local-video" autoplay muted playsinline></video>
                <div class="call-controls">
                    <button id="mute-audio" class="active"><i class="fas fa-microphone"></i></button>
                    <button id="mute-video" class="active"><i class="fas fa-video"></i></button>
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
                <label for="file-input" style="cursor: pointer;"><i class="fas fa-paperclip"></i></label>
                <input type="file" id="file-input" multiple accept="image/*,.pdf,.doc,.docx">
                <input type="text" id="chat-input" placeholder="Écrivez un message...">
                <button id="chat-send"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Overlay d'attente -->
<div id="waiting-overlay">
    <div class="waiting-content">
        <div class="spinner"></div>
        <p id="waiting-message"><?= $waitingMessage ?></p>
    </div>
</div>

<!-- Conteneur de notifications toast -->
<div id="toast-container"></div>

<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
<script>
    // Variables globales injectées de manière sécurisée
    const roomId = <?= json_encode($room_id) ?>;
    const currentUser = <?= json_encode([
        'id' => $current_user['user_id'],
        'name' => trim(($current_user['prenom'] ?? '') . ' ' . ($current_user['nom'] ?? '')),
        'avatar' => $current_avatar_url
    ]) ?>;
    const otherUser = <?= json_encode([
        'id' => getProp($other_user, 'id', ''),
        'name' => trim($other_prenom . ' ' . $other_nom),
        'avatar' => $other_avatar_url
    ]) ?>;
    const currentRole = <?= json_encode($current_role) ?>; // 'patient' ou 'medecin'
    const SIGNALING_SERVER = <?= json_encode('http://localhost:3000') ?>;
</script>
<!-- Inclusion unique du script de consultation -->
<script src="<?= base_url('assets/js/consultation.js') ?>"></script>
</body>
</html>


/*racine_du_projet/
│
├── application/
│   ├── controllers/
│   │   └── Joinconsultation.php          # Contrôleur principal
│   │
│   ├── models/
│   │   ├── Consultation_model.php        # Gestion des consultations
│   │   └── User_model.php                 # Gestion des utilisateurs (si absent)
│   │
│   └── views/
│       ├── join_password.php              # Formulaire de mot de passe
│       └── consultation_video.php         # Interface de la consultation
│
├── assets/
│   ├── css/
│   │   └── consultation.css               # (optionnel) styles séparés
│   └── js/
│       └── consultation.js                 # Logique WebRTC et chat
│
├── uploads/
│   ├── medecin_photos/                             # Avatars des utilisateurs
│   └── consultations/                       # Fichiers échangés (PDF, images)
│
├── server/                                  # Serveur de signalisation Node.js
│   ├── index.js                             # Fichier principal du serveur
│   ├── package.json
│   ├── server.key                           # Clé SSL (à générer)
│   └── server.cert                          # Certificat SSL (à générer)
│
└── .htaccess                                 # Règles de réécriture (si nécessaire)*/