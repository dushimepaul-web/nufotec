// consultation.js - Version corrigée v3 pour nufotec.com

let localStream;
let peerConnection;
let dataChannel;
let otherSocketId = null;
let isInitiator = false;
let iceServers = {
  iceServers: [
    { urls: 'stun:stun.l.google.com:19302' },
    { urls: 'stun:stun1.l.google.com:19302' }
  ]
};

// Éléments DOM
const localVideo = document.getElementById('local-video');
const remoteVideo = document.getElementById('remote-video');
const chatMessages = document.getElementById('chat-messages');
const chatInput = document.getElementById('chat-input');
const chatSend = document.getElementById('chat-send');
const fileInput = document.getElementById('file-input');
const muteAudioBtn = document.getElementById('mute-audio');
const muteVideoBtn = document.getElementById('mute-video');
const leaveBtn = document.getElementById('leave-call');
const toggleChatBtn = document.getElementById('toggle-chat');
const chatCloseBtn = document.getElementById('chat-close');
const chatArea = document.getElementById('chat-area');
const otherStatus = document.getElementById('other-status');
const waitingOverlay = document.getElementById('waiting-overlay');

// États
let audioEnabled = true;
let videoEnabled = true;
let connectionEstablished = false;
let pendingIceCandidates = []; // File d'attente pour les candidats ICE

// CORRECTION: Configuration Socket.IO forcée en polling d'abord si WebSocket échoue
const socket = io("https://consultation.nufotec.com", {
  path: '/socket.io/',
  transports: ['polling', 'websocket'], // Polling d'abord pour cPanel !
  reconnection: true,
  reconnectionAttempts: 10,
  reconnectionDelay: 2000,
  reconnectionDelayMax: 10000,
  timeout: 20000,
  forceNew: true, // Force nouvelle connexion
  autoConnect: true
});

// Debug du transport utilisé
socket.on('connect', () => {
  console.log('✅ Connecté au serveur:', socket.id);
  console.log('📡 Transport utilisé:', socket.io.engine.transport.name);
  
  // Log du changement de transport
  socket.io.engine.on('upgrade', (transport) => {
    console.log('⬆️ Transport upgradé vers:', transport.name);
  });
});

// ========== Fonctions utilitaires ==========
function showToast(message, type = 'info', duration = 4000) {
  const container = document.getElementById('toast-container');
  if (!container) return;
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle')}"></i><span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), duration);
}

function updateOtherStatus(online) {
  if (!otherStatus) return;
  otherStatus.textContent = online ? 'En ligne' : 'Hors ligne';
  otherStatus.style.color = online ? '#00a884' : '#8696a0';
}

function closeWaitingOverlay() {
  if (waitingOverlay) {
    waitingOverlay.style.display = 'none';
    console.log('✅ Overlay fermé');
  }
}

function showWaitingOverlay() {
  if (waitingOverlay) {
    waitingOverlay.style.display = 'flex';
  }
}

// ========== Gestion des médias ==========
async function initMedia() {
  try {
    localStream = await navigator.mediaDevices.getUserMedia({ 
      video: { width: { ideal: 1280 }, height: { ideal: 720 } },
      audio: true 
    });
    if (localVideo) {
      localVideo.srcObject = localStream;
      localVideo.muted = true; // Éviter l'écho sur sa propre vidéo
    }
    console.log('✅ Médias locaux initialisés');
  } catch (err) {
    console.error('❌ Erreur média:', err);
    showToast('Impossible d\'accéder à la caméra/micro.', 'error');
    throw err;
  }
}

