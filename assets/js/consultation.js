// ============================================
// ⭐ NUFOTEC CONSULTATION - VERSION ULTIME
// ============================================

let localStream;
let peerConnection;
let dataChannel;
let otherSocketId = null;
let isInitiator = false;
let iceServersConfig = null;
let pendingIceCandidates = [];

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
let usingRelay = false;

// ============================================
// ⭐ CONFIGURATION SOCKET.IO CORRIGÉE
// ============================================
const SOCKET_URL = 'https://nufotec.com';  // Domaine principal
const SOCKET_PATH = '/socket/socket.io';    // Chemin corrigé

const socket = io(SOCKET_URL, {
  path: SOCKET_PATH,                          // ⚠️ CRITIQUE - correspond au serveur
  transports: ['websocket', 'polling'],       // websocket en PRIORITÉ
  withCredentials: true,                       // IMPORTANT pour les cookies
  reconnection: true,
  reconnectionAttempts: 10,
  reconnectionDelay: 1000,
  reconnectionDelayMax: 5000,
  timeout: 20000,
  autoConnect: true
});

// ============================================
// ⭐ FONCTIONS UTILITAIRES
// ============================================
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

// ============================================
// ⭐ CHARGEMENT CONFIGURATION ICE - CORRIGÉ
// ============================================
async function loadIceServers() {
  try {
    console.log('🌐 Chargement configuration ICE...');
    // URL corrigée avec /socket/
    const response = await fetch('/socket/api/ice-servers', {
      headers: {
        'Accept': 'application/json'
      }
    });
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }
    
    const config = await response.json();
    iceServersConfig = config;
    console.log('✅ Configuration ICE chargée:', iceServersConfig);
    return iceServersConfig;
  } catch (err) {
    console.error('❌ Erreur chargement ICE, fallback STUN:', err);
    // Fallback minimal
    iceServersConfig = {
      iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
        { urls: 'stun:stun2.l.google.com:19302' },
        { urls: 'stun:stun3.l.google.com:19302' }
      ]
    };
    return iceServersConfig;
  }
}

// ============================================
// ⭐ GESTION DES MÉDIAS
// ============================================
async function initMedia() {
  try {
    localStream = await navigator.mediaDevices.getUserMedia({ 
      video: { 
        width: { ideal: 1280 }, 
        height: { ideal: 720 } 
      },
      audio: {
        echoCancellation: true,
        noiseSuppression: true,
        autoGainControl: true
      }
    });
    
    if (localVideo) {
      localVideo.srcObject = localStream;
      localVideo.muted = true;
    }
    console.log('✅ Médias locaux initialisés');
  } catch (err) {
    console.error('❌ Erreur média:', err);
    showToast('Impossible d\'accéder à la caméra/micro.', 'error');
    throw err;
  }
}

