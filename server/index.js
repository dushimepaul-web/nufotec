const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);
const PORT = process.env.PORT || 3002;  // Utiliser variable d'environnement
const BASE_PATH = '/socket';

// CORS configuration - Plus restrictive pour la sécurité
const allowedOrigins = process.env.ALLOWED_ORIGINS 
    ? process.env.ALLOWED_ORIGINS.split(',') 
    : ['*'];

app.use((req, res, next) => {
    const origin = req.headers.origin;
    if (allowedOrigins.includes('*') || allowedOrigins.includes(origin)) {
        res.header('Access-Control-Allow-Origin', origin || '*');
    }
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
        transport: 'polling-compatible',
        version: '6.0.0'
    });
});

app.get(BASE_PATH + '/api/ice-servers', (req, res) => {
    // Configuration ICE avec TURN si disponible
    const iceServers = [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
        { urls: 'stun:stun2.l.google.com:19302' },
        { urls: 'stun:stun3.l.google.com:19302' }
    ];
    
    // Ajouter TURN si configuré
    if (process.env.TURN_SERVER) {
        iceServers.push({
            urls: process.env.TURN_SERVER,
            username: process.env.TURN_USERNAME,
            credential: process.env.TURN_CREDENTIAL
        });
    }
    
    res.json({ iceServers });
});

// Socket.IO configuration optimisée
const io = socketIo(server, {
    path: BASE_PATH + '/socket.io',
    cors: { 
        origin: allowedOrigins.includes('*') ? "*" : allowedOrigins,
        methods: ["GET", "POST"],
        credentials: true,
        allowedHeaders: ["Content-Type", "X-Requested-With", "X-Client-Version"]
    },
    transports: ['polling', 'websocket'],
    pingTimeout: 60000,
    pingInterval: 25000,
    allowUpgrades: true,
    upgradeTimeout: 10000,
    // Optimisations pour serveur mutualisé
    maxHttpBufferSize: 1e6, // 1MB max
    perMessageDeflate: {
        threshold: 1024 // Compresser les messages > 1KB
    },
    transportOptions: {
        polling: {
            maxPayload: 1000000, // 1MB max payload
            closeTimeout: 30000,
            extraHeaders: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        },
        websocket: {
            extraHeaders: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }
    }
});

// Gestionnaire de rooms avec nettoyage automatique
const rooms = new Map();
const userRooms = new Map(); // Pour un accès rapide

// Nettoyage périodique des rooms vides (optionnel)
setInterval(() => {
    rooms.forEach((participants, roomId) => {
        if (participants.size === 0) {
            rooms.delete(roomId);
            console.log(`🧹 Nettoyage automatique: room ${roomId} supprimée`);
        }
    });
}, 3600000); // Toutes les heures

