const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);
const PORT = 3002;

app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET, POST');
    res.header('Access-Control-Allow-Headers', 'Content-Type');
    next();
});

// Route ICE servers avec TURN fiables pour connexions internationales
app.get('/socket/api/ice-servers', (req, res) => {
    res.json({
        iceServers: [
            // STUN servers
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' },
            { urls: 'stun:stun3.l.google.com:19302' },
            { urls: 'stun:stun4.l.google.com:19302' },
            { urls: 'stun:stun.stunprotocol.org:3478' },
            
            // TURN server 1 - Metered.ca (fiable)
            {
                urls: [
                    'turn:openrelay.metered.ca:80',
                    'turn:openrelay.metered.ca:443',
                    'turn:openrelay.metered.ca:443?transport=tcp'
                ],
                username: 'openrelayproject',
                credential: 'openrelayproject'
            },
            
            // TURN server 2 - Twilio (fiable)
            {
                urls: [
                    'turn:global.turn.twilio.com:3478?transport=udp',
                    'turn:global.turn.twilio.com:3478?transport=tcp',
                    'turn:global.turn.twilio.com:443?transport=tcp'
                ],
                username: 'any',
                credential: 'any'
            },
            
            // TURN server 3 - ExpressTurn (backup)
            {
                urls: [
                    'turn:relay1.expressturn.com:3478',
                    'turn:relay2.expressturn.com:3478',
                    'turn:relay1.expressturn.com:3478?transport=tcp',
                    'turn:relay2.expressturn.com:3478?transport=tcp'
                ],
                username: 'efXUJXUAWDGQ6BM7XJ',
                credential: 'RcGeFf7zEbUeY4bt'
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
    console.log(`✅ Connecté: ${socket.id}`);
    
    socket.on('ping', (data) => {
        socket.emit('pong', { time: data.time });
    });
    
    socket.on('join-room', (roomId) => {
        if (socket.currentRoom) {
            socket.leave(socket.currentRoom);
            const oldCount = rooms.get(socket.currentRoom) || 0;
            if (oldCount <= 1) {
                rooms.delete(socket.currentRoom);
            } else {
                rooms.set(socket.currentRoom, oldCount - 1);
            }
        }
        
        socket.join(roomId);
        socket.currentRoom = roomId;
        
        const newCount = (rooms.get(roomId) || 0) + 1;
        rooms.set(roomId, newCount);
        
        console.log(`📌 ${socket.id} → ${roomId} (${newCount} participants)`);
        
        socket.to(roomId).emit('user-connected', { id: socket.id });
    });
    
    socket.on('offer', (data) => {
        const target = io.sockets.sockets.get(data.target);
        if (target && target.connected) {
            target.emit('offer', { sdp: data.sdp, sender: socket.id });
        }
    });
    
    socket.on('answer', (data) => {
        const target = io.sockets.sockets.get(data.target);
        if (target && target.connected) {
            target.emit('answer', { sdp: data.sdp, sender: socket.id });
        }
    });
    
    socket.on('ice-candidate', (data) => {
        const target = io.sockets.sockets.get(data.target);
        if (target && target.connected) {
            target.emit('ice-candidate', { candidate: data.candidate, sender: socket.id });
        }
    });
    
    socket.on('disconnect', () => {
        console.log(`❌ Déconnecté: ${socket.id}`);
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
    console.log(`✅ Serveur démarré sur port ${PORT}`);
});