// ========== CORRECTION 2: Gestion améliorée de la connexion WebRTC ==========
async function createPeerConnection() {
  if (peerConnection) {
    console.log('Fermeture ancienne connexion');
    peerConnection.close();
  }
  
  peerConnection = new RTCPeerConnection(iceServers);

  // Ajout des tracks locaux
  if (localStream) {
    localStream.getTracks().forEach(track => {
      peerConnection.addTrack(track, localStream);
    });
  }

  // Gestion des candidats ICE avec file d'attente
  peerConnection.onicecandidate = (event) => {
    if (event.candidate && otherSocketId) {
      console.log('📤 Envoi candidat ICE');
      socket.emit('ice-candidate', { 
        target: otherSocketId, 
        candidate: event.candidate 
      });
    }
  };

  // Surveillance de l'état de connexion
  peerConnection.onconnectionstatechange = () => {
    console.log('État connexion:', peerConnection.connectionState);
    if (peerConnection.connectionState === 'connected') {
      connectionEstablished = true;
      closeWaitingOverlay();
      updateOtherStatus(true);
    } else if (peerConnection.connectionState === 'disconnected' || 
               peerConnection.connectionState === 'failed') {
      connectionEstablished = false;
      updateOtherStatus(false);
    }
  };

  peerConnection.ontrack = (event) => {
    console.log('📹 Track reçu:', event.track.kind);
    if (remoteVideo && event.streams[0]) {
      remoteVideo.srcObject = event.streams[0];
      connectionEstablished = true;
      closeWaitingOverlay();
      updateOtherStatus(true);
    }
  };

  peerConnection.ondatachannel = (event) => {
    console.log('📡 Data channel reçu');
    dataChannel = event.channel;
    setupDataChannel();
  };

  return peerConnection;
}

function setupDataChannel() {
  if (!dataChannel) return;

  dataChannel.onopen = () => {
    console.log('✅ Data channel ouvert');
    closeWaitingOverlay();
  };
  
  dataChannel.onclose = () => {
    console.log('❌ Data channel fermé');
  };
  
  dataChannel.onerror = (err) => {
    console.error('⚠️ Erreur data channel:', err);
  };
  
  dataChannel.onmessage = (event) => {
    try {
      const data = JSON.parse(event.data);
      console.log('📨 Message reçu:', data.type);
      
      if (data.type === 'chat') {
        displayMessage(data.message, data.sender, data.timestamp);
      } else if (data.type === 'file') {
        displayMessage(data.file.name, data.sender, data.timestamp, data.file);
      }
    } catch (e) {
      console.error('Erreur parsing message:', e);
    }
  };
}

// ========== CORRECTION 3: Signalisation Socket robuste ==========

socket.on('connect', () => {
  console.log('✅ Connecté au serveur, socket ID:', socket.id);
  if (typeof roomId !== 'undefined' && roomId) {
    console.log('📤 Tentative join-room:', roomId);
    socket.emit('join-room', roomId);
  } else {
    console.error('❌ roomId non défini');
    showToast('Erreur: ID de salle manquant', 'error');
  }
});

socket.on('connect_error', (err) => {
  console.error('❌ Erreur connexion socket:', err.message);
  showToast('Problème de connexion au serveur', 'warning');
});

socket.on('disconnect', (reason) => {
  console.log('❌ Déconnecté:', reason);
  updateOtherStatus(false);
});

socket.on('reconnect', (attemptNumber) => {
  console.log('🔄 Reconnecté après', attemptNumber, 'tentatives');
});

socket.on('room-full', (msg) => {
  console.log('Salle pleine:', msg);
  showToast(msg.message || 'Salle pleine', 'error');
  setTimeout(() => window.location.href = '/', 3000);
});

socket.on('user-connected', async (socketId) => {
  console.log('👤 Autre utilisateur connecté:', socketId);
  
  // Éviter la double connexion
  if (otherSocketId === socketId) {
    console.log('Déjà connecté à cet utilisateur');
    return;
  }
  
  otherSocketId = socketId;
  updateOtherStatus(true);
  
  const otherName = (typeof otherUser !== 'undefined' && otherUser.name) 
    ? otherUser.name 
    : 'L\'autre utilisateur';
  showToast(`${otherName} a rejoint la consultation.`, 'success');

  // Seul l'initiateur crée l'offre
  if (!peerConnection && !isInitiator) {
    isInitiator = true;
    console.log('🎯 Je suis l\'initiateur, création de l\'offre...');
    
    try {
      await createPeerConnection();
      
      // Création du data channel
      dataChannel = peerConnection.createDataChannel('chat', {
        ordered: true
      });
      setupDataChannel();
      
      // Création de l'offre
      const offer = await peerConnection.createOffer();
      await peerConnection.setLocalDescription(offer);
      
      console.log('📤 Envoi offre à:', otherSocketId);
      socket.emit('offer', { target: otherSocketId, sdp: offer });
    } catch (err) {
      console.error('❌ Erreur création offre:', err);
      showToast('Erreur de connexion WebRTC', 'error');
    }
  }
});