// ============================================
// ⭐ CONNEXION WEBRTC OPTIMISÉE
// ============================================
async function createPeerConnection() {
  if (peerConnection) {
    peerConnection.close();
  }
  
  // Charger configuration ICE si nécessaire
  if (!iceServersConfig) {
    await loadIceServers();
  }
  
  const connectionStart = Date.now();
  
  // Configuration avec STUN/TURN
  const config = {
    ...iceServersConfig,
    iceTransportPolicy: 'all',
    iceCandidatePoolSize: 10
  };
  
  peerConnection = new RTCPeerConnection(config);

  // Ajout des tracks locaux
  if (localStream) {
    localStream.getTracks().forEach(track => {
      peerConnection.addTrack(track, localStream);
    });
  }

  // Gestion des candidats ICE
  peerConnection.onicecandidate = (event) => {
    if (event.candidate && otherSocketId) {
      // Détection du type de candidat
      const candidateStr = event.candidate.candidate;
      if (candidateStr.includes('relay')) {
        usingRelay = true;
        console.log('🔄 Utilisation TURN');
      } else if (candidateStr.includes('srflx')) {
        console.log('🌍 Utilisation STUN');
      }
      
      socket.emit('ice-candidate', { 
        target: otherSocketId, 
        candidate: event.candidate 
      });
    }
  };

  // Surveillance état connexion
  peerConnection.oniceconnectionstatechange = () => {
    const state = peerConnection.iceConnectionState;
    const duration = ((Date.now() - connectionStart) / 1000).toFixed(1);
    console.log(`📊 ICE ${state} (${duration}s)`);
    
    if (state === 'connected') {
      connectionEstablished = true;
      closeWaitingOverlay();
      updateOtherStatus(true);
      
      // Envoyer stats au serveur
      socket.emit('connection-quality', {
        quality: usingRelay ? 'relay' : 'direct',
        duration: duration
      });
    } else if (state === 'failed' || state === 'disconnected') {
      connectionEstablished = false;
      updateOtherStatus(false);
    }
  };

  peerConnection.onconnectionstatechange = () => {
    console.log('État connexion:', peerConnection.connectionState);
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

// ============================================
// ⭐ ÉVÉNEMENTS SOCKET.IO
// ============================================
socket.on('connect', () => {
  console.log('✅ Connecté au serveur:', socket.id);
  console.log('📡 Transport utilisé:', socket.io.engine.transport.name);
  
  if (typeof roomId !== 'undefined' && roomId) {
    console.log('📤 Rejoindre salle:', roomId);
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
  if (roomId) {
    socket.emit('join-room', roomId);
  }
});

socket.on('ice-servers', (servers) => {
  console.log('📡 Configuration ICE reçue du serveur');
  iceServersConfig = servers;
});

socket.on('room-full', (msg) => {
  showToast(msg.message || 'Salle pleine', 'error');
  setTimeout(() => window.location.href = '/', 3000);
});

socket.on('user-connected', async (data) => {
  const socketId = data.id || data;
  console.log('👤 Autre utilisateur connecté:', socketId);
  
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

  if (!peerConnection && !isInitiator) {
    isInitiator = true;
    console.log('🎯 Création de l\'offre...');
    
    try {
      await createPeerConnection();
      
      dataChannel = peerConnection.createDataChannel('chat', {
        ordered: true
      });
      setupDataChannel();
      
      const offer = await peerConnection.createOffer({
        offerToReceiveAudio: true,
        offerToReceiveVideo: true
      });
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
    console.error('Offre invalide');
    return;
  }
  
  otherSocketId = data.sender;
  isInitiator = false;

  try {
    if (!peerConnection) {
      await createPeerConnection();
    }

    await peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
    
    // Traiter candidats en attente
    while (pendingIceCandidates.length > 0) {
      const candidate = pendingIceCandidates.shift();
      await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
    }

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
    console.error('Pas de peerConnection');
    return;
  }

  try {
    if (peerConnection.signalingState === 'have-local-offer') {
      await peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
      console.log('✅ Réponse appliquée');
      
      while (pendingIceCandidates.length > 0) {
        const candidate = pendingIceCandidates.shift();
        await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
      }
    }
  } catch (err) {
    console.error('❌ Erreur traitement réponse:', err);
  }
});

socket.on('ice-candidate', async (data) => {
  console.log('📩 Candidat ICE reçu');
  
  if (!data.candidate) return;

  if (peerConnection && peerConnection.remoteDescription) {
    try {
      await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
      console.log('✅ Candidat ICE ajouté');
    } catch (err) {
      console.error('❌ Erreur ajout ICE:', err);
    }
  } else {
    console.log('⏳ Candidat ICE mis en attente');
    pendingIceCandidates.push(data.candidate);
  }
});

socket.on('user-disconnected', (data) => {
  const socketId = data.id || data;
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
    
    if (peerConnection) {
      peerConnection.close();
      peerConnection = null;
    }
    dataChannel = null;
  }
});

// ============================================
// ⭐ GESTION DU CHAT
// ============================================
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

// ============================================
// ⭐ EVENT LISTENERS
// ============================================
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

if (toggleChatBtn && chatArea) {
  toggleChatBtn.addEventListener('click', () => chatArea.classList.add('chat-visible'));
}

if (chatCloseBtn && chatArea) {
  chatCloseBtn.addEventListener('click', () => chatArea.classList.remove('chat-visible'));
}

// ============================================
// ⭐ INITIALISATION
// ============================================
(async function init() {
  try {
    // Charger configuration ICE d'abord
    await loadIceServers();
    
    // Puis initialiser médias
    await initMedia();
    
    console.log('🚀 Initialisation terminée');
    console.log('📡 Serveur:', SOCKET_URL);
    console.log('🔌 Path:', SOCKET_PATH);
  } catch (err) {
    console.error('Échec initialisation:', err);
  }
})();