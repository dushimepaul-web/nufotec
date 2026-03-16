<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Téléconsultation - <?= htmlspecialchars($room_id) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body,html { height:100%; overflow:hidden; background:#1a1a2e; font-family:'Segoe UI',sans-serif; }
        
        .info-bar { 
            position:fixed; top:0; left:0; right:0; height:70px; 
            background:rgba(255,255,255,0.95); backdrop-filter:blur(10px); 
            padding:0 24px; z-index:1000; display:flex; 
            justify-content:space-between; align-items:center; 
            box-shadow:0 4px 20px rgba(0,0,0,0.1); 
        }
        .brand-section { display:flex; align-items:center; gap:20px; }
        .logo-icon { 
            width:45px; height:45px; 
            background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); 
            border-radius:12px; display:flex; align-items:center; 
            justify-content:center; color:white; font-size:24px; 
        }
        .consultation-badge { 
            background:rgba(102,126,234,0.1); padding:8px 16px; 
            border-radius:30px; border:1px solid rgba(102,126,234,0.2); 
        }
        .participants-info { 
            display:flex; gap:15px; margin-top:5px; 
            font-size:12px; color:#4a5568; 
        }
        .call-controls { display:flex; align-items:center; gap:20px; }
        .timer-container { 
            background:#2d3748; padding:8px 20px; border-radius:30px; 
            color:white; font-family:'Courier New',monospace; 
            font-size:20px; font-weight:700; 
        }
        .btn-end-call { 
            background:linear-gradient(135deg,#f56565 0%,#e53e3e 100%); 
            color:white; border:none; padding:10px 25px; 
            border-radius:30px; font-weight:600; 
            display:flex; align-items:center; gap:8px; 
            transition:all 0.3s ease; 
        }
        .btn-end-call:hover { 
            transform:translateY(-2px); 
            box-shadow:0 6px 20px rgba(229,62,62,0.4); 
        }
        
        #video-container { 
            position:fixed; top:70px; left:0; right:0; bottom:0; 
            background:#1a1a2e; display:none; 
        }
        #remoteVideo { width:100%; height:100%; object-fit:cover; }
        #localVideo { 
            position:absolute; bottom:100px; right:20px; 
            width:240px; height:180px; border-radius:12px; 
            border:3px solid white; object-fit:cover; 
            box-shadow:0 10px 30px rgba(0,0,0,0.3); 
        }
        #controls { 
            position:absolute; bottom:30px; left:50%; 
            transform:translateX(-50%); display:flex; gap:15px; 
            background:rgba(0,0,0,0.5); padding:15px 30px; 
            border-radius:50px; backdrop-filter:blur(10px); 
        }
        .control-btn { 
            width:55px; height:55px; border-radius:50%; border:none; 
            display:flex; align-items:center; justify-content:center; 
            font-size:24px; cursor:pointer; transition:all 0.3s ease; 
        }
        .control-btn.primary { background:white; color:#333; }
        .control-btn.primary:hover { background:#f0f0f0; }
        .control-btn.danger { background:#f56565; color:white; }
        .control-btn.danger:hover { background:#e53e3e; transform:scale(1.1); }
        .control-btn.muted { background:#f56565 !important; color:white !important; }
        
        #prejoin-screen { 
            position:fixed; top:70px; left:0; right:0; bottom:0; 
            background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%); 
            display:flex; align-items:center; justify-content:center; 
            padding:20px; z-index:100; 
        }
        .prejoin-card { 
            background:rgba(255,255,255,0.05); backdrop-filter:blur(10px); 
            border-radius:20px; padding:40px; text-align:center; 
            max-width:450px; width:100%; border:1px solid rgba(255,255,255,0.1); 
        }
        .user-avatar { 
            width:120px; height:120px; border-radius:50%; 
            border:4px solid #667eea; object-fit:cover; 
            margin:0 auto 20px; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); 
            display:flex; align-items:center; justify-content:center; 
            color:white; font-size:48px; font-weight:700; 
        }
        .user-name { color:white; font-size:24px; font-weight:700; margin-bottom:8px; }
        .connection-status { 
            display:flex; align-items:center; justify-content:center; 
            gap:8px; margin-bottom:25px; color:#48bb78; font-size:14px; 
        }
        .device-controls { display:flex; gap:15px; justify-content:center; margin-bottom:25px; }
        .device-btn { 
            background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); 
            color:white; padding:15px 25px; border-radius:12px; 
            cursor:pointer; transition:all 0.3s ease; 
            display:flex; flex-direction:column; align-items:center; 
            gap:8px; min-width:100px; 
        }
        .device-btn.active { 
            background:rgba(72,187,120,0.2); border-color:#48bb78; color:#48bb78; 
        }
        .device-btn i { font-size:24px; }
        
        .join-btn { 
            background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); 
            color:white; border:none; padding:18px 40px; 
            border-radius:50px; font-weight:600; font-size:16px; 
            cursor:pointer; transition:all 0.3s ease; width:100%; 
            display:flex; align-items:center; justify-content:center; gap:10px; 
        }
        .join-btn:hover { 
            transform:translateY(-2px); 
            box-shadow:0 15px 40px rgba(102,126,234,0.4); 
        }
        
        .waiting-overlay { 
            position:absolute; top:50%; left:50%; 
            transform:translate(-50%,-50%); text-align:center; color:white; 
        }
        .spinner-border { width:3rem; height:3rem; margin-bottom:20px; }
        
        @media (max-width:768px) { 
            .info-bar { padding:0 15px; } 
            .consultation-badge { display:none; } 
            #localVideo { width:120px; height:90px; bottom:100px; } 
            .prejoin-card { padding:30px 20px; } 
        }
    </style>
