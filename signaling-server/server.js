const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const PORT = 3001;

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
    
    // ✅ CORRIGÉ - Gérer les deux formats (objet ou string)
    socket.on('join-room', (data, callback) => {
        // Si data est un objet (nouveau format), extraire roomId
        const roomId = typeof data === 'object' ? data.room : data;
        const userData = typeof data === 'object' ? data : { userId: socket.id };
        
        if (!roomId) {
            if (typeof callback === 'function') {
                callback({ success: false, error: 'Room ID manquant' });
            }
            return;
        }
        
        const room = io.sockets.adapter.rooms.get(roomId);
        const numClients = room ? room.size : 0;
        
        // Vérifier si room pleine (max 2 participants)
        if (numClients >= 2) {
            if (typeof callback === 'function') {
                callback({ success: false, error: 'Room pleine' });
            }
            return;
        }
        
        socket.join(roomId);
        socket.roomId = roomId;
        socket.userData = userData;  // Stocker les infos utilisateur
        
        // Notifier l'autre participant S'IL EXISTE
        if (numClients === 1) {
            socket.to(roomId).emit('user-joined', { 
                socketId: socket.id,
                userId: userData.userId,
                username: userData.username,
                userType: userData.userType
            });
        }
        
        // Répondre au client qui rejoint
        if (typeof callback === 'function') {
            callback({ 
                success: true, 
                isInitiator: numClients === 0,  // Premier = initiateur
                participants: numClients + 1,
                roomId: roomId
            });
        }
        
        console.log(`✅ Room ${roomId}: ${numClients + 1} participant(s)`);
        console.log(`   - User: ${userData.username || 'Anonyme'} (${userData.userType || 'unknown'})`);
    });
    
    // ✅ CORRIGÉ - Gérer le signal avec roomId depuis socket
    socket.on('signal', (data) => {
        if (!socket.roomId) {
            console.log('❌ Signal sans room');
            return;
        }
        
        // Relay vers l'autre participant de la room
        socket.to(socket.roomId).emit('signal', {
            from: socket.id,
            type: data.type,
            data: data.data,
            fromUser: socket.userData  // Optionnel: infos de l'expéditeur
        });
        
        console.log(`📡 Signal ${data.type} de ${socket.id} dans ${socket.roomId}`);
    });
    
    socket.on('disconnect', () => {
        console.log('❌ Déconnecté:', socket.id);
        if (socket.roomId) {
            socket.to(socket.roomId).emit('user-left', { 
                socketId: socket.id,
                username: socket.userData?.username 
            });
        }
    });
});

server.listen(PORT, () => {
    console.log(`🚀 Serveur démarré sur http://localhost:${PORT}`);
    console.log('Appuyez sur CTRL+C pour arrêter');
});