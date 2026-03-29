<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Consultation avec <?= htmlspecialchars($other_prenom . ' ' . $other_nom) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #111b21; height: 100vh; overflow: hidden; }
        
        .header {
            background: #202c33;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 200;
        }
        
        .user-info { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .user-name { font-weight: 600; color: #e9edef; }
        .user-status { font-size: 0.8rem; color: #8696a0; }
        
        .header-actions button {
            background: none;
            border: none;
            color: #aebac1;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: background 0.2s;
        }
        
        .header-actions button.danger:hover { background: #dc3c3c; color: white; }
        
        #jitsi-container {
            position: fixed;
            top: 60px;
            left: 0;
            width: 100%;
            height: calc(100% - 60px);
            z-index: 100;
            background: #111b21;
        }
        
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
        
        .waiting-content { text-align: center; color: white; }
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
            animation: slideIn 0.3s;
        }
        
        .toast.success { background: #00a884; }
        .toast.error { background: #F44336; }
        .toast.warning { background: #FF9800; }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>

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

<div id="jitsi-container"></div>

<div id="waiting-overlay">
    <div class="waiting-content">
        <div class="spinner"></div>
        <h3><?= htmlspecialchars($waitingMessage) ?></h3>
        <p>Préparation de la consultation...</p>
    </div>
</div>

<div id="toast-container"></div>

<!-- Jitsi Meet SDK -->
<script src="https://meet.jit.si/external_api.js"></script>

<script>
// Configuration
const jitsiRoomName = <?= json_encode($jitsi_room_name) ?>;
const consultationId = <?= json_encode(is_object($consultation) ? $consultation->id : $consultation['id']) ?>;
const userName = <?= json_encode(($current_role === 'patient' ? 'Patient' : 'Dr. ') . ($current_user['prenom'] ?? 'Utilisateur')) ?>;

// Éléments DOM
const leaveBtn = document.getElementById('leave-call');
const otherStatus = document.getElementById('other-status');
const waitingOverlay = document.getElementById('waiting-overlay');
const toastContainer = document.getElementById('toast-container');
const jitsiContainer = document.getElementById('jitsi-container');

let jitsiApi = null;
let isConnected = false;

function showToast(message, type = 'info') {
    if (!toastContainer) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span>${message}</span>`;
    toastContainer.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function updateStatus(status, isOnline = true) {
    if (otherStatus) {
        otherStatus.textContent = status;
        otherStatus.style.color = isOnline ? '#00a884' : '#8696a0';
    }
}

function closeWaitingOverlay() {
    if (waitingOverlay) waitingOverlay.style.display = 'none';
}

function startJitsiCall() {
    console.log('🚀 Démarrage Jitsi Meet...');
    console.log('📌 Salon:', jitsiRoomName);
    console.log('👤 Utilisateur:', userName);
    
    updateStatus('Connexion...', true);
    
    if (!jitsiRoomName) {
        console.error('❌ Pas de nom de salon');
        showToast('Erreur: Impossible de créer la salle', 'error');
        setTimeout(() => window.location.href = '/', 3000);
        return;
    }
    
    try {
        const domain = 'meet.jit.si';
        const options = {
            roomName: jitsiRoomName,
            width: '100%',
            height: '100%',
            parentNode: jitsiContainer,
            userInfo: {
                displayName: userName
            },
            configOverwrite: {
                startWithAudioMuted: false,
                startWithVideoMuted: false,
                disableDeepLinking: true,
                disableInviteFunctions: true,
                enableWelcomePage: false,
                enableClosePage: false,
                disableProfile: true,
                defaultLanguage: 'fr',
                lang: 'fr'
            },
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                DEFAULT_BACKGROUND: '#111b21',
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 
                    'fullscreen', 'fodeviceselection', 'hangup', 
                    'profile', 'chat', 'recording', 'settings', 
                    'shareaudio', 'sharedvideo', 'tileview'
                ]
            }
        };
        
        jitsiApi = new JitsiMeetExternalAPI(domain, options);
        
        jitsiApi.addListener('videoConferenceJoined', () => {
            console.log('✅ Consultation rejointe!');
            isConnected = true;
            closeWaitingOverlay();
            updateStatus('En ligne', true);
            showToast('Consultation démarrée!', 'success');
        });
        
        jitsiApi.addListener('participantJoined', (participant) => {
            console.log('👤 Participant rejoint:', participant.displayName);
            updateStatus('En ligne', true);
            showToast(`${participant.displayName || 'Le participant'} a rejoint`, 'success');
        });
        
        jitsiApi.addListener('participantLeft', (participant) => {
            console.log('👤 Participant quitté:', participant.displayName);
            updateStatus('Hors ligne', false);
            showToast('Le participant a quitté', 'warning');
        });
        
        jitsiApi.addListener('readyToClose', () => {
            console.log('❌ Consultation terminée');
            isConnected = false;
            window.location.href = '/';
        });
        
    } catch (error) {
        console.error('❌ Erreur Jitsi:', error);
        showToast('Erreur de démarrage: ' + error.message, 'error');
        updateStatus('Erreur', false);
    }
}

// Bouton quitter
if (leaveBtn) {
    leaveBtn.onclick = async () => {
        if (confirm('Quitter la consultation ?')) {
            if (consultationId) {
                try {
                    await fetch(`/Joinconsultation/endConsultationApi/${consultationId}`, { 
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                } catch (e) {}
            }
            if (jitsiApi) {
                jitsiApi.executeCommand('hangup');
            }
            window.location.href = '/';
        }
    };
}

// Démarrer
startJitsiCall();

window.debugJitsi = () => {
    console.log('=== DEBUG JITSI ===');
    console.log('Salon:', jitsiRoomName);
    console.log('Connecté:', isConnected);
    console.log('Jitsi API:', !!jitsiApi);
    console.log('User:', userName);
};
</script>
</body>
</html>