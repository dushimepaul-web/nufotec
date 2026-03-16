<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Call - <?= $room_id ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #1a1a2e; color: white; height: 100vh; overflow: hidden; }
        #container { display: flex; flex-direction: column; height: 100vh; }
        #videos { flex: 1; position: relative; background: #16213e; }
        #remoteVideo { width: 100%; height: 100%; object-fit: cover; background: #0f0f1e; }
        #localVideo { position: absolute; bottom: 100px; right: 20px; width: 200px; height: 150px; object-fit: cover; border-radius: 10px; border: 3px solid #e94560; box-shadow: 0 4px 15px rgba(0,0,0,0.5); z-index: 10; }
        #info { position: absolute; top: 20px; left: 20px; background: rgba(0,0,0,0.7); padding: 15px; border-radius: 8px; font-size: 14px; }
        #waiting { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; background: rgba(0,0,0,0.8); padding: 30px; border-radius: 15px; }
        .spinner { width: 50px; height: 50px; border: 4px solid rgba(255,255,255,0.3); border-top-color: #e94560; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        #share-link { margin-top: 15px; padding: 10px; background: rgba(255,255,255,0.1); border-radius: 5px; font-family: monospace; font-size: 12px; cursor: pointer; }
        #controls { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.9); padding: 20px; display: flex; justify-content: center; gap: 20px; z-index: 100; }
        button { width: 60px; height: 60px; border-radius: 50%; border: none; font-size: 24px; cursor: pointer; transition: all 0.3s; background: rgba(255,255,255,0.2); color: white; }
        button:hover { transform: scale(1.1); background: rgba(255,255,255,0.3); }
        button.active { background: #e94560; }
        button.danger { background: #ff4757; }
    </style>
</head>
<body>
    <div id="container">
        <div id="videos">
            <video id="remoteVideo" autoplay playsinline></video>
            <video id="localVideo" autoplay muted playsinline></video>
            <div id="info">
                <div>🔴 Room: <strong><?= $room_id ?></strong></div>
                <div>👤 <?= $username ?></div>
                <div id="status" style="margin-top:5px;color:#aaa;">Initialisation...</div>
            </div>
            <div id="waiting">
                <div class="spinner"></div>
                <h3>En attente d'un participant...</h3>
                <div id="share-link" onclick="copyLink()"><?= base_url('videocall?room=' . $room_id) ?></div>
            </div>
        </div>
        <div id="controls">
            <button id="btn-mic" onclick="toggleMic()">🎤</button>
            <button id="btn-cam" onclick="toggleCam()">📹</button>
            <button class="danger" onclick="endCall()">📞</button>
        </div>
    </div>

    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script>
        const CONFIG = {
            roomId: "<?= $room_id ?>",
            userId: "<?= $user_id ?>",
            username: "<?= $username ?>",
            wsUrl: "<?= $ws_url ?>"
        };

        let socket, pc, localStream;
        let isInitiator = false;
        let iceCandidatesQueue = []; // File d'attente pour ICE candidates
        let makingOffer = false;

        async function init() {
            try {
                updateStatus('Caméra...');
                localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                document.getElementById('localVideo').srcObject = localStream;
                updateStatus('Connexion...');
                connectSocket();
            } catch (err) {
                alert('Erreur caméra: ' + err.message);
            }
        }

        function connectSocket() {
            socket = io(CONFIG.wsUrl);
            
            socket.on('connect', () => {
                console.log('✅ Connecté');
                joinRoom();
            });
            
            socket.on('user-joined', () => {
                console.log('👥 Participant arrivé');
                document.getElementById('waiting').style.display = 'none';
                isInitiator = true;
                startCall();
            });
            
            // Gestion des signaux avec file d'attente
            socket.on('signal', async (data) => {
                try {
                    if (data.type === 'offer') {
                        await handleOffer(data.data);
                    } else if (data.type === 'answer') {
                        await handleAnswer(data.data);
                    } else if (data.type === 'ice-candidate') {
                        await handleIceCandidate(data.data);
                    }
                } catch (err) {
                    console.error('Erreur signal:', err);
                }
            });
            
            socket.on('user-left', () => location.reload());
        }

        function joinRoom() {
            socket.emit('join-room', CONFIG.roomId, (response) => {
                if (response.success && !response.isInitiator) {
                    document.getElementById('waiting').style.display = 'none';
                    ensurePeerConnection(); // Préparer PC en avance
                }
            });
        }

        // Crée PC une SEULE fois
        function ensurePeerConnection() {
            if (pc) return pc;
            
            console.log('🔧 Création PeerConnection');
            pc = new RTCPeerConnection({
                iceServers: [{ urls: "stun:stun.l.google.com:19302" }]
            });
            
            localStream.getTracks().forEach(track => pc.addTrack(track, localStream));
            
            pc.ontrack = (event) => {
                console.log('📺 Vidéo reçue!');
                document.getElementById('remoteVideo').srcObject = event.streams[0];
                updateStatus('Connecté ✓');
            };
            
            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    socket.emit('signal', { type: 'ice-candidate', data: event.candidate });
                }
            };
            
            return pc;
        }

        async function startCall() {
            const pc = ensurePeerConnection();
            makingOffer = true;
            
            try {
                const offer = await pc.createOffer();
                await pc.setLocalDescription(offer);
                socket.emit('signal', { type: 'offer', data: offer });
            } catch (err) {
                console.error('Erreur offer:', err);
            } finally {
                makingOffer = false;
            }
        }

        async function handleOffer(offer) {
            const pc = ensurePeerConnection();
            
            // Éviter la collision d'offers
            if (pc.signalingState !== 'stable') {
                console.log('⚠️ État non stable, on ignore');
                return;
            }
            
            await pc.setRemoteDescription(new RTCSessionDescription(offer));
            
            // Traiter les ICE candidates en attente
            processIceQueue();
            
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            socket.emit('signal', { type: 'answer', data: answer });
        }

        async function handleAnswer(answer) {
            if (!pc) return;
            await pc.setRemoteDescription(new RTCSessionDescription(answer));
            processIceQueue(); // Traiter les ICE en attente
        }

        async function handleIceCandidate(candidate) {
            if (!pc) {
                iceCandidatesQueue.push(candidate);
                return;
            }
            
            if (pc.remoteDescription && pc.remoteDescription.type) {
                await pc.addIceCandidate(new RTCIceCandidate(candidate));
            } else {
                iceCandidatesQueue.push(candidate);
            }
        }

        function processIceQueue() {
            while (iceCandidatesQueue.length > 0) {
                const candidate = iceCandidatesQueue.shift();
                pc.addIceCandidate(new RTCIceCandidate(candidate)).catch(console.error);
            }
        }

        function toggleMic() {
            if (!localStream) return;
            const track = localStream.getAudioTracks()[0];
            track.enabled = !track.enabled;
            document.getElementById('btn-mic').classList.toggle('active', !track.enabled);
        }

        function toggleCam() {
            if (!localStream) return;
            const track = localStream.getVideoTracks()[0];
            track.enabled = !track.enabled;
            document.getElementById('btn-cam').classList.toggle('active', !track.enabled);
        }

        function endCall() {
            if (pc) pc.close();
            if (socket) socket.disconnect();
            if (localStream) localStream.getTracks().forEach(t => t.stop());
            window.location.href = '/nufotec';
        }

        function updateStatus(text) { document.getElementById('status').textContent = text; }
        function copyLink() {
            navigator.clipboard.writeText("<?= base_url('videocall?room=' . $room_id) ?>")
                .then(() => alert('Lien copié!'));
        }

        init();
    </script>
</body>
</html>