</head>
<body>
    <!-- Barre d'info -->
    <div class="info-bar">
        <div class="brand-section">
            <div class="logo-icon"><i class="bi bi-heart-pulse-fill"></i></div>
            <div class="consultation-badge">
                <div class="d-flex align-items-center gap-2 fw-bold text-dark">
                    <i class="bi bi-qr-code"></i> <?= htmlspecialchars($room_id) ?>
                </div>
                <div class="participants-info">
                    <div class="d-flex align-items-center gap-1">
                        <i class="bi bi-person"></i> <?= htmlspecialchars($patient_name) ?>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <i class="bi bi-person-badge"></i> <?= htmlspecialchars($medecin_name) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="call-controls">
            <div class="timer-container" id="timer">00:00</div>
            <button class="btn-end-call" onclick="endCall()">
                <i class="bi bi-telephone-x-fill"></i> Terminer
            </button>
        </div>
    </div>

    <!-- Écran de pré-join -->
    <div id="prejoin-screen">
        <div class="prejoin-card">
            <?php
            $user_initials = strtoupper(substr($username, 0, 1));
            if (strpos($username, ' ') !== false) {
                $parts = explode(' ', $username);
                $user_initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
            }
            ?>
            <div class="user-avatar"><?= $user_initials ?></div>
            <div class="user-name"><?= htmlspecialchars($username) ?></div>
            <div class="connection-status">
                <i class="bi bi-wifi"></i> <span>Connexion Internet détectée</span>
            </div>
            <div class="device-controls">
                <button class="device-btn active" id="micBtn" onclick="toggleDevice('mic')">
                    <i class="bi bi-mic-fill"></i><span>Micro</span>
                </button>
                <button class="device-btn active" id="camBtn" onclick="toggleDevice('cam')">
                    <i class="bi bi-camera-video-fill"></i><span>Caméra</span>
                </button>
            </div>
            <button onclick="enterConsultation()" class="join-btn">
                <i class="bi bi-camera-video-fill"></i> Rejoindre la consultation
            </button>
        </div>
    </div>

    <!-- Zone vidéo -->
    <div id="video-container">
        <video id="remoteVideo" autoplay playsinline></video>
        <video id="localVideo" autoplay playsinline muted></video>
        <div class="waiting-overlay" id="waiting">
            <div class="spinner-border text-light" role="status"></div>
            <h4>En attente du participant...</h4>
            <p class="text-light opacity-75">Partagez le code : <strong><?= $room_id ?></strong></p>
        </div>
        <div id="controls">
            <button class="control-btn primary" id="btn-mic" onclick="toggleMic()">
                <i class="bi bi-mic-fill"></i>
            </button>
            <button class="control-btn primary" id="btn-cam" onclick="toggleCam()">
                <i class="bi bi-camera-video-fill"></i>
            </button>
            <button class="control-btn danger" onclick="endCall()">
                <i class="bi bi-telephone-x-fill"></i>
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    
    <script>
        // ✅ Configuration simplifiée comme votre code qui marche
        const CONFIG = {
            roomId: "<?= $room_id ?>",
            userId: "<?= $user_id ?>",
            username: "<?= htmlspecialchars($username) ?>",
            userType: "<?= $user_type ?>",
            wsUrl: "<?= $ws_url ?>",
            consultationId: "<?= $consultation_id ?>",
            isInitiator: <?= $is_initiator ? 'true' : 'false' ?>
        };

        let socket, pc, localStream;
        let micEnabled = true, camEnabled = true;
        let iceCandidatesQueue = [];
        let timerInterval;
        let makingOffer = false;

        // Contrôles pré-join
        function toggleDevice(type) {
            const btn = document.getElementById(type + 'Btn');
            const isActive = btn.classList.contains('active');
            
            if (type === 'mic') {
                micEnabled = !isActive;
                btn.classList.toggle('active');
                btn.innerHTML = micEnabled 
                    ? '<i class="bi bi-mic-fill"></i><span>Micro</span>' 
                    : '<i class="bi bi-mic-mute-fill" style="color: #f56565;"></i><span style="color: #f56565;">Off</span>';
            } else {
                camEnabled = !isActive;
                btn.classList.toggle('active');
                btn.innerHTML = camEnabled 
                    ? '<i class="bi bi-camera-video-fill"></i><span>Caméra</span>' 
                    : '<i class="bi bi-camera-video-off-fill" style="color: #f56565;"></i><span style="color: #f56565;">Off</span>';
            }
        }

        function enterConsultation() {
            const prejoin = document.getElementById('prejoin-screen');
            prejoin.style.opacity = '0';
            prejoin.style.transition = 'opacity 0.3s';
            
            setTimeout(async () => {
                prejoin.style.display = 'none';
                document.getElementById('video-container').style.display = 'block';
                await init();
            }, 300);
        }

        // ✅ Initialisation simplifiée comme votre code
        async function init() {
            try {
                console.log('🔧 Demande accès caméra/micro...');
                
                // ✅ Contraintes audio explicites
                const constraints = {
                    video: camEnabled ? { width: 1280, height: 720 } : false,
                    audio: micEnabled ? {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    } : false
                };
                
                localStream = await navigator.mediaDevices.getUserMedia(constraints);
                
                // Log des tracks
                console.log('🎵 Audio tracks:', localStream.getAudioTracks().length);
                console.log('🎥 Video tracks:', localStream.getVideoTracks().length);
                
                // Forcer activation audio
                localStream.getAudioTracks().forEach(t => t.enabled = true);
                
                document.getElementById('localVideo').srcObject = localStream;
                connectSocket();
                startTimer();
            } catch (err) {
                alert('Erreur caméra: ' + err.message);
            }
        }

        // ✅ Connexion Socket simplifiée comme votre code
        function connectSocket() {
            socket = io(CONFIG.wsUrl);
            
            socket.on('connect', () => {
                console.log('✅ Connecté au serveur');
                joinRoom();
            });
            
            socket.on('user-joined', () => {
                console.log('👥 Participant arrivé');
                document.getElementById('waiting').style.display = 'none';
                CONFIG.isInitiator = true;
                startCall();
            });
            
            // ✅ Gestion des signaux avec file d'attente
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
            
            socket.on('user-left', () => {
                console.log('👋 Participant parti');
                location.reload();
            });
        }

        // ✅ Join room comme votre code
        function joinRoom() {
            socket.emit('join-room', CONFIG.roomId, (response) => {
                console.log('📋 Réponse join-room:', response);
                if (response.success && !response.isInitiator) {
                    document.getElementById('waiting').style.display = 'none';
                    ensurePeerConnection(); // Préparer PC en avance
                }
            });
        }

        // ✅ Création PC une SEULE fois - comme votre code
        function ensurePeerConnection() {
            if (pc) return pc;
            
            console.log('🔧 Création PeerConnection');
            
            pc = new RTCPeerConnection({
                iceServers: <?= json_encode($ice_servers) ?>
            });
            
            // ✅ Ajouter les tracks locaux
            localStream.getTracks().forEach(track => {
                console.log('➕ Ajout track:', track.kind);
                pc.addTrack(track, localStream);
            });
            
            // ✅ Gestion des tracks distants - CRITIQUE POUR L'AUDIO
            pc.ontrack = (event) => {
                console.log('📺 Track distant reçu:', event.track.kind);
                
                const remoteVideo = document.getElementById('remoteVideo');
                
                // ✅ Attacher le stream distant
                if (event.streams && event.streams[0]) {
                    remoteVideo.srcObject = event.streams[0];
                    
                    // ✅ FORCER le volume et démuter
                    remoteVideo.volume = 1.0;
                    remoteVideo.muted = false;
                    
                    // ✅ Jouer explicitement
                    remoteVideo.play().then(() => {
                        console.log('▶️ Lecture démarrée');
                    }).catch(e => console.error('❌ Erreur lecture:', e));
                }
                
                document.getElementById('waiting').style.display = 'none';
            };
            
            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    socket.emit('signal', { 
                        type: 'ice-candidate', 
                        data: event.candidate 
                    });
                }
            };
            
            return pc;
        }

        // ✅ Démarrer l'appel comme votre code
        async function startCall() {
            const pc = ensurePeerConnection();
            makingOffer = true;
            
            try {
                console.log('📤 Création offre...');
                
                const offer = await pc.createOffer({
                    offerToReceiveAudio: true,  // ✅ Explicitement demander l'audio
                    offerToReceiveVideo: true
                });
                
                await pc.setLocalDescription(offer);
                socket.emit('signal', { type: 'offer', data: offer });
                console.log('✅ Offre envoyée');
                
            } catch (err) {
                console.error('Erreur offer:', err);
            } finally {
                makingOffer = false;
            }
        }

        // ✅ Gérer l'offre reçue
        async function handleOffer(offer) {
            const pc = ensurePeerConnection();
            
            // ✅ Éviter collision d'offers
            if (pc.signalingState !== 'stable') {
                console.log('⚠️ État non stable, on ignore');
                return;
            }
            
            await pc.setRemoteDescription(new RTCSessionDescription(offer));
            console.log('✅ Offre distante acceptée');
            
            // Traiter les ICE en attente
            processIceQueue();
            
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            socket.emit('signal', { type: 'answer', data: answer });
            console.log('✅ Réponse envoyée');
        }

        // ✅ Gérer la réponse
        async function handleAnswer(answer) {
            if (!pc) return;
            await pc.setRemoteDescription(new RTCSessionDescription(answer));
            processIceQueue();
            console.log('✅ Réponse distante acceptée');
        }

        // ✅ Gérer les ICE candidates avec file d'attente
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

        // ✅ Contrôles pendant l'appel
        function toggleMic() {
            if (!localStream) return;
            const track = localStream.getAudioTracks()[0];
            if (!track) return;
            
            track.enabled = !track.enabled;
            micEnabled = track.enabled;
            
            const btn = document.getElementById('btn-mic');
            btn.innerHTML = micEnabled 
                ? '<i class="bi bi-mic-fill"></i>' 
                : '<i class="bi bi-mic-mute-fill"></i>';
            btn.classList.toggle('muted', !micEnabled);
            
            console.log('🎤 Micro:', micEnabled ? 'ON' : 'OFF');
        }

        function toggleCam() {
            if (!localStream) return;
            const track = localStream.getVideoTracks()[0];
            if (!track) return;
            
            track.enabled = !track.enabled;
            camEnabled = track.enabled;
            
            const btn = document.getElementById('btn-cam');
            btn.innerHTML = camEnabled 
                ? '<i class="bi bi-camera-video-fill"></i>' 
                : '<i class="bi bi-camera-video-off-fill"></i>';
            btn.classList.toggle('muted', !camEnabled);
        }

        // ✅ Terminer l'appel
        async function endCall() {
            if (CONFIG.userType === 'medecin') {
                try {
                    await fetch('<?= base_url('Videocall/endConsultation') ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ room_id: CONFIG.roomId })
                    });
                } catch (e) { 
                    console.error('Erreur fin consultation', e); 
                }
            }
            
            if (pc) pc.close();
            if (socket) socket.disconnect();
            if (localStream) localStream.getTracks().forEach(t => t.stop());
            if (timerInterval) clearInterval(timerInterval);
            
            window.location.href = '<?= base_url('Dashboard') ?>';
        }

        function startTimer() {
            let seconds = 0;
            timerInterval = setInterval(() => {
                seconds++;
                const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
                const secs = (seconds % 60).toString().padStart(2, '0');
                document.getElementById('timer').textContent = `${mins}:${secs}`;
            }, 1000);
        }
    </script>
</body>
</html>