class WebRTCClient {
    constructor(config) {
        this.config = config;
        this.pc = null;
        this.localStream = null;
        this.socket = io(config.wsUrl);
        this.isInitiator = false;
        
        this.init();
    }
    
    async init() {
        try {
            this.localStream = await navigator.mediaDevices.getUserMedia({
                video: { width: 1280, height: 720 },
                audio: true
            });
            
            document.getElementById('localVideo').srcObject = this.localStream;
            
            this.setupSocketListeners();
            this.socket.emit('join', this.config.roomId);
            
        } catch(err) {
            console.error('Erreur accès média:', err);
            alert('Impossible d\'accéder à la caméra/micro');
        }
    }
    
    setupSocketListeners() {
        this.socket.on('user-joined', async (userId) => {
            this.isInitiator = true;
            await this.createOffer();
        });
        
        this.socket.on('signal', async (data) => {
            await this.handleSignal(data);
        });
    }
    
    async createPeerConnection() {
        this.pc = new RTCPeerConnection({ iceServers: this.config.iceServers });
        
        this.localStream.getTracks().forEach(track => {
            this.pc.addTrack(track, this.localStream);
        });
        
        this.pc.ontrack = (event) => {
            document.getElementById('remoteVideo').srcObject = event.streams[0];
            document.getElementById('waiting-message').style.display = 'none';
        };
        
        this.pc.onicecandidate = (event) => {
            if(event.candidate) {
                this.socket.emit('signal', {
                    room: this.config.roomId,
                    candidate: event.candidate
                });
            }
        };
    }
    
    // ... méthodes createOffer, handleSignal, etc.
}

// Initialisation
const client = new WebRTCClient(CONFIG);