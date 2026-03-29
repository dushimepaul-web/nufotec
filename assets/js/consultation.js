// ============================================
// CONSULTATION.JS - VERSION STABLE
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
        isReconnecting: false,
        pendingIceCandidates: [],
        reconnectAttempts: 0,
        heartbeatInterval: null,
        lastPongTime: Date.now(),
        connectionStable: false
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
            utils.log('🔧 Création PeerConnection...');
            
            if (state.peerConnection) {
                try { state.peerConnection.close(); } catch(e) {}
                state.peerConnection = null;
            }
            
            try {
                const response = await fetch('/socket/api/ice-servers');
                const config = await response.json();
                
                utils.log(`📡 ${config.iceServers.length} serveurs ICE`);
                
                state.peerConnection = new RTCPeerConnection({
                    iceServers: config.iceServers,
                    iceCandidatePoolSize: 10
                });
                
                // Timeout pour ICE
                let iceConnectionTimeout = setTimeout(() => {
                    if (state.peerConnection && !state.isConnected) {
                        const iceState = state.peerConnection.iceConnectionState;
                        if (iceState === 'checking' || iceState === 'new') {
                            utils.log('⚠️ ICE trop long, tentative de restart...');
                            this.restartIce();
                        }
                    }
                }, 30000);
                
                state.peerConnection.onicecandidate = (event) => {
                    if (event.candidate && state.otherSocketId && state.socket && state.socket.connected) {
                        utils.log(`🧊 Envoi candidat ICE`);
                        state.socket.emit('ice-candidate', {
                            target: state.otherSocketId,
                            candidate: event.candidate
                        });
                    }
                };
                
                state.peerConnection.ontrack = (event) => {
                    utils.log(`📹 Track reçu: ${event.track.kind}`);
                    
                    if (!state.remoteStream) {
                        state.remoteStream = new MediaStream();
                    }
                    state.remoteStream.addTrack(event.track);
                    
                    if (elements.remoteVideo && elements.remoteVideo.srcObject !== state.remoteStream) {
                        elements.remoteVideo.srcObject = state.remoteStream;
                        elements.remoteVideo.play().catch(() => {});
                    }
                    
                    if (event.track.kind === 'video' && !state.isConnected) {
                        state.isConnected = true;
                        state.connectionStable = true;
                        state.reconnectAttempts = 0;
                        utils.closeWaitingOverlay();
                        utils.updateOtherStatus(true);
                        utils.showToast('Consultation démarrée!', 'success');
                    }
                };
                
                state.peerConnection.onconnectionstatechange = () => {
                    const connState = state.peerConnection.connectionState;
                    utils.log(`📊 Connection: ${connState}`);
                    
                    if (connState === 'connected') {
                        clearTimeout(iceConnectionTimeout);
                        if (!state.isConnected) {
                            state.isConnected = true;
                            state.connectionStable = true;
                            utils.closeWaitingOverlay();
                            utils.updateOtherStatus(true);
                            utils.showToast('Connecté!', 'success');
                        }
                    } else if (connState === 'failed') {
                        clearTimeout(iceConnectionTimeout);
                        state.isConnected = false;
                        state.connectionStable = false;
                        utils.log('❌ Connexion échouée');
                        if (!state.isReconnecting) {
                            this.handleDisconnection();
                        }
                    } else if (connState === 'disconnected') {
                        utils.log('⚠️ Connexion instable');
                        state.connectionStable = false;
                    }
                };
                
                state.peerConnection.oniceconnectionstatechange = () => {
                    const iceState = state.peerConnection.iceConnectionState;
                    utils.log(`🧊 ICE: ${iceState}`);
                    
                    if (iceState === 'connected' || iceState === 'completed') {
                        clearTimeout(iceConnectionTimeout);
                    } else if (iceState === 'failed') {
                        utils.log('❌ ICE échoué');
                        this.restartIce();
                    } else if (iceState === 'disconnected') {
                        utils.log('⚠️ ICE déconnecté');
                        setTimeout(() => this.restartIce(), 3000);
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
                    utils.log(`📦 Traitement de ${state.pendingIceCandidates.length} candidats`);
                    for (const candidate of state.pendingIceCandidates) {
                        try {
                            await state.peerConnection.addIceCandidate(candidate);
                        } catch(e) {}
                    }
                    state.pendingIceCandidates = [];
                }
                
                return state.peerConnection;
            } catch (error) {
                utils.log(`❌ Erreur création: ${error.message}`);
                throw error;
            }
        },
        
        async restartIce() {
            if (!state.peerConnection || state.isConnected || state.isReconnecting) return;
            
            utils.log('🔄 Restart ICE...');
            state.isReconnecting = true;
            
            try {
                const offer = await state.peerConnection.createOffer({
                    iceRestart: true,
                    offerToReceiveAudio: true,
                    offerToReceiveVideo: true
                });
                await state.peerConnection.setLocalDescription(offer);
                
                if (state.otherSocketId && state.socket) {
                    state.socket.emit('offer', {
                        target: state.otherSocketId,
                        sdp: state.peerConnection.localDescription
                    });
                    utils.log('📤 ICE restart envoyé');
                }
                
                // Timeout pour le restart
                setTimeout(() => {
                    state.isReconnecting = false;
                }, 10000);
            } catch (error) {
                utils.log(`❌ Restart échoué: ${error.message}`);
                state.isReconnecting = false;
            }
        },
        
        async handleDisconnection() {
            if (state.reconnectAttempts >= 5) {
                utils.log('❌ Trop de tentatives');
                utils.showToast('Connexion perdue. Veuillez rafraîchir.', 'error');
                return;
            }
            
            state.reconnectAttempts++;
            const delay = Math.min(5000 * state.reconnectAttempts, 30000);
            
            utils.log(`🔄 Reconnexion dans ${delay/1000}s (${state.reconnectAttempts}/5)`);
            utils.showToast(`Tentative de reconnexion...`, 'warning');
            
            setTimeout(async () => {
                if (!state.isConnected && state.otherSocketId) {
                    this.cleanup();
                    await this.createPeerConnection();
                    if (state.isInitiator) {
                        await this.createOffer();
                    }
                }
            }, delay);
        },
        
        async createOffer() {
            if (!state.peerConnection) return;
            
            try {
                utils.log('📤 Création offre...');
                const offer = await state.peerConnection.createOffer({
                    offerToReceiveAudio: true,
                    offerToReceiveVideo: true
                });
                
                await state.peerConnection.setLocalDescription(offer);
                
                // Attendre un peu pour les candidats ICE
                await new Promise(r => setTimeout(r, 500));
                
                if (state.otherSocketId && state.socket) {
                    state.socket.emit('offer', {
                        target: state.otherSocketId,
                        sdp: state.peerConnection.localDescription
                    });
                    utils.log('📤 Offre envoyée');
                }
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
                
                if (state.socket) {
                    state.socket.emit('answer', {
                        target: data.sender,
                        sdp: state.peerConnection.localDescription
                    });
                    utils.log('📤 Réponse envoyée');
                }
            } catch (error) {
                utils.log(`❌ Erreur offre: ${error.message}`);
            }
        },
        
        async handleAnswer(data) {
            utils.log('📩 Réponse reçue');
            if (!state.peerConnection) return;
            
            try {
                await state.peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
                utils.log('✅ Réponse appliquée');
            } catch (error) {
                utils.log(`❌ Erreur réponse: ${error.message}`);
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
            state.connectionStable = false;
            if (state.peerConnection) {
                try { state.peerConnection.close(); } catch(e) {}
                state.peerConnection = null;
            }
            state.remoteStream = null;
            if (elements.remoteVideo) elements.remoteVideo.srcObject = null;
            state.pendingIceCandidates = [];
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
            reconnectionDelayMax: 10000,
            timeout: 20000
        });
        
        state.socket.on('connect', () => {
            utils.log('✅ Socket connecté');
            if (CONFIG.roomId) {
                state.socket.emit('join-room', CONFIG.roomId);
            }
        });
        
        state.socket.on('connect_error', (error) => {
            utils.log(`❌ Socket erreur: ${error.message}`);
        });
        
        state.socket.on('disconnect', (reason) => {
            utils.log(`⚠️ Socket déconnecté: ${reason}`);
            utils.updateOtherStatus(false);
        });
        
        state.socket.on('user-connected', async (data) => {
            if (data.id === state.socket.id) return;
            if (state.otherSocketId === data.id) return;
            
            utils.log('👤 Participant connecté');
            state.otherSocketId = data.id;
            state.isInitiator = true;
            utils.updateOtherStatus(true);
            
            // Attendre un peu pour éviter les collisions
            await new Promise(r => setTimeout(r, 500));
            
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
        
        // Heartbeat pour garder la connexion active
        setInterval(() => {
            if (state.socket && state.socket.connected) {
                state.socket.emit('ping', { time: Date.now() });
            }
        }, 25000);
        
        state.socket.on('pong', (data) => {
            const latency = Date.now() - data.time;
            if (latency > 1000) {
                utils.log(`⚠️ Latence élevée: ${latency}ms`);
            }
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
            utils.log(`❌ Erreur média: ${error.message}`);
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
                        utils.showToast(track.enabled ? 'Micro activé' : 'Micro coupé', 'info');
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
                        utils.showToast(track.enabled ? 'Caméra activée' : 'Caméra coupée', 'info');
                    }
                }
            };
        }
        
        if (elements.leaveBtn) {
            elements.leaveBtn.onclick = async () => {
                if (confirm('Quitter la consultation ?')) {
                    if (CONFIG.consultationId) {
                        try {
                            await fetch(`/Joinconsultation/endConsultationApi/${CONFIG.consultationId}`, { 
                                method: 'POST',
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });
                        } catch (e) {}
                    }
                    if (state.peerConnection) {
                        state.peerConnection.close();
                    }
                    if (state.socket) {
                        state.socket.disconnect();
                    }
                    window.location.href = '/';
                }
            };
        }
    }

    async function init() {
        utils.log('🚀 Démarrage consultation...');
        
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
        console.log('Stable:', state.connectionStable);
        if (state.peerConnection) {
            console.log('ICE state:', state.peerConnection.iceConnectionState);
            console.log('Connection:', state.peerConnection.connectionState);
            console.log('Signaling:', state.peerConnection.signalingState);
        }
    };
})();