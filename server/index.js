const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);

// Configuration Socket.IO pour cPanel/Passenger
const io = socketIo(server, {
  cors: { 
    origin: "*", 
    methods: ["GET", "POST"],
    credentials: true
  },
  pingTimeout: 60000,
  pingInterval: 25000,
  path: '/socket.io/',
  transports: ['polling', 'websocket'], // Polling d'abord !
  allowUpgrades: true,
  upgradeTimeout: 10000
});

// Logs
process.on('uncaughtException', (err) => console.error('❌ Exception:', err));
process.on('unhandledRejection', (reason) => console.error('❌ Rejet:', reason));

// Routes
app.get('/', (req, res) => {
  res.json({ 
    status: 'ok', 
    time: new Date().toISOString(),
    transport: 'polling/websocket'
  });
});

// Socket.IO
io.on('connection', (socket) => {
  console.log(`✅ Connecté: ${socket.id} (${socket.conn.transport.name})`);

  socket.on('join-room', (roomId) => {
    const room = io.sockets.adapter.rooms.get(roomId);
    const numClients = room ? room.size : 0;

    if (numClients >= 2) {
      socket.emit('room-full', { message: 'Salle pleine' });
      return;
    }

    socket.join(roomId);
    console.log(`📌 ${socket.id} → ${roomId} (${numClients + 1}/2)`);
    socket.to(roomId).emit('user-connected', socket.id);
  });

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
    console.log(`❌ Déconnecté: ${socket.id}`);
    socket.rooms.forEach(room => {
      if (room !== socket.id) {
        socket.to(room).emit('user-disconnected', socket.id);
      }
    });
  });
});

// Démarrage (Passenger fournit process.env.PORT)
const port = process.env.PORT || 3002;

server.listen(port, () => {
  console.log(`🚀 Serveur démarré sur port ${port}`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
  console.log('SIGTERM, fermeture...');
  server.close(() => process.exit(0));
});