// ============================================
// NUFOTEC CONSULTATION - CLIENT PRINCIPAL
// Version : 5.3.0 - CodeIgniter 3
// Description : Consultation vidéo peer-to-peer - Mode Polling Robuste
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
        maxReconnectionAttempts: 50,
        iceServers: null,
        debug: true,
        forcePolling: true,
        connectionTimeout: 15000,
        iceGatheringTimeout: 10000,
        // ✅ NOUVEAU: Configuration pour le renvoi d'offre
        offerRetryDelay: 3000,
        offerMaxRetries: 3
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
        connectionState: 'new',
        makingOffer: false,
        ignoreOffer: false,
        polite: false,
        remoteStream: null,
        transportReady: false,
        // ✅ NOUVEAU: État pour le renvoi d'offre
        offerRetryCount: 0,
        offerTimeout: null,
        answerReceived: false
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
            const emoji = { error: '❌', warn: '⚠️', success: '✅', info: 'ℹ️', debug: '🔍', polling: '📮', offer: '📤', answer: '📩' }[level] || '';
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
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, duration);
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
            if (elements.waitingOverlay) elements.waitingOverlay.style.display = 'none'; 
        },
        
        showWaitingOverlay: function() { 
            if (elements.waitingOverlay) elements.waitingOverlay.style.display = 'flex'; 
        },
        
        resetRemoteVideo: function() { 
            if (elements.remoteVideo) { 
                elements.remoteVideo.srcObject = null; 
                elements.remoteVideo.load(); 
            }
            state.remoteStream = null;
        },

        checkBrowserCompatibility: function() {
            const issues = [];
            if (!navigator.mediaDevices?.getUserMedia) issues.push('getUserMedia non supporté');
            if (!window.RTCPeerConnection) issues.push('WebRTC non supporté');
            if (!window.io) issues.push('Socket.IO non chargé');
            if (issues.length) { 
                this.log('error', 'Problèmes de compatibilité:', issues); 
                this.showToast('Navigateur non compatible avec WebRTC', 'error', 10000); 
                return false; 
            }
            return true;
        },

        cleanup: function() {
            if (state.heartbeatInterval) { 
                clearInterval(state.heartbeatInterval); 
                state.heartbeatInterval = null; 
            }
            if (state.socket) { 
                state.socket.removeAllListeners(); 
                state.socket.disconnect(); 
                state.socket = null; 
            }
            webrtc.cleanup();
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
            if (modal) { 
                modal.style.display = 'flex'; 
                this.loadDevices(); 
            }
        },

        hideModal: function() {
            const modal = document.getElementById('permissionModal');
            if (modal) modal.style.display = 'none';
            this.stopPreview();
        },

        loadDevices: async function() {
            try {
                const tempStream = await navigator.mediaDevices.getUserMedia({ 
                    video: true, 
                    audio: true 
                });
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
                cameraSelect.onchange = () => { 
                    this.selectedCamera = cameraSelect.value; 
                    this.startPreview(); 
                };
            }
            
            if (microSelect) {
                microSelect.innerHTML = '<option value="">Sélectionner un micro...</option>';
                this.devices.micros.forEach((mic, i) => {
                    const opt = document.createElement('option');
                    opt.value = mic.deviceId;
                    opt.textContent = mic.label || `Micro ${i + 1}`;
                    microSelect.appendChild(opt);
                });
                microSelect.onchange = () => { 
                    this.selectedMicro = microSelect.value; 
                };
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
            } catch (error) { 
                console.error('Erreur preview:', error); 
            }
        },

        stopPreview: function() {
            if (this.previewStream) { 
                this.previewStream.getTracks().forEach(t => t.stop()); 
                this.previewStream = null; 
            }
            const previewVideo = document.getElementById('previewVideo');
            const previewPlaceholder = document.querySelector('.preview-placeholder');
            if (previewVideo) { 
                previewVideo.srcObject = null; 
                previewVideo.style.display = 'none'; 
            }
            if (previewPlaceholder) previewPlaceholder.style.display = 'block';
        },

        getStream: async function() {
            const constraints = {
                video: this.selectedCamera ? { 
                    deviceId: { exact: this.selectedCamera },
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: 'user'
                } : {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: 'user'
                },
                audio: this.selectedMicro ? { 
                    deviceId: { exact: this.selectedMicro },
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                } : {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                }
            };
            
            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.permissionGranted = true;
            return stream;
        },

        showNoPermissionMessage: function() {
            const previewContainer = document.getElementById('previewContainer');
            if (previewContainer) {
                previewContainer.innerHTML = `
                    <div class="preview-placeholder" style="color: #F44336;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Permission refusée</p>
                        <small>Veuillez autoriser l'accès à la caméra et au micro.</small>
                    </div>`;
            }
        },

        checkExistingPermission: async function() {
            try {
                const testStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                testStream.getTracks().forEach(t => t.stop());
                this.permissionGranted = true;
                return true;
            } catch { 
                this.permissionGranted = false; 
                return false; 
            }
        }
    };

    // ============================================
    // ⭐ WEBRTC - VERSION ROBUSTE
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
                utils.log('polling', '📮 Mode polling - Utilisation STUN publics');
                CONFIG.iceServers = { 
                    iceServers: [
                        { urls: 'stun:stun.l.google.com:19302' },
                        { urls: 'stun:stun1.l.google.com:19302' },
                        { urls: 'stun:stun2.l.google.com:19302' },
                        { urls: 'stun:stun3.l.google.com:19302' }
                    ]
                };
                return CONFIG.iceServers;
            }
        },

        async createPeerConnection() {
            try {
                if (state.peerConnection && 
                    ['new', 'checking', 'connected', 'completed'].includes(state.peerConnection.iceConnectionState)) {
                    utils.log('info', 'ℹ️ Réutilisation PeerConnection existante');
                    return state.peerConnection;
                }

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
                    rtcpMuxPolicy: 'require',
                    sdpSemantics: 'unified-plan'
                };

                state.peerConnection = new RTCPeerConnection(config);
                this.setupPeerConnectionListeners();
                this.addLocalTracks();

                utils.log('success', '✅ PeerConnection créée');
                return state.peerConnection;
            } catch (error) {
                utils.log('error', '❌ Erreur création PeerConnection:', error);
                utils.showToast('Erreur de connexion WebRTC', 'error');
                throw error;
            }
        },

        setupPeerConnectionListeners() {
            if (!state.peerConnection) return;

            state.peerConnection.onconnectionstatechange = () => {
                const connState = state.peerConnection.connectionState;
                state.connectionState = connState;
                utils.log('info', `📊 Connection state: ${connState}`);
                
                switch(connState) {
                    case 'connected':
                        state.isConnected = true;
                        utils.closeWaitingOverlay();
                        utils.updateOtherStatus(true);
                        utils.showToast('Consultation vidéo établie', 'success');
                        // ✅ NOUVEAU: Réinitialiser le compteur de renvoi
                        state.offerRetryCount = 0;
                        state.answerReceived = true;
                        if (state.offerTimeout) {
                            clearTimeout(state.offerTimeout);
                            state.offerTimeout = null;
                        }
                        break;
                    case 'disconnected':
                        state.isConnected = false;
                        utils.updateOtherStatus(false);
                        utils.showToast('Connexion interrompue', 'warning');
                        break;
                    case 'failed':
                        state.isConnected = false;
                        utils.log('error', '❌ Connection failed');
                        this.handleConnectionFailure();
                        break;
                    case 'closed':
                        state.isConnected = false;
                        break;
                }
            };

            state.peerConnection.oniceconnectionstatechange = () => {
                const iceState = state.peerConnection.iceConnectionState;
                utils.log('info', `📊 ICE state: ${iceState}`);
                
                if (iceState === 'failed') {
                    this.handleIceFailure();
                }
            };

            state.peerConnection.onicecandidate = (event) => {
                if (event.candidate && state.otherSocketId && state.socket) {
                    const candidateStr = event.candidate.candidate;
                    if (candidateStr.includes('relay')) {
                        state.usingRelay = true;
                        utils.log('info', '🔄 Utilisation TURN (relay)');
                    }
                    
                    state.socket.emit('ice-candidate', { 
                        target: state.otherSocketId, 
                        candidate: event.candidate 
                    });
                }
            };

            state.peerConnection.ontrack = (event) => {
                utils.log('info', `📹 Track reçu: ${event.track.kind}`);
                
                if (!state.remoteStream) {
                    state.remoteStream = new MediaStream();
                }
                
                state.remoteStream.addTrack(event.track);
                
                if (elements.remoteVideo) {
                    if (elements.remoteVideo.srcObject !== state.remoteStream) {
                        elements.remoteVideo.srcObject = state.remoteStream;
                        utils.log('success', '✅ Stream distant attaché');
                    }
                    
                    this.playRemoteVideo();
                }
                
                if (event.track.kind === 'video') {
                    state.isConnected = true;
                    utils.closeWaitingOverlay();
                    utils.updateOtherStatus(true);
                }
            };

            state.peerConnection.ondatachannel = (event) => {
                utils.log('info', '📡 DataChannel reçu');
                chat.setDataChannel(event.channel);
            };

            state.peerConnection.onnegotiationneeded = async () => {
                utils.log('info', '🔄 Négociation nécessaire');
                
                if (state.makingOffer) {
                    utils.log('debug', 'ℹ️ Négociation déjà en cours, ignorée');
                    return;
                }
                
                try {
                    state.makingOffer = true;
                    await this.createOffer();
                } catch (err) {
                    utils.log('error', '❌ Erreur négociation:', err);
                } finally {
                    state.makingOffer = false;
                }
            };
        },

        async playRemoteVideo() {
            if (!elements.remoteVideo) return;
            
            const attemptPlay = async (retryCount = 0) => {
                try {
                    if (elements.remoteVideo.paused) {
                        await elements.remoteVideo.play();
                        utils.log('success', '▶️ Lecture vidéo démarrée');
                    }
                } catch (error) {
                    utils.log('warn', `⚠️ Erreur lecture vidéo (tentative ${retryCount + 1}):`, error.name);
                    
                    if (error.name === 'NotAllowedError' && retryCount < 3) {
                        elements.remoteVideo.muted = true;
                        setTimeout(() => attemptPlay(retryCount + 1), 200);
                    } else if (retryCount < 3) {
                        setTimeout(() => attemptPlay(retryCount + 1), 500);
                    } else {
                        utils.log('error', '❌ Impossible de démarrer la vidéo après plusieurs tentatives');
                    }
                }
            };
            
            attemptPlay();
        },

        addLocalTracks() {
            if (!state.localStream || !state.peerConnection) return;

            const senders = state.peerConnection.getSenders();
            
            state.localStream.getTracks().forEach(track => {
                const alreadyAdded = senders.some(sender => sender.track === track);
                if (alreadyAdded) {
                    utils.log('debug', `ℹ️ Track ${track.kind} déjà présent`);
                    return;
                }

                try {
                    state.peerConnection.addTrack(track, state.localStream);
                    utils.log('debug', `➕ Track ajouté: ${track.kind}`);
                } catch (error) { 
                    utils.log('error', `❌ Erreur ajout track ${track.kind}:`, error); 
                }
            });
        },

        // ✅ CORRECTION: Création d'offre avec mécanisme de renvoi
        async createOffer() {
            try {
                if (!state.peerConnection) {
                    await this.createPeerConnection();
                }

                if (state.makingOffer) {
                    utils.log('warn', '⚠️ Offre déjà en cours de création');
                    return;
                }

                // ✅ NOUVEAU: Vérifier si on a déjà reçu une réponse
                if (state.answerReceived) {
                    utils.log('info', '✅ Réponse déjà reçue, pas besoin de renvoyer');
                    return;
                }

                state.makingOffer = true;
                state.offerRetryCount++;
                utils.log('offer', `🎯 Création offre (tentative ${state.offerRetryCount}/${CONFIG.offerMaxRetries})...`);

                if (state.isInitiator && !state.dataChannel) {
                    const dataChannel = state.peerConnection.createDataChannel('chat', { 
                        ordered: true, 
                        maxRetransmits: 3 
                    });
                    chat.setDataChannel(dataChannel);
                }

                const offer = await state.peerConnection.createOffer({
                    offerToReceiveAudio: true,
                    offerToReceiveVideo: true
                });

                await state.peerConnection.setLocalDescription(offer);
                await this.waitForIceGathering();

                if (!state.otherSocketId || !state.socket) {
                    throw new Error('Socket non disponible');
                }

                const finalOffer = state.peerConnection.localDescription;
                utils.log('offer', `📤 Envoi offre à: ${state.otherSocketId}`);
                
                state.socket.emit('offer', { 
                    target: state.otherSocketId, 
                    sdp: finalOffer 
                });

                // ✅ NOUVEAU: Planifier le renvoi si pas de réponse
                if (state.offerTimeout) {
                    clearTimeout(state.offerTimeout);
                }
                
                if (state.offerRetryCount < CONFIG.offerMaxRetries && !state.answerReceived) {
                    state.offerTimeout = setTimeout(() => {
                        if (!state.answerReceived && state.otherSocketId) {
                            utils.log('offer', `⏰ Pas de réponse, tentative de renvoi ${state.offerRetryCount + 1}...`);
                            state.makingOffer = false;
                            this.createOffer();
                        }
                    }, CONFIG.offerRetryDelay);
                } else if (state.offerRetryCount >= CONFIG.offerMaxRetries) {
                    utils.log('error', '❌ Nombre maximum de tentatives atteint');
                    utils.showToast('Impossible d\'établir la connexion. Veuillez rafraîchir.', 'error');
                }

            } catch (error) { 
                utils.log('error', '❌ Erreur création offre:', error); 
                utils.showToast('Erreur de connexion', 'error'); 
            } finally {
                state.makingOffer = false;
            }
        },

        waitForIceGathering() {
            return new Promise((resolve) => {
                if (!state.peerConnection) {
                    resolve();
                    return;
                }

                if (state.peerConnection.iceGatheringState === 'complete') {
                    resolve();
                    return;
                }

                const checkState = () => {
                    if (state.peerConnection.iceGatheringState === 'complete') {
                        cleanup();
                        resolve();
                    }
                };

                const timeout = setTimeout(() => {
                    utils.log('warn', '⚠️ Timeout gathering ICE, envoi avec candidats partiels');
                    cleanup();
                    resolve();
                }, CONFIG.iceGatheringTimeout);

                const cleanup = () => {
                    clearTimeout(timeout);
                    state.peerConnection.removeEventListener('icegatheringstatechange', checkState);
                };

                state.peerConnection.addEventListener('icegatheringstatechange', checkState);
            });
        },

       // ✅ CORRECTION: Gestion robuste des offres avec logs de débogage et meilleure gestion des collisions
