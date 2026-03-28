// ============================================
// NUFOTEC CONSULTATION - CLIENT PRINCIPAL
// Version : 5.3.0 - CodeIgniter 3
// Description : Consultation vidéo peer-to-peer
// Optimisé pour hébergement mutualisé (polling uniquement)
// ============================================

(function() {
    'use strict';

    // ============================================
    // ⭐ CONFIGURATION
    // ============================================
    const CONFIG = {
        socketUrl: window.location.origin,
        socketPath: '/socket/socket.io',
        roomId: window.roomId || null,
        currentUser: window.currentUser && typeof window.currentUser === 'object' 
            ? window.currentUser 
            : { id: 'me', name: 'Moi', avatar: '/assets/img/default-avatar.png' },
        otherUser: window.otherUser && typeof window.otherUser === 'object'
            ? window.otherUser 
            : { id: 'other', name: 'Autre', avatar: '/assets/img/default-avatar.png' },
        currentRole: window.currentRole || 'participant',
        consultationId: window.consultationId || null,
        maxReconnectionAttempts: 30,
        iceServers: null,
        debug: true,
        iceConnectionTimeout: 30000,
        videoRetryAttempts: 3,
        videoRetryDelay: 1000
    };

    // ============================================
    // ⭐ ÉTAT DE L'APPLICATION
    // ============================================
    const state = {
        socket: null,
        peerConnection: null,
        dataChannel: null,
        localStream: null,
        otherSocketId: null,
        isInitiator: false,
        isConnected: false,
        usingRelay: false,
        pendingIceCandidates: [],
        audioEnabled: true,
        videoEnabled: true,
        connectionQuality: 'unknown',
        reconnectionAttempts: 0,
        initializationComplete: false,
        heartbeatInterval: null,
        videoRetryCount: 0,
        remoteStream: null,
        iceConnectionStartTime: null,
        reconnectTimer: null
    };

    // ============================================
    // ⭐ ÉLÉMENTS DOM
    // ============================================
    const elements = {
        localVideo: document.getElementById('local-video'),
        remoteVideo: document.getElementById('remote-video'),
        chatMessages: document.getElementById('chat-messages'),
        chatInput: document.getElementById('chat-input'),
        chatSend: document.getElementById('chat-send'),
        fileInput: document.getElementById('file-input'),
        muteAudioBtn: document.getElementById('mute-audio'),
        muteVideoBtn: document.getElementById('mute-video'),
        leaveBtn: document.getElementById('leave-call'),
        toggleChatBtn: document.getElementById('toggle-chat'),
        chatCloseBtn: document.getElementById('chat-close'),
        chatArea: document.getElementById('chat-area'),
        otherStatus: document.getElementById('other-status'),
        waitingOverlay: document.getElementById('waiting-overlay'),
        offlineIndicator: document.getElementById('offline-indicator'),
        toastContainer: document.getElementById('toast-container')
    };

    // ============================================
    // ⭐ UTILITAIRES
    // ============================================
    const utils = {
        log: function(level, ...args) {
            if (!CONFIG.debug && level !== 'error') return;
            const prefix = `[${new Date().toLocaleTimeString()}]`;
            const emoji = { error: '❌', warn: '⚠️', success: '✅', info: 'ℹ️', debug: '🔍' }[level] || '';
            const logFn = level === 'error' ? console.error : level === 'warn' ? console.warn : console.log;
            logFn(prefix, emoji, ...args);
        },

        showToast: function(message, type = 'info', duration = 4000) {
            if (!elements.toastContainer) return;
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            const icon = { success: 'check-circle', error: 'exclamation-circle', warning: 'exclamation-triangle', info: 'info-circle' }[type] || 'info-circle';
            toast.innerHTML = `<i class="fas fa-${icon}"></i><span>${this.escapeHtml(message)}</span>`;
            elements.toastContainer.appendChild(toast);
            setTimeout(() => toast.remove(), duration);
        },

        escapeHtml: function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        formatTime: function(timestamp) {
            try {
                const date = new Date(timestamp);
                return isNaN(date.getTime()) ? '--:--' : date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } catch { return '--:--'; }
        },

        getCurrentUserId: function() { return CONFIG.currentUser?.id || 'me'; },

        updateOtherStatus: function(online) {
            if (!elements.otherStatus) return;
            elements.otherStatus.textContent = online ? 'En ligne' : 'Hors ligne';
            elements.otherStatus.style.color = online ? '#00a884' : '#8696a0';
            if (elements.offlineIndicator) {
                elements.offlineIndicator.classList.toggle('show', !online && state.isConnected);
            }
        },

        closeWaitingOverlay: function() { 
            if (elements.waitingOverlay) {
                elements.waitingOverlay.style.display = 'none';
            }
        },
        
        showWaitingOverlay: function() { 
            if (elements.waitingOverlay) {
                elements.waitingOverlay.style.display = 'flex';
            }
        },
        
        resetRemoteVideo: function() { 
            if (elements.remoteVideo) { 
                elements.remoteVideo.srcObject = null; 
                elements.remoteVideo.load(); 
                state.remoteStream = null;
                state.videoRetryCount = 0;
            } 
        },

        checkBrowserCompatibility: function() {
            const issues = [];
            if (!navigator.mediaDevices?.getUserMedia) issues.push('getUserMedia non supporté');
            if (!window.RTCPeerConnection) issues.push('WebRTC non supporté');
            if (!window.io) issues.push('Socket.IO non chargé');
            if (issues.length) { this.log('error', 'Problèmes:', issues); this.showToast('Navigateur non compatible', 'error'); return false; }
            return true;
        },

        cleanup: function() {
            if (state.heartbeatInterval) { clearInterval(state.heartbeatInterval); state.heartbeatInterval = null; }
            if (state.reconnectTimer) { clearTimeout(state.reconnectTimer); state.reconnectTimer = null; }
            if (state.socket) { 
                try {
                    state.socket.removeAllListeners(); 
                    state.socket.disconnect(); 
                } catch(e) { utils.log('warn', 'Erreur nettoyage socket:', e); }
                state.socket = null; 
            }
            webrtc.cleanup();
        },

        playVideoWithRetry: function(videoElement, maxRetries = CONFIG.videoRetryAttempts, delay = CONFIG.videoRetryDelay) {
            if (!videoElement || !videoElement.srcObject) {
                utils.log('warn', 'Impossible de jouer la vidéo: pas de source');
                return Promise.reject(new Error('No video source'));
            }
            
            const attemptPlay = (retryCount) => {
                return videoElement.play().catch(error => {
                    utils.log('warn', `Tentative de lecture ${retryCount + 1}/${maxRetries} échouée:`, error.message);
                    
                    if (retryCount < maxRetries - 1) {
                        return new Promise(resolve => {
                            setTimeout(() => {
                                attemptPlay(retryCount + 1).then(resolve).catch(resolve);
                            }, delay);
                        });
                    }
                    throw error;
                });
            };
            
            return attemptPlay(0);
        }
    };

    // ============================================
    // ⭐ GESTIONNAIRE PERMISSIONS
    // ============================================
    const PermissionManager = {
        devices: { cameras: [], micros: [] },
        selectedCamera: null,
        selectedMicro: null,
        previewStream: null,
        permissionGranted: false,

        showModal: function() {
            const modal = document.getElementById('permissionModal');
            if (modal) { modal.style.display = 'flex'; this.loadDevices(); }
        },

        hideModal: function() {
            const modal = document.getElementById('permissionModal');
            if (modal) modal.style.display = 'none';
            this.stopPreview();
        },

        loadDevices: async function() {
            try {
                const tempStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                tempStream.getTracks().forEach(track => track.stop());
                const devices = await navigator.mediaDevices.enumerateDevices();
                this.devices.cameras = devices.filter(d => d.kind === 'videoinput');
                this.devices.micros = devices.filter(d => d.kind === 'audioinput');
                this.populateSelectors();
            } catch (error) {
                console.error('Erreur chargement périphériques:', error);
                this.showNoPermissionMessage();
            }
        },

        populateSelectors: function() {
            const cameraSelect = document.getElementById('cameraSelect');
            const microSelect = document.getElementById('microSelect');
            
            if (cameraSelect) {
                cameraSelect.innerHTML = '<option value="">Sélectionner une caméra...</option>';
                this.devices.cameras.forEach((cam, i) => {
                    const opt = document.createElement('option');
                    opt.value = cam.deviceId;
                    opt.textContent = cam.label || `Caméra ${i + 1}`;
                    cameraSelect.appendChild(opt);
                });
                cameraSelect.onchange = () => { this.selectedCamera = cameraSelect.value; this.startPreview(); };
            }
            
            if (microSelect) {
                microSelect.innerHTML = '<option value="">Sélectionner un micro...</option>';
                this.devices.micros.forEach((mic, i) => {
                    const opt = document.createElement('option');
                    opt.value = mic.deviceId;
                    opt.textContent = mic.label || `Micro ${i + 1}`;
                    microSelect.appendChild(opt);
                });
                microSelect.onchange = () => { this.selectedMicro = microSelect.value; this.startPreview(); };
            }
            
            if (this.devices.cameras.length) {
                cameraSelect.value = this.devices.cameras[0].deviceId;
                this.selectedCamera = this.devices.cameras[0].deviceId;
            }
            if (this.devices.micros.length) {
                microSelect.value = this.devices.micros[0].deviceId;
                this.selectedMicro = this.devices.micros[0].deviceId;
            }
            if (this.selectedCamera) this.startPreview();
        },

        startPreview: async function() {
            this.stopPreview();
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: this.selectedCamera ? { deviceId: { exact: this.selectedCamera } } : true,
                    audio: false
                });
                this.previewStream = stream;
                const previewVideo = document.getElementById('previewVideo');
                const previewPlaceholder = document.querySelector('.preview-placeholder');
                if (previewVideo && previewPlaceholder) {
                    previewVideo.srcObject = stream;
                    previewVideo.style.display = 'block';
                    previewPlaceholder.style.display = 'none';
                    previewVideo.play().catch(e => console.log('Preview error:', e));
                }
            } catch (error) { console.error('Erreur preview:', error); }
        },

        stopPreview: function() {
            if (this.previewStream) { this.previewStream.getTracks().forEach(t => t.stop()); this.previewStream = null; }
            const previewVideo = document.getElementById('previewVideo');
            const previewPlaceholder = document.querySelector('.preview-placeholder');
            if (previewVideo) { previewVideo.srcObject = null; previewVideo.style.display = 'none'; }
            if (previewPlaceholder) previewPlaceholder.style.display = 'block';
        },

        getStream: async function() {
            const constraints = {
                video: this.selectedCamera ? { deviceId: { exact: this.selectedCamera } } : true,
                audio: this.selectedMicro ? { deviceId: { exact: this.selectedMicro } } : true
            };
            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.permissionGranted = true;
            return stream;
        },

        showNoPermissionMessage: function() {
            const previewContainer = document.getElementById('previewContainer');
            if (previewContainer) {
                previewContainer.innerHTML = `<div class="preview-placeholder" style="color: #F44336;">
                    <i class="fas fa-exclamation-triangle"></i><p>Permission refusée</p>
                    <small>Veuillez autoriser l'accès à la caméra et au micro.</small></div>`;
            }
        },

        checkExistingPermission: async function() {
            try {
                const testStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                testStream.getTracks().forEach(t => t.stop());
                this.permissionGranted = true;
                return true;
            } catch { this.permissionGranted = false; return false; }
        }
    };

    // ============================================
    // ⭐ WEBRTC
    // ============================================
    const webrtc = {
        async loadIceServers() {
            try {
                utils.log('info', '🌐 Chargement ICE...');
                const response = await fetch('/socket/api/ice-servers');
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                CONFIG.iceServers = await response.json();
                utils.log('success', '✅ Serveurs ICE chargés');
                return CONFIG.iceServers;
            } catch (error) {
                utils.log('error', '❌ Erreur ICE:', error.message);
                CONFIG.iceServers = { iceServers: [
                    { urls: 'stun:stun.l.google.com:19302' },
                    { urls: 'stun:stun1.l.google.com:19302' },
                    { urls: 'stun:stun2.l.google.com:19302' },
                    { urls: 'stun:stun3.l.google.com:19302' },
                    { urls: 'stun:stun4.l.google.com:19302' }
                ]};
                utils.showToast('Utilisation des serveurs STUN publics', 'info');
                return CONFIG.iceServers;
            }
        },

        async initMedia() {
            try {
                utils.log('info', '📹 Initialisation des médias...');
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('getUserMedia non supporté');
                }
                state.localStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user', frameRate: { ideal: 30 } },
                    audio: { echoCancellation: true, noiseSuppression: true, autoGainControl: true }
                });
                if (elements.localVideo) {
                    elements.localVideo.srcObject = state.localStream;
                    elements.localVideo.muted = true;
                    utils.playVideoWithRetry(elements.localVideo).catch(e => utils.log('warn', 'Erreur play local:', e));
                }
                utils.log('success', '✅ Médias initialisés');
                return state.localStream;
            } catch (error) {
                utils.log('error', '❌ Erreur média:', error.message);
                let message = 'Impossible d\'accéder à la caméra/micro';
                if (error.name === 'NotAllowedError') message = 'Autorisation caméra/micro refusée';
                else if (error.name === 'NotFoundError') message = 'Aucun périphérique trouvé';
                utils.showToast(message, 'error');
                throw error;
            }
        },

        async createPeerConnection() {
            try {
                if (state.peerConnection) { 
                    state.peerConnection.close(); 
                    state.peerConnection = null; 
                }
                if (!CONFIG.iceServers) await this.loadIceServers();
                const config = { 
                    ...CONFIG.iceServers, 
                    iceTransportPolicy: 'all', 
                    iceCandidatePoolSize: 10, 
                    bundlePolicy: 'max-bundle', 
                    rtcpMuxPolicy: 'require' 
                };
                state.peerConnection = new RTCPeerConnection(config);
                this.setupPeerConnectionListeners();
                this.addLocalTracks();
                utils.log('info', '🔄 Connexion WebRTC créée');
                state.iceConnectionStartTime = Date.now();
                return state.peerConnection;
            } catch (error) {
                utils.log('error', '❌ Erreur création PeerConnection:', error);
                utils.showToast('Erreur de connexion WebRTC', 'error');
                throw error;
            }
        },

        setupPeerConnectionListeners() {
            const startTime = Date.now();
            
            let iceTimeout = setTimeout(() => {
                if (!state.isConnected && state.peerConnection && 
                    state.peerConnection.iceConnectionState !== 'connected' &&
                    state.peerConnection.iceConnectionState !== 'completed' &&
                    state.peerConnection.iceConnectionState !== 'failed') {
                    utils.log('warn', '⚠️ Timeout connexion ICE, tentative de renégociation...');
                    if (state.otherSocketId && state.socket && state.socket.connected) {
                        webrtc.createOffer();
                    }
                }
            }, CONFIG.iceConnectionTimeout);
            
            state.peerConnection.onicecandidate = (event) => {
                if (event.candidate && state.otherSocketId && state.socket && state.socket.connected) {
                    try {
                        const candidateStr = event.candidate.candidate;
                        if (candidateStr.includes('relay')) {
                            state.usingRelay = true;
                            utils.log('info', '🔄 Utilisation TURN (relais)');
                        }
                        state.socket.emit('ice-candidate', { target: state.otherSocketId, candidate: event.candidate });
                    } catch (error) { utils.log('error', 'Erreur envoi candidat ICE:', error); }
                }
            };
            
            state.peerConnection.oniceconnectionstatechange = () => {
                const iceState = state.peerConnection.iceConnectionState;
                const duration = ((Date.now() - startTime) / 1000).toFixed(1);
                utils.log('info', `📊 ICE ${iceState} (${duration}s)`);
                switch(iceState) {
                    case 'connected':
                    case 'completed':
                        clearTimeout(iceTimeout);
                        state.isConnected = true;
                        utils.closeWaitingOverlay();
                        utils.updateOtherStatus(true);
                        utils.showToast('Connexion vidéo établie', 'success', 2000);
                        if (state.socket && state.socket.connected) {
                            state.socket.emit('connection-quality', { 
                                quality: state.usingRelay ? 'relay' : 'direct', 
                                duration: duration 
                            });
                        }
                        break;
                    case 'failed':
                        utils.log('error', '❌ Échec connexion ICE');
                        utils.showToast('Problème de connexion réseau', 'error');
                        state.isConnected = false;
                        utils.updateOtherStatus(false);
                        break;
                    case 'disconnected':
                        utils.log('warn', '⚠️ Connexion ICE interrompue');
                        state.isConnected = false;
                        utils.updateOtherStatus(false);
                        utils.showToast('Problème de connexion détecté', 'warning');
                        break;
                    case 'closed':
                        clearTimeout(iceTimeout);
                        state.isConnected = false;
                        break;
                }
            };
            
            state.peerConnection.ontrack = (event) => {
                utils.log('info', `📹 Track reçu: ${event.track.kind}`);
                if (elements.remoteVideo && event.streams[0]) {
                    state.remoteStream = event.streams[0];
                    
                    if (elements.remoteVideo.srcObject !== event.streams[0]) {
                        elements.remoteVideo.srcObject = event.streams[0];
                    }
                    
                    const playVideo = () => {
                        utils.playVideoWithRetry(elements.remoteVideo, 5, 500)
                            .then(() => {
                                utils.log('success', '✅ Lecture vidéo démarrée');
                                state.isConnected = true;
                                utils.closeWaitingOverlay();
                                utils.updateOtherStatus(true);
                            })
                            .catch(error => {
                                utils.log('error', '❌ Échec lecture vidéo:', error);
                            });
                    };
                    
                    if (event.streams[0].getTracks().length > 0) {
                        playVideo();
                    } else {
                        elements.remoteVideo.addEventListener('loadedmetadata', playVideo, { once: true });
                    }
                }
            };
            
            state.peerConnection.ondatachannel = (event) => {
                utils.log('info', '📡 Canal de données reçu');
                chat.setDataChannel(event.channel);
            };
            
            state.peerConnection.onicegatheringstatechange = () => {
                utils.log('debug', '🔄 ICE gathering:', state.peerConnection.iceGatheringState);
            };
            
            state.peerConnection.onsignalingstatechange = () => {
                utils.log('debug', '🔄 Signalement:', state.peerConnection.signalingState);
            };
            
            state.peerConnection.onconnectionstatechange = () => {
                utils.log('info', '🔌 État connexion:', state.peerConnection.connectionState);
                if (state.peerConnection.connectionState === 'failed') {
                    utils.showToast('Échec de connexion, tentative de reconnexion...', 'error');
                    if (state.otherSocketId && state.socket && state.socket.connected && !state.isInitiator) {
                        setTimeout(() => webrtc.createOffer(), 2000);
                    }
                }
            };
        },

        addLocalTracks() {
            if (!state.localStream || !state.peerConnection) return;
            state.localStream.getTracks().forEach(track => {
                try {
                    state.peerConnection.addTrack(track, state.localStream);
                    utils.log('debug', `➕ Track ajouté: ${track.kind}`);
                } catch (error) { utils.log('error', `Erreur ajout track ${track.kind}:`, error); }
            });
        },

        async createOffer() {
            try {
                utils.log('info', '🎯 Création offre...');
                if (!state.peerConnection) await this.createPeerConnection();
                if (!state.peerConnection) throw new Error('PeerConnection non créée');
                
                if (!state.dataChannel) {
                    const dataChannel = state.peerConnection.createDataChannel('chat', { ordered: true, maxRetransmits: 3 });
                    chat.setDataChannel(dataChannel);
                }
                
                const offer = await state.peerConnection.createOffer({ 
                    offerToReceiveAudio: true, 
                    offerToReceiveVideo: true, 
                    iceRestart: false 
                });
                await state.peerConnection.setLocalDescription(offer);
                if (!state.otherSocketId || !state.socket || !state.socket.connected) throw new Error('Socket non disponible');
                utils.log('info', `📤 Envoi offre à: ${state.otherSocketId}`);
                state.socket.emit('offer', { target: state.otherSocketId, sdp: offer });
            } catch (error) { 
                utils.log('error', '❌ Erreur création offre:', error); 
                utils.showToast('Erreur de connexion', 'error'); 
            }
        },

        async handleOffer(data) {
            if (!data?.sdp || !data?.sender) { utils.log('error', 'Offre invalide'); return; }
            utils.log('info', `📩 Offre reçue de: ${data.sender}`);
            try {
                if (!state.peerConnection) await this.createPeerConnection();
                if (!state.peerConnection) throw new Error('PeerConnection non créée');
                await state.peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
                await this.processPendingCandidates();
                const answer = await state.peerConnection.createAnswer();
                await state.peerConnection.setLocalDescription(answer);
                utils.log('info', `📤 Envoi réponse à: ${data.sender}`);
                if (state.socket && state.socket.connected) state.socket.emit('answer', { target: data.sender, sdp: answer });
            } catch (error) { 
                utils.log('error', '❌ Erreur traitement offre:', error); 
            }
        },

        async handleAnswer(data) {
            if (!data?.sdp || !data?.sender) { utils.log('error', 'Réponse invalide'); return; }
            utils.log('info', `📩 Réponse reçue de: ${data.sender}`);
            if (!state.peerConnection) { utils.log('error', 'Pas de PeerConnection'); return; }
            try {
                if (state.peerConnection.signalingState === 'have-local-offer') {
                    await state.peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
                    utils.log('success', '✅ Réponse appliquée');
                    await this.processPendingCandidates();
                } else {
                    utils.log('warn', `État de signalisation inattendu: ${state.peerConnection.signalingState}`);
                }
            } catch (error) { 
                utils.log('error', '❌ Erreur traitement réponse:', error); 
            }
        },

        async handleIceCandidate(data) {
            if (!data?.candidate) return;
            try {
                if (state.peerConnection && state.peerConnection.remoteDescription) {
                    await state.peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
                    utils.log('debug', '✅ Candidat ICE ajouté');
                } else {
                    state.pendingIceCandidates.push(data.candidate);
                    utils.log('debug', `⏳ Candidat mis en attente (${state.pendingIceCandidates.length})`);
                }
            } catch (error) { 
                utils.log('error', '❌ Erreur ajout ICE:', error); 
            }
        },

        async processPendingCandidates() {
            if (!state.peerConnection) return;
            while (state.pendingIceCandidates.length > 0) {
                const candidate = state.pendingIceCandidates.shift();
                try { 
                    await state.peerConnection.addIceCandidate(new RTCIceCandidate(candidate)); 
                    utils.log('debug', '✅ Candidat en attente ajouté');
                }
                catch (error) { utils.log('error', '❌ Erreur ajout candidat:', error); }
            }
        },

        toggleAudio: function() {
            if (!state.localStream) { utils.showToast('Aucun flux audio disponible', 'warning'); return false; }
            state.audioEnabled = !state.audioEnabled;
            state.localStream.getAudioTracks().forEach(track => track.enabled = state.audioEnabled);
            if (elements.muteAudioBtn) {
                elements.muteAudioBtn.classList.toggle('active', state.audioEnabled);
                elements.muteAudioBtn.innerHTML = state.audioEnabled ? '<i class="fas fa-microphone"></i>' : '<i class="fas fa-microphone-slash"></i>';
            }
            utils.showToast(state.audioEnabled ? 'Microphone activé' : 'Microphone coupé', 'info', 1000);
            return state.audioEnabled;
        },

        toggleVideo: function() {
            if (!state.localStream) { utils.showToast('Aucun flux vidéo disponible', 'warning'); return false; }
            state.videoEnabled = !state.videoEnabled;
            state.localStream.getVideoTracks().forEach(track => track.enabled = state.videoEnabled);
            if (elements.muteVideoBtn) {
                elements.muteVideoBtn.classList.toggle('active', state.videoEnabled);
                elements.muteVideoBtn.innerHTML = state.videoEnabled ? '<i class="fas fa-video"></i>' : '<i class="fas fa-video-slash"></i>';
            }
            utils.showToast(state.videoEnabled ? 'Caméra activée' : 'Caméra coupée', 'info', 1000);
            return state.videoEnabled;
        },

        cleanup: function() {
            if (state.peerConnection) { 
                state.peerConnection.close(); 
                state.peerConnection = null; 
            }
            utils.resetRemoteVideo();
            state.pendingIceCandidates = [];
            state.usingRelay = false;
            state.isConnected = false;
            state.otherSocketId = null;
            state.dataChannel = null;
            state.videoRetryCount = 0;
            state.remoteStream = null;
            utils.log('info', '🧹 Nettoyage WebRTC effectué');
        },

        disconnect: function() {
            this.cleanup();
            if (state.localStream) { 
                state.localStream.getTracks().forEach(track => track.stop()); 
                state.localStream = null; 
            }
        }
    };

    // ============================================
    // ⭐ CHAT
    // ============================================
    const chat = {
        setDataChannel: function(channel) {
            if (!channel) { utils.log('error', 'Tentative de setDataChannel avec null'); return; }
            state.dataChannel = channel;
            this.setupDataChannelListeners();
        },

        setupDataChannelListeners: function() {
            if (!state.dataChannel) return;
            state.dataChannel.onopen = () => { 
                utils.log('success', '✅ Canal de données ouvert'); 
                utils.showToast('Chat connecté', 'success');
            };
            state.dataChannel.onclose = () => utils.log('warn', '❌ Canal de données fermé');
            state.dataChannel.onerror = (error) => { 
                utils.log('error', '⚠️ Erreur data channel:', error); 
                utils.showToast('Erreur de chat', 'error');
            };
            state.dataChannel.onmessage = (event) => this.handleIncomingMessage(event.data);
        },

        handleIncomingMessage: function(data) {
            try {
                const message = JSON.parse(data);
                if (!message || !message.type) { utils.log('warn', 'Message invalide reçu'); return; }
                switch (message.type) {
                    case 'chat': this.displayMessage(message.message || '', message.sender, message.timestamp || Date.now()); break;
                    case 'file': this.displayFile(message.file || {}, message.sender, message.timestamp || Date.now()); break;
                    default: utils.log('debug', 'Type de message inconnu:', message.type);
                }
            } catch (error) { utils.log('error', 'Erreur parsing message:', error); }
        },

        sendMessage: function() {
            if (!state.dataChannel || state.dataChannel.readyState !== 'open') { 
                utils.showToast('Le chat n\'est pas encore connecté', 'warning'); 
                return false; 
            }
            if (!elements.chatInput) return false;
            const text = elements.chatInput.value.trim();
            if (!text) return false;
            const message = { type: 'chat', sender: utils.getCurrentUserId(), timestamp: Date.now(), message: text.substring(0, 500) };
            try {
                state.dataChannel.send(JSON.stringify(message));
                this.displayMessage(text, message.sender, message.timestamp);
                elements.chatInput.value = '';
                return true;
            } catch (error) { 
                utils.log('error', 'Erreur envoi message:', error); 
                utils.showToast('Échec de l\'envoi', 'error'); 
                return false; 
            }
        },

        sendFiles: function(files) {
            if (!files || files.length === 0) return;
            if (!state.dataChannel || state.dataChannel.readyState !== 'open') { 
                utils.showToast('Le chat n\'est pas connecté', 'warning'); 
                return; 
            }
            const filesArray = Array.from(files).slice(0, 5);
            filesArray.forEach(file => {
                if (file.size > 10 * 1024 * 1024) { 
                    utils.showToast(`Fichier trop volumineux: ${file.name}`, 'warning'); 
                    return; 
                }
                try {
                    const fileUrl = URL.createObjectURL(file);
                    const fileInfo = { name: file.name, type: file.type, size: file.size, url: fileUrl };
                    const message = { type: 'file', sender: utils.getCurrentUserId(), timestamp: Date.now(), file: fileInfo };
                    state.dataChannel.send(JSON.stringify(message));
                    this.displayFile(fileInfo, message.sender, message.timestamp);
                } catch (error) { 
                    utils.log('error', 'Erreur envoi fichier:', error); 
                    utils.showToast(`Échec de l'envoi de ${file.name}`, 'error'); 
                }
            });
        },

        displayMessage: function(text, senderId, timestamp) {
            if (!elements.chatMessages) return;
            const isOwn = senderId === utils.getCurrentUserId();
            const sender = isOwn ? CONFIG.currentUser : CONFIG.otherUser;
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isOwn ? 'own' : 'other'}`;
            const bubble = document.createElement('div');
            bubble.className = 'bubble';
            if (!isOwn && sender?.name) {
                const senderName = document.createElement('div');
                senderName.className = 'sender';
                senderName.textContent = sender.name;
                bubble.appendChild(senderName);
            }
            const textDiv = document.createElement('div');
            textDiv.className = 'text';
            textDiv.textContent = text;
            bubble.appendChild(textDiv);
            const timeDiv = document.createElement('div');
            timeDiv.className = 'time';
            timeDiv.textContent = utils.formatTime(timestamp);
            bubble.appendChild(timeDiv);
            const avatar = document.createElement('img');
            avatar.src = sender?.avatar || '/assets/img/default-avatar.png';
            avatar.className = 'avatar';
            avatar.onerror = () => { avatar.src = '/assets/img/default-avatar.png'; };
            if (isOwn) { messageDiv.appendChild(bubble); messageDiv.appendChild(avatar); }
            else { messageDiv.appendChild(avatar); messageDiv.appendChild(bubble); }
            elements.chatMessages.appendChild(messageDiv);
            elements.chatMessages.scrollTop = elements.chatMessages.scrollHeight;
        },

        displayFile: function(file, senderId, timestamp) {
            if (!elements.chatMessages) return;
            const isOwn = senderId === utils.getCurrentUserId();
            const sender = isOwn ? CONFIG.currentUser : CONFIG.otherUser;
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isOwn ? 'own' : 'other'}`;
            const bubble = document.createElement('div');
            bubble.className = 'bubble';
            if (!isOwn && sender?.name) {
                const senderName = document.createElement('div');
                senderName.className = 'sender';
                senderName.textContent = sender.name;
                bubble.appendChild(senderName);
            }
            const fileDiv = document.createElement('div');
            fileDiv.className = 'file';
            const icon = document.createElement('i');
            icon.className = file.type?.startsWith('image/') ? 'fas fa-image' : 'fas fa-file';
            fileDiv.appendChild(icon);
            const link = document.createElement('a');
            link.href = file.url || '#';
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = file.name || 'Fichier';
            link.style.color = 'white';
            fileDiv.appendChild(link);
            bubble.appendChild(fileDiv);
            const timeDiv = document.createElement('div');
            timeDiv.className = 'time';
            timeDiv.textContent = utils.formatTime(timestamp);
            bubble.appendChild(timeDiv);
            const avatar = document.createElement('img');
            avatar.src = sender?.avatar || '/assets/img/default-avatar.png';
            avatar.className = 'avatar';
            avatar.onerror = () => { avatar.src = '/assets/img/default-avatar.png'; };
            if (isOwn) { messageDiv.appendChild(bubble); messageDiv.appendChild(avatar); }
            else { messageDiv.appendChild(avatar); messageDiv.appendChild(bubble); }
            elements.chatMessages.appendChild(messageDiv);
            elements.chatMessages.scrollTop = elements.chatMessages.scrollHeight;
        }
    };

    // ============================================
    // ⭐ SOCKET.IO - CONFIGURATION SIMPLIFIÉE
    // ============================================
    function initSocket() {
        if (!window.io) { 
            utils.log('error', 'Socket.IO non chargé'); 
            utils.showToast('Erreur de chargement du chat', 'error'); 
            return; 
        }
        
        utils.log('info', '🔌 Initialisation socket...', CONFIG.socketUrl + CONFIG.socketPath);
        
        try {
            // Configuration SIMPLE et fiable
            state.socket = io(CONFIG.socketUrl, {
                path: CONFIG.socketPath,
                transports: ['polling'],
                reconnection: true,
                reconnectionAttempts: CONFIG.maxReconnectionAttempts,
                reconnectionDelay: 1000,
                reconnectionDelayMax: 5000,
                timeout: 20000
            });
            
            setupSocketListeners();
            startHeartbeat();
            
        } catch (error) { 
            utils.log('error', '❌ Erreur création socket:', error); 
            utils.showToast('Erreur de connexion', 'error'); 
        }
    }

    function startHeartbeat() {
        if (state.heartbeatInterval) clearInterval(state.heartbeatInterval);
        state.heartbeatInterval = setInterval(() => {
            if (state.socket && state.socket.connected) {
                utils.log('debug', '💓 Heartbeat - Socket OK');
                try {
                    state.socket.emit('ping', { time: Date.now(), role: CONFIG.currentRole });
                } catch(e) {}
            }
        }, 30000);
        window.addEventListener('beforeunload', () => { if (state.heartbeatInterval) clearInterval(state.heartbeatInterval); });
    }

    function setupSocketListeners() {
        if (!state.socket) return;
        state.socket.removeAllListeners();
        
        state.socket.on('connect', () => {
            utils.log('success', `✅ Socket connectée: ${state.socket.id}`);
            utils.log('info', `📡 Transport: ${state.socket.io?.engine?.transport?.name || 'polling'}`);
            utils.updateOtherStatus(true);
            if (CONFIG.roomId) { 
                utils.log('info', `📤 Rejoindre salle: ${CONFIG.roomId}`); 
                state.socket.emit('join-room', CONFIG.roomId); 
            }
            utils.showToast('Connecté au serveur', 'success', 2000);
            state.reconnectionAttempts = 0;
        });
        
        state.socket.on('connect_error', (error) => { 
            utils.log('error', '❌ Erreur socket:', error.message); 
        });
        
        state.socket.on('disconnect', (reason) => { 
            utils.log('warn', `❌ Déconnecté: ${reason}`); 
            utils.updateOtherStatus(false); 
        });
        
        state.socket.on('reconnect', (attemptNumber) => { 
            utils.log('info', `🔄 Reconnecté après ${attemptNumber} tentatives`); 
            if (CONFIG.roomId && state.socket) state.socket.emit('join-room', CONFIG.roomId); 
            utils.showToast('Reconnecté au serveur', 'success', 2000); 
        });
        
        state.socket.on('reconnect_attempt', (attempt) => { 
            utils.log('debug', `🔄 Tentative ${attempt}`); 
            state.reconnectionAttempts = attempt; 
        });
        
        state.socket.on('room-full', (data) => { 
            utils.showToast(data?.message || 'Salle pleine', 'error', 5000); 
            setTimeout(() => window.location.href = '/', 3000); 
        });
        
        state.socket.on('user-connected', (data) => {
            const socketId = data?.id || data;
            if (!socketId || socketId === state.otherSocketId) return;
            utils.log('info', `👤 Utilisateur connecté: ${socketId}`);
            state.otherSocketId = socketId;
            utils.updateOtherStatus(true);
            const otherName = CONFIG.otherUser.name || 'Participant';
            utils.showToast(`${otherName} a rejoint la consultation.`, 'success');
            
            if (!state.peerConnection && !state.isInitiator) { 
                state.isInitiator = true; 
                webrtc.createOffer(); 
            }
        });
        
        state.socket.on('user-disconnected', (data) => {
            const socketId = data?.id || data;
            if (!socketId || socketId !== state.otherSocketId) return;
            utils.log('info', `👤 Utilisateur déconnecté: ${socketId}`);
            state.otherSocketId = null;
            state.isInitiator = false;
            utils.updateOtherStatus(false);
            const otherName = CONFIG.otherUser.name || 'Participant';
            utils.showToast(`${otherName} a quitté la consultation.`, 'warning');
            webrtc.cleanup();
            utils.showWaitingOverlay();
        });
        
        state.socket.on('ice-servers', (servers) => { 
            if (servers) { 
                CONFIG.iceServers = servers; 
                utils.log('info', '📡 Configuration ICE mise à jour'); 
            } 
        });
        
        state.socket.on('offer', (data) => webrtc.handleOffer(data));
        state.socket.on('answer', (data) => webrtc.handleAnswer(data));
        state.socket.on('ice-candidate', (data) => webrtc.handleIceCandidate(data));
        
        state.socket.on('pong', (data) => { 
            const latency = Date.now() - data.time; 
            utils.log('debug', `📊 Latence: ${latency}ms`); 
            state.connectionQuality = latency < 300 ? 'good' : latency < 800 ? 'medium' : 'poor'; 
        });
    }

    // ============================================
    // ⭐ INITIALISATION DES ÉVÉNEMENTS UI
    // ============================================
    function initEventListeners() {
        if (elements.chatSend && elements.chatInput) {
            elements.chatSend.addEventListener('click', (e) => { e.preventDefault(); chat.sendMessage(); });
            elements.chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); chat.sendMessage(); } });
        }
        if (elements.fileInput) { elements.fileInput.addEventListener('change', (e) => { chat.sendFiles(e.target.files); elements.fileInput.value = ''; }); }
        if (elements.muteAudioBtn) { elements.muteAudioBtn.addEventListener('click', (e) => { e.preventDefault(); webrtc.toggleAudio(); }); }
        if (elements.muteVideoBtn) { elements.muteVideoBtn.addEventListener('click', (e) => { e.preventDefault(); webrtc.toggleVideo(); }); }
        if (elements.leaveBtn) {
            elements.leaveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (confirm('Voulez-vous vraiment quitter la consultation ?')) {
                    if (CONFIG.consultationId) {
                        utils.log('info', `📤 Terminaison consultation ${CONFIG.consultationId}`);
                        fetch(`/Joinconsultation/endConsultationApi/${CONFIG.consultationId}`, { 
                            method: 'POST', 
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' } 
                        })
                            .then(response => response.json())
                            .then(data => { if (data.success) utils.log('success', `✅ Consultation terminée - Durée: ${data.duration} minutes`); })
                            .catch(err => utils.log('error', '❌ Erreur API fin consultation:', err))
                            .finally(() => { utils.cleanup(); setTimeout(() => window.location.href = '/', 500); });
                    } else { utils.log('warn', '⚠️ Aucun ID de consultation trouvé'); utils.cleanup(); window.location.href = '/'; }
                }
            });
        }
        if (elements.toggleChatBtn && elements.chatArea) { elements.toggleChatBtn.addEventListener('click', (e) => { e.preventDefault(); elements.chatArea.classList.add('chat-visible'); }); }
        if (elements.chatCloseBtn && elements.chatArea) { elements.chatCloseBtn.addEventListener('click', (e) => { e.preventDefault(); elements.chatArea.classList.remove('chat-visible'); }); }
        window.addEventListener('beforeunload', () => { if (state.heartbeatInterval) clearInterval(state.heartbeatInterval); if (state.socket) { try { state.socket.removeAllListeners(); state.socket.disconnect(); } catch(e) {} } webrtc.disconnect(); });
        window.addEventListener('resize', () => { if (window.innerWidth > 768 && elements.chatArea) elements.chatArea.classList.remove('chat-visible'); });
    }

    // ============================================
    // ⭐ INITIALISATION AVEC PERMISSION
    // ============================================
    async function initWithPermission() {
        try {
            const hasPermission = await PermissionManager.checkExistingPermission();
            if (!hasPermission) {
                PermissionManager.showModal();
                return new Promise((resolve, reject) => {
                    const grantBtn = document.getElementById('grantPermissionBtn');
                    const cancelBtn = document.getElementById('cancelPermissionBtn');
                    const handleGrant = async () => {
                        grantBtn.removeEventListener('click', handleGrant);
                        cancelBtn.removeEventListener('click', handleCancel);
                        try { resolve(await PermissionManager.getStream()); } catch(e) { reject(e); }
                        PermissionManager.hideModal();
                    };
                    const handleCancel = () => {
                        grantBtn.removeEventListener('click', handleGrant);
                        cancelBtn.removeEventListener('click', handleCancel);
                        reject(new Error('Permission refusée'));
                        PermissionManager.hideModal();
                    };
                    grantBtn.addEventListener('click', handleGrant);
                    cancelBtn.addEventListener('click', handleCancel);
                });
            } else {
                return await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            }
        } catch (error) { throw error; }
    }

    // ============================================
    // ⭐ INITIALISATION PRINCIPALE
    // ============================================
    async function init() {
        try {
            utils.log('info', '🚀 Initialisation consultation...');
            if (!utils.checkBrowserCompatibility()) return;
            if (!CONFIG.roomId) throw new Error('roomId manquant');
            utils.showWaitingOverlay();
            utils.log('info', '📹 Demande de permission caméra/micro...');
            let stream;
            try {
                stream = await initWithPermission();
                state.localStream = stream;
                if (elements.localVideo) { 
                    elements.localVideo.srcObject = stream; 
                    elements.localVideo.muted = true; 
                    utils.playVideoWithRetry(elements.localVideo).catch(e => utils.log('warn', 'Erreur play local:', e));
                }
                utils.log('success', '✅ Autorisation obtenue');
                utils.showToast('Caméra et micro autorisés', 'success');
            } catch (error) {
                utils.log('error', '❌ Permission refusée:', error.message);
                utils.showToast('Impossible d\'accéder à la caméra/microphone. La consultation ne peut pas continuer.', 'error', 5000);
                setTimeout(() => { if (confirm('Voulez-vous réessayer d\'autoriser la caméra et le micro ?')) window.location.reload(); else window.location.href = '/'; }, 2000);
                return;
            }
            initSocket();
            await webrtc.loadIceServers();
            initEventListeners();
            state.initializationComplete = true;
            utils.log('success', '✅ Consultation prête');
        } catch (error) {
            utils.log('error', '❌ Échec initialisation:', error.message);
            utils.showToast('Erreur de démarrage: ' + error.message, 'error', 5000);
            setTimeout(() => window.location.reload(), 3000);
        }
    }

    // Démarrage
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();

    window.consultation = { state, utils, webrtc, chat, CONFIG, version: '5.3.0' };
})();