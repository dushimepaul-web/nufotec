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
        
        #daily-container {
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

<div id="daily-container"></div>

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
const roomId = <?= json_encode($room_id ?? null) ?>;
const consultationId = <?= json_encode($consultation->id ?? $consultation['id'] ?? null) ?>;
const userName = <?= json_encode(($current_role === 'patient' ? 'Patient' : 'Dr. ') . ($current_user['prenom'] ?? 'Utilisateur')) ?>;

// Éléments DOM
const leaveBtn = document.getElementById('leave-call');
const otherStatus = document.getElementById('other-status');
const waitingOverlay = document.getElementById('waiting-overlay');
const toastContainer = document.getElementById('toast-container');
const dailyContainer = document.getElementById('daily-container');

let callFrame = null;
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

async function startCall() {
    console.log('🚀 Démarrage Daily.co...');
    updateStatus('Connexion...', true);
    
    // Vérifier que roomId existe
    if (!roomId) {
        console.error('❌ Pas de roomId');
        showToast('Erreur: Identifiant de consultation manquant', 'error');
        setTimeout(() => window.location.href = '/', 3000);
        return;
    }
    
    // VOTRE DOMAINE Daily.co (vous avez créé nufotec.daily.co)
    const DAILY_DOMAIN = 'nufotec.daily.co';
    const roomUrl = `https://${DAILY_DOMAIN}/${roomId}`;
    
    console.log(`📌 Salon URL: ${roomUrl}`);
    console.log(`👤 Utilisateur: ${userName}`);
    
    try {
        // Créer l'iframe Daily.co
        callFrame = DailyIframe.createFrame(dailyContainer, {
            iframeStyle: {
                position: 'absolute',
                top: 0,
                left: 0,
                width: '100%',
                height: '100%',
                border: 'none'
            },
            showLeaveButton: false,
            showFullscreenButton: true,
            showParticipantsBar: true,
            videoSource: true,
            audioSource: true,
            userName: userName,
            lang: 'fr'
        });
        
        // Événements
        callFrame.on('joining-meeting', () => {
            console.log('🔄 Connexion au salon...');
            updateStatus('Connexion...', true);
        });
        
        callFrame.on('joined-meeting', (e) => {
            console.log('✅ Salon rejoint avec succès!', e);
            isConnected = true;
            closeWaitingOverlay();
            updateStatus('En ligne', true);
            showToast('Consultation démarrée!', 'success');
        });
        
        callFrame.on('participant-joined', (e) => {
            console.log('👤 Participant rejoint:', e.participant.userName);
            updateStatus('En ligne', true);
            showToast(`${e.participant.userName || 'Le participant'} a rejoint`, 'success');
        });
        
        callFrame.on('participant-left', (e) => {
            console.log('👤 Participant quitté');
            updateStatus('Hors ligne', false);
            showToast(`Le participant a quitté`, 'warning');
        });
        
        callFrame.on('left-meeting', () => {
            console.log('❌ Salon quitté');
            isConnected = false;
            window.location.href = '/';
        });
        
        callFrame.on('error', (e) => {
            console.error('❌ Erreur Daily:', e);
            showToast('Erreur de connexion: ' + (e.errorMsg || 'Vérifiez votre connexion internet'), 'error');
            updateStatus('Erreur', false);
        });
        
        // Rejoindre le salon
        callFrame.join({ url: roomUrl });
        
    } catch (error) {
        console.error('❌ Erreur:', error);
        showToast('Erreur de démarrage: ' + error.message, 'error');
        updateStatus('Erreur', false);
    }
}

// Bouton quitter
if (leaveBtn) {
    leaveBtn.onclick = async () => {
        if (confirm('Voulez-vous vraiment quitter la consultation ?')) {
            if (consultationId) {
                try {
                    await fetch(`/Joinconsultation/endConsultationApi/${consultationId}`, { 
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                } catch (e) {}
            }
            if (callFrame) {
                callFrame.leave();
            } else {
                window.location.href = '/';
            }
        }
    };
}

// Démarrer
startCall();

// Helper debug
window.debugDaily = () => {
    console.log('=== DEBUG DAILY ===');
    console.log('Connecté:', isConnected);
    console.log('CallFrame:', !!callFrame);
    console.log('Room:', roomId);
    console.log('User:', userName);
    console.log('Domain:', 'nufotec.daily.co');
};
</script>
</body>
</html>