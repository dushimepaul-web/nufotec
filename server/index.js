// ============================================================
// NUFOTEC CONSULTATION - SERVEUR DE SIGNALISATION
// Fichier : /server/index.js
// Démarrage : node /server/index.js
// ============================================================

const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const os = require('os');

const app = express();
const server = http.createServer(app);

// ============================================================
// CONFIGURATION
// ============================================================
const PORT = 3000;
const BASE_PATH = '/socket';  // Important: correspond au .htaccess

// Statistiques
const stats = {
    startTime: Date.now(),
    connections: 0,
    messages: 0
};

// ============================================================
// MIDDLEWARE CORS
// ============================================================
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
    
    if (req.method === 'OPTIONS') {
        return res.status(204).end();
    }
    next();
});

// ============================================================
// SERVEURS STUN/TURN
// ============================================================
const ICE_SERVERS = {
    iceServers: [
        // Serveurs STUN Google (gratuits)
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
        { urls: 'stun:stun2.l.google.com:19302' },
        { urls: 'stun:stun3.l.google.com:19302' },
        { urls: 'stun:stun4.l.google.com:19302' }
    ],
    iceTransportPolicy: 'all',
    iceCandidatePoolSize: 10
};

// ============================================================
// ROUTES API (toutes préfixées par /socket)
// ============================================================

// Route de test
app.get(BASE_PATH + '/', (req, res) => {
    res.json({
        status: 'running',
        server: 'NUFOTEC Signaling Server',
        time: new Date().toISOString()
    });
});

// Health check
app.get(BASE_PATH + '/health', (req, res) => {
    res.json({
        status: 'healthy',
        uptime: process.uptime(),
        connections: stats.connections,
        timestamp: Date.now()
    });
});

// Configuration ICE (utilisé par le client)
app.get(BASE_PATH + '/api/ice-servers', (req, res) => {
    res.json(ICE_SERVERS);
});

// Statistiques
app.get(BASE_PATH + '/api/stats', (req, res) => {
    res.json({
        connections: stats.connections,
        totalConnections: stats.connections,
        uptime: Date.now() - stats.startTime,
        memory: process.memoryUsage(),
        cpu: os.loadavg(),
        messages: stats.messages
    });
});

// Version
app.get(BASE_PATH + '/api/version', (req, res) => {
    res.json({
        version: '1.0.0',
        node: process.version,
        environment: process.env.NODE_ENV || 'production'
    });
});

// ============================================================
// SOCKET.IO CONFIGURATION
// ============================================================
const io = socketIo(server, {
    path: BASE_PATH + '/socket.io',  // Chemin complet: /socket/socket.io
    cors: {
        origin: "*",
        methods: ["GET", "POST"],
        credentials: true
    },
    transports: ['websocket', 'polling'],
    pingTimeout: 60000,
    pingInterval: 25000
});

// Incrémenter les stats
io.engine.on('connection', () => {
    stats.connections++;
});

// ============================================================
// GESTION DES ÉVÉNEMENTS SOCKET
// ============================================================
io.on('connection', (socket) => {
    console.log(`✅ [${new Date().toISOString()}] Client connecté: ${socket.id}`);

    // Envoyer la config ICE au client
    socket.emit('ice-servers', ICE_SERVERS);

    // Rejoindre une salle
    socket.on('join-room', (roomId) => {
        const rooms = io.sockets.adapter.rooms;
        const room = rooms.get(roomId);
        const numClients = room ? room.size : 0;

        console.log(`📌 ${socket.id} → salle ${roomId} (${numClients + 1}/2)`);

        if (numClients >= 2) {
            socket.emit('room-full', { 
                message: 'Salle pleine (maximum 2 personnes)'
            });
            return;
        }

        socket.join(roomId);
        
        // Notifier l'autre participant
        socket.to(roomId).emit('user-connected', {
            id: socket.id,
            timestamp: Date.now()
        });
    });

    // Offre WebRTC
    socket.on('offer', (data) => {
        stats.messages++;
        socket.to(data.target).emit('offer', {
            sdp: data.sdp,
            sender: socket.id,
            timestamp: Date.now()
        });
    });

    // Réponse WebRTC
    socket.on('answer', (data) => {
        stats.messages++;
        socket.to(data.target).emit('answer', {
            sdp: data.sdp,
            sender: socket.id,
            timestamp: Date.now()
        });
    });

    // Candidat ICE
    socket.on('ice-candidate', (data) => {
        stats.messages++;
        socket.to(data.target).emit('ice-candidate', {
            candidate: data.candidate,
            sender: socket.id,
            timestamp: Date.now()
        });
    });

    // Qualité de connexion
    socket.on('connection-quality', (data) => {
        console.log(`📊 Qualité ${socket.id}: ${data.quality} (${data.duration}s)`);
    });

    // Déconnexion
    socket.on('disconnect', (reason) => {
        console.log(`❌ [${new Date().toISOString()}] Déconnecté: ${socket.id} (${reason})`);
        
        // Notifier les autres dans les salles
        socket.rooms.forEach(room => {
            if (room !== socket.id) {
                socket.to(room).emit('user-disconnected', {
                    id: socket.id,
                    reason: reason,
                    timestamp: Date.now()
                });
            }
        });
    });
});

// ============================================================
// DÉMARRAGE DU SERVEUR
// ============================================================
server.listen(PORT, '127.0.0.1', () => {
    console.log(`
╔════════════════════════════════════════════════════════════╗
║  🚀 NUFOTEC SIGNALING SERVER                              ║
╠════════════════════════════════════════════════════════════╣
║  📡 Port: ${PORT}                                          ║
║  🔌 Socket.IO: ${BASE_PATH}/socket.io                      ║
║  🌐 URL: http://localhost:${PORT}${BASE_PATH}              ║
║  📁 Fichier: /server/index.js                              ║
║  📊 STUN: ${ICE_SERVERS.iceServers.length} serveurs        ║
╚════════════════════════════════════════════════════════════╝
    `);
});

// Gestion des erreurs
process.on('uncaughtException', (err) => {
    console.error('❌ Exception:', err);
});

process.on('unhandledRejection', (reason) => {
    console.error('❌ Rejection:', reason);
});