socket.on('offer', async (data) => {
  console.log('📩 Offre reçue de:', data.sender);
  
  if (!data.sdp) {
    console.error('Offre invalide, pas de SDP');
    return;
  }
  
  otherSocketId = data.sender;
  isInitiator = false; // Je ne suis pas l'initiateur

  try {
    if (!peerConnection) {
      await createPeerConnection();
    }

    // Définir la description distante
    await peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
    
    // Traiter les candidats en attente
    while (pendingIceCandidates.length > 0) {
      const candidate = pendingIceCandidates.shift();
      await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
    }

    // Créer la réponse
    const answer = await peerConnection.createAnswer();
    await peerConnection.setLocalDescription(answer);
    
    console.log('📤 Envoi réponse à:', otherSocketId);
    socket.emit('answer', { target: otherSocketId, sdp: answer });
  } catch (err) {
    console.error('❌ Erreur traitement offre:', err);
  }
});

socket.on('answer', async (data) => {
  console.log('📩 Réponse reçue de:', data.sender);
  
  if (!peerConnection) {
    console.error('Pas de peerConnection pour traiter la réponse');
    return;
  }

  try {
    if (peerConnection.signalingState === 'have-local-offer') {
      await peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
      console.log('✅ Réponse appliquée');
      
      // Traiter les candidats en attente
      while (pendingIceCandidates.length > 0) {
        const candidate = pendingIceCandidates.shift();
        await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
      }
    } else {
      console.warn('État inattendu pour answer:', peerConnection.signalingState);
    }
  } catch (err) {
    console.error('❌ Erreur traitement réponse:', err);
  }
});

socket.on('ice-candidate', async (data) => {
  console.log('📩 Candidat ICE reçu');
  
  if (!data.candidate) {
    console.log('Candidat ICE null (fin de gathering)');
    return;
  }

  if (peerConnection && peerConnection.remoteDescription) {
    try {
      await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
      console.log('✅ Candidat ICE ajouté');
    } catch (err) {
      console.error('❌ Erreur ajout ICE:', err);
    }
  } else {
    // Mise en file d'attente si la description distante n'est pas encore définie
    console.log('⏳ Candidat ICE mis en attente');
    pendingIceCandidates.push(data.candidate);
  }
});

socket.on('user-disconnected', (socketId) => {
  console.log('👤 Utilisateur déconnecté:', socketId);
  if (socketId === otherSocketId) {
    otherSocketId = null;
    isInitiator = false;
    updateOtherStatus(false);
    
    const otherName = (typeof otherUser !== 'undefined' && otherUser.name) 
      ? otherUser.name 
      : 'L\'autre utilisateur';
    showToast(`${otherName} a quitté la consultation.`, 'warning');
    
    if (remoteVideo) remoteVideo.srcObject = null;
    connectionEstablished = false;
    showWaitingOverlay();
    
    // Nettoyer la connexion
    if (peerConnection) {
      peerConnection.close();
      peerConnection = null;
    }
    dataChannel = null;
  }
});

