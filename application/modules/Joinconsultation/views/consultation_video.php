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
        .video-area { flex: 1; background: #0b141a; display: flex; flex-direction: column; padding: 10px; position: relative; }
        .video-container { position: relative; flex: 1; background: #1f2c33; border-radius: 12px; overflow: hidden; }
        .remote-video { width: 100%; height: 100%; object-fit: cover; background: #000; }
        .local-video { position: absolute; bottom: 20px; right: 20px; width: 150px; height: 100px; border-radius: 8px; object-fit: cover; border: 2px solid #00a884; background: #2a3942; z-index: 10; }
        .call-controls { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 15px; background: #202c33cc; padding: 10px 20px; border-radius: 40px; backdrop-filter: blur(10px); z-index: 20; }
        .call-controls button { background: none; border: none; color: white; font-size: 1.2rem; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
        .call-controls button.active { background: #00a884; }
        .call-controls button:not(.active) { background: #374045; }
        .call-controls button.danger { background: #dc3c3c !important; }
        .call-controls button:hover { transform: scale(1.1); }
        #waiting-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(11,20,26,0.95); display: flex; align-items: center; justify-content: center; z-index: 2000; }
        .waiting-content { text-align: center; color: white; padding: 40px; }
        .spinner { border: 4px solid rgba(255,255,255,0.1); border-top: 4px solid #00a884; border-radius: 50%; width: 60px; height: 60px; animation: spin 1s linear infinite; margin: 0 auto 20px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        #toast-container { position: fixed; top: 20px; right: 20px; z-index: 2500; display: flex; flex-direction: column; gap: 10px; }
        .toast { background: #333; color: white; padding: 12px 20px; border-radius: 8px; display: flex; align-items: center; gap: 12px; animation: slideIn 0.3s; min-width: 250px; }
        .toast.info { background: #2196F3; }
        .toast.success { background: #00a884; }
        .toast.error { background: #F44336; }
        .toast.warning { background: #FF9800; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .offline-indicator { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #8696a0; display: none; z-index: 5; background: rgba(0,0,0,0.7); padding: 20px; border-radius: 12px; }
        .offline-indicator i { font-size: 4rem; margin-bottom: 15px; display: block; }
        .offline-indicator.show { display: block; }
        @media (max-width: 768px) {
            .local-video { width: 100px; height: 75px; bottom: 100px; }
            .call-controls button { width: 40px; height: 40px; }
        }
        .permission-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); backdrop-filter: blur(8px); z-index: 10000; display: flex; align-items: center; justify-content: center; }
        .permission-modal { background: #1e2a2f; border-radius: 24px; max-width: 500px; width: 90%; padding: 30px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
        .permission-icon { width: 80px; height: 80px; background: linear-gradient(135deg, #00a884, #0f4c3a); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .permission-icon i { font-size: 40px; color: white; }
        .permission-modal h3 { font-size: 1.5rem; margin-bottom: 10px; color: #e9edef; }
        .permission-modal p { color: #8696a0; margin-bottom: 20px; }
        .permission-preview { background: #111b21; border-radius: 16px; padding: 15px; margin-bottom: 25px; min-height: 180px; display: flex; align-items: center; justify-content: center; }
        .preview-video { width: 100%; max-width: 300px; border-radius: 12px; background: #000; transform: scaleX(-1); }
        .preview-placeholder { text-align: center; color: #8696a0; }
        .preview-placeholder i { font-size: 48px; margin-bottom: 10px; display: block; }
        .device-selectors { display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; }
        .device-select { flex: 1; background: #2a3942; border: 1px solid #3b4a54; border-radius: 12px; padding: 10px 15px; color: white; font-size: 0.9rem; cursor: pointer; }
        .device-select:focus { outline: none; border-color: #00a884; }
        .permission-buttons { display: flex; gap: 15px; }
        .btn-permission { flex: 1; padding: 12px 20px; border: none; border-radius: 30px; font-weight: 600; cursor: pointer; font-size: 1rem; }
        .btn-permission-primary { background: #00a884; color: white; }
        .btn-permission-primary:hover { background: #008f6b; }
        .btn-permission-secondary { background: #2a3942; color: #e9edef; }
        .btn-permission-secondary:hover { background: #3b4a54; }
    </style>
</head>
<body>
<?php
function getProp($data, $prop, $default = '') {
    if (is_object($data)) return $data->$prop ?? $default;
    if (is_array($data)) return $data[$prop] ?? $default;
    return $default;
}
$other_prenom = getProp($other_user, 'prenom', '');
$other_nom = getProp($other_user, 'nom', '');
$other_photo = getProp($other_user, 'photo', '');
$current_prenom = $current_user['prenom'] ?? '';
$current_nom = $current_user['nom'] ?? '';
$current_photo = $current_user['photo'] ?? '';
$other_avatar_url = (!empty($other_photo) && $other_photo !== 'default-avatar.png') ? base_url('attachments/Users/' . $other_photo) : 'https://ui-avatars.com/api/?name=' . urlencode(strtoupper(substr($other_prenom,0,1).substr($other_nom,0,1) ?: 'U')) . '&size=128&background=random&color=fff';
$current_avatar_url = (!empty($current_photo) && $current_photo !== 'default-avatar.png') ? base_url('attachments/Users/' . $current_photo) : 'https://ui-avatars.com/api/?name=' . urlencode(strtoupper(substr($current_prenom,0,1).substr($current_nom,0,1) ?: 'U')) . '&size=128&background=random&color=fff';
$waitingMessage = ($current_role === 'patient') ? 'En attente du médecin...' : 'En attente du patient...';
$otherRoleLabel = ($current_role === 'patient') ? 'Dr. ' : '';
?>
<div id="app">
    <div class="header">
        <div class="user-info">
            <img src="<?= htmlspecialchars($other_avatar_url) ?>" class="avatar" alt="Avatar">
            <div>
                <div class="user-name"><?= htmlspecialchars($otherRoleLabel . $other_prenom . ' ' . $other_nom) ?></div>
                <div class="user-status" id="other-status">Hors ligne</div>
            </div>
        </div>
        <div class="header-actions">
            <button id="leave-call" class="danger"><i class="fas fa-phone-slash"></i></button>
        </div>
    </div>
    <div class="main">
        <div class="video-area">
            <div class="video-container">
                <video id="remote-video" class="remote-video" autoplay playsinline></video>
                <div class="offline-indicator" id="offline-indicator"><i class="fas fa-user-slash"></i><p>Participant déconnecté</p></div>
                <video id="local-video" class="local-video" autoplay muted playsinline></video>
                <div class="call-controls">
                    <button id="mute-audio" class="active"><i class="fas fa-microphone"></i></button>
                    <button id="mute-video" class="active"><i class="fas fa-video"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="waiting-overlay"><div class="waiting-content"><div class="spinner"></div><h3><?= htmlspecialchars($waitingMessage) ?></h3><p>Veuillez patienter, la consultation va démarrer...</p></div></div>
<div id="permissionModal" class="permission-modal-overlay" style="display: none;">
    <div class="permission-modal">
        <div class="permission-icon"><i class="fas fa-video"></i></div>
        <h3>Autorisation caméra et micro</h3>
        <p>Pour commencer la consultation, nous avons besoin d'accéder à votre caméra et microphone.</p>
        <div class="permission-preview" id="previewContainer">
            <div class="preview-placeholder"><i class="fas fa-camera"></i><p>Aperçu vidéo</p></div>
            <video id="previewVideo" class="preview-video" autoplay muted playsinline style="display: none;"></video>
        </div>
        <div class="device-selectors">
            <select id="cameraSelect" class="device-select"><option value="">Sélectionner une caméra...</option></select>
            <select id="microSelect" class="device-select"><option value="">Sélectionner un micro...</option></select>
        </div>
        <div class="permission-buttons">
            <button class="btn-permission btn-permission-secondary" id="cancelPermissionBtn"><i class="fas fa-times"></i> Annuler</button>
            <button class="btn-permission btn-permission-primary" id="grantPermissionBtn"><i class="fas fa-check"></i> Autoriser et continuer</button>
        </div>
    </div>
</div>
<div id="toast-container"></div>
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
window.roomId = <?= json_encode($room_id ?? null) ?>;
window.currentUser = <?= json_encode($current_user ?? ['id' => 'me', 'name' => 'Moi', 'avatar' => $current_avatar_url]) ?>;
window.otherUser = <?= json_encode($other_user ?? ['id' => 'other', 'name' => 'Autre', 'avatar' => $other_avatar_url]) ?>;
window.currentRole = <?= json_encode($current_role ?? 'participant') ?>;
window.consultationId = <?= json_encode($consultation->id ?? $consultation['id'] ?? null) ?>;
</script>
<script src="<?= base_url('assets/js/consultation.js?v=' . time()) ?>"></script>
</body>
</html>