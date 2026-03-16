const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const path = require('path');

const app = express();
const server = http.createServer(app);

const io = socketIo(server, {
  cors: { origin: "*", methods: ["GET", "POST"] },
  pingTimeout: 60000,
  pingInterval: 25000
});

// Gestion des erreurs
process.on('uncaughtException', (err) => console.error('❌ Exception:', err));
process.on('unhandledRejection', (reason) => console.error('❌ Rejet non géré:', reason));

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

    socket.on('disconnect', () => {
      socket.to(roomId).emit('user-disconnected', socket.id);
      console.log(`❌ ${socket.id} déconnecté de la salle ${roomId}`);
    });

    socket.on('offer', (data) => socket.to(data.target).emit('offer', { sdp: data.sdp, sender: socket.id }));
    socket.on('answer', (data) => socket.to(data.target).emit('answer', { sdp: data.sdp, sender: socket.id }));
    socket.on('ice-candidate', (data) => socket.to(data.target).emit('ice-candidate', { candidate: data.candidate, sender: socket.id }));
  });

  socket.on('error', (err) => console.error(`⚠️ Erreur socket ${socket.id}:`, err));
});

const PORT = 3000;
server.listen(PORT, '0.0.0.0', () => {
  console.log(`🚀 Serveur signalisation sur http://localhost:${PORT}`);
});