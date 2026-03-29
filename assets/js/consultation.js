// ============================================
// CONSULTATION.JS - VERSION FINALE CORRIGÉE
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

    // État global
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

    // Éléments DOM
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

    // Utilitaires
    const utils = {
        log: function(msg, level = 'info') {
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

    // ============================================
    // WEBRTC CORRIGÉ (SANS ERREUR DE SCOPE)
    // ============================================
    const webrtc = {
        async createPeerConnection() {
            utils.log('🔧 Création PeerConnection...');
            
            // Nettoyer l'ancienne
            if (state.peerConnection) {
                try {
                    state.peerConnection.close();
                } catch(e) {}
                state.peerConnection = null;
            }
            
            try {
                const response = await fetch('/socket/api/ice-servers');
                const config = await response.json();
                
                state.peerConnection = new RTCPeerConnection(config);
                utils.log('✅ PeerConnection créée');
                
                // Gestion des candidats ICE
                state.peerConnection.onicecandidate = (event) => {
                    if (event.candidate && state.otherSocketId && state.socket) {
                        utils.log('🧊 Envoi candidat ICE');
                        state.socket.emit('ice-candidate', {
                            target: state.otherSocketId,
                            candidate: event.candidate
                        });
                    }
                };
                
                // Gestion des tracks entrants
                state.peerConnection.ontrack = (event) => {
                    utils.log(`📹 Track reçu: ${event.track.kind}`);
                    
                    if (!state.remoteStream) {
                        state.remoteStream = new MediaStream();
                    }
                    state.remoteStream.addTrack(event.track);
                    
                    if (elements.remoteVideo) {
                        // Éviter l'erreur de play interruption
                        const video = elements.remoteVideo;
                        const currentSrc = video.srcObject;
                        
                        if (currentSrc !== state.remoteStream) {
                            video.srcObject = state.remoteStream;
                            // Attendre un peu avant de jouer
                            setTimeout(() => {
                                if (video.paused) {
                                    video.play().catch(e => {
                                        if (e.name !== 'AbortError') {
                                            utils.log(`Erreur play: ${e.message}`);
                                        }
                                    });
                                }
                            }, 100);
                        }
                    }
                };
                
                // Gestion de la connexion - CORRIGÉE (pas de conflit de nom)
                state.peerConnection.onconnectionstatechange = () => {
                    const connState = state.peerConnection.connectionState;
                    utils.log(`📊 Connection state: ${connState}`);
                    
                    if (connState === 'connected') {
                        state.isConnected = true;
                        utils.closeWaitingOverlay();
                        utils.updateOtherStatus(true);
                        utils.showToast('Consultation démarrée!', 'success');
                    } else if (connState === 'failed' || connState === 'disconnected') {
                        state.isConnected = false;
                        utils.updateOtherStatus(false);
                        utils.showWaitingOverlay();
                    } else if (connState === 'closed') {
                        state.isConnected = false;
                    }
                };
                
                // Gestion ICE
                state.peerConnection.oniceconnectionstatechange = () => {
                    const iceState = state.peerConnection.iceConnectionState;
                    utils.log(`🧊 ICE state: ${iceState}`);
                };
                
                // Ajouter les tracks locaux
                if (state.localStream) {
                    state.localStream.getTracks().forEach(track => {
                        try {
                            state.peerConnection.addTrack(track, state.localStream);
                            utils.log(`➕ Track local ajouté: ${track.kind}`);
                        } catch(e) {
                            utils.log(`❌ Erreur ajout track ${track.kind}: ${e.message}`);
                        }
                    });
                }
                
                // Traiter les candidats en attente
                if (state.pendingIceCandidates.length > 0) {
                    utils.log(`📦 Traitement de ${state.pendingIceCandidates.length} candidats en attente...`);
                    for (const candidate of state.pendingIceCandidates) {
                        try {
                            await state.peerConnection.addIceCandidate(candidate);
                        } catch (e) {
                            utils.log(`❌ Erreur candidat: ${e.message}`);
                        }
                    }
                    state.pendingIceCandidates = [];
                }
                
                return state.peerConnection;
            } catch (error) {
                utils.log(`❌ Erreur création PeerConnection: ${error.message}`);
                throw error;
            }
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
                utils.log('✅ Offre créée, envoi...');
                
                // Attendre ICE gathering (max 3 secondes)
                await new Promise((resolve) => {
                    if (state.peerConnection.iceGatheringState === 'complete') {
                        resolve();
                    } else {
                        const timeout = setTimeout(resolve, 3000);
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
                utils.log('📤 Offre envoyée avec succès');
            } catch (error) {
                utils.log(`❌ Erreur création offre: ${error.message}`);
            }
        },
        
        async handleOffer(data) {
            utils.log('📩 Offre reçue');
            
            if (!state.peerConnection) {
                utils.log('🔧 Création PeerConnection...');
                await this.createPeerConnection();
            }
            
            try {
                await state.peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
                utils.log('✅ Description distante définie');
                
                const answer = await state.peerConnection.createAnswer();
                await state.peerConnection.setLocalDescription(answer);
                utils.log('✅ Réponse créée');
                
                state.socket.emit('answer', {
                    target: data.sender,
                    sdp: state.peerConnection.localDescription
                });
                utils.log('📤 Réponse envoyée');
            } catch (error) {
                utils.log(`❌ Erreur handleOffer: ${error.message}`);
            }
        },
        
        async handleAnswer(data) {
            utils.log('📩 Réponse reçue');
            
            if (!state.peerConnection) {
                utils.log('❌ PeerConnection inexistante');
                return;
            }
            
            try {
                await state.peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp));
                utils.log('✅ Réponse appliquée avec succès');
            } catch (error) {
                utils.log(`❌ Erreur handleAnswer: ${error.message}`);
            }
        },
        
        async handleIceCandidate(data) {
            const candidate = new RTCIceCandidate(data.candidate);
            
            if (!state.peerConnection) {
                state.pendingIceCandidates.push(candidate);
                utils.log(`⏳ Candidat mis en attente (${state.pendingIceCandidates.length})`);
                return;
            }
            
            try {
                await state.peerConnection.addIceCandidate(candidate);
                utils.log('🧊 Candidat ICE ajouté');
            } catch (error) {
                utils.log(`❌ Erreur ajout ICE: ${error.message}`);
            }
        },
        
        cleanup() {
            if (state.peerConnection) {
                try {
                    state.peerConnection.close();
                } catch(e) {}
                state.peerConnection = null;
            }
            state.remoteStream = null;
            if (elements.remoteVideo) {
                elements.remoteVideo.srcObject = null;
            }
            state.pendingIceCandidates = [];
            utils.log('🧹 Nettoyage WebRTC effectué');
        }
    };

    // ============================================
    // SOCKET
    // ============================================
    function initSocket() {
        utils.log('📮 Connexion socket...');
        
        state.socket = io(CONFIG.socketUrl, {
            path: CONFIG.socketPath,
            transports: ['polling'],
            upgrade: false,
            reconnection: true,
            reconnectionAttempts: 10,
            reconnectionDelay: 2000,
            timeout: 10000
        });
        
        state.socket.on('connect', () => {
            utils.log('✅ Socket connecté');
            if (CONFIG.roomId) {
                state.socket.emit('join-room', CONFIG.roomId);
                utils.log(`📌 Salle rejointe: ${CONFIG.roomId}`);
            }
        });
        
        state.socket.on('connect_error', (error) => {
            utils.log(`❌ Erreur socket: ${error.message}`);
        });
        
        state.socket.on('disconnect', () => {
            utils.log('⚠️ Socket déconnecté');
            utils.updateOtherStatus(false);
        });
        
        state.socket.on('user-connected', async (data) => {
            const socketId = data.id;
            if (socketId === state.socket.id) return;
            
            // Éviter les doubles connexions
            if (state.otherSocketId === socketId) {
                utils.log('👤 Utilisateur déjà connecté, ignoré');
                return;
            }
            
            utils.log('👤 Autre utilisateur connecté');
            state.otherSocketId = socketId;
            utils.updateOtherStatus(true);
            
            // Petite pause pour éviter les conflits
            await new Promise(r => setTimeout(r, 500));
            
            await webrtc.createPeerConnection();
            await webrtc.createOffer();
        });
        
        state.socket.on('user-disconnected', (data) => {
            if (data.id === state.otherSocketId) {
                utils.log('👤 Autre utilisateur déconnecté');
                state.otherSocketId = null;
                webrtc.cleanup();
                utils.updateOtherStatus(false);
                utils.showWaitingOverlay();
                utils.showToast('Le participant a quitté', 'warning');
            }
        });
        
        state.socket.on('offer', async (data) => {
            if (data.sender === state.socket.id) return;
            utils.log('📨 Offre reçue du serveur');
            state.otherSocketId = data.sender;
            await webrtc.handleOffer(data);
        });
        
        state.socket.on('answer', async (data) => {
            if (data.sender === state.socket.id) return;
            utils.log('📨 Réponse reçue du serveur');
            await webrtc.handleAnswer(data);
        });
        
        state.socket.on('ice-candidate', async (data) => {
            if (data.sender === state.socket.id) return;
            await webrtc.handleIceCandidate(data);
        });
    }

    // ============================================
    // PERMISSIONS
    // ============================================
    async function getMediaStream() {
        utils.log('📹 Demande accès caméra/micro...');
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true
                }
            });
            
            if (elements.localVideo) {
                elements.localVideo.srcObject = stream;
                elements.localVideo.muted = true;
                await elements.localVideo.play();
            }
            
            utils.log('✅ Caméra/micro activés');
            return stream;
        } catch (error) {
            utils.log(`❌ Erreur accès média: ${error.message}`);
            throw error;
        }
    }

    // ============================================
    // CONTROLES UI
    // ============================================
    function setupControls() {
        if (elements.muteAudioBtn) {
            elements.muteAudioBtn.onclick = () => {
                if (state.localStream) {
                    const audioTrack = state.localStream.getAudioTracks()[0];
                    if (audioTrack) {
                        audioTrack.enabled = !audioTrack.enabled;
                        elements.muteAudioBtn.classList.toggle('muted', !audioTrack.enabled);
                        utils.showToast(audioTrack.enabled ? 'Micro activé' : 'Micro coupé', 'info');
                    }
                }
            };
        }
        
        if (elements.muteVideoBtn) {
            elements.muteVideoBtn.onclick = () => {
                if (state.localStream) {
                    const videoTrack = state.localStream.getVideoTracks()[0];
                    if (videoTrack) {
                        videoTrack.enabled = !videoTrack.enabled;
                        elements.muteVideoBtn.classList.toggle('off', !videoTrack.enabled);
                        utils.showToast(videoTrack.enabled ? 'Caméra activée' : 'Caméra coupée', 'info');
                    }
                }
            };
        }
        
        if (elements.leaveBtn) {
            elements.leaveBtn.onclick = async () => {
                if (confirm('Voulez-vous vraiment quitter la consultation ?')) {
                    if (CONFIG.consultationId) {
                        try {
                            await fetch(`/Joinconsultation/endConsultationApi/${CONFIG.consultationId}`, { 
                                method: 'POST',
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });
                        } catch (e) {}
                    }
                    window.location.href = '/';
                }
            };
        }
    }

    // ============================================
    // INITIALISATION
    // ============================================
    async function init() {
        utils.log('🚀 Démarrage consultation...');
        
        if (!CONFIG.roomId) {
            utils.log('❌ Erreur: roomId manquant');
            utils.showToast('Erreur de configuration', 'error');
            return;
        }
        
        utils.showWaitingOverlay();
        
        try {
            // Obtenir le stream local
            state.localStream = await getMediaStream();
            
            // Initialiser socket
            initSocket();
            
            // Configurer les contrôles
            setupControls();
            
            utils.log('✅ Consultation prête, en attente du participant...');
            utils.updateOtherStatus(false);
            
        } catch (error) {
            utils.log(`❌ Erreur initialisation: ${error.message}`);
            utils.showToast('Impossible d\'accéder à la caméra/micro', 'error');
            setTimeout(() => window.location.href = '/', 3000);
        }
    }
    
    // Démarrer
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Helper pour debug
    window.debugConsultation = () => {
        console.log('=== DEBUG ===');
        console.log('Socket connecté:', state.socket?.connected);
        console.log('PeerConnection:', !!state.peerConnection);
        console.log('Other ID:', state.otherSocketId);
        console.log('Connected:', state.isConnected);
        console.log('Local stream:', !!state.localStream);
        console.log('Remote stream:', !!state.remoteStream);
        console.log('Pending ICE:', state.pendingIceCandidates.length);
        if (state.peerConnection) {
            console.log('ICE state:', state.peerConnection.iceConnectionState);
            console.log('Connection state:', state.peerConnection.connectionState);
            console.log('Signaling state:', state.peerConnection.signalingState);
        }
    };
})();