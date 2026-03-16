class WebRTCClient {
    constructor(config) {
        this.config = config;
        this.pc = null;
        this.localStream = null;
        this.socket = io(config.wsUrl);
        this.isInitiator = false;
        this.iceCandidatesQueue = [];
        
        this.init();
    }
    
    async init() {
        try {
            const constraints = {
                video: { width: 1280, height: 720 },
                audio: true
            };
            this.localStream = await navigator.mediaDevices.getUserMedia(constraints);
            document.getElementById('localVideo').srcObject = this.localStream;
            
            this.setupSocketListeners();
            this.joinRoom();
            
        } catch(err) {
            console.error('Erreur accès média:', err);
            alert('Impossible d\'accéder à la caméra/micro');
        }
    }
    
    joinRoom() {
        this.socket.emit('join-room', this.config.roomId, (response) => {
            if (response.success) {
                this.isInitiator = response.isInitiator;
                console.log('Rejoint la salle, initiateur:', this.isInitiator);
                // Si on est l'initiateur, on attend que quelqu'un rejoigne
                if (!this.isInitiator) {
                    // Si on n'est pas l'initiateur, on peut déjà préparer la connexion?
                    // Rien à faire pour l'instant
                }
            } else {
                alert(response.error);
            }
        });
    }
    
    setupSocketListeners() {
        this.socket.on('user-joined', (data) => {
            console.log('Un participant a rejoint');
            // Si on est l'initiateur, on crée l'offre
            if (this.isInitiator) {
                this.createOffer();
            }
        });
        
        this.socket.on('signal', async (data) => {
            await this.handleSignal(data);
        });
    }
    
    async createPeerConnection() {
        if (this.pc) return this.pc;
        
        this.pc = new RTCPeerConnection({ iceServers: this.config.iceServers });
        
        this.localStream.getTracks().forEach(track => {
            this.pc.addTrack(track, this.localStream);
        });
        
        this.pc.ontrack = (event) => {
            document.getElementById('remoteVideo').srcObject = event.streams[0];
            document.getElementById('waiting').style.display = 'none';
        };
        
        this.pc.onicecandidate = (event) => {
            if (event.candidate) {
                this.socket.emit('signal', {
                    type: 'ice-candidate',
                    data: event.candidate
                });
            }
        };
        
        return this.pc;
    }
    
    async createOffer() {
        const pc = await this.createPeerConnection();
        try {
            const offer = await pc.createOffer();
            await pc.setLocalDescription(offer);
            this.socket.emit('signal', {
                type: 'offer',
                data: offer
            });
        } catch(err) {
            console.error('Erreur création offre:', err);
        }
    }
    
    async handleSignal(data) {
        const pc = await this.createPeerConnection();
        
        if (data.type === 'offer') {
            await pc.setRemoteDescription(new RTCSessionDescription(data.data));
            this.processIceQueue();
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            this.socket.emit('signal', {
                type: 'answer',
                data: answer
            });
        } else if (data.type === 'answer') {
            await pc.setRemoteDescription(new RTCSessionDescription(data.data));
            this.processIceQueue();
        } else if (data.type === 'ice-candidate') {
            if (!pc.remoteDescription) {
                this.iceCandidatesQueue.push(data.data);
            } else {
                await pc.addIceCandidate(new RTCIceCandidate(data.data));
            }
        }
    }
    
    processIceQueue() {
        while (this.iceCandidatesQueue.length > 0) {
            const candidate = this.iceCandidatesQueue.shift();
            this.pc.addIceCandidate(new RTCIceCandidate(candidate));
        }
    }
}

// Initialisation
const client = new WebRTCClient(CONFIG);