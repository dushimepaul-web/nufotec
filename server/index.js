const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);
const PORT = 3002;

// PAS DE CORS COMPLIQUÉ
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET, POST');
    res.header('Access-Control-Allow-Headers', 'Content-Type');
    next();
});

// Route ICE servers
app.get('/socket/api/ice-servers', (req, res) => {
    res.json({
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
        ]
    });
});

// Socket.IO - MODE POLLING UNIQUEMENT
const io = socketIo(server, {
    path: '/socket/socket.io',
    transports: ['polling'],        // ← FORCÉ polling
    allowUpgrades: false,            // ← PAS d'upgrade WebSocket
    pingTimeout: 60000,
    pingInterval: 25000,
    cors: { origin: "*" }
});

// Stockage simple
const rooms = new Map();

io.on('connection', (socket) => {
    console.log(`✅ Connecté: ${socket.id}`);
    
    socket.on('ping', (data) => {
        socket.emit('pong', { time: data.time });
    });
    
    socket.on('join-room', (roomId) => {
        socket.join(roomId);
        rooms.set(roomId, (rooms.get(roomId) || 0) + 1);
        
        socket.to(roomId).emit('user-connected', { id: socket.id });
        console.log(`📌 ${socket.id} → ${roomId} (${rooms.get(roomId)} participants)`);
    });
    
    socket.on('offer', (data) => {
        const target = io.sockets.sockets.get(data.target);
        if (target) target.emit('offer', { sdp: data.sdp, sender: socket.id });
    });
    
    socket.on('answer', (data) => {
        const target = io.sockets.sockets.get(data.target);
        if (target) target.emit('answer', { sdp: data.sdp, sender: socket.id });
    });
    
    socket.on('ice-candidate', (data) => {
        const target = io.sockets.sockets.get(data.target);
        if (target) target.emit('ice-candidate', { candidate: data.candidate, sender: socket.id });
    });
    
    socket.on('disconnect', () => {
        console.log(`❌ Déconnecté: ${socket.id}`);
    });
});

server.listen(PORT, '127.0.0.1', () => {
    console.log(`✅ Serveur démarré sur port ${PORT}`);
    console.log(`📮 Mode: POLLING uniquement`);
});