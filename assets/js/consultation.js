// ============================================
// CONSULTATION.JS - VERSION FINALE QUI MARCHE
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
        pendingIceCandidates: []
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
            console.log(`[${new Date().toLocaleTimeString()}] ${msg}`);
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

    // ============================================
    // WEBRTC SIMPLIFIÉ
    // ============================================
    const webrtc = {
        async createPeerConnection() {
            try {
                // Récupérer ICE servers
                const response = await fetch('/socket/api/ice-servers');
                const config = await response.json();
                
                state.peerConnection = new RTCPeerConnection(config);
                
                // Listeners
                state.peerConnection.onicecandidate = (event) => {
                    if (event.candidate && state.otherSocketId && state.socket) {
                        state.socket.emit('ice-candidate', {
                            target: state.otherSocketId,
                            candidate: event.candidate
                        });
                    }
                };
                
                state.peerConnection.ontrack = (event) => {
                    if (!state.remoteStream) {
                        state.remoteStream = new MediaStream();
                    }
                    state.remoteStream.addTrack(event.track);
                    if (elements.remoteVideo) {
                        elements.remoteVideo.srcObject = state.remoteStream;
                        elements.remoteVideo.play().catch(e => console.log(e));
                    }
                };
                
                state.peerConnection.onconnectionstatechange = () => {
                    if (state.peerConnection.connectionState === 'connected') {
                        state.isConnected = true;
                        utils.closeWaitingOverlay();
                        utils.updateOtherStatus(true);
                        utils.showToast('Connecté!', 'success');
                    }
                };
                
                // Ajouter tracks locaux
                if (state.localStream) {
                    state.localStream.getTracks().forEach(track => {
                        state.peerConnection.addTrack(track, state.localStream);
                    });
                }
                
                utils.log('✅ PeerConnection créée');
                return state.peerConnection;
            } catch (error) {
                utils.log('❌ Erreur PeerConnection: ' + error.message);
                throw error;
            }
        },
        
        async createOffer() {
            try {
                const offer = await state.peerConnection.createOffer();
                await state.peerConnection.setLocalDescription(offer);
                
                // Attendre ICE gathering
                await new Promise(resolve => {
                    if (state.peerConnection.iceGatheringState === 'complete') {
                        resolve();
                    } else {
                        state.peerConnection.onicegatheringstatechange = () => {
                            if (state.peerConnection.iceGatheringState === 'complete') {
                                resolve();
                            }
                        };
                    }
                });
                
                state.socket.emit('offer', {
                    target: state.otherSocketId,
                    sdp: state.peerConnection.localDescription
                });
                utils.log('📤 Offre envoyée');
            } catch (error) {
                utils.log('❌ Erreur offre: ' + error.message);
            }
        },
        
        async handleOffer(data) {
            try {
                await state.peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
                const answer = await state.peerConnection.createAnswer();
                await state.peerConnection.setLocalDescription(answer);
                
                state.socket.emit('answer', {
                    target: data.sender,
                    sdp: state.peerConnection.localDescription
                });
                utils.log('📤 Réponse envoyée');
            } catch (error) {
                utils.log('❌ Erreur réponse: ' + error.message);
            }
        },
        
        async handleAnswer(data) {
            try {
                await state.peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
                utils.log('✅ Réponse reçue');
            } catch (error) {
                utils.log('❌ Erreur setAnswer: ' + error.message);
            }
        },
        
        async handleIceCandidate(data) {
            try {
                await state.peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
            } catch (error) {
                utils.log('❌ Erreur ICE: ' + error.message);
            }
        },
        
        cleanup() {
            if (state.peerConnection) {
                state.peerConnection.close();
                state.peerConnection = null;
            }
            state.remoteStream = null;
            if (elements.remoteVideo) elements.remoteVideo.srcObject = null;
        }
    };

    // ============================================
    // SOCKET SIMPLIFIÉ
    // ============================================
    function initSocket() {
        utils.log('📮 Connexion socket...');
        
        state.socket = io(CONFIG.socketUrl, {
            path: CONFIG.socketPath,
            transports: ['polling'],      // ← POLLING SEULEMENT
            upgrade: false,
            reconnection: true,
            reconnectionAttempts: 10,
            reconnectionDelay: 2000
        });
        
        state.socket.on('connect', () => {
            utils.log('✅ Socket connecté');
            if (CONFIG.roomId) {
                state.socket.emit('join-room', CONFIG.roomId);
            }
        });
        
        state.socket.on('connect_error', (error) => {
            utils.log('❌ Erreur socket: ' + error.message);
        });
        
        state.socket.on('user-connected', async (data) => {
            const socketId = data.id;
            if (socketId === state.socket.id) return;
            
            utils.log('👤 Autre utilisateur connecté');
            state.otherSocketId = socketId;
            state.isInitiator = true; // Simple: le premier est initiateur
            utils.updateOtherStatus(true);
            
            await webrtc.createPeerConnection();
            await webrtc.createOffer();
        });
        
        state.socket.on('user-disconnected', () => {
            utils.log('👤 Autre utilisateur déconnecté');
            state.otherSocketId = null;
            webrtc.cleanup();
            utils.updateOtherStatus(false);
            utils.showWaitingOverlay();
        });
        
        state.socket.on('offer', (data) => {
            if (data.sender !== state.socket.id) {
                webrtc.handleOffer(data);
            }
        });
        
        state.socket.on('answer', (data) => {
            webrtc.handleAnswer(data);
        });
        
        state.socket.on('ice-candidate', (data) => {
            webrtc.handleIceCandidate(data);
        });
    }

    // ============================================
    // PERMISSIONS
    // ============================================
    async function getMediaStream() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { width: 640, height: 480 },
                audio: true
            });
            if (elements.localVideo) {
                elements.localVideo.srcObject = stream;
                elements.localVideo.muted = true;
                elements.localVideo.play().catch(e => console.log(e));
            }
            return stream;
        } catch (error) {
            utils.log('❌ Pas d\'accès caméra: ' + error.message);
            throw error;
        }
    }

    // ============================================
    // INITIALISATION
    // ============================================
    async function init() {
        utils.log('🚀 Démarrage consultation...');
        
        if (!CONFIG.roomId) {
            utils.log('❌ Pas de roomId');
            return;
        }
        
        utils.showWaitingOverlay();
        
        try {
            state.localStream = await getMediaStream();
            utils.log('✅ Caméra/micro OK');
            
            initSocket();
            utils.log('✅ Prêt, en attente...');
        } catch (error) {
            utils.log('❌ Erreur: ' + error.message);
            utils.showToast('Impossible d\'accéder à la caméra', 'error');
            setTimeout(() => window.location.href = '/', 3000);
        }
    }
    
    // Contrôles
    if (elements.muteAudioBtn) {
        elements.muteAudioBtn.onclick = () => {
            if (state.localStream) {
                const enabled = state.localStream.getAudioTracks()[0].enabled;
                state.localStream.getAudioTracks().forEach(t => t.enabled = !enabled);
                elements.muteAudioBtn.classList.toggle('muted', enabled);
            }
        };
    }
    
    if (elements.muteVideoBtn) {
        elements.muteVideoBtn.onclick = () => {
            if (state.localStream) {
                const enabled = state.localStream.getVideoTracks()[0].enabled;
                state.localStream.getVideoTracks().forEach(t => t.enabled = !enabled);
                elements.muteVideoBtn.classList.toggle('off', enabled);
            }
        };
    }
    
    if (elements.leaveBtn) {
        elements.leaveBtn.onclick = () => {
            if (confirm('Quitter la consultation ?')) {
                if (CONFIG.consultationId) {
                    fetch(`/Joinconsultation/endConsultationApi/${CONFIG.consultationId}`, { method: 'POST' });
                }
                window.location.href = '/';
            }
        };
    }
    
    // Démarrer
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();