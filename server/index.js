const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);

// ============================================
// ⭐ MIDDLEWARE CORS + ANTI-CACHE (DOIT ÊTRE EN PREMIER)
// ============================================
app.use((req, res, next) => {
  // CORS headers
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
  res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
  
  // ⭐ Anti-cache headers pour Cloudflare/LiteSpeed
  res.header('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate');
  res.header('Pragma', 'no-cache');
  res.header('Expires', '0');
  res.header('Surrogate-Control', 'no-store');
  
  // Identifier que la réponse vient de Node.js
  res.header('X-Server', 'NodeJS-SocketIO');
  
  // Répondre immédiatement aux OPTIONS (pre-flight)
  if (req.method === 'OPTIONS') {
    console.log('🔄 OPTIONS:', req.path);
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
// Routes
// ============================================
app.get('/test', (req, res) => {
  res.json({ 
    status: 'ok', 
    server: 'nodejs',
    cors: 'enabled',
    time: new Date().toISOString()
  });
});

app.get('/', (req, res) => {
  res.json({ 
    status: 'Serveur Socket.IO opérationnel',
    server: 'nodejs',
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
  console.log(`📡 CORS activé avec anti-cache`);
});

process.on('SIGTERM', () => {
  console.log('SIGTERM, fermeture...');
  server.close(() => process.exit(0));
});