// ========== Gestion du chat (inchangé, mais avec vérifications) ==========
function displayMessage(text, senderId, timestamp, file = null) {
  if (!chatMessages) return;
  
  const currentUserId = (typeof currentUser !== 'undefined' && currentUser.id) 
    ? currentUser.id 
    : 'me';
  const isOwn = senderId === currentUserId;
  const sender = isOwn 
    ? (currentUser || { name: 'Moi', avatar: '' }) 
    : (otherUser || { name: 'Autre', avatar: '' });

  const messageDiv = document.createElement('div');
  messageDiv.classList.add('message', isOwn ? 'own' : 'other');

  const avatar = document.createElement('img');
  avatar.src = sender.avatar || 'default-avatar.png';
  avatar.alt = '';
  avatar.classList.add('avatar');
  avatar.onerror = () => { avatar.src = 'default-avatar.png'; };

  const bubble = document.createElement('div');
  bubble.classList.add('bubble');

  if (!isOwn) {
    const senderName = document.createElement('div');
    senderName.classList.add('sender');
    senderName.textContent = sender.name || 'Inconnu';
    bubble.appendChild(senderName);
  }

  if (file) {
    const fileDiv = document.createElement('div');
    fileDiv.classList.add('file');
    const icon = document.createElement('i');
    icon.className = file.type && file.type.startsWith('image/') 
      ? 'fas fa-image' 
      : 'fas fa-file';
    fileDiv.appendChild(icon);
    const link = document.createElement('a');
    link.href = file.url || '#';
    link.target = '_blank';
    link.textContent = file.name || 'Fichier';
    link.style.color = 'white';
    fileDiv.appendChild(link);
    bubble.appendChild(fileDiv);
  } else {
    const textDiv = document.createElement('div');
    textDiv.classList.add('text');
    textDiv.textContent = text;
    bubble.appendChild(textDiv);
  }

  const timeDiv = document.createElement('div');
  timeDiv.classList.add('time');
  timeDiv.textContent = new Date(timestamp).toLocaleTimeString([], { 
    hour: '2-digit', 
    minute: '2-digit' 
  });
  bubble.appendChild(timeDiv);

  if (isOwn) {
    messageDiv.appendChild(bubble);
    messageDiv.appendChild(avatar);
  } else {
    messageDiv.appendChild(avatar);
    messageDiv.appendChild(bubble);
  }

  chatMessages.appendChild(messageDiv);
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

function sendMessage(text, file = null) {
  if (!dataChannel || dataChannel.readyState !== 'open') {
    showToast('La connexion chat n\'est pas encore établie.', 'warning');
    return;
  }
  
  const currentUserId = (typeof currentUser !== 'undefined' && currentUser.id) 
    ? currentUser.id 
    : 'me';
  
  const message = {
    type: file ? 'file' : 'chat',
    sender: currentUserId,
    timestamp: Date.now(),
    message: text,
    file: file
  };
  
  try {
    dataChannel.send(JSON.stringify(message));
    displayMessage(text, currentUserId, Date.now(), file);
  } catch (err) {
    console.error('Erreur envoi message:', err);
    showToast('Erreur d\'envoi du message', 'error');
  }
}

// Event listeners avec vérifications
if (chatSend && chatInput) {
  chatSend.addEventListener('click', () => {
    const text = chatInput.value.trim();
    if (text) {
      sendMessage(text);
      chatInput.value = '';
    }
  });

  chatInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') chatSend.click();
  });
}

if (fileInput) {
  fileInput.addEventListener('change', (e) => {
    Array.from(e.target.files).forEach(file => {
      const fileInfo = {
        name: file.name,
        type: file.type,
        size: file.size,
        url: URL.createObjectURL(file)
      };
      sendMessage('', fileInfo);
    });
    fileInput.value = '';
  });
}

// ========== Contrôles média ==========
if (muteAudioBtn) {
  muteAudioBtn.addEventListener('click', () => {
    if (!localStream) return;
    audioEnabled = !audioEnabled;
    localStream.getAudioTracks().forEach(track => track.enabled = audioEnabled);
    muteAudioBtn.classList.toggle('active', audioEnabled);
    muteAudioBtn.innerHTML = audioEnabled 
      ? '<i class="fas fa-microphone"></i>' 
      : '<i class="fas fa-microphone-slash"></i>';
  });
}

if (muteVideoBtn) {
  muteVideoBtn.addEventListener('click', () => {
    if (!localStream) return;
    videoEnabled = !videoEnabled;
    localStream.getVideoTracks().forEach(track => track.enabled = videoEnabled);
    muteVideoBtn.classList.toggle('active', videoEnabled);
    muteVideoBtn.innerHTML = videoEnabled 
      ? '<i class="fas fa-video"></i>' 
      : '<i class="fas fa-video-slash"></i>';
  });
}

if (leaveBtn) {
  leaveBtn.addEventListener('click', () => {
    if (peerConnection) peerConnection.close();
    if (localStream) localStream.getTracks().forEach(track => track.stop());
    socket.disconnect();
    window.location.href = '/';
  });
}

// ========== Gestion du chat mobile ==========
if (toggleChatBtn && chatArea) {
  toggleChatBtn.addEventListener('click', () => chatArea.classList.add('chat-visible'));
}

if (chatCloseBtn && chatArea) {
  chatCloseBtn.addEventListener('click', () => chatArea.classList.remove('chat-visible'));
}

// ========== Initialisation ==========
(async function init() {
  try {
    await initMedia();
    console.log('🚀 Initialisation terminée, en attente d\'un participant...');
  } catch (err) {
    console.error('Échec initialisation:', err);
  }
})();