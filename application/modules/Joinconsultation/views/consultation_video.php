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
        
        /* Masquer les vidéos natives car Daily.co utilise son iframe */
        #local-video, #remote-video {
            display: none !important;
        }
        
        /* Container pour l'iframe Daily.co */
        .daily-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 100;
            background: #111b21;
        }
        
        .daily-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .header {
            background: #202c33;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #2a3942;
            position: relative;
            z-index: 200;
        }
        
        .user-info { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; background: #2a3942; }
        .user-name { font-weight: 600; font-size: 1.1rem; }
        .user-status { font-size: 0.8rem; color: #8696a0; }
        .header-actions { display: flex; gap: 15px; }
        .header-actions button { background: none; border: none; color: #aebac1; font-size: 1.2rem; cursor: pointer; padding: 8px; border-radius: 50%; transition: background 0.2s; }
        .header-actions button:hover { background: #2a3942; color: #fff; }
        .header-actions button.danger:hover { background: #dc3c3c; color: white; }
        
        #waiting-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(11,20,26,0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .waiting-content { text-align: center; color: white; padding: 40px; }
        .spinner { border: 4px solid rgba(255,255,255,0.1); border-top: 4px solid #00a884; border-radius: 50%; width: 60px; height: 60px; animation: spin 1s linear infinite; margin: 0 auto 20px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        #toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .toast {
            background: #333;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
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
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .user-name { font-size: 0.9rem; }
            .avatar { width: 32px; height: 32px; }
        }
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

$other_avatar_url = (!empty($other_photo) && $other_photo !== 'default-avatar.png') 
    ? base_url('attachments/Users/' . $other_photo) 
    : 'https://ui-avatars.com/api/?name=' . urlencode(strtoupper(substr($other_prenom,0,1).substr($other_nom,0,1) ?: 'U')) . '&size=128&background=random&color=fff';

$waitingMessage = ($current_role === 'patient') ? 'En attente du médecin...' : 'En attente du patient...';
$otherRoleLabel = ($current_role === 'patient') ? 'Dr. ' : '';
?>

<div id="app">
    <div class="header">
        <div class="user-info">
            <img src="<?= htmlspecialchars($other_avatar_url) ?>" class="avatar" alt="Avatar">
            <div>
                <div class="user-name"><?= htmlspecialchars($otherRoleLabel . $other_prenom . ' ' . $other_nom) ?></div>
                <div class="user-status" id="other-status">Connexion...</div>
            </div>
        </div>
        <div class="header-actions">
            <button id="leave-call" class="danger"><i class="fas fa-phone-slash"></i></button>
        </div>
    </div>
    
    <!-- Container pour Daily.co -->
    <div id="daily-container" class="daily-container"></div>
</div>

<div id="waiting-overlay">
    <div class="waiting-content">
        <div class="spinner"></div>
        <h3><?= htmlspecialchars($waitingMessage) ?></h3>
        <p>Préparation de la consultation...</p>
    </div>
</div>

<div id="toast-container"></div>

<!-- Daily.co SDK -->
<script src="https://unpkg.com/@daily-co/daily-js@0.45.0"></script>

<script>
// Configuration
const CONFIG = {
    roomId: <?= json_encode($room_id ?? null) ?>,
    consultationId: <?= json_encode($consultation->id ?? $consultation['id'] ?? null) ?>,
    currentUser: <?= json_encode($current_user ?? ['name' => 'Utilisateur']) ?>,
    otherUser: <?= json_encode($other_user ?? ['name' => 'Participant']) ?>,
    currentRole: <?= json_encode($current_role ?? 'participant') ?>
};

// Éléments DOM
const elements = {
    leaveBtn: document.getElementById('leave-call'),
    otherStatus: document.getElementById('other-status'),
    waitingOverlay: document.getElementById('waiting-overlay'),
    toastContainer: document.getElementById('toast-container'),
    dailyContainer: document.getElementById('daily-container')
};

let callFrame = null;
let isConnected = false;

// Utilitaires
const utils = {
    log: function(msg) {
        console.log(`[${new Date().toLocaleTimeString()}] ${msg}`);
    },
    
    showToast: function(message, type = 'info') {
        if (!elements.toastContainer) return;
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `<span>${message}</span>`;
        elements.toastContainer.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    },
    
    updateOtherStatus: function(status, isOnline = true) {
        if (elements.otherStatus) {
            elements.otherStatus.textContent = status;
            elements.otherStatus.style.color = isOnline ? '#00a884' : '#8696a0';
        }
    },
    
    closeWaitingOverlay: function() {
        if (elements.waitingOverlay) {
            elements.waitingOverlay.style.display = 'none';
        }
    }
};

// Fonction principale pour démarrer Daily.co
async function startDailyCall() {
    utils.log('🚀 Démarrage Daily.co...');
    utils.updateOtherStatus('Connexion...', true);
    
    // Créer un identifiant de salon unique
    const roomName = CONFIG.roomId || `consultation-${Date.now()}`;
    const userName = CONFIG.currentUser?.name || (CONFIG.currentRole === 'patient' ? 'Patient' : 'Médecin');
    
    // URL du salon Daily.co (utilise leur infrastructure mondiale)
    // Tu dois créer un compte gratuit sur daily.co et remplacer par ton domaine
    const roomUrl = `https://nufotec.daily.co/${roomName}`;
    
    utils.log(`📌 Salon: ${roomName}`);
    utils.log(`👤 Utilisateur: ${userName}`);
    
    try {
        // Créer l'iframe Daily.co
        callFrame = DailyIframe.createFrame(elements.dailyContainer, {
            iframeStyle: {
                position: 'absolute',
                top: 0,
                left: 0,
                width: '100%',
                height: '100%',
                border: 'none'
            },
            showLeaveButton: false, // On utilise notre propre bouton
            showFullscreenButton: true,
            showParticipantsBar: true,
            videoSource: true,
            audioSource: true,
            userName: userName,
            lang: 'fr'
        });
        
        // Événements Daily.co
        callFrame.on('joining-meeting', () => {
            utils.log('🔄 Rejoint le salon...');
            utils.updateOtherStatus('Connexion...', true);
        });
        
        callFrame.on('joined-meeting', () => {
            utils.log('✅ Salon rejoint avec succès!');
            isConnected = true;
            utils.closeWaitingOverlay();
            utils.updateOtherStatus('En ligne', true);
            utils.showToast('Consultation démarrée!', 'success');
        });
        
        callFrame.on('participant-joined', (event) => {
            utils.log(`👤 Participant rejoint: ${event.participant.userName || 'Inconnu'}`);
            utils.updateOtherStatus('En ligne', true);
            utils.showToast(`${event.participant.userName || 'Le participant'} a rejoint`, 'success');
        });
        
        callFrame.on('participant-left', (event) => {
            utils.log(`👤 Participant quitté: ${event.participant.userName || 'Inconnu'}`);
            utils.updateOtherStatus('Hors ligne', false);
            utils.showToast(`${event.participant.userName || 'Le participant'} a quitté`, 'warning');
        });
        
        callFrame.on('left-meeting', () => {
            utils.log('❌ Salon quitté');
            isConnected = false;
            // Rediriger vers l'accueil
            window.location.href = '/';
        });
        
        callFrame.on('error', (error) => {
            utils.log(`❌ Erreur Daily.co: ${error.errorMsg || 'Inconnue'}`);
            utils.showToast('Erreur de connexion, réessayez...', 'error');
        });
        
        // Rejoindre le salon
        callFrame.join({ url: roomUrl });
        
    } catch (error) {
        utils.log(`❌ Erreur: ${error.message}`);
        utils.showToast('Erreur de démarrage de la consultation', 'error');
        utils.updateOtherStatus('Erreur', false);
    }
}

// Gestion du bouton quitter
if (elements.leaveBtn) {
    elements.leaveBtn.onclick = async () => {
        if (confirm('Voulez-vous vraiment quitter la consultation ?')) {
            utils.log('👋 Fin de consultation...');
            
            // Notifier le serveur de la fin
            if (CONFIG.consultationId) {
                try {
                    await fetch(`/Joinconsultation/endConsultationApi/${CONFIG.consultationId}`, { 
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                } catch (e) {}
            }
            
            // Quitter l'appel Daily
            if (callFrame) {
                callFrame.leave();
            } else {
                window.location.href = '/';
            }
        }
    };
}

// Démarrer l'application
startDailyCall();

// Helper pour debug
window.debugDaily = () => {
    console.log('=== DEBUG DAILY ===');
    console.log('Connecté:', isConnected);
    console.log('CallFrame:', !!callFrame);
    console.log('Room:', CONFIG.roomId);
    console.log('User:', CONFIG.currentUser?.name);
};
</script>
</body>
</html>