async handleOffer(data) {
    // ✅ NOUVEAU: Logs de débogage détaillés
    utils.log('offer', '📨 handleOffer appelé avec:', {
        sender: data?.sender,
        sdpType: data?.sdp?.type,
        hasSdp: !!data?.sdp?.sdp,
        sdpLength: data?.sdp?.sdp?.length || 0
    });
    
    if (!data?.sdp || !data?.sender) { 
        utils.log('error', '❌ Offre invalide - données manquantes'); 
        return; 
    }

    utils.log('offer', `📩 Offre reçue de: ${data.sender}`);

    try {
        const myId = state.socket?.id || '';
        const otherId = data.sender;
        state.polite = myId < otherId;

        const readyForOffer = !state.makingOffer && 
            (state.peerConnection?.signalingState === 'stable' || 
             state.peerConnection?.signalingState === 'have-remote-offer');

        const offerCollision = !readyForOffer;

        if (offerCollision) {
            if (!state.polite) {
                // ✅ CORRECTION: Au lieu d'ignorer l'offre, réinitialiser et accepter
                utils.log('warn', '⚔️ Collision d\'offres - je suis impoli, je réinitialise et accepte cette offre');
                
                // Réinitialiser l'état pour accepter la nouvelle offre
                if (state.peerConnection) {
                    const pc = state.peerConnection;
                    if (pc.signalingState !== 'closed') {
                        await pc.setLocalDescription({type: "rollback"});
                        utils.log('info', '🔄 Rollback effectué pour accepter la nouvelle offre');
                    }
                }
                
                // Réinitialiser les compteurs
                state.makingOffer = false;
                state.offerRetryCount = 0;
                if (state.offerTimeout) {
                    clearTimeout(state.offerTimeout);
                    state.offerTimeout = null;
                }
                
                // Continuer le traitement normal
            } else {
                utils.log('info', '⚔️ Collision d\'offres - je suis poli, j\'accepte cette offre');
            }
        }

        if (!state.peerConnection) {
            utils.log('info', '🔧 Création PeerConnection pour répondre...');
            await this.createPeerConnection();
        }

        const pc = state.peerConnection;
        const signalingState = pc.signalingState;
        utils.log('info', `📊 État de signalisation: ${signalingState}`);

        if (signalingState === 'have-local-offer') {
            if (state.polite) {
                utils.log('info', '🔄 Rollback de mon offre locale');
                await pc.setLocalDescription({type: "rollback"});
            } else {
                // ✅ CORRECTION: Si impoli mais qu'on est là, on a déjà fait le rollback plus haut
                if (!offerCollision || (offerCollision && !state.polite)) {
                    utils.log('warn', '⚠️ État inattendu have-local-offer, tentative de rollback');
                    try {
                        await pc.setLocalDescription({type: "rollback"});
                    } catch (err) {
                        utils.log('error', '❌ Échec du rollback:', err);
                        return;
                    }
                }
            }
        }

        utils.log('info', '📥 Application de la description distante...');
        await pc.setRemoteDescription(new RTCSessionDescription(data.sdp));
        utils.log('success', '✅ Description distante définie');

        await this.processPendingCandidates();

        utils.log('info', '📝 Création de la réponse...');
        const answer = await pc.createAnswer();
        await pc.setLocalDescription(answer);
        await this.waitForIceGathering();

        const finalAnswer = pc.localDescription;
        utils.log('answer', `📤 Envoi réponse à: ${data.sender}`);
        
        if (state.socket) {
            state.socket.emit('answer', { 
                target: data.sender, 
                sdp: finalAnswer 
            });
            utils.log('success', '✅ Réponse envoyée avec succès');
        }

    } catch (error) { 
        utils.log('error', '❌ Erreur traitement offre:', error); 
    }
}

        // ✅ CORRECTION: Gestion des réponses avec flag
        async handleAnswer(data) {
            if (!data?.sdp || !data?.sender) { 
                utils.log('error', '❌ Réponse invalide'); 
                return; 
            }

            utils.log('answer', `📩 Réponse reçue de: ${data.sender}`);

            // ✅ NOUVEAU: Marquer qu'on a reçu une réponse
            state.answerReceived = true;
            if (state.offerTimeout) {
                clearTimeout(state.offerTimeout);
                state.offerTimeout = null;
            }

            if (!state.peerConnection) { 
                utils.log('error', '❌ Pas de PeerConnection'); 
                return; 
            }

            try {
                const pc = state.peerConnection;
                const signalingState = pc.signalingState;
                
                utils.log('info', `📊 État de signalisation: ${signalingState}`);

                if (signalingState === 'have-local-offer') {
                    await pc.setRemoteDescription(new RTCSessionDescription(data.sdp));
                    utils.log('success', '✅ Réponse appliquée');
                    await this.processPendingCandidates();
                } else if (signalingState === 'stable') {
                    utils.log('warn', '⚠️ Déjà en état stable, réponse ignorée');
                } else {
                    utils.log('warn', `⚠️ État inattendu ${signalingState}, réponse mise en attente`);
                    setTimeout(() => this.handleAnswer(data), 500);
                }

            } catch (error) { 
                utils.log('error', '❌ Erreur traitement réponse:', error); 
            }
        },

        async handleIceCandidate(data) {
            if (!data?.candidate) return;

            try {
                const pc = state.peerConnection;
                
                if (pc && pc.remoteDescription && pc.remoteDescription.type) {
                    await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
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
                catch (error) { 
                    utils.log('error', '❌ Erreur ajout candidat en attente:', error); 
                }
            }
        },

        async handleConnectionFailure() {
            utils.log('error', '❌ Échec de connexion détecté');
            utils.showToast('Problème de connexion - tentative de reconnexion...', 'warning');
            
            this.cleanup();
            utils.showWaitingOverlay();
            
            // ✅ NOUVEAU: Réinitialiser pour permettre une nouvelle tentative
            state.offerRetryCount = 0;
            state.answerReceived = false;
            
            if (state.isInitiator && state.otherSocketId) {
                setTimeout(() => {
                    utils.log('info', '🔄 Tentative de reconnexion...');
                    this.createOffer();
                }, 2000);
            }
        },

        async handleIceFailure() {
            if (!state.peerConnection) return;

            try {
                utils.log('info', '🔄 Redémarrage ICE avec iceRestart...');
                
                const offer = await state.peerConnection.createOffer({ 
                    iceRestart: true,
                    offerToReceiveAudio: true,
                    offerToReceiveVideo: true
                });
                
                await state.peerConnection.setLocalDescription(offer);
                await this.waitForIceGathering();

                if (state.otherSocketId && state.socket) {
                    state.socket.emit('offer', { 
                        target: state.otherSocketId, 
                        sdp: state.peerConnection.localDescription 
                    });
                    utils.log('info', '📤 Offre ICE restart envoyée');
                }
            } catch (error) {
                utils.log('error', '❌ Échec redémarrage ICE:', error);
            }
        },

        toggleAudio: function() {
            if (!state.localStream) { 
                utils.showToast('Aucun flux audio disponible', 'warning'); 
                return false; 
            }
            
            state.audioEnabled = !state.audioEnabled;
            state.localStream.getAudioTracks().forEach(track => {
                track.enabled = state.audioEnabled;
            });
            
            if (elements.muteAudioBtn) {
                const isMuted = !state.audioEnabled;
                elements.muteAudioBtn.classList.toggle('muted', isMuted);
                elements.muteAudioBtn.innerHTML = state.audioEnabled ? 
                    '<i class="fas fa-microphone"></i>' : 
                    '<i class="fas fa-microphone-slash"></i>';
                elements.muteAudioBtn.title = state.audioEnabled ? 'Couper le micro' : 'Activer le micro';
            }
            
            utils.showToast(state.audioEnabled ? 'Micro activé' : 'Micro coupé', 'info', 2000);
            return state.audioEnabled;
        },

        toggleVideo: function() {
            if (!state.localStream) { 
                utils.showToast('Aucun flux vidéo disponible', 'warning'); 
                return false; 
            }
            
            state.videoEnabled = !state.videoEnabled;
            state.localStream.getVideoTracks().forEach(track => {
                track.enabled = state.videoEnabled;
            });
            
            if (elements.muteVideoBtn) {
                const isOff = !state.videoEnabled;
                elements.muteVideoBtn.classList.toggle('off', isOff);
                elements.muteVideoBtn.innerHTML = state.videoEnabled ? 
                    '<i class="fas fa-video"></i>' : 
                    '<i class="fas fa-video-slash"></i>';
                elements.muteVideoBtn.title = state.videoEnabled ? 'Couper la caméra' : 'Activer la caméra';
            }
            
            utils.showToast(state.videoEnabled ? 'Caméra activée' : 'Caméra coupée', 'info', 2000);
            return state.videoEnabled;
        },

        cleanup: function() {
            if (state.peerConnection) { 
                state.peerConnection.close(); 
                state.peerConnection = null; 
            }
            state.dataChannel = null;
            state.remoteStream = null;
            utils.resetRemoteVideo();
            state.pendingIceCandidates = [];
            state.usingRelay = false;
            state.isConnected = false;
            state.connectionState = 'new';
            state.makingOffer = false;
            state.ignoreOffer = false;
            // ✅ NOUVEAU: Nettoyer les timeouts
            if (state.offerTimeout) {
                clearTimeout(state.offerTimeout);
                state.offerTimeout = null;
            }
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
            if (!channel) { 
                utils.log('error', 'Tentative de setDataChannel avec null'); 
                return; 
            }
            state.dataChannel = channel;
            this.setupDataChannelListeners();
        },

        setupDataChannelListeners: function() {
            if (!state.dataChannel) return;
            
            state.dataChannel.onopen = () => { 
                utils.log('success', '✅ DataChannel ouvert'); 
                utils.showToast('Chat connecté', 'success'); 
            };
            
            state.dataChannel.onclose = () => {
                utils.log('warn', '❌ DataChannel fermé');
                state.dataChannel = null;
            };
            
            state.dataChannel.onerror = (error) => { 
                utils.log('error', '⚠️ Erreur DataChannel:', error); 
                utils.showToast('Erreur de chat', 'error'); 
            };
            
            state.dataChannel.onmessage = (event) => this.handleIncomingMessage(event.data);
        },

        handleIncomingMessage: function(data) {
            try {
                const message = JSON.parse(data);
                if (!message || !message.type) { 
                    utils.log('warn', 'Message invalide reçu'); 
                    return; 
                }
                
                switch (message.type) {
                    case 'chat': 
                        this.displayMessage(message.message || '', message.sender, message.timestamp || Date.now()); 
                        break;
                    case 'file': 
                        this.displayFile(message.file || {}, message.sender, message.timestamp || Date.now()); 
                        break;
                    default: 
                        utils.log('debug', 'Type de message inconnu:', message.type);
                }
            } catch (error) { 
                utils.log('error', 'Erreur parsing message:', error); 
            }
        },

        sendMessage: function() {
            if (!state.dataChannel || state.dataChannel.readyState !== 'open') { 
                utils.showToast('Le chat n\'est pas encore connecté', 'warning'); 
                return false; 
            }
            
            if (!elements.chatInput) return false;
            
            const text = elements.chatInput.value.trim();
            if (!text) return false;
            
            const message = { 
                type: 'chat', 
                sender: utils.getCurrentUserId(), 
                timestamp: Date.now(), 
                message: text.substring(0, 500) 
            };
            
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
                    const fileInfo = { 
                        name: file.name, 
                        type: file.type, 
                        size: file.size, 
                        url: fileUrl 
                    };
                    const message = { 
                        type: 'file', 
                        sender: utils.getCurrentUserId(), 
                        timestamp: Date.now(), 
                        file: fileInfo 
                    };
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
            
            if (isOwn) { 
                messageDiv.appendChild(bubble); 
                messageDiv.appendChild(avatar); 
            } else { 
                messageDiv.appendChild(avatar); 
                messageDiv.appendChild(bubble); 
            }
            
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
            
            if (isOwn) { 
                messageDiv.appendChild(bubble); 
                messageDiv.appendChild(avatar); 
            } else { 
                messageDiv.appendChild(avatar); 
                messageDiv.appendChild(bubble); 
            }
            
            elements.chatMessages.appendChild(messageDiv);
            elements.chatMessages.scrollTop = elements.chatMessages.scrollHeight;
        }
    };

    // ============================================
    // ⭐ SOCKET.IO - MODE POLLING UNIQUEMENT
    // ============================================
    function initSocket() {
        if (!window.io) { 
            utils.log('error', 'Socket.IO non chargé'); 
            utils.showToast('Erreur de chargement du chat', 'error'); 
            return; 
        }

        utils.log('polling', '📮 Initialisation socket en mode POLLING...');
        
        try {
            const socketOptions = {
                path: CONFIG.socketPath,
                transports: ['polling'],
                withCredentials: true,
                reconnection: true,
                reconnectionAttempts: CONFIG.maxReconnectionAttempts,
                reconnectionDelay: 2000,
                reconnectionDelayMax: 10000,
                timeout: 30000,
                autoConnect: true,
                forceNew: true,
                transportOptions: {
                    polling: {
                        extraHeaders: {
                            'X-Client-Version': '5.3.0',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        xhr: {
                            withCredentials: true
                        }
                    }
                }
            };

            state.socket = io(CONFIG.socketUrl, socketOptions);

            state.socket.io.on("transport", (transport) => { 
                utils.log('polling', `📮 Transport actif: ${transport.name}`); 
            });

            state.socket.io.on("upgrade_error", (err) => {
                utils.log('polling', '📮 Upgrade WebSocket échoué - maintien du polling');
            });

            state.socket.io.on("reconnect_attempt", (attempt) => { 
                utils.log('polling', `📮 Tentative de reconnexion ${attempt}/${CONFIG.maxReconnectionAttempts}`); 
                state.reconnectionAttempts = attempt; 
            });

            state.socket.io.on("reconnect_error", (error) => { 
                utils.log('error', '❌ Erreur de reconnexion:', error.message); 
            });

            state.socket.io.on("reconnect_failed", () => {
                utils.log('error', `❌ Échec de reconnexion après ${CONFIG.maxReconnectionAttempts} tentatives`);
                utils.showToast('Connexion perdue. Veuillez recharger la page.', 'error');
                setTimeout(() => { 
                    if (confirm('La connexion au serveur est perdue. Recharger la page ?')) {
                        window.location.reload(); 
                    }
                }, 5000);
            });

            state.socket.io.engine.on("packet", (packet) => {
                if (packet.type === "ping") {
                    state.transportReady = true;
                }
            });

            setupSocketListeners();
            startHeartbeat();
            
            utils.log('polling', '📮 Socket configuré en mode POLLING uniquement');
            
        } catch (error) { 
            utils.log('error', '❌ Erreur création socket:', error); 
            utils.showToast('Erreur de connexion', 'error'); 
        }
    }

    function startHeartbeat() {
        if (state.heartbeatInterval) clearInterval(state.heartbeatInterval);
        
        state.heartbeatInterval = setInterval(() => {
            if (state.socket && state.socket.connected) {
                state.socket.emit('ping', { time: Date.now(), role: CONFIG.currentRole });
                utils.log('debug', '💓 Heartbeat envoyé (polling)');
            } else if (state.socket && !state.socket.connected) {
                utils.log('warn', '⚠️ Heartbeat - Socket déconnecté');
                
                if (!state.socket.io._reconnecting) {
                    utils.log('info', '🔄 Tentative de reconnexion forcée...');
                    state.socket.connect();
                }
            }
        }, 20000);

        window.addEventListener('beforeunload', () => { 
            if (state.heartbeatInterval) clearInterval(state.heartbeatInterval); 
        });
    }

    // ✅ CORRECTION MAJEURE: Gestion améliorée de user-connected
    function setupSocketListeners() {
        if (!state.socket) return;
        
        state.socket.removeAllListeners();

        state.socket.on('connect', () => {
            utils.log('success', `✅ Socket connecté (polling): ${state.socket.id}`);
            state.transportReady = true;
            utils.updateOtherStatus(true);
            
            if (CONFIG.roomId) { 
                utils.log('info', `📤 Rejoindre salle: ${CONFIG.roomId}`); 
                state.socket.emit('join-room', CONFIG.roomId); 
            }
            
            utils.showToast('Connecté au serveur (mode polling)', 'success', 3000);
            state.reconnectionAttempts = 0;
        });

        state.socket.on('connect_error', (error) => { 
            utils.log('error', '❌ Erreur connexion socket:', error.message); 
            
            if (state.reconnectionAttempts > 5 && state.reconnectionAttempts % 5 === 0) {
                utils.showToast('Problème de connexion persistant. Vérifiez votre réseau.', 'warning', 5000); 
            }
        });

        state.socket.on('disconnect', (reason) => { 
            utils.log('warn', `❌ Déconnecté: ${reason}`); 
            state.transportReady = false;
            utils.updateOtherStatus(false);
            
            if (reason === 'io server disconnect' || reason === 'transport close') {
                setTimeout(() => { 
                    if (state.socket && !state.socket.connected) {
                        utils.log('info', '🔄 Reconnexion après déconnexion serveur...');
                        state.socket.connect(); 
                    }
                }, 2000); 
            }
        });

        state.socket.on('reconnect', (attemptNumber) => { 
            utils.log('success', `🔄 Reconnecté après ${attemptNumber} tentatives (polling)`); 
            if (CONFIG.roomId && state.socket) {
                state.socket.emit('join-room', CONFIG.roomId); 
            }
            utils.showToast('Reconnecté au serveur', 'success', 2000); 
        });

        state.socket.on('reconnect_attempt', (attempt) => { 
            utils.log('polling', `📮 Tentative ${attempt} (polling)`); 
            state.reconnectionAttempts = attempt; 
        });

        state.socket.on('error', (error) => {
            utils.log('error', '❌ Erreur socket générale:', error);
        });

        state.socket.on('room-full', (data) => { 
            utils.showToast(data?.message || 'Salle pleine', 'error', 5000); 
            setTimeout(() => window.location.href = '/', 3000); 
        });

        // ✅ CORRECTION CRITIQUE: Gestion robuste de user-connected avec délai
        state.socket.on('user-connected', (data) => {
            const socketId = data?.id || data;
            
            if (!socketId) {
                utils.log('error', '❌ ID socket invalide reçu');
                return;
            }

            if (socketId === state.socket.id) {
                utils.log('debug', 'ℹ️ Ignoré: propre connexion');
                return;
            }

            // ✅ CORRECTION: Mettre à jour même si changement d'ID (reconnexion)
            if (state.otherSocketId && state.otherSocketId !== socketId) {
                utils.log('warn', `⚠️ Changement d'ID détecté: ${state.otherSocketId} → ${socketId}`);
                webrtc.cleanup();
                // Réinitialiser les états de connexion
                state.offerRetryCount = 0;
                state.answerReceived = false;
                if (state.offerTimeout) {
                    clearTimeout(state.offerTimeout);
                    state.offerTimeout = null;
                }
            }

            utils.log('info', `👤 Utilisateur connecté: ${socketId}`);
            state.otherSocketId = socketId;
            utils.updateOtherStatus(true);
            
            const otherName = CONFIG.otherUser.name || 'Participant';
            utils.showToast(`${otherName} a rejoint la consultation.`, 'success');

            // Décision d'initiateur
            const myId = state.socket.id;
            const otherId = socketId;
            
            state.isInitiator = myId < otherId;
            state.polite = state.isInitiator;

            utils.log('info', `🎯 Rôle: ${state.isInitiator ? 'Initiateur' : 'Récepteur'} (mon ID: ${myId}, autre: ${otherId})`);

            // ✅ CORRECTION: Délai plus long pour s'assurer que les deux sont prêts
            if (state.isInitiator) {
                // Attendre que le récepteur soit prêt
                setTimeout(() => {
                    utils.log('info', '🚀 Lancement de la création d\'offre...');
                    // Forcer la création même si une connexion existe (elle a été nettoyée si changement d'ID)
                    if (!state.peerConnection || state.peerConnection.signalingState === 'closed') {
                        webrtc.createOffer();
                    } else if (state.peerConnection.signalingState === 'stable') {
                        // Si stable mais pas connecté, c'est une reconnexion
                        webrtc.cleanup();
                        setTimeout(() => webrtc.createOffer(), 500);
                    }
                }, 2000); // ✅ Délai de 2 secondes pour laisser le temps au récepteur de s'initialiser
            }
        });

        state.socket.on('user-disconnected', (data) => {
            const socketId = data?.id || data;
            
            if (!socketId || socketId !== state.otherSocketId) return;
            
            utils.log('info', `👤 Utilisateur déconnecté: ${socketId}`);
            state.otherSocketId = null;
            state.isInitiator = false;
            state.polite = false;
            state.answerReceived = false;
            state.offerRetryCount = 0;
            if (state.offerTimeout) {
                clearTimeout(state.offerTimeout);
                state.offerTimeout = null;
            }
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

        state.socket.on('offer', (data) => {
            utils.log('offer', '📨 Événement offer reçu du serveur');
            if (data?.sdp && data?.sender) {
                webrtc.handleOffer(data);
            } else {
                utils.log('error', '❌ Offre malformée reçue:', data);
            }
        });

        state.socket.on('answer', (data) => {
            utils.log('answer', '📨 Événement answer reçu du serveur');
            if (data?.sdp && data?.sender) {
                webrtc.handleAnswer(data);
            } else {
                utils.log('error', '❌ Réponse malformée reçue:', data);
            }
        });

        state.socket.on('ice-candidate', (data) => {
            if (data?.candidate) {
                webrtc.handleIceCandidate(data);
            }
        });

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
            elements.chatSend.addEventListener('click', (e) => { 
                e.preventDefault(); 
                chat.sendMessage(); 
            });
            
            elements.chatInput.addEventListener('keypress', (e) => { 
                if (e.key === 'Enter' && !e.shiftKey) { 
                    e.preventDefault(); 
                    chat.sendMessage(); 
                } 
            });
        }

        if (elements.fileInput) { 
            elements.fileInput.addEventListener('change', (e) => { 
                chat.sendFiles(e.target.files); 
                elements.fileInput.value = ''; 
            }); 
        }

        if (elements.muteAudioBtn) { 
            elements.muteAudioBtn.addEventListener('click', (e) => { 
                e.preventDefault(); 
                webrtc.toggleAudio(); 
            }); 
        }
        
        if (elements.muteVideoBtn) { 
            elements.muteVideoBtn.addEventListener('click', (e) => { 
                e.preventDefault(); 
                webrtc.toggleVideo(); 
            }); 
        }

        if (elements.leaveBtn) {
            elements.leaveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                
                if (confirm('Voulez-vous vraiment quitter la consultation ?')) {
                    endConsultation();
                }
            });
        }

        if (elements.toggleChatBtn && elements.chatArea) { 
            elements.toggleChatBtn.addEventListener('click', (e) => { 
                e.preventDefault(); 
                elements.chatArea.classList.add('chat-visible'); 
            }); 
        }
        
        if (elements.chatCloseBtn && elements.chatArea) { 
            elements.chatCloseBtn.addEventListener('click', (e) => { 
                e.preventDefault(); 
                elements.chatArea.classList.remove('chat-visible'); 
            }); 
        }

        window.addEventListener('beforeunload', (e) => {
            if (state.heartbeatInterval) clearInterval(state.heartbeatInterval); 
            if (state.socket) { 
                state.socket.removeAllListeners(); 
                state.socket.disconnect(); 
            } 
            webrtc.disconnect();
        });

        window.addEventListener('resize', () => { 
            if (window.innerWidth > 768 && elements.chatArea) {
                elements.chatArea.classList.remove('chat-visible'); 
            }
        });
    }

    async function endConsultation() {
        if (CONFIG.consultationId) {
            utils.log('info', `📤 Terminaison consultation ${CONFIG.consultationId}`);
            
            try {
                const response = await fetch(`/Joinconsultation/endConsultationApi/${CONFIG.consultationId}`, { 
                    method: 'POST', 
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest', 
                        'Content-Type': 'application/json' 
                    } 
                });
                
                const data = await response.json();
                if (data.success) {
                    utils.log('success', `✅ Consultation terminée - Durée: ${data.duration} minutes`);
                }
            } catch (err) {
                utils.log('error', '❌ Erreur API fin consultation:', err);
            }
        }
        
        utils.cleanup();
        setTimeout(() => window.location.href = '/', 500);
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
                    
                    if (!grantBtn || !cancelBtn) {
                        reject(new Error('Boutons de permission non trouvés'));
                        return;
                    }

                    const handleGrant = async () => {
                        cleanup();
                        try { 
                            const stream = await PermissionManager.getStream();
                            PermissionManager.hideModal();
                            resolve(stream); 
                        } catch(e) { 
                            reject(e); 
                        }
                    };

                    const handleCancel = () => {
                        cleanup();
                        reject(new Error('Permission refusée par l\'utilisateur'));
                        PermissionManager.hideModal();
                    };

                    const cleanup = () => {
                        grantBtn.removeEventListener('click', handleGrant);
                        cancelBtn.removeEventListener('click', handleCancel);
                    };

                    grantBtn.addEventListener('click', handleGrant);
                    cancelBtn.addEventListener('click', handleCancel);
                });
            } else {
                return await navigator.mediaDevices.getUserMedia({ 
                    video: { width: 1280, height: 720 }, 
                    audio: true 
                });
            }
        } catch (error) { 
            throw error; 
        }
    }

    // ============================================
    // ⭐ INITIALISATION PRINCIPALE
    // ============================================
    async function init() {
        try {
            utils.log('info', '🚀 Initialisation consultation v5.3.0 (Mode Polling + Retry)...');
            
            if (!utils.checkBrowserCompatibility()) {
                throw new Error('Navigateur non compatible');
            }
            
            if (!CONFIG.roomId) {
                throw new Error('roomId manquant - impossible de rejoindre une consultation');
            }

            utils.showWaitingOverlay();
            utils.log('info', '📹 Demande de permission caméra/micro...');

            let stream;
            try {
                stream = await initWithPermission();
                state.localStream = stream;
                
                if (elements.localVideo) { 
                    elements.localVideo.srcObject = stream; 
                    elements.localVideo.muted = true; 
                    elements.localVideo.play().catch(e => utils.log('warn', 'Erreur play local:', e)); 
                }
                
                utils.log('success', '✅ Autorisation média obtenue');
                utils.showToast('Caméra et micro activés', 'success');
            } catch (error) {
                utils.log('error', '❌ Permission refusée:', error.message);
                utils.showToast('Accès caméra/microphone requis pour la consultation', 'error', 5000);
                
                setTimeout(() => { 
                    if (confirm('Voulez-vous réessayer d\'autoriser la caméra et le micro ?')) {
                        window.location.reload(); 
                    } else {
                        window.location.href = '/';
                    }
                }, 2000);
                return;
            }

            initSocket();
            await webrtc.loadIceServers();
            initEventListeners();
            
            state.initializationComplete = true;
            utils.log('success', '✅ Consultation prête - en attente de l\'autre participant (mode polling + retry)');
            
        } catch (error) {
            utils.log('error', '❌ Échec initialisation:', error.message);
            utils.showToast('Erreur de démarrage: ' + error.message, 'error', 5000);
            setTimeout(() => window.location.reload(), 3000);
        }
    }

    // Démarrage
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Exposition globale pour debug
    window.consultation = { 
        state, 
        utils, 
        webrtc, 
        chat, 
        CONFIG, 
        version: '5.3.0-polling-retry',
        restart: () => {
            webrtc.cleanup();
            state.offerRetryCount = 0;
            state.answerReceived = false;
            if (state.offerTimeout) {
                clearTimeout(state.offerTimeout);
                state.offerTimeout = null;
            }
            if (state.otherSocketId) {
                setTimeout(() => webrtc.createOffer(), 500);
            }
        }
    };
})();