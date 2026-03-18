const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const os = require('os');
const path = require('path');

const app = express();
const server = http.createServer(app);

// ============================================
// ⭐ STATISTIQUES - DÉCLARÉES EN PREMIER
// ============================================
const stats = {
  connections: 0,
  startTime: Date.now()
};

// ============================================
// ⭐ CORS ULTRA-PERMISSIF
// ============================================
const cors = require('cors');

// Configuration CORS complète
app.use(cors({
  origin: '*',
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With'],
  credentials: true,
  preflightContinue: false,
  optionsSuccessStatus: 204
}));

// Middleware CORS manuel supplémentaire
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
  res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
  res.header('Access-Control-Allow-Credentials', 'true');
  res.header('Access-Control-Max-Age', '86400');
  
  // Anti-cache
  res.header('Cache-Control', 'no-store, no-cache, must-revalidate');
  res.header('Pragma', 'no-cache');
  
  if (req.method === 'OPTIONS') {
    return res.status(204).end();
  }
  next();
});

// ============================================
// ⭐ BODY PARSER
// ============================================
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// ============================================
// ⭐ SERVEURS TURN/STUN
// ============================================
const TURN_CONFIG = {
  iceServers: [
    { urls: 'stun:stun.l.google.com:19302' },
    { urls: 'stun:stun1.l.google.com:19302' },
    { urls: 'stun:stun2.l.google.com:19302' },
    { urls: 'stun:stun3.l.google.com:19302' },
    { urls: 'stun:stun4.l.google.com:19302' },
    { urls: 'stun:stun.voipbuster.com:3478' }
  ],
  iceTransportPolicy: 'all',
  iceCandidatePoolSize: 10
};

// ============================================
// ⭐ SOCKET.IO - CONFIGURATION POUR /socket/
// ============================================
const io = socketIo(server, {
  path: '/socket/socket.io',
  cors: { 
    origin: "*",
    methods: ["GET", "POST"],
    allowedHeaders: ["Content-Type"],
    credentials: true,
  },
  pingTimeout: 60000,
  pingInterval: 25000,
  transports: ['websocket', 'polling'],
  allowUpgrades: true,
  connectTimeout: 45000
});

// ============================================
// ⭐ INCREMENTATION DES STATS
// ============================================
io.engine.on('connection', () => {
  stats.connections++;
});

// ============================================
// ⭐ ROUTES API
// ============================================

// Route de test - accessible via /socket/
app.get('/', (req, res) => {
  res.json({ 
    status: 'OK',
    server: 'NUFOTEC Consultation',
    message: 'Serveur de signalisation opérationnel',
    path: '/socket/',
    time: new Date().toISOString()
  });
});

// ICE servers - accessible via /socket/api/ice-servers
app.get('/api/ice-servers', (req, res) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.json(TURN_CONFIG);
});

// Health check - accessible via /socket/health
app.get('/health', (req, res) => {
  res.json({ 
    status: 'healthy', 
    uptime: process.uptime(),
    connections: io.engine.clientsCount,
    totalConnections: stats.connections,
    timestamp: Date.now()
  });
});

// Stats - accessible via /socket/api/stats
app.get('/api/stats', (req, res) => {
  res.json({
    connections: io.engine.clientsCount,
    totalConnections: stats.connections,
    uptime: Date.now() - stats.startTime,
    memory: process.memoryUsage(),
    cpu: os.loadavg()
  });
});

// Version - accessible via /socket/api/version
app.get('/api/version', (req, res) => {
  res.json({
    version: '3.0',
    node: process.version,
    environment: process.env.NODE_ENV || 'production'
  });
});

// Test simple - accessible via /socket/api/test
app.get('/api/test', (req, res) => {
  res.json({ 
    message: 'API fonctionnelle',
    timestamp: Date.now()
  });
});

// ============================================
// ⭐ SOCKET.IO EVENTS
// ============================================
io.on('connection', (socket) => {
  console.log(`✅ Connecté: ${socket.id} (${socket.conn.transport.name})`);

  // Envoyer configuration ICE au client
  socket.emit('ice-servers', TURN_CONFIG);

  socket.on('join-room', (roomId) => {
    const rooms = io.sockets.adapter.rooms;
    const room = rooms.get(roomId);
    const numClients = room ? room.size : 0;

    if (numClients >= 2) {
      socket.emit('room-full', { message: 'Salle pleine (max 2)' });
      return;
    }

    socket.join(roomId);
    console.log(`📌 ${socket.id} → ${roomId} (${numClients + 1}/2)`);
    
    socket.to(roomId).emit('user-connected', {
      id: socket.id,
      timestamp: Date.now()
    });
  });

  socket.on('offer', (data) => {
    socket.to(data.target).emit('offer', { 
      sdp: data.sdp, 
      sender: socket.id,
      timestamp: Date.now()
    });
  });

  socket.on('answer', (data) => {
    socket.to(data.target).emit('answer', { 
      sdp: data.sdp, 
      sender: socket.id,
      timestamp: Date.now()
    });
  });

  socket.on('ice-candidate', (data) => {
    socket.to(data.target).emit('ice-candidate', { 
      candidate: data.candidate, 
      sender: socket.id,
      timestamp: Date.now()
    });
  });

  socket.on('connection-quality', (data) => {
    console.log(`📶 Qualité ${socket.id}: ${data.quality}`);
  });

  socket.on('disconnect', (reason) => {
    console.log(`❌ Déconnecté: ${socket.id} (${reason})`);
    
    socket.rooms.forEach(room => {
      if (room !== socket.id) {
        socket.to(room).emit('user-disconnected', {
          id: socket.id,
          reason: reason,
          timestamp: Date.now()
        });
      }
    });
  });
});

// ============================================
// ⭐ DÉMARRAGE
// ============================================
const port = process.env.PORT || 3000;

server.listen(port, () => {
  console.log(`
╔════════════════════════════════════════════════════════════╗
║  🚀 NUFOTEC CONSULTATION - SIGNALING SERVER               ║
╠════════════════════════════════════════════════════════════╣
║  📡 Port: ${port.toString().padEnd(45)}║
║  🔌 Socket.IO Path: /socket/socket.io${' '.repeat(30)}║
║  🌐 Public URL: https://nufotec.com/socket/${' '.repeat(27)}║
║  🔧 CORS: * (tous domaines)${' '.repeat(32)}║
║  📊 STUN: 6 serveurs actifs${' '.repeat(31)}║
║  📋 Routes disponibles:                                    ║
║     • /                                                    ║
║     • /api/ice-servers                                     ║
║     • /health                                              ║
║     • /api/stats                                           ║
║     • /api/version                                         ║
║     • /api/test                                            ║
╚════════════════════════════════════════════════════════════╝
  `);
});

// Gestion des erreurs
process.on('uncaughtException', (err) => {
  console.error('❌ Exception non catchée:', err);
});

process.on('unhandledRejection', (reason) => {
  console.error('❌ Rejection non gérée:', reason);
});