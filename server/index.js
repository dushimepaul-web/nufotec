const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);
const PORT = 3002;

// Logging pour debug
app.use((req, res, next) => {
    console.log(`${new Date().toISOString()} - ${req.method} ${req.url}`);
    next();
});

app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET, POST');
    res.header('Access-Control-Allow-Headers', 'Content-Type');
    next();
});

// Route de test
app.get('/socket/health', (req, res) => {
    res.json({ status: 'ok', time: new Date().toISOString() });
});

// Route ICE servers
app.get('/socket/api/ice-servers', (req, res) => {
    console.log('📡 ICE servers requested');
    res.json({
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            {
                urls: [
                    'turn:openrelay.metered.ca:80',
                    'turn:openrelay.metered.ca:443',
                    'turn:openrelay.metered.ca:443?transport=tcp'
                ],
                username: 'openrelayproject',
                credential: 'openrelayproject'
            }
        ]
    });
});

// Socket.IO
const io = socketIo(server, {
    path: '/socket/socket.io',
    transports: ['polling', 'websocket'],
    allowUpgrades: true,
    pingTimeout: 60000,
    pingInterval: 25000,
    cors: { origin: "*" }
});

const rooms = new Map();

io.on('connection', (socket) => {
    console.log(`✅ Client connecté: ${socket.id}`);
    
    socket.on('ping', (data) => {
        socket.emit('pong', { time: data.time });
    });
    
    socket.on('join-room', (roomId) => {
        if (socket.currentRoom) {
            socket.leave(socket.currentRoom);
        }
        
        socket.join(roomId);
        socket.currentRoom = roomId;
        
        const count = (rooms.get(roomId) || 0) + 1;
        rooms.set(roomId, count);
        
        console.log(`📌 ${socket.id} → ${roomId} (${count} participants)`);
        socket.to(roomId).emit('user-connected', { id: socket.id });
    });
    
    socket.on('offer', (data) => {
        const target = io.sockets.sockets.get(data.target);
        if (target) {
            target.emit('offer', { sdp: data.sdp, sender: socket.id });
            console.log(`📤 Offer: ${socket.id} → ${data.target}`);
        }
    });
    
    socket.on('answer', (data) => {
        const target = io.sockets.sockets.get(data.target);
        if (target) {
            target.emit('answer', { sdp: data.sdp, sender: socket.id });
            console.log(`📤 Answer: ${socket.id} → ${data.target}`);
        }
    });
    
    socket.on('ice-candidate', (data) => {
        const target = io.sockets.sockets.get(data.target);
        if (target) {
            target.emit('ice-candidate', { candidate: data.candidate, sender: socket.id });
        }
    });
    
    socket.on('disconnect', () => {
        console.log(`❌ Client déconnecté: ${socket.id}`);
        if (socket.currentRoom && rooms.has(socket.currentRoom)) {
            const count = rooms.get(socket.currentRoom) - 1;
            if (count <= 0) {
                rooms.delete(socket.currentRoom);
            } else {
                rooms.set(socket.currentRoom, count);
            }
            socket.to(socket.currentRoom).emit('user-disconnected', { id: socket.id });
        }
    });
});

server.listen(PORT, '0.0.0.0', () => {
    console.log(`✅ Serveur Socket.IO démarré`);
    console.log(`📡 Port: ${PORT}`);
    console.log(`🌐 Path: /socket/socket.io`);
    console.log(`🧊 ICE: /socket/api/ice-servers`);
});

// Gestion propre de l'arrêt
process.on('SIGINT', () => {
    console.log('🛑 Arrêt du serveur...');
    server.close(() => {
        console.log('✅ Serveur arrêté');
        process.exit(0);
    });
});