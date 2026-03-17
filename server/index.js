const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);

// ============================================
// ⭐ CORS GLOBAL - DOIT ÊTRE EN PREMIER
// ============================================
app.use((req, res, next) => {
  // Autoriser tous les domaines (ou spécifiez 'https://nufotec.com')
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
  res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization, Cache-Control');
  res.header('Access-Control-Allow-Credentials', 'false');
  
  // Intercepter et répondre immédiatement aux requêtes OPTIONS
  if (req.method === 'OPTIONS') {
    console.log('🔄 Requête OPTIONS interceptée');
    return res.status(200).end();
  }
  
  next();
});

// ============================================
// Configuration Socket.IO
// ============================================
const io = socketIo(server, {
  cors: { 
    origin: "*", 
    methods: ["GET", "POST", "OPTIONS"],
    credentials: false,
    allowedHeaders: ["Content-Type", "Authorization"]
  },
  pingTimeout: 60000,
  pingInterval: 25000,
  path: '/socket.io/',
  transports: ['polling', 'websocket'],
  allowUpgrades: true,
  upgradeTimeout: 10000
});

// ============================================
// Logs
// ============================================
process.on('uncaughtException', (err) => console.error('❌ Exception:', err));
process.on('unhandledRejection', (reason) => console.error('❌ Rejet:', reason));

// ============================================
// Routes de test
// ============================================
app.get('/test', (req, res) => {
  res.json({ 
    status: 'ok', 
    cors: 'enabled',
    origin: req.headers.origin || 'no origin',
    time: new Date().toISOString()
  });
});

app.get('/', (req, res) => {
  res.json({ 
    status: 'Serveur Socket.IO opérationnel',
    cors: 'enabled',
    time: new Date().toISOString()
  });
});

// ============================================
// Socket.IO Events
// ============================================
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

// ============================================
// Démarrage
// ============================================
const port = process.env.PORT || 3002;

server.listen(port, () => {
  console.log(`🚀 Serveur démarré sur port ${port}`);
  console.log(`📡 CORS activé pour toutes les origines`);
});

process.on('SIGTERM', () => {
  console.log('SIGTERM, fermeture...');
  server.close(() => process.exit(0));
});