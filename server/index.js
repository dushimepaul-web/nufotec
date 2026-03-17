const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const path = require('path');

const app = express();
const server = http.createServer(app);

const io = socketIo(server, {
  cors: { 
    origin: "*", 
    methods: ["GET", "POST"],
    credentials: true
  },
  pingTimeout: 60000,
  pingInterval: 25000,
  path: '/socket.io/',
  transports: ['websocket', 'polling'] // Fallback to polling if websocket fails
});

// Error handling
process.on('uncaughtException', (err) => console.error('❌ Exception:', err));
process.on('unhandledRejection', (reason) => console.error('❌ Rejet non géré:', reason));

// Health check route
app.get('/', (req, res) => {
  res.send('Serveur de signalisation opérationnel');
});

// Socket.IO connection handling
io.on('connection', (socket) => {
  console.log(`✅ Client connecté: ${socket.id}`);

  socket.on('join-room', (roomId) => {
    const room = io.sockets.adapter.rooms.get(roomId);
    const numClients = room ? room.size : 0;

    if (numClients >= 2) {
      socket.emit('room-full', { message: 'Salle pleine (max 2 participants).' });
      return;
    }

    socket.join(roomId);
    console.log(`📌 ${socket.id} a rejoint salle ${roomId} (${numClients + 1}/2)`);
    socket.to(roomId).emit('user-connected', socket.id);
  });

  // Move these outside join-room to prevent duplicate listeners
  socket.on('offer', (data) => {
    socket.to(data.target).emit('offer', { sdp: data.sdp, sender: socket.id });
  });
  
  socket.on('answer', (data) => {
    socket.to(data.target).emit('answer', { sdp: data.sdp, sender: socket.id });
  });
  
  socket.on('ice-candidate', (data) => {
    socket.to(data.target).emit('ice-candidate', { candidate: data.candidate, sender: socket.id });
  });

  socket.on('disconnect', () => {
    console.log(`❌ ${socket.id} déconnecté`);
    // Broadcast to all rooms this socket was in
    socket.rooms.forEach(room => {
      if (room !== socket.id) {
        socket.to(room).emit('user-disconnected', socket.id);
      }
    });
  });

  socket.on('error', (err) => console.error(`⚠️ Erreur socket ${socket.id}:`, err));
});

// IMPORTANT: Use only port, no host binding for Passenger compatibility
const port = process.env.PORT || 3002;

server.listen(port, () => {
  console.log(`🚀 Serveur de signalisation démarré sur le port ${port}`);
});