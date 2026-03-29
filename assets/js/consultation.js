// ============================================
// CONSULTATION.JS - VERSION FINALE PRODUCTION
// ============================================

(function() {
    'use strict';

    const CONFIG = {
        socketUrl: window.location.origin,
        socketPath: '/socket/socket.io',
        roomId: window.roomId || null,
        currentUser: window.currentUser || { id: 'me', name: 'Moi' },
        otherUser: window.otherUser || { id: 'other', name: 'Autre' },
        consultationId: window.consultationId || null
    };

    const state = {
        socket: null,
        peerConnection: null,
        localStream: null,
        remoteStream: null,
        otherSocketId: null,
        isInitiator: false,
        isConnected: false,
        pendingIceCandidates: [],
        reconnectAttempts: 0,
        turnConnected: false
    };

    const elements = {
        localVideo: document.getElementById('local-video'),
        remoteVideo: document.getElementById('remote-video'),
        muteAudioBtn: document.getElementById('mute-audio'),
        muteVideoBtn: document.getElementById('mute-video'),
        leaveBtn: document.getElementById('leave-call'),
        otherStatus: document.getElementById('other-status'),
        waitingOverlay: document.getElementById('waiting-overlay'),
        toastContainer: document.getElementById('toast-container')
    };

    const utils = {
        log: function(msg) {
            const time = new Date().toLocaleTimeString();
            console.log(`[${time}] ${msg}`);
        },
        
        showToast: function(msg, type = 'info') {
            if (!elements.toastContainer) return;
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `<span>${msg}</span>`;
            elements.toastContainer.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        },
        
        updateOtherStatus: function(online) {
            if (elements.otherStatus) {
                elements.otherStatus.textContent = online ? 'En ligne' : 'Hors ligne';
                elements.otherStatus.style.color = online ? '#00a884' : '#8696a0';
            }
        },
        
        closeWaitingOverlay: function() {
            if (elements.waitingOverlay) elements.waitingOverlay.style.display = 'none';
        },
        
        showWaitingOverlay: function() {
            if (elements.waitingOverlay) elements.waitingOverlay.style.display = 'flex';
        }
    };

    const webrtc = {
        async createPeerConnection() {
            utils.log('🔧 Création PeerConnection avec TURN...');
            
            if (state.peerConnection) {
                try { state.peerConnection.close(); } catch(e) {}
                state.peerConnection = null;
            }
            
            try {
                const response = await fetch('/socket/api/ice-servers');
                const config = await response.json();
                
                utils.log(`📡 ${config.iceServers.length} serveurs ICE configurés`);
                
                state.peerConnection = new RTCPeerConnection({
                    iceServers: config.iceServers,
                    iceTransportPolicy: 'relay',
                    iceCandidatePoolSize: 10,
                    bundlePolicy: 'max-bundle',
                    rtcpMuxPolicy: 'require'
                });
                
                let iceTimeout = setTimeout(() => {
                    if (state.peerConnection && !state.isConnected) {
                        const iceState = state.peerConnection.iceConnectionState;
                        if (iceState === 'checking') {
                            utils.log('⚠️ Connexion lente, tentative avec STUN+RELAY...');
                            this.switchToHybridMode();
                        }
                    }
                }, 30000);
                
                state.peerConnection.onicecandidate = (event) => {
                    if (event.candidate && state.otherSocketId && state.socket) {
                        const candidateType = event.candidate.candidate.includes('relay') ? '🔄 TURN' : 
                                             (event.candidate.candidate.includes('srflx') ? '📡 STUN' : '💻 HOST');
                        utils.log(`${candidateType}: ${event.candidate.type || 'candidate'}`);
                        
                        if (candidateType === '🔄 TURN') {
                            state.turnConnected = true;
                            utils.log('✅ TURN relay actif!');
                        }
                        
                        state.socket.emit('ice-candidate', {
                            target: state.otherSocketId,
                            candidate: event.candidate
                        });
                    }
                };
                
                // Gestion des tracks - Version corrigée sans erreur play
                state.peerConnection.ontrack = (event) => {
                    utils.log(`📹 Track reçu: ${event.track.kind}`);
                    
                    if (!state.remoteStream) {
                        state.remoteStream = new MediaStream();
                    }
                    state.remoteStream.addTrack(event.track);
                    
                    if (elements.remoteVideo && elements.remoteVideo.srcObject !== state.remoteStream) {
                        elements.remoteVideo.srcObject = state.remoteStream;
                        // Lecture silencieuse - ignorer toutes les erreurs de play
                        elements.remoteVideo.play().catch(() => {});
                    }
                };
                
                state.peerConnection.onconnectionstatechange = () => {
                    const connState = state.peerConnection.connectionState;
                    utils.log(`📊 Connection: ${connState}`);
                    
                    if (connState === 'connected') {
                        clearTimeout(iceTimeout);
                        state.isConnected = true;
                        state.reconnectAttempts = 0;
                        utils.closeWaitingOverlay();
                        utils.updateOtherStatus(true);
                        const mode = state.turnConnected ? 'TURN (relais international)' : 'STUN (direct)';
                        utils.showToast(`Connecté via ${mode}`, 'success');
                    } else if (connState === 'failed') {
                        clearTimeout(iceTimeout);
                        state.isConnected = false;
                        utils.log('❌ Connexion échouée');
                        this.handleFailure();
                    }
                };
                
                state.peerConnection.oniceconnectionstatechange = () => {
                    const iceState = state.peerConnection.iceConnectionState;
                    utils.log(`🧊 ICE: ${iceState}`);
                    
                    if (iceState === 'connected' || iceState === 'completed') {
                        clearTimeout(iceTimeout);
                        utils.log('✅ ICE connecté');
                    } else if (iceState === 'failed') {
                        utils.log('❌ ICE échoué');
                        this.restartIce();
                    }
                };
                
                if (state.localStream) {
                    state.localStream.getTracks().forEach(track => {
                        try {
                            state.peerConnection.addTrack(track, state.localStream);
                            utils.log(`➕ Track local: ${track.kind}`);
                        } catch(e) {
                            utils.log(`❌ Erreur track: ${e.message}`);
                        }
                    });
                }
                
                if (state.pendingIceCandidates.length > 0) {
                    utils.log(`📦 ${state.pendingIceCandidates.length} candidats en attente`);
                    for (const candidate of state.pendingIceCandidates) {
                        try {
                            await state.peerConnection.addIceCandidate(candidate);
                        } catch(e) {}
                    }
                    state.pendingIceCandidates = [];
                }
                
                return state.peerConnection;
            } catch (error) {
                utils.log(`❌ Erreur: ${error.message}`);
                throw error;
            }
        },
        
        async switchToHybridMode() {
            if (!state.peerConnection || state.isConnected) return;
            
            utils.log('🔄 Passage en mode hybride (TURN+STUN)...');
            try {
                const response = await fetch('/socket/api/ice-servers');
                const config = await response.json();
                
                const newPC = new RTCPeerConnection({
                    iceServers: config.iceServers,
                    iceTransportPolicy: 'all',
                    iceCandidatePoolSize: 10
                });
                
                if (state.localStream) {
                    state.localStream.getTracks().forEach(track => {
                        newPC.addTrack(track, state.localStream);
                    });
                }
                
                const oldPC = state.peerConnection;
                state.peerConnection = newPC;
                oldPC.close();
                
                if (state.isInitiator) {
                    await this.createOffer();
                }
            } catch (error) {
                utils.log(`❌ Switch hybride échoué: ${error.message}`);
            }
        },
        
        async restartIce() {
            if (!state.peerConnection || state.isConnected) return;
            
            utils.log('🔄 Restart ICE...');
            try {
                const offer = await state.peerConnection.createOffer({
                    iceRestart: true,
                    offerToReceiveAudio: true,
                    offerToReceiveVideo: true
                });
                await state.peerConnection.setLocalDescription(offer);
                
                state.socket.emit('offer', {
                    target: state.otherSocketId,
                    sdp: state.peerConnection.localDescription
                });
                utils.log('📤 Offer ICE restart envoyée');
            } catch (error) {
                utils.log(`❌ Restart échoué: ${error.message}`);
            }
        },
        
        async handleFailure() {
            if (state.reconnectAttempts >= 5) {
                utils.log('❌ Abandon après 5 tentatives');
                utils.showToast('Connexion impossible. Vérifiez votre connexion internet.', 'error');
                return;
            }
            
            state.reconnectAttempts++;
            const delay = 5000 * Math.pow(2, state.reconnectAttempts - 1);
            utils.log(`🔄 Nouvelle tentative dans ${delay/1000}s (${state.reconnectAttempts}/5)...`);
            
            setTimeout(() => {
                this.cleanup();
                this.createPeerConnection().then(() => {
                    if (state.isInitiator && state.otherSocketId) {
                        setTimeout(() => this.createOffer(), 1000);
                    }
                });
            }, delay);
        },
        
        async createOffer() {
            if (!state.peerConnection) {
                utils.log('❌ Pas de PeerConnection');
                return;
            }
            
            try {
                utils.log('📤 Création offre...');
                const offer = await state.peerConnection.createOffer({
                    offerToReceiveAudio: true,
                    offerToReceiveVideo: true
                });
                
                await state.peerConnection.setLocalDescription(offer);
                
                await new Promise((resolve) => {
                    if (state.peerConnection.iceGatheringState === 'complete') {
                        resolve();
                    } else {
                        const timeout = setTimeout(resolve, 10000);
                        const checkState = () => {
                            if (state.peerConnection.iceGatheringState === 'complete') {
                                clearTimeout(timeout);
                                state.peerConnection.removeEventListener('icegatheringstatechange', checkState);
                                resolve();
                            }
                        };
                        state.peerConnection.addEventListener('icegatheringstatechange', checkState);
                    }
                });
                
                state.socket.emit('offer', {
                    target: state.otherSocketId,
                    sdp: state.peerConnection.localDescription
                });
                utils.log('📤 Offre envoyée');
            } catch (error) {
                utils.log(`❌ Erreur offre: ${error.message}`);
            }
        },
        
        async handleOffer(data) {
            utils.log('📩 Offre reçue');
            
            if (!state.peerConnection) {
                await this.createPeerConnection();
            }
            
            try {
                await state.peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
                utils.log('✅ Remote description OK');
                
                const answer = await state.peerConnection.createAnswer();
                await state.peerConnection.setLocalDescription(answer);
                
                state.socket.emit('answer', {
                    target: data.sender,
                    sdp: state.peerConnection.localDescription
                });
                utils.log('📤 Réponse envoyée');
            } catch (error) {
                utils.log(`❌ Erreur: ${error.message}`);
            }
        },
        
        async handleAnswer(data) {
            utils.log('📩 Réponse reçue');
            
            if (!state.peerConnection) return;
            
            try {
                await state.peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
                utils.log('✅ Réponse appliquée');
            } catch (error) {
                utils.log(`❌ Erreur: ${error.message}`);
            }
        },
        
        async handleIceCandidate(data) {
            const candidate = new RTCIceCandidate(data.candidate);
            
            if (!state.peerConnection) {
                state.pendingIceCandidates.push(candidate);
                return;
            }
            
            try {
                await state.peerConnection.addIceCandidate(candidate);
                utils.log('🧊 Candidat ajouté');
            } catch (error) {
                utils.log(`❌ Erreur ICE: ${error.message}`);
            }
        },
        
        cleanup() {
            if (state.peerConnection) {
                try { state.peerConnection.close(); } catch(e) {}
                state.peerConnection = null;
            }
            state.remoteStream = null;
            if (elements.remoteVideo) elements.remoteVideo.srcObject = null;
            state.pendingIceCandidates = [];
            state.turnConnected = false;
            utils.log('🧹 Nettoyé');
        }
    };

    function initSocket() {
        utils.log('📮 Connexion socket...');
        
        state.socket = io(CONFIG.socketUrl, {
            path: CONFIG.socketPath,
            transports: ['polling', 'websocket'],
            upgrade: true,
            reconnection: true,
            reconnectionAttempts: 10,
            reconnectionDelay: 2000,
            timeout: 15000
        });
        
        state.socket.on('connect', () => {
            utils.log('✅ Socket connecté');
            if (CONFIG.roomId) {
                state.socket.emit('join-room', CONFIG.roomId);
            }
        });
        
        state.socket.on('connect_error', (error) => {
            utils.log(`❌ Socket: ${error.message}`);
        });
        
        state.socket.on('user-connected', async (data) => {
            if (data.id === state.socket.id) return;
            if (state.otherSocketId === data.id) return;
            
            utils.log('👤 Participant connecté');
            state.otherSocketId = data.id;
            state.isInitiator = true;
            utils.updateOtherStatus(true);
            
            await webrtc.createPeerConnection();
            await webrtc.createOffer();
        });
        
        state.socket.on('user-disconnected', (data) => {
            if (data.id === state.otherSocketId) {
                utils.log('👤 Participant déconnecté');
                state.otherSocketId = null;
                webrtc.cleanup();
                utils.updateOtherStatus(false);
                utils.showWaitingOverlay();
                utils.showToast('Le participant a quitté', 'warning');
            }
        });
        
        state.socket.on('offer', async (data) => {
            if (data.sender === state.socket.id) return;
            state.otherSocketId = data.sender;
            await webrtc.handleOffer(data);
        });
        
        state.socket.on('answer', async (data) => {
            if (data.sender === state.socket.id) return;
            await webrtc.handleAnswer(data);
        });
        
        state.socket.on('ice-candidate', async (data) => {
            if (data.sender === state.socket.id) return;
            await webrtc.handleIceCandidate(data);
        });
    }

    async function getMediaStream() {
        utils.log('📹 Demande caméra/micro...');
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { width: 1280, height: 720 },
                audio: { echoCancellation: true, noiseSuppression: true }
            });
            
            if (elements.localVideo) {
                elements.localVideo.srcObject = stream;
                elements.localVideo.muted = true;
                await elements.localVideo.play();
            }
            
            utils.log('✅ Caméra/micro OK');
            return stream;
        } catch (error) {
            utils.log(`❌ Erreur: ${error.message}`);
            throw error;
        }
    }

    function setupControls() {
        if (elements.muteAudioBtn) {
            elements.muteAudioBtn.onclick = () => {
                if (state.localStream) {
                    const track = state.localStream.getAudioTracks()[0];
                    if (track) {
                        track.enabled = !track.enabled;
                        elements.muteAudioBtn.classList.toggle('muted', !track.enabled);
                    }
                }
            };
        }
        
        if (elements.muteVideoBtn) {
            elements.muteVideoBtn.onclick = () => {
                if (state.localStream) {
                    const track = state.localStream.getVideoTracks()[0];
                    if (track) {
                        track.enabled = !track.enabled;
                        elements.muteVideoBtn.classList.toggle('off', !track.enabled);
                    }
                }
            };
        }
        
        if (elements.leaveBtn) {
            elements.leaveBtn.onclick = async () => {
                if (confirm('Quitter la consultation ?')) {
                    if (CONFIG.consultationId) {
                        await fetch(`/Joinconsultation/endConsultationApi/${CONFIG.consultationId}`, { method: 'POST' });
                    }
                    window.location.href = '/';
                }
            };
        }
    }

    async function init() {
        utils.log('🚀 Démarrage consultation v6.3 (TURN forcé)...');
        
        if (!CONFIG.roomId) {
            utils.log('❌ roomId manquant');
            return;
        }
        
        utils.showWaitingOverlay();
        
        try {
            state.localStream = await getMediaStream();
            initSocket();
            setupControls();
            utils.log('✅ Prêt, en attente...');
        } catch (error) {
            utils.log(`❌ Erreur: ${error.message}`);
            utils.showToast('Impossible d\'accéder à la caméra', 'error');
            setTimeout(() => window.location.href = '/', 3000);
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    window.debugConsultation = () => {
        console.log('=== DEBUG ===');
        console.log('Socket:', state.socket?.connected);
        console.log('PeerConnection:', !!state.peerConnection);
        console.log('Connected:', state.isConnected);
        console.log('TURN actif:', state.turnConnected);
        if (state.peerConnection) {
            console.log('ICE state:', state.peerConnection.iceConnectionState);
            console.log('Connection:', state.peerConnection.connectionState);
        }
    };
})();