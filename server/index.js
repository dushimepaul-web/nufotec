const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);
const PORT = 3002;
const BASE_PATH = '/socket';

// CORS configuration
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, X-Client-Version');
    res.header('Access-Control-Allow-Credentials', 'true');
    
    if (req.method === 'OPTIONS') {
        return res.status(204).end();
    }
    next();
});

// API routes
app.get(BASE_PATH + '/health', (req, res) => {
    res.json({ 
        status: 'healthy', 
        port: PORT, 
        time: new Date().toISOString(),
        transport: 'polling-compatible'
    });
});

app.get(BASE_PATH + '/api/ice-servers', (req, res) => {
    res.json({
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' },
            { urls: 'stun:stun3.l.google.com:19302' }
        ]
    });
});

// Socket.IO configuration
const io = socketIo(server, {
    path: BASE_PATH + '/socket.io',
    cors: { 
        origin: "*", 
        methods: ["GET", "POST"],
        credentials: true,
        allowedHeaders: ["Content-Type", "X-Requested-With", "X-Client-Version"]
    },
    transports: ['polling', 'websocket'],
    pingTimeout: 60000,
    pingInterval: 25000,
    allowUpgrades: true,
    upgradeTimeout: 10000,
    transportOptions: {
        polling: {
            extraHeaders: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }
    }
});

const rooms = new Map();

io.on('connection', (socket) => {
    console.log(`✅ Client connecté: ${socket.id} (transport: ${socket.conn.transport.name})`);
    
    socket.on('ping', (data) => {
        socket.emit('pong', { 
            time: data.time, 
            serverTime: Date.now(),
            role: data.role || 'unknown'
        });
    });

    socket.on('join-room', (roomId) => {
        if (!roomId) {
            console.log(`❌ Tentative de join sans roomId par ${socket.id}`);
            return;
        }

        socket.rooms.forEach(room => {
            if (room !== socket.id) {
                socket.leave(room);
                console.log(`📤 ${socket.id} a quitté ${room}`);
            }
        });

        socket.join(roomId);
        
        if (!rooms.has(roomId)) {
            rooms.set(roomId, new Set());
        }
        rooms.get(roomId).add(socket.id);

        const roomSize = rooms.get(roomId).size;
        console.log(`📌 ${socket.id} → salle ${roomId} (${roomSize} participant(s))`);

        socket.to(roomId).emit('user-connected', { 
            id: socket.id,
            timestamp: Date.now()
        });

        if (roomSize > 2) {
            socket.emit('room-full', { 
                message: 'Cette consultation est déjà en cours (2 participants maximum)',
                currentParticipants: roomSize
            });
        }
    });

    socket.on('offer', (data) => {
        if (!data?.target || !data?.sdp) {
            console.log(`❌ Offre invalide de ${socket.id}`);
            return;
        }
        
        console.log(`📤 Offre de ${socket.id} vers ${data.target}`);
        
        const targetSocket = io.sockets.sockets.get(data.target);
        if (targetSocket) {
            targetSocket.emit('offer', { 
                sdp: data.sdp, 
                sender: socket.id,
                timestamp: Date.now()
            });
        } else {
            console.log(`⚠️ Cible ${data.target} non trouvée pour l'offre`);
        }
    });

    socket.on('answer', (data) => {
        if (!data?.target || !data?.sdp) {
            console.log(`❌ Réponse invalide de ${socket.id}`);
            return;
        }
        
        console.log(`📤 Réponse de ${socket.id} vers ${data.target}`);
        
        const targetSocket = io.sockets.sockets.get(data.target);
        if (targetSocket) {
            targetSocket.emit('answer', { 
                sdp: data.sdp, 
                sender: socket.id,
                timestamp: Date.now()
            });
        } else {
            console.log(`⚠️ Cible ${data.target} non trouvée pour la réponse`);
        }
    });

    socket.on('ice-candidate', (data) => {
        if (!data?.target || !data?.candidate) {
            console.log(`❌ Candidat ICE invalide de ${socket.id}`);
            return;
        }
        
        const targetSocket = io.sockets.sockets.get(data.target);
        if (targetSocket) {
            targetSocket.emit('ice-candidate', { 
                candidate: data.candidate, 
                sender: socket.id 
            });
        }
    });

    socket.on('disconnect', (reason) => {
        console.log(`❌ Client déconnecté: ${socket.id} (raison: ${reason})`);
        
        rooms.forEach((participants, roomId) => {
            if (participants.has(socket.id)) {
                participants.delete(socket.id);
                socket.to(roomId).emit('user-disconnected', { 
                    id: socket.id,
                    timestamp: Date.now()
                });
                console.log(`📤 ${socket.id} déconnecté de la room ${roomId}`);
                
                if (participants.size === 0) {
                    rooms.delete(roomId);
                    console.log(`🗑️ Room ${roomId} supprimée (vide)`);
                }
            }
        });
    });

    socket.on('error', (error) => {
        console.error(`❌ Erreur socket ${socket.id}:`, error);
    });
});

server.listen(PORT, '127.0.0.1', () => {
    console.log(`✅ Serveur Socket.IO démarré sur le port ${PORT}`);
    console.log(`📮 Mode polling activé (compatible hébergement mutualisé)`);
    console.log(`🌐 Path: ${BASE_PATH}/socket.io`);
});