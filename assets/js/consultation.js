// ============================================
// NUFOTEC CONSULTATION - VERSION CORRIGÉE v6.1.0
// Correction: Échec ICE, reconnexion améliorée
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
        maxReconnectionAttempts: 5,
        iceServers: null,
        debug: true,
        connectionTimeout: 30000,
        iceGatheringTimeout: 8000,
        offerRetryDelay: 3000,
        offerMaxRetries: 3,
        heartbeatInterval: 25000,
        iceConnectionTimeout: 15000
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
        iceConnectionTimer: null
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
                track: '🎥', reconnect: '🔄', ice: '🧊'
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
            if (state.reconnectTimer) {
                clearTimeout(state.reconnectTimer);
                state.reconnectTimer = null;
            }
            if (state.iceConnectionTimer) {
                clearTimeout(state.iceConnectionTimer);
                state.iceConnectionTimer = null;
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
    // GESTIONNAIRE PERMISSIONS (simplifié)
    // ============================================
    const PermissionManager = {
        getStream: async function() {
            const constraints = {
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true
                }
            };
            return await navigator.mediaDevices.getUserMedia(constraints);
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
    // WEBRTC - VERSION CORRIGÉE
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
                utils.log('polling', '📮 Utilisation STUN publics');
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
            if (state.isConnecting) {
                utils.log('debug', '⏳ Connexion déjà en cours...');
                return new Promise(resolve => {
                    const interval = setInterval(() => {
                        if (!state.isConnecting && state.peerConnection) {
                            clearInterval(interval);
                            resolve(state.peerConnection);
                        }
                    }, 100);
                    setTimeout(() => {
                        clearInterval(interval);
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

                // Configuration ICE plus permissive
                const config = { 
                    iceServers: CONFIG.iceServers.iceServers,
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
                throw error;
            } finally {
                state.isConnecting = false;
            }
        },

        setupPeerConnectionListeners() {
            if (!state.peerConnection) return;

            // Timeout pour la connexion ICE
            state.iceConnectionTimer = setTimeout(() => {
                if (state.peerConnection && !state.isConnected) {
                    const iceState = state.peerConnection.iceConnectionState;
                    if (iceState === 'checking' || iceState === 'new') {
                        utils.log('warn', '⚠️ Délai ICE dépassé, tentative de restart...');
                        this.restartIce();
                    }
                }
            }, CONFIG.iceConnectionTimeout);

            state.peerConnection.onconnectionstatechange = () => {
                const connState = state.peerConnection.connectionState;
                utils.log('info', `📊 Connection state: ${connState}`);
                
                switch(connState) {
                    case 'connected':
                        if (state.iceConnectionTimer) {
                            clearTimeout(state.iceConnectionTimer);
                            state.iceConnectionTimer = null;
                        }
                        state.isConnected = true;
                        state.reconnectAttempts = 0;
                        state.offerRetryCount = 0;
                        utils.closeWaitingOverlay();
                        utils.updateOtherStatus(true);
                        utils.showToast('Consultation vidéo établie', 'success');
                        break;
                        
                    case 'failed':
                        state.isConnected = false;
                        utils.log('error', '❌ Connection failed');
                        this.handleConnectionFailure();
                        break;
                }
            };

            state.peerConnection.oniceconnectionstatechange = () => {
                const iceState = state.peerConnection.iceConnectionState;
                utils.log('info', `📊 ICE state: ${iceState}`);
                
                if (iceState === 'connected' || iceState === 'completed') {
                    if (state.iceConnectionTimer) {
                        clearTimeout(state.iceConnectionTimer);
                        state.iceConnectionTimer = null;
                    }
                }
                
                if (iceState === 'failed') {
                    utils.log('error', '❌ ICE failed');
                    this.restartIce();
                }
            };

            // Envoi des candidats ICE
            state.peerConnection.onicecandidate = (event) => {
                if (event.candidate && state.otherSocketId && state.socket && state.socket.connected) {
                    utils.log('debug', `🧊 Envoi candidat ICE: ${event.candidate.candidate.substring(0, 50)}...`);
                    state.socket.emit('ice-candidate', { 
                        target: state.otherSocketId, 
                        candidate: event.candidate 
                    });
                }
            };

            // Réception des tracks
            state.peerConnection.ontrack = (event) => {
                utils.log('track', `📹 Track reçu: ${event.track.kind}`);
                
                if (!state.remoteStream) {
                    state.remoteStream = new MediaStream();
                }
                state.remoteStream.addTrack(event.track);
                
                if (elements.remoteVideo && elements.remoteVideo.srcObject !== state.remoteStream) {
                    elements.remoteVideo.srcObject = state.remoteStream;
                    this.safePlayRemoteVideo();
                }
                
                if (event.track.kind === 'video') {
                    utils.closeWaitingOverlay();
                    utils.updateOtherStatus(true);
                }
            };

            // Négociation
            state.peerConnection.onnegotiationneeded = async () => {
                if (state.isInitiator && !state.answerReceived) {
                    utils.log('info', '🔄 Négociation nécessaire');
                    await this.createOffer();
                }
            };
        },

        restartIce() {
            if (!state.peerConnection || state.isConnected) return;
            
            utils.log('info', '🔄 Redémarrage ICE...');
            this.createOffer(true).catch(e => utils.log('error', 'Erreur restart ICE:', e));
        },

        safePlayRemoteVideo() {
            if (!elements.remoteVideo || !elements.remoteVideo.srcObject) return;
            
            const video = elements.remoteVideo;
            if (video.paused) {
                video.play().catch(e => {
                    if (e.name === 'NotAllowedError') {
                        video.muted = true;
                        video.play().catch(() => {});
                    }
                });
            }
        },

        async addLocalTracks() {
            if (!state.localStream || !state.peerConnection) return;

            state.localStream.getTracks().forEach(track => {
                try {
                    state.peerConnection.addTrack(track, state.localStream);
                    utils.log('debug', `➕ Track ajouté: ${track.kind}`);
                } catch (error) { 
                    utils.log('error', `❌ Erreur ajout track ${track.kind}:`, error); 
                }
            });
        },

        async createOffer(iceRestart = false) {
            if (!state.peerConnection || !state.otherSocketId || !state.socket) return;
            
            if (state.offerRetryCount >= CONFIG.offerMaxRetries) {
                utils.log('error', '❌ Tentatives max atteintes');
                return;
            }
            
            try {
                state.offerRetryCount++;
                utils.log('offer', `🎯 Création offre (tentative ${state.offerRetryCount})${iceRestart ? ' [ICE restart]' : ''}`);

                const offerOptions = {
                    offerToReceiveAudio: true,
                    offerToReceiveVideo: true
                };
                if (iceRestart) offerOptions.iceRestart = true;

                const offer = await state.peerConnection.createOffer(offerOptions);
                await state.peerConnection.setLocalDescription(offer);
                
                // Attendre la collecte ICE
                await this.waitForIceGathering();

                const finalOffer = state.peerConnection.localDescription;
                utils.log('offer', `📤 Envoi offre`);
                
                state.socket.emit('offer', { 
                    target: state.otherSocketId, 
                    sdp: finalOffer 
                });

                // Timeout pour la réponse
                setTimeout(() => {
                    if (!state.answerReceived && state.offerRetryCount < CONFIG.offerMaxRetries && !state.isConnected) {
                        utils.log('offer', `⏰ Pas de réponse, nouvelle tentative`);
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
                setTimeout(() => this.handleOffer(data), 500);
                return;
            }

            try {
                state.isProcessingOffer = true;
                utils.log('offer', `📩 Offre reçue`);

                if (!state.peerConnection) {
                    await this.createPeerConnection();
                }

                const pc = state.peerConnection;
                
                // Attendre état stable
                let waitCount = 0;
                while (pc.signalingState !== 'stable' && waitCount < 30) {
                    await new Promise(r => setTimeout(r, 100));
                    waitCount++;
                }

                await pc.setRemoteDescription(new RTCSessionDescription(data.sdp));
                
                // Traiter les candidats en attente
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
                    state.answerReceived = true;
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
                    utils.log('debug', '✅ Candidat ICE ajouté');
                } else {
                    state.pendingIceCandidates.push(data.candidate);
                    utils.log('debug', `⏳ Candidat en attente (${state.pendingIceCandidates.length})`);
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
                    utils.log('debug', '✅ Candidat en attente ajouté');
                } catch (error) { 
                    utils.log('error', '❌ Erreur ajout candidat en attente:', error); 
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
            const delay = Math.min(3000 * Math.pow(1.5, state.reconnectAttempts - 1), 20000);
            
            utils.log('reconnect', `🔄 Reconnexion dans ${delay/1000}s (tentative ${state.reconnectAttempts})`);
            
            if (state.reconnectTimer) clearTimeout(state.reconnectTimer);
            state.reconnectTimer = setTimeout(async () => {
                this.cleanup();
                utils.showWaitingOverlay();
                
                if (state.otherSocketId) {
                    await this.createPeerConnection();
                    if (state.isInitiator) {
                        this.createOffer();
                    }
                }
            }, delay);
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
                    state.peerConnection.close();
                } catch(e) {}
                state.peerConnection = null; 
            }
            
            state.remoteStream = null;
            utils.resetRemoteVideo();
            state.pendingIceCandidates = [];
            state.isConnected = false;
            state.answerReceived = false;
            state.offerRetryCount = 0;
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
                transports: ['polling', 'websocket'],
                withCredentials: true,
                reconnection: true,
                reconnectionAttempts: CONFIG.maxReconnectionAttempts,
                reconnectionDelay: 2000,
                reconnectionDelayMax: 10000,
                timeout: CONFIG.connectionTimeout
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
                state.socket.emit('ping', { time: Date.now() });
            }
        }, CONFIG.heartbeatInterval);
    }

    function setupSocketListeners() {
        if (!state.socket) return;
        
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

        state.socket.on('user-connected', async (data) => {
            const socketId = data?.id || data;
            if (!socketId || socketId === state.socket?.id) return;

            utils.log('info', `👤 Utilisateur connecté: ${socketId.substring(0, 8)}...`);
            state.otherSocketId = socketId;
            utils.updateOtherStatus(true);
            
            const myId = state.socket.id;
            state.isInitiator = myId < socketId;
            utils.log('info', `🎯 Rôle: ${state.isInitiator ? 'Initiateur' : 'Récepteur'}`);

            // Attendre un peu pour la stabilité
            await new Promise(r => setTimeout(r, 1000));
            
            if (!state.peerConnection) {
                await webrtc.createPeerConnection();
                if (state.isInitiator) {
                    await webrtc.createOffer();
                }
            }
        });

        state.socket.on('user-disconnected', (data) => {
            const socketId = data?.id || data;
            if (!socketId || socketId !== state.otherSocketId) return;
            
            utils.log('info', `👤 Utilisateur déconnecté`);
            state.otherSocketId = null;
            state.isInitiator = false;
            webrtc.cleanup();
            utils.showWaitingOverlay();
            utils.updateOtherStatus(false);
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
    // INITIALISATION
    // ============================================
    function initEventListeners() {
        if (elements.muteAudioBtn) { 
            elements.muteAudioBtn.addEventListener('click', () => webrtc.toggleAudio()); 
        }
        if (elements.muteVideoBtn) { 
            elements.muteVideoBtn.addEventListener('click', () => webrtc.toggleVideo()); 
        }
        if (elements.leaveBtn) {
            elements.leaveBtn.addEventListener('click', () => {
                if (confirm('Quitter la consultation ?')) {
                    endConsultation();
                }
            });
        }
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
            utils.log('info', '🚀 Initialisation consultation v6.1.0...');
            
            if (!utils.checkBrowserCompatibility()) throw new Error('Navigateur non compatible');
            if (!CONFIG.roomId) throw new Error('roomId manquant');

            utils.showWaitingOverlay();

            // Obtenir le stream
            let stream;
            try {
                const hasPermission = await PermissionManager.checkExistingPermission();
                if (!hasPermission) {
                    // Demander permission de façon simple
                    stream = await PermissionManager.getStream();
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
            } catch (error) {
                utils.log('error', '❌ Permission refusée');
                utils.showToast('Accès caméra/micro requis', 'error');
                setTimeout(() => window.location.href = '/', 3000);
                return;
            }

            initSocket();
            await webrtc.loadIceServers();
            initEventListeners();
            
            utils.log('success', '✅ Consultation prête');
            
        } catch (error) {
            utils.log('error', '❌ Échec initialisation:', error.message);
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
        state, webrtc, version: '6.1.0',
        debug: () => console.log('Connected:', state.isConnected, 'PC:', !!state.peerConnection)
    };
})();