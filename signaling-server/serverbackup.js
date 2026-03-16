const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const PORT = 3000;

const app = express();
app.use(cors());

const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

io.on('connection', (socket) => {
    console.log('✅ Connexion:', socket.id);
    
    // ✅ CORRIGÉ - Vérification du callback partout
    socket.on('join-room', (roomId, callback) => {
        const room = io.sockets.adapter.rooms.get(roomId);
        const numClients = room ? room.size : 0;
        
        // Vérifier si room pleine
        if (numClients >= 2) {
            if (typeof callback === 'function') {
                callback({ success: false, error: 'Room pleine' });
            }
            return;
        }
        
        socket.join(roomId);
        socket.roomId = roomId;
        
        // Notifier l'autre participant
        if (numClients === 1) {
            socket.to(roomId).emit('user-joined', { socketId: socket.id });
        }
        
        // ✅ Vérification avant appel du callback
        if (typeof callback === 'function') {
            callback({ 
                success: true, 
                isInitiator: numClients === 0,
                participants: numClients + 1
            });
        }
        
        console.log(`Room ${roomId}: ${numClients + 1} participant(s)`);
    });
    
    socket.on('signal', (data) => {
        if (!socket.roomId) return;
        
        socket.to(socket.roomId).emit('signal', {
            from: socket.id,
            type: data.type,
            data: data.data
        });
    });
    
    socket.on('disconnect', () => {
        console.log('❌ Déconnecté:', socket.id);
        if (socket.roomId) {
            socket.to(socket.roomId).emit('user-left', { socketId: socket.id });
        }
    });
});

server.listen(PORT, () => {
    console.log(`🚀 Serveur démarré sur http://localhost:${PORT}`);
    console.log('Appuyez sur CTRL+C pour arrêter');
});