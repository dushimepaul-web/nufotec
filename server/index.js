// ============================================
// NUFOTEC SIGNALING - VERSION CORRIGÉE
// ============================================

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
            { urls: 'stun:stun2.l.google.com:19302' }
        ]
    });
});

// Socket.IO
const io = socketIo(server, {
    path: BASE_PATH + '/socket.io',
    cors: { origin: "*", methods: ["GET", "POST"] },
    transports: ['websocket', 'polling']
});

io.on('connection', (socket) => {
    console.log(`✅ Client connecté: ${socket.id}`);

    socket.on('join-room', (roomId) => {
        socket.join(roomId);
        socket.to(roomId).emit('user-connected', socket.id);
        console.log(`📌 ${socket.id} → salle ${roomId}`);
    });

    socket.on('offer', (data) => {
        socket.to(data.target).emit('offer', {
            sdp: data.sdp,
            sender: socket.id
        });
    });

    socket.on('answer', (data) => {
        socket.to(data.target).emit('answer', {
            sdp: data.sdp,
            sender: socket.id
        });
    });

    socket.on('ice-candidate', (data) => {
        socket.to(data.target).emit('ice-candidate', {
            candidate: data.candidate,
            sender: socket.id
        });
    });

    socket.on('disconnect', () => {
        console.log(`❌ Client déconnecté: ${socket.id}`);
        socket.broadcast.emit('user-disconnected', socket.id);
    });
});

// UN SEUL listen() - celui-ci
server.listen(PORT, '127.0.0.1', () => {
    console.log(`
╔════════════════════════════════════════╗
║  🚀 SERVEUR DÉMARRÉ                    ║
╠════════════════════════════════════════╣
║  📡 Port: ${PORT}                        ║
║  🔍 Test: curl localhost:${PORT}${BASE_PATH}/health ║
╚════════════════════════════════════════╝
    `);
});