io.on('connection', (socket) => {
    const transport = socket.conn.transport.name;
    console.log(`✅ Client connecté: ${socket.id} (transport: ${transport})`);
    
    // Enregistrer l'heure de connexion
    socket.connectedAt = Date.now();

    // Gestion des pings avec latence
    socket.on('ping', (data) => {
        const latency = Date.now() - (data.time || 0);
        socket.emit('pong', { 
            time: data.time, 
            serverTime: Date.now(),
            role: data.role || 'unknown',
            latency: latency
        });
        
        // Log si latence élevée
        if (latency > 1000) {
            console.log(`⚠️ Latence élevée pour ${socket.id}: ${latency}ms`);
        }
    });

    socket.on('join-room', (roomId) => {
        if (!roomId || typeof roomId !== 'string') {
            console.log(`❌ Tentative de join sans roomId valide par ${socket.id}`);
            socket.emit('error', { message: 'Invalid room ID' });
            return;
        }

        // Nettoyer les anciennes rooms de l'utilisateur
        if (userRooms.has(socket.id)) {
            const oldRoom = userRooms.get(socket.id);
            if (oldRoom !== roomId) {
                socket.leave(oldRoom);
                if (rooms.has(oldRoom)) {
                    rooms.get(oldRoom).delete(socket.id);
                }
                console.log(`📤 ${socket.id} a quitté ${oldRoom}`);
            }
        }

        // Rejoindre la nouvelle room
        socket.join(roomId);
        userRooms.set(socket.id, roomId);
        
        if (!rooms.has(roomId)) {
            rooms.set(roomId, new Set());
        }
        rooms.get(roomId).add(socket.id);

        const roomSize = rooms.get(roomId).size;
        console.log(`📌 ${socket.id} → salle ${roomId} (${roomSize} participant(s))`);

        // Informer les autres participants
        socket.to(roomId).emit('user-connected', { 
            id: socket.id,
            timestamp: Date.now(),
            role: socket.handshake.query.role || 'participant'
        });

        // Vérifier la limite de participants
        if (roomSize > 2) {
            socket.emit('room-full', { 
                message: 'Cette consultation est déjà en cours (2 participants maximum)',
                currentParticipants: roomSize
            });
            // Optionnel: déconnecter le 3ème participant
            // socket.disconnect();
        }
    });

    socket.on('offer', (data) => {
        if (!data?.target || !data?.sdp) {
            console.log(`❌ Offre invalide de ${socket.id}`);
            socket.emit('error', { message: 'Invalid offer data' });
            return;
        }
        
        console.log(`📤 Offre de ${socket.id} vers ${data.target}`);
        
        const targetSocket = io.sockets.sockets.get(data.target);
        if (targetSocket && targetSocket.connected) {
            targetSocket.emit('offer', { 
                sdp: data.sdp, 
                sender: socket.id,
                timestamp: Date.now()
            });
        } else {
            console.log(`⚠️ Cible ${data.target} non trouvée ou déconnectée`);
            socket.emit('error', { message: 'Target user not found' });
        }
    });

    socket.on('answer', (data) => {
        if (!data?.target || !data?.sdp) {
            console.log(`❌ Réponse invalide de ${socket.id}`);
            return;
        }
        
        console.log(`📤 Réponse de ${socket.id} vers ${data.target}`);
        
        const targetSocket = io.sockets.sockets.get(data.target);
        if (targetSocket && targetSocket.connected) {
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
        if (targetSocket && targetSocket.connected) {
            targetSocket.emit('ice-candidate', { 
                candidate: data.candidate, 
                sender: socket.id 
            });
        }
    });

    socket.on('disconnect', (reason) => {
        const duration = Date.now() - (socket.connectedAt || Date.now());
        console.log(`❌ Client déconnecté: ${socket.id} (raison: ${reason}, durée: ${Math.round(duration/1000)}s)`);
        
        // Nettoyer les rooms
        const roomId = userRooms.get(socket.id);
        if (roomId && rooms.has(roomId)) {
            rooms.get(roomId).delete(socket.id);
            socket.to(roomId).emit('user-disconnected', { 
                id: socket.id,
                timestamp: Date.now(),
                reason: reason
            });
            console.log(`📤 ${socket.id} déconnecté de la room ${roomId}`);
            
            // Supprimer la room si vide
            if (rooms.get(roomId).size === 0) {
                rooms.delete(roomId);
                console.log(`🗑️ Room ${roomId} supprimée (vide)`);
            }
        }
        
        userRooms.delete(socket.id);
    });

    socket.on('error', (error) => {
        console.error(`❌ Erreur socket ${socket.id}:`, error.message);
    });
});

// Gestion des erreurs serveur
server.on('error', (error) => {
    console.error('❌ Erreur serveur HTTP:', error);
    if (error.code === 'EADDRINUSE') {
        console.error(`⚠️ Le port ${PORT} est déjà utilisé`);
        process.exit(1);
    }
});

// Démarrage du serveur
server.listen(PORT, '127.0.0.1', () => {
    console.log(`✅ Serveur Socket.IO démarré avec succès`);
    console.log(`📡 Port: ${PORT}`);
    console.log(`📮 Mode polling activé (compatible hébergement mutualisé)`);
    console.log(`🌐 Path: ${BASE_PATH}/socket.io`);
    console.log(`🔄 Transports supportés: polling, websocket`);
    console.log(`💾 Mémoire: ${Math.round(process.memoryUsage().heapUsed / 1024 / 1024)}MB`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Arrêt du serveur...');
    io.close(() => {
        server.close(() => {
            console.log('✅ Serveur arrêté');
            process.exit(0);
        });
    });
});

// Export pour tests
module.exports = { app, server, io };