// ============================================
// NUFOTEC CONSULTATION - VERSION SANS CHAT v6.0.0
// Optimisée pour serveur mutualisé
// ============================================

(function() {
    'use strict';

    // ============================================
    // CONFIGURATION
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
        maxReconnectionAttempts: 10,
        iceServers: null,
        debug: true,
        forcePolling: true,
        connectionTimeout: 20000,
        iceGatheringTimeout: 15000,
        offerRetryDelay: 5000,
        offerMaxRetries: 2,
        heartbeatInterval: 30000
    };

    // ============================================
    // ÉTAT DE L'APPLICATION
    // ============================================
    const state = {
        socket: null,
        peerConnection: null,
        localStream: null,
        remoteStream: null,
        otherSocketId: null,
        isInitiator: false,
        isConnected: false,
        isConnecting: false,
        pendingIceCandidates: [],
        audioEnabled: true,
        videoEnabled: true,
        heartbeatInterval: null,
        reconnectTimer: null,
        reconnectAttempts: 0,
        offerRetryCount: 0,
        answerReceived: false,
        isProcessingOffer: false,
        isProcessingAnswer: false,
        connectionStable: false,
        degradedMode: false
    };

    // ============================================
    // ÉLÉMENTS DOM
    // ============================================
    const elements = {
        localVideo: document.getElementById('local-video'),
        remoteVideo: document.getElementById('remote-video'),
        muteAudioBtn: document.getElementById('mute-audio'),
        muteVideoBtn: document.getElementById('mute-video'),
        leaveBtn: document.getElementById('leave-call'),
        toggleChatBtn: document.getElementById('toggle-chat'),
        chatArea: document.getElementById('chat-area'),
        otherStatus: document.getElementById('other-status'),
        waitingOverlay: document.getElementById('waiting-overlay'),
        offlineIndicator: document.getElementById('offline-indicator'),
        toastContainer: document.getElementById('toast-container')
    };

    // ============================================
    // UTILITAIRES
    // ============================================
    const utils = {
        log: function(level, ...args) {
            if (!CONFIG.debug && level !== 'error') return;
            const prefix = `[${new Date().toLocaleTimeString()}]`;
            const emoji = { 
                error: '❌', warn: '⚠️', success: '✅', info: 'ℹ️', 
                debug: '🔍', polling: '📮', offer: '📤', answer: '📩', 
                track: '🎥', reconnect: '🔄'
            }[level] || '';
            const logFn = level === 'error' ? console.error : level === 'warn' ? console.warn : console.log;
            logFn(prefix, emoji, ...args);
        },

        showToast: function(message, type = 'info', duration = 4000) {
            if (!elements.toastContainer) return;
            
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            const icon = { 
                success: 'check-circle', error: 'exclamation-circle', 
                warning: 'exclamation-triangle', info: 'info-circle' 
            }[type] || 'info-circle';
            toast.innerHTML = `<i class="fas fa-${icon}"></i><span>${this.escapeHtml(message)}</span>`;
            elements.toastContainer.appendChild(toast);
            
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.opacity = '0';
                    setTimeout(() => {
                        if (toast.parentNode) toast.remove();
                    }, 300);
                }
            }, duration);
        },

        escapeHtml: function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
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
                if (elements.remoteVideo.srcObject) {
                    const oldStream = elements.remoteVideo.srcObject;
                    if (oldStream && oldStream.getTracks) {
                        oldStream.getTracks().forEach(track => {
                            track.onunmute = null;
                            track.onmute = null;
                        });
                    }
                }
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
            if (state.reconnectTimer) {
                clearTimeout(state.reconnectTimer);
                state.reconnectTimer = null;
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
    // GESTIONNAIRE PERMISSIONS
    // ============================================
    const PermissionManager = {
        devices: { cameras: [], micros: [] },
        selectedCamera: null,
        selectedMicro: null,
        previewStream: null,

        showModal: function() {
            const modal = document.getElementById('permissionModal');
            if (modal) modal.style.display = 'flex';
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
                this.startPreview();
            }
            if (this.devices.micros.length) {
                microSelect.value = this.devices.micros[0].deviceId;
                this.selectedMicro = this.devices.micros[0].deviceId;
            }
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
                video: {
                    width: { ideal: 640, max: 1280 },
                    height: { ideal: 480, max: 720 },
                    frameRate: { ideal: 30, max: 30 }
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                }
            };
            
            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            return stream;
        },

        checkExistingPermission: async function() {
            try {
                const testStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                testStream.getTracks().forEach(track => track.stop());
                return true;
            } catch { 
                return false; 
            }
        }
    };

    // ============================================
    // WEBRTC - VERSION SANS CHAT
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
                        { urls: 'stun:stun1.l.google.com:19302' }
                    ]
                };
                return CONFIG.iceServers;
            }
        },

        async createPeerConnection() {
            if (state.isConnecting) {
                utils.log('debug', '⏳ Connexion déjà en cours...');
                return new Promise(resolve => {
                    const checkInterval = setInterval(() => {
                        if (!state.isConnecting && state.peerConnection) {
                            clearInterval(checkInterval);
                            resolve(state.peerConnection);
                        }
                    }, 100);
                    setTimeout(() => {
                        clearInterval(checkInterval);
                        resolve(state.peerConnection);
                    }, 5000);
                });
            }
            
            try {
                state.isConnecting = true;
                
                if (state.peerConnection) {
                    this.cleanup();
                }

                if (!CONFIG.iceServers) await this.loadIceServers();

                const config = { 
                    ...CONFIG.iceServers, 
                    iceTransportPolicy: 'all',
                    bundlePolicy: 'max-bundle', 
                    rtcpMuxPolicy: 'require',
                    sdpSemantics: 'unified-plan'
                };

                state.peerConnection = new RTCPeerConnection(config);
                
                this.setupPeerConnectionListeners();
                
                if (state.localStream) {
                    await this.addLocalTracks();
                }

                utils.log('success', '✅ PeerConnection créée');
                return state.peerConnection;
            } catch (error) {
                utils.log('error', '❌ Erreur création PeerConnection:', error);
                utils.showToast('Erreur de connexion WebRTC', 'error');
                throw error;
            } finally {
                state.isConnecting = false;
            }
        },

        setupPeerConnectionListeners() {
            if (!state.peerConnection) return;

            state.peerConnection.onconnectionstatechange = () => {
                const connState = state.peerConnection.connectionState;
                utils.log('info', `📊 Connection state: ${connState}`);
                
                switch(connState) {
                    case 'connected':
                        state.isConnected = true;
                        state.connectionStable = true;
                        state.reconnectAttempts = 0;
                        state.offerRetryCount = 0;
                        utils.closeWaitingOverlay();
                        utils.updateOtherStatus(true);
                        utils.showToast('Consultation vidéo établie', 'success');
                        break;
                        
                    case 'disconnected':
                        state.isConnected = false;
                        state.connectionStable = false;
                        utils.updateOtherStatus(false);
                        utils.showToast('Connexion interrompue', 'warning');
                        setTimeout(() => {
                            if (!state.isConnected && state.otherSocketId) {
                                this.handleConnectionFailure();
                            }
                        }, 3000);
                        break;
                        
                    case 'failed':
                        state.isConnected = false;
                        state.connectionStable = false;
                        utils.log('error', '❌ Connection failed');
                        this.handleConnectionFailure();
                        break;
                }
            };

            state.peerConnection.oniceconnectionstatechange = () => {
                const iceState = state.peerConnection.iceConnectionState;
                utils.log('info', `📊 ICE state: ${iceState}`);
                
                if (iceState === 'failed' && !state.degradedMode && !state.isConnected) {
                    this.handleIceFailure();
                }
            };

            let lastCandidateTime = 0;
            state.peerConnection.onicecandidate = (event) => {
                if (event.candidate && state.otherSocketId && state.socket && state.socket.connected) {
                    const now = Date.now();
                    if (now - lastCandidateTime > 50) {
                        lastCandidateTime = now;
                        state.socket.emit('ice-candidate', { 
                            target: state.otherSocketId, 
                            candidate: event.candidate 
                        });
                    }
                }
            };

            state.peerConnection.ontrack = (event) => {
                utils.log('track', `📹 Track reçu: ${event.track.kind}`);
                
                const remoteStream = event.streams[0] || new MediaStream([event.track]);
                
                if (!state.remoteStream) {
                    state.remoteStream = remoteStream;
                } else if (!state.remoteStream.getTracks().includes(event.track)) {
                    state.remoteStream.addTrack(event.track);
                }
                
                if (elements.remoteVideo && elements.remoteVideo.srcObject !== state.remoteStream) {
                    elements.remoteVideo.srcObject = state.remoteStream;
                    this.safePlayRemoteVideo();
                }
                
                if (event.track.kind === 'video') {
                    state.isConnected = true;
                    utils.closeWaitingOverlay();
                    utils.updateOtherStatus(true);
                    
                    event.track.onunmute = () => {
                        utils.log('track', '🎥 Track vidéo actif');
                        this.safePlayRemoteVideo();
                    };
                }
            };

            let negotiationPending = false;
            state.peerConnection.onnegotiationneeded = async () => {
                if (negotiationPending || state.isProcessingOffer || state.isProcessingAnswer) {
                    utils.log('debug', 'ℹ️ Négociation déjà en cours');
                    return;
                }
                
                negotiationPending = true;
                try {
                    if (state.isInitiator && !state.answerReceived) {
                        await this.createOffer();
                    }
                } catch (err) {
                    utils.log('error', '❌ Erreur négociation:', err);
                } finally {
                    negotiationPending = false;
                }
            };
        },

        safePlayRemoteVideo() {
            if (!elements.remoteVideo || !elements.remoteVideo.srcObject) return;
            
            const video = elements.remoteVideo;
            const playVideo = () => {
                video.play().catch(e => {
                    if (e.name === 'NotAllowedError') {
                        video.muted = true;
                        video.play().catch(() => {});
                    }
                });
            };
            
            if (video.paused) {
                playVideo();
            }
        },

        async addLocalTracks() {
            if (!state.localStream || !state.peerConnection) return;

            const senders = state.peerConnection.getSenders();
            
            state.localStream.getTracks().forEach(track => {
                if (!senders.some(sender => sender.track === track)) {
                    try {
                        state.peerConnection.addTrack(track, state.localStream);
                        utils.log('debug', `➕ Track ajouté: ${track.kind}`);
                    } catch (error) { 
                        utils.log('error', `❌ Erreur ajout track:`, error); 
                    }
                }
            });
        },

        async createOffer() {
            if (!state.peerConnection || !state.otherSocketId || !state.socket) return;
            
            if (state.offerRetryCount >= CONFIG.offerMaxRetries) {
                utils.log('error', '❌ Tentatives max atteintes');
                return;
            }
            
            if (state.answerReceived) return;
            
            try {
                state.offerRetryCount++;
                utils.log('offer', `🎯 Création offre (tentative ${state.offerRetryCount})...`);

                const offer = await state.peerConnection.createOffer({
                    offerToReceiveAudio: true,
                    offerToReceiveVideo: true
                });

                await state.peerConnection.setLocalDescription(offer);
                await this.waitForIceGathering();

                const finalOffer = state.peerConnection.localDescription;
                utils.log('offer', `📤 Envoi offre`);
                
                state.socket.emit('offer', { 
                    target: state.otherSocketId, 
                    sdp: finalOffer 
                });

                setTimeout(() => {
                    if (!state.answerReceived && state.offerRetryCount < CONFIG.offerMaxRetries) {
                        utils.log('offer', `⏰ Pas de réponse, tentative ${state.offerRetryCount + 1}`);
                        this.createOffer();
                    }
                }, CONFIG.offerRetryDelay);

            } catch (error) { 
                utils.log('error', '❌ Erreur création offre:', error); 
            }
        },

        waitForIceGathering() {
            return new Promise((resolve) => {
                if (!state.peerConnection || state.peerConnection.iceGatheringState === 'complete') {
                    resolve();
                    return;
                }

                const timeout = setTimeout(() => {
                    utils.log('warn', '⚠️ Timeout gathering ICE');
                    resolve();
                }, CONFIG.iceGatheringTimeout);

                const onGatheringComplete = () => {
                    if (state.peerConnection.iceGatheringState === 'complete') {
                        clearTimeout(timeout);
                        state.peerConnection.removeEventListener('icegatheringstatechange', onGatheringComplete);
                        resolve();
                    }
                };

                state.peerConnection.addEventListener('icegatheringstatechange', onGatheringComplete);
            });
        },

        async handleOffer(data) {
            if (!data?.sdp || !data?.sender) return;
            
            if (state.isProcessingOffer) {
                setTimeout(() => this.handleOffer(data), 1000);
                return;
            }

            try {
                state.isProcessingOffer = true;
                utils.log('offer', `📩 Offre reçue`);

                if (!state.peerConnection || state.peerConnection.signalingState === 'closed') {
                    await this.createPeerConnection();
                }

                const pc = state.peerConnection;
                
                let waitCount = 0;
                while (pc.signalingState !== 'stable' && waitCount < 20) {
                    await new Promise(r => setTimeout(r, 100));
                    waitCount++;
                }

                await pc.setRemoteDescription(new RTCSessionDescription(data.sdp));
                await this.processPendingCandidates();

                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);
                await this.waitForIceGathering();

                const finalAnswer = pc.localDescription;
                utils.log('answer', `📤 Envoi réponse`);
                
                if (state.socket) {
                    state.socket.emit('answer', { 
                        target: data.sender, 
                        sdp: finalAnswer 
                    });
                }

            } catch (error) { 
                utils.log('error', '❌ Erreur traitement offre:', error);
            } finally {
                state.isProcessingOffer = false;
            }
        },

        async handleAnswer(data) {
            if (!data?.sdp || !data?.sender) return;
            
            if (state.isProcessingAnswer) return;

            try {
                state.isProcessingAnswer = true;
                utils.log('answer', `📩 Réponse reçue`);

                state.answerReceived = true;
                
                if (!state.peerConnection) return;

                const pc = state.peerConnection;
                
                if (pc.signalingState === 'have-local-offer') {
                    await pc.setRemoteDescription(new RTCSessionDescription(data.sdp));
                    await this.processPendingCandidates();
                    utils.log('success', '✅ Réponse appliquée');
                } else {
                    utils.log('warn', `⚠️ État inattendu: ${pc.signalingState}`);
                }

            } catch (error) { 
                utils.log('error', '❌ Erreur traitement réponse:', error);
            } finally {
                state.isProcessingAnswer = false;
            }
        },

        async handleIceCandidate(data) {
            if (!data?.candidate) return;

            try {
                const pc = state.peerConnection;
                
                if (pc && pc.remoteDescription && pc.remoteDescription.type) {
                    await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
                } else if (state.pendingIceCandidates.length < 50) {
                    state.pendingIceCandidates.push(data.candidate);
                }
            } catch (error) { 
                utils.log('error', '❌ Erreur ajout ICE:', error); 
            }
        },

        async processPendingCandidates() {
            if (!state.peerConnection) return;
            
            const candidates = [...state.pendingIceCandidates];
            state.pendingIceCandidates = [];
            
            for (const candidate of candidates) {
                try { 
                    await state.peerConnection.addIceCandidate(new RTCIceCandidate(candidate)); 
                } catch (error) { 
                    utils.log('error', '❌ Erreur ajout candidat:', error); 
                }
            }
        },

        async handleConnectionFailure() {
            if (state.reconnectAttempts >= CONFIG.maxReconnectionAttempts) {
                utils.log('error', '❌ Échec reconnexion');
                utils.showToast('Problème de connexion. Veuillez rafraîchir.', 'error');
                return;
            }
            
            state.reconnectAttempts++;
            const delay = Math.min(5000 * Math.pow(1.5, state.reconnectAttempts - 1), 30000);
            
            utils.log('reconnect', `🔄 Reconnexion dans ${delay/1000}s (tentative ${state.reconnectAttempts})`);
            
            if (state.reconnectTimer) clearTimeout(state.reconnectTimer);
            state.reconnectTimer = setTimeout(async () => {
                this.cleanup();
                utils.showWaitingOverlay();
                
                if (state.otherSocketId && state.isInitiator) {
                    await this.createPeerConnection();
                    this.createOffer();
                }
            }, delay);
        },

        async handleIceFailure() {
            if (state.degradedMode) return;
            
            state.degradedMode = true;
            utils.log('warn', '⚠️ Mode dégradé activé');
            utils.showToast('Connexion en mode dégradé', 'warning', 3000);
        },

        toggleAudio: function() {
            if (!state.localStream) return false;
            
            state.audioEnabled = !state.audioEnabled;
            state.localStream.getAudioTracks().forEach(track => {
                track.enabled = state.audioEnabled;
            });
            
            if (elements.muteAudioBtn) {
                elements.muteAudioBtn.classList.toggle('muted', !state.audioEnabled);
                elements.muteAudioBtn.innerHTML = state.audioEnabled ? 
                    '<i class="fas fa-microphone"></i>' : 
                    '<i class="fas fa-microphone-slash"></i>';
            }
            
            return state.audioEnabled;
        },

        toggleVideo: function() {
            if (!state.localStream) return false;
            
            state.videoEnabled = !state.videoEnabled;
            state.localStream.getVideoTracks().forEach(track => {
                track.enabled = state.videoEnabled;
            });
            
            if (elements.muteVideoBtn) {
                elements.muteVideoBtn.classList.toggle('off', !state.videoEnabled);
                elements.muteVideoBtn.innerHTML = state.videoEnabled ? 
                    '<i class="fas fa-video"></i>' : 
                    '<i class="fas fa-video-slash"></i>';
            }
            
            return state.videoEnabled;
        },

        cleanup: function() {
            if (state.peerConnection) { 
                try {
                    state.peerConnection.onconnectionstatechange = null;
                    state.peerConnection.oniceconnectionstatechange = null;
                    state.peerConnection.onicecandidate = null;
                    state.peerConnection.ontrack = null;
                    state.peerConnection.onnegotiationneeded = null;
                    state.peerConnection.close();
                } catch(e) {}
                state.peerConnection = null; 
            }
            
            state.remoteStream = null;
            utils.resetRemoteVideo();
            state.pendingIceCandidates = [];
            state.isConnected = false;
            state.connectionStable = false;
            state.answerReceived = false;
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
    // SOCKET.IO
    // ============================================
    function initSocket() {
        if (!window.io) { 
            utils.log('error', 'Socket.IO non chargé'); 
            return; 
        }

        utils.log('polling', '📮 Initialisation socket...');
        
        try {
            const socketOptions = {
                path: CONFIG.socketPath,
                transports: ['polling'],
                withCredentials: true,
                reconnection: true,
                reconnectionAttempts: CONFIG.maxReconnectionAttempts,
                reconnectionDelay: 3000,
                reconnectionDelayMax: 15000,
                timeout: CONFIG.connectionTimeout,
                autoConnect: true
            };

            state.socket = io(CONFIG.socketUrl, socketOptions);

            setupSocketListeners();
            startHeartbeat();
            
        } catch (error) { 
            utils.log('error', '❌ Erreur création socket:', error); 
        }
    }

    function startHeartbeat() {
        if (state.heartbeatInterval) clearInterval(state.heartbeatInterval);
        
        state.heartbeatInterval = setInterval(() => {
            if (state.socket && state.socket.connected) {
                state.socket.emit('ping', { time: Date.now(), role: CONFIG.currentRole });
            }
        }, CONFIG.heartbeatInterval);

        window.addEventListener('beforeunload', () => { 
            if (state.heartbeatInterval) clearInterval(state.heartbeatInterval); 
        });
    }

    function setupSocketListeners() {
        if (!state.socket) return;
        
        state.socket.removeAllListeners();

        state.socket.on('connect', () => {
            utils.log('success', `✅ Socket connecté: ${state.socket.id}`);
            
            if (CONFIG.roomId) { 
                state.socket.emit('join-room', CONFIG.roomId); 
            }
            
            state.reconnectAttempts = 0;
        });

        state.socket.on('connect_error', (error) => { 
            utils.log('error', '❌ Erreur connexion:', error.message);
        });

        state.socket.on('disconnect', (reason) => { 
            utils.log('warn', `❌ Déconnecté: ${reason}`);
            utils.updateOtherStatus(false);
        });

        state.socket.on('reconnect', (attemptNumber) => { 
            utils.log('success', `🔄 Reconnecté après ${attemptNumber} tentatives`);
            if (CONFIG.roomId && state.socket) {
                state.socket.emit('join-room', CONFIG.roomId);
            }
        });

        state.socket.on('user-connected', async (data) => {
            const socketId = data?.id || data;
            
            if (!socketId || socketId === state.socket?.id) return;

            if (state.otherSocketId && state.otherSocketId !== socketId) {
                webrtc.cleanup();
            }

            utils.log('info', `👤 Utilisateur connecté`);
            state.otherSocketId = socketId;
            utils.updateOtherStatus(true);
            
            const myId = state.socket.id;
            const otherId = socketId;
            
            state.isInitiator = myId < otherId;
            
            utils.log('info', `🎯 Rôle: ${state.isInitiator ? 'Initiateur' : 'Récepteur'}`);

            if (state.isInitiator) {
                setTimeout(async () => {
                    if (!state.peerConnection) {
                        await webrtc.createPeerConnection();
                        await webrtc.createOffer();
                    }
                }, 3000);
            }
        });

        state.socket.on('user-disconnected', (data) => {
            const socketId = data?.id || data;
            
            if (!socketId || socketId !== state.otherSocketId) return;
            
            utils.log('info', `👤 Utilisateur déconnecté`);
            state.otherSocketId = null;
            state.isInitiator = false;
            state.answerReceived = false;
            state.offerRetryCount = 0;
            
            utils.updateOtherStatus(false);
            webrtc.cleanup();
            utils.showWaitingOverlay();
        });

        state.socket.on('offer', (data) => {
            if (data?.sdp && data?.sender) {
                webrtc.handleOffer(data);
            }
        });

        state.socket.on('answer', (data) => {
            if (data?.sdp && data?.sender) {
                webrtc.handleAnswer(data);
            }
        });

        state.socket.on('ice-candidate', (data) => {
            if (data?.candidate) {
                webrtc.handleIceCandidate(data);
            }
        });

        state.socket.on('pong', (data) => { 
            const latency = Date.now() - data.time; 
            if (latency > 1000) {
                utils.log('warn', `📊 Latence élevée: ${latency}ms`);
            }
        });
    }

    // ============================================
    // INITIALISATION UI
    // ============================================
    function initEventListeners() {
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

        // Masquer le bouton de chat s'il existe
        if (elements.toggleChatBtn) {
            elements.toggleChatBtn.style.display = 'none';
        }
        
        // Masquer la zone de chat si elle existe
        if (elements.chatArea) {
            elements.chatArea.style.display = 'none';
        }

        window.addEventListener('beforeunload', () => {
            utils.cleanup();
        });
    }

    async function endConsultation() {
        if (CONFIG.consultationId) {
            try {
                await fetch(`/Joinconsultation/endConsultationApi/${CONFIG.consultationId}`, { 
                    method: 'POST', 
                    headers: { 'X-Requested-With': 'XMLHttpRequest' } 
                });
            } catch (err) {}
        }
        
        utils.cleanup();
        setTimeout(() => window.location.href = '/', 500);
    }

    async function init() {
        try {
            utils.log('info', '🚀 Initialisation consultation v6.0.0 (Sans Chat)...');
            
            if (!utils.checkBrowserCompatibility()) {
                throw new Error('Navigateur non compatible');
            }
            
            if (!CONFIG.roomId) {
                throw new Error('roomId manquant');
            }

            utils.showWaitingOverlay();

            let stream;
            try {
                const hasPermission = await PermissionManager.checkExistingPermission();
                if (!hasPermission) {
                    PermissionManager.showModal();
                    await PermissionManager.loadDevices();
                    
                    stream = await new Promise((resolve, reject) => {
                        const grantBtn = document.getElementById('grantPermissionBtn');
                        const handleGrant = async () => {
                            try {
                                const s = await PermissionManager.getStream();
                                PermissionManager.hideModal();
                                resolve(s);
                            } catch(e) { reject(e); }
                        };
                        const handleCancel = () => {
                            reject(new Error('Permission refusée'));
                        };
                        grantBtn?.addEventListener('click', handleGrant);
                        document.getElementById('cancelPermissionBtn')?.addEventListener('click', handleCancel);
                        setTimeout(() => reject(new Error('Timeout')), 30000);
                    });
                } else {
                    stream = await PermissionManager.getStream();
                }
                
                state.localStream = stream;
                
                if (elements.localVideo) { 
                    elements.localVideo.srcObject = stream; 
                    elements.localVideo.muted = true; 
                    elements.localVideo.play().catch(() => {});
                }
                
                utils.log('success', '✅ Autorisation média obtenue');
                utils.showToast('Caméra et micro activés', 'success');
            } catch (error) {
                utils.log('error', '❌ Permission refusée:', error.message);
                utils.showToast('Accès caméra/microphone requis', 'error');
                setTimeout(() => window.location.href = '/', 3000);
                return;
            }

            initSocket();
            await webrtc.loadIceServers();
            initEventListeners();
            
            utils.log('success', '✅ Consultation prête - en attente de l\'autre participant');
            
        } catch (error) {
            utils.log('error', '❌ Échec initialisation:', error.message);
            utils.showToast('Erreur de démarrage', 'error');
            setTimeout(() => window.location.reload(), 5000);
        }
    }

    // Démarrage
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.consultation = { 
        state, 
        webrtc, 
        version: '6.0.0-nochat',
        debug: function() {
            console.log('=== DEBUG CONSULTATION ===');
            console.log('État connexion:', state.isConnected);
            console.log('PeerConnection:', !!state.peerConnection);
            console.log('Mode dégradé:', state.degradedMode);
            console.log('Tentatives:', state.reconnectAttempts);
            console.log('========================');
        }
    };
})();