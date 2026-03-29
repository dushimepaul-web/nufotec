// ============================================
// CONSULTATION.JS - VERSION FINALE WEBRTC
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
                
                state.peerConnection.onicecandidate = (event) => {
                    if (event.candidate && state.otherSocketId && state.socket) {
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
                    
                    if (event.track.kind === 'video') {
                        state.isConnected = true;
                        utils.closeWaitingOverlay();
                        utils.updateOtherStatus(true);
                        utils.showToast('Consultation démarrée!', 'success');
                    }
                };
                
                state.peerConnection.onconnectionstatechange = () => {
                    const connState = state.peerConnection.connectionState;
                    utils.log(`📊 Connection: ${connState}`);
                    
                    if (connState === 'connected') {
                        state.isConnected = true;
                        utils.closeWaitingOverlay();
                        utils.updateOtherStatus(true);
                        utils.showToast('Connecté!', 'success');
                    } else if (connState === 'failed') {
                        state.isConnected = false;
                        utils.log('❌ Connexion échouée');
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
        
        async createOffer() {
            if (!state.peerConnection) return;
            
            try {
                utils.log('📤 Création offre...');
                const offer = await state.peerConnection.createOffer({
                    offerToReceiveAudio: true,
                    offerToReceiveVideo: true
                });
                
                await state.peerConnection.setLocalDescription(offer);
                
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
            state.isConnected = false;
            utils.log('🧹 Nettoyé');
        }
    };

    function initSocket() {
        utils.log('📮 Connexion socket...');
        
        state.socket = io(CONFIG.socketUrl, {
            path: CONFIG.socketPath,
            transports: ['polling'],
            upgrade: false,
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
        if (state.peerConnection) {
            console.log('ICE state:', state.peerConnection.iceConnectionState);
            console.log('Connection:', state.peerConnection.connectionState);
        }
    };
})();