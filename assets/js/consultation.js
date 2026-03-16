// consultation.js - Version robuste v2

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

// Connexion Socket.io
const socket = io(SIGNALING_SERVER);

// ========== Fonctions utilitaires ==========
function showToast(message, type = 'info', duration = 4000) {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle')}"></i><span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), duration);
}

function updateOtherStatus(online) {
  otherStatus.textContent = online ? 'En ligne' : 'Hors ligne';
  otherStatus.style.color = online ? '#00a884' : '#8696a0';
}

function closeWaitingOverlay() {
  if (waitingOverlay) {
    waitingOverlay.style.display = 'none';
    console.log('✅ Overlay fermé');
  } else {
    console.error('❌ Élément waiting-overlay introuvable');
  }
}

// ========== Gestion des médias ==========
async function initMedia() {
  try {
    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    localVideo.srcObject = localStream;
  } catch (err) {
    console.error('Erreur média:', err);
    showToast('Impossible d\'accéder à la caméra/micro.', 'error');
    throw err;
  }
}

// ========== Gestion WebRTC ==========
async function createPeerConnection() {
  if (peerConnection) peerConnection.close();
  peerConnection = new RTCPeerConnection(iceServers);

  localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

  peerConnection.onicecandidate = (event) => {
    if (event.candidate && otherSocketId) {
      socket.emit('ice-candidate', { target: otherSocketId, candidate: event.candidate });
    }
  };

  peerConnection.ontrack = (event) => {
    remoteVideo.srcObject = event.streams[0];
    connectionEstablished = true;
    closeWaitingOverlay();   // ← Ajout
    updateOtherStatus(true);
  };

  peerConnection.ondatachannel = (event) => {
    dataChannel = event.channel;
    setupDataChannel();
  };

  return peerConnection;
}

function setupDataChannel() {
  if (!dataChannel) return;

  // Vérifier si déjà ouvert
  if (dataChannel.readyState === 'open') {
    console.log('Data channel déjà ouvert');
    closeWaitingOverlay();
  }

  dataChannel.onopen = () => {
    console.log('Data channel ouvert (event)');
    closeWaitingOverlay();
  };
  dataChannel.onclose = () => console.log('Data channel fermé');
  dataChannel.onmessage = (event) => {
    try {
      const data = JSON.parse(event.data);
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

// ========== Signalisation Socket ==========
socket.on('connect', () => {
  console.log('Connecté au serveur de signalisation');
  socket.emit('join-room', roomId);
});

socket.on('room-full', (msg) => {
  showToast(msg.message, 'error');
  setTimeout(() => window.location.href = '/', 3000);
});

socket.on('user-connected', async (socketId) => {
  console.log('Autre utilisateur connecté:', socketId);
  otherSocketId = socketId;
  updateOtherStatus(true);
  showToast(`${otherUser.name} a rejoint la consultation.`, 'success');

  if (!peerConnection) {
    isInitiator = true;
    await createPeerConnection();
    dataChannel = peerConnection.createDataChannel('chat');
    setupDataChannel();
    try {
      const offer = await peerConnection.createOffer();
      await peerConnection.setLocalDescription(offer);
      socket.emit('offer', { target: otherSocketId, sdp: offer });
    } catch (err) {
      console.error('Erreur création offre:', err);
    }
  }
});

socket.on('offer', async (data) => {
  console.log('Offre reçue de', data.sender);
  otherSocketId = data.sender;
  if (!peerConnection) {
    await createPeerConnection();
  }
  try {
    await peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
    const answer = await peerConnection.createAnswer();
    await peerConnection.setLocalDescription(answer);
    socket.emit('answer', { target: otherSocketId, sdp: answer });
  } catch (err) {
    console.error('Erreur traitement offre:', err);
  }
});

socket.on('answer', async (data) => {
  console.log('Réponse reçue de', data.sender);
  otherSocketId = data.sender;
  if (peerConnection && peerConnection.signalingState !== 'stable') {
    try {
      await peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
    } catch (err) {
      console.error('Erreur traitement réponse:', err);
    }
  }
});

socket.on('ice-candidate', async (data) => {
  console.log('Candidat ICE reçu');
  if (peerConnection) {
    try {
      await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
    } catch (err) {
      console.error('Erreur ajout ICE:', err);
    }
  }
});

socket.on('user-disconnected', (socketId) => {
  console.log('Utilisateur déconnecté:', socketId);
  if (socketId === otherSocketId) {
    otherSocketId = null;
    updateOtherStatus(false);
    showToast(`${otherUser.name} a quitté la consultation.`, 'warning');
    remoteVideo.srcObject = null;
    connectionEstablished = false;
    waitingOverlay.style.display = 'flex'; // Réafficher l'attente
  }
});

// ========== Gestion du chat ==========
function displayMessage(text, senderId, timestamp, file = null) {
  const isOwn = senderId === currentUser.id;
  const sender = isOwn ? currentUser : otherUser;
  const messageDiv = document.createElement('div');
  messageDiv.classList.add('message', isOwn ? 'own' : 'other');

  const avatar = document.createElement('img');
  avatar.src = sender.avatar;
  avatar.alt = '';
  avatar.classList.add('avatar');

  const bubble = document.createElement('div');
  bubble.classList.add('bubble');

  if (!isOwn) {
    const senderName = document.createElement('div');
    senderName.classList.add('sender');
    senderName.textContent = sender.name;
    bubble.appendChild(senderName);
  }

  if (file) {
    const fileDiv = document.createElement('div');
    fileDiv.classList.add('file');
    const icon = document.createElement('i');
    icon.className = file.type.startsWith('image/') ? 'fas fa-image' : 'fas fa-file-pdf';
    fileDiv.appendChild(icon);
    const link = document.createElement('a');
    link.href = file.url;
    link.target = '_blank';
    link.textContent = file.name;
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
  timeDiv.textContent = new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
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
  const message = {
    type: file ? 'file' : 'chat',
    sender: currentUser.id,
    timestamp: Date.now(),
    message: text,
    file: file
  };
  dataChannel.send(JSON.stringify(message));
  displayMessage(text, currentUser.id, Date.now(), file);
}

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

// ========== Contrôles média ==========
muteAudioBtn.addEventListener('click', () => {
  audioEnabled = !audioEnabled;
  localStream.getAudioTracks().forEach(track => track.enabled = audioEnabled);
  muteAudioBtn.classList.toggle('active', audioEnabled);
  muteAudioBtn.innerHTML = audioEnabled ? '<i class="fas fa-microphone"></i>' : '<i class="fas fa-microphone-slash"></i>';
});

muteVideoBtn.addEventListener('click', () => {
  videoEnabled = !videoEnabled;
  localStream.getVideoTracks().forEach(track => track.enabled = videoEnabled);
  muteVideoBtn.classList.toggle('active', videoEnabled);
  muteVideoBtn.innerHTML = videoEnabled ? '<i class="fas fa-video"></i>' : '<i class="fas fa-video-slash"></i>';
});

// ========== Quitter l'appel ==========
leaveBtn.addEventListener('click', () => {
  if (peerConnection) peerConnection.close();
  localStream.getTracks().forEach(track => track.stop());
  socket.disconnect();
  window.location.href = '/'; // À adapter
});

// ========== Gestion du chat mobile ==========
toggleChatBtn.addEventListener('click', () => chatArea.classList.add('chat-visible'));
chatCloseBtn.addEventListener('click', () => chatArea.classList.remove('chat-visible'));

// ========== Initialisation ==========
(async function() {
  try {
    await initMedia();
    // L'overlay reste affiché en attendant l'autre
  } catch (err) {}
})();