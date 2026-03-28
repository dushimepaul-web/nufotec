const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);
const PORT = 3002;
const BASE_PATH = '/socket';

// CORS
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Content-Type');
    if (req.method === 'OPTIONS') return res.status(204).end();
    next();
});

// Routes API
app.get(BASE_PATH + '/health', (req, res) => {
    res.json({ status: 'healthy', port: PORT, time: new Date().toISOString() });
});

app.get(BASE_PATH + '/api/ice-servers', (req, res) => {
    res.json({
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' },
            { urls: 'stun:stun3.l.google.com:19302' },
            { urls: 'stun:stun4.l.google.com:19302' },
            // Serveurs TURN publics (au cas où P2P direct échoue)
            {
                urls: 'turn:openrelay.metered.ca:80',
                username: 'openrelayproject',
                credential: 'openrelayproject'
            },
            {
                urls: 'turn:openrelay.metered.ca:443',
                username: 'openrelayproject',
                credential: 'openrelayproject'
            }
        ]
    });
});

// Socket.IO - Configuration pour mutualisé
// On autorise BOTH pour que ça fonctionne avec votre .htaccess
const io = socketIo(server, {
    path: BASE_PATH + '/socket.io',
    cors: { origin: "*", methods: ["GET", "POST"] },
    transports: ['websocket', 'polling'],  // Les deux, le client choisira
    allowEIO3: true,
    pingTimeout: 60000,
    pingInterval: 25000
});

// Stockage des salles pour le débogage
const rooms = new Map();

io.on('connection', (socket) => {
    const transport = socket.conn.transport.name;
    console.log(`✅ Client connecté: ${socket.id} (transport: ${transport})`);
    
    socket.on('join-room', (roomId) => {
        socket.join(roomId);
        
        // Stocker la salle pour le débogage
        if (!rooms.has(roomId)) {
            rooms.set(roomId, new Set());
        }
        rooms.get(roomId).add(socket.id);
        
        console.log(`📌 ${socket.id} → salle ${roomId} (${rooms.get(roomId).size} participants)`);
        
        // Informer les autres participants
        socket.to(roomId).emit('user-connected', { id: socket.id, transport: transport });
        
        // Envoyer la liste des participants au nouveau venu
        const participants = Array.from(rooms.get(roomId)).filter(id => id !== socket.id);
        if (participants.length > 0) {
            socket.emit('participants-list', { participants: participants });
        }
    });
    
    socket.on('leave-room', (roomId) => {
        if (roomId && rooms.has(roomId)) {
            rooms.get(roomId).delete(socket.id);
            if (rooms.get(roomId).size === 0) {
                rooms.delete(roomId);
            }
        }
        socket.leave(roomId);
        console.log(`👋 ${socket.id} a quitté la salle ${roomId}`);
    });

    socket.on('offer', (data) => {
        console.log(`📤 Offer: ${socket.id} → ${data.target}`);
        socket.to(data.target).emit('offer', { 
            sdp: data.sdp, 
            sender: socket.id,
            transport: transport
        });
    });

    socket.on('answer', (data) => {
        console.log(`📤 Answer: ${socket.id} → ${data.target}`);
        socket.to(data.target).emit('answer', { 
            sdp: data.sdp, 
            sender: socket.id,
            transport: transport
        });
    });

    socket.on('ice-candidate', (data) => {
        console.log(`🧊 ICE candidate: ${socket.id} → ${data.target}`);
        socket.to(data.target).emit('ice-candidate', { 
            candidate: data.candidate, 
            sender: socket.id 
        });
    });
    
    // Heartbeat pour garder la connexion active
    socket.on('ping', (data) => {
        socket.emit('pong', { time: Date.now(), serverTime: Date.now() });
    });
    
    // Signalement de qualité de connexion
    socket.on('connection-quality', (data) => {
        console.log(`📊 Qualité connexion ${socket.id}: ${data.quality} (${data.duration}s)`);
    });

    socket.on('disconnect', (reason) => {
        console.log(`❌ Client déconnecté: ${socket.id} (${reason})`);
        
        // Nettoyer les salles
        for (const [roomId, participants] of rooms.entries()) {
            if (participants.has(socket.id)) {
                participants.delete(socket.id);
                if (participants.size === 0) {
                    rooms.delete(roomId);
                }
                socket.to(roomId).emit('user-disconnected', { id: socket.id, reason: reason });
                console.log(`📢 Broadcast déconnexion dans salle ${roomId}`);
            }
        }
    });
});

// UN SEUL listen()
server.listen(PORT, '0.0.0.0', () => {
    console.log(`
    ═══════════════════════════════════════════════════
    ✅ SERVEUR DE SIGNALISATION DÉMARRÉ
    ═══════════════════════════════════════════════════
    📡 Port: ${PORT}
    🔗 Path: ${BASE_PATH}/socket.io
    🌐 Transports: websocket + polling
    📍 Adresse: 127.0.0.1:${PORT}
    ═══════════════════════════════════════════════════
    `);
});

// Gestion des erreurs non capturées
process.on('uncaughtException', (error) => {
    console.error('❌ Erreur non capturée:', error);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('❌ Promesse non gérée:', reason);
});