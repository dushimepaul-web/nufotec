const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const os = require('os');

const app = express();
const server = http.createServer(app);

// ============================================
// ⭐ MIDDLEWARE DE SECURITE MAXIMALE
// ============================================
app.use((req, res, next) => {
  // CORS ultra-permissif pour test
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
  res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
  
  // Anti-cache agressif
  res.header('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0');
  res.header('Pragma', 'no-cache');
  res.header('Expires', '0');
  res.header('Surrogate-Control', 'no-store');
  
  // Headers sécurité
  res.header('X-Content-Type-Options', 'nosniff');
  res.header('X-Frame-Options', 'DENY');
  res.header('X-XSS-Protection', '1; mode=block');
  res.header('X-Server', 'NUFOTEC-ULTIME');
  
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }
  next();
});

// ============================================
// ⭐ SERVEURS TURN/STUN DYNAMIQUES
// ============================================
const TURN_CONFIG = {
  iceServers: [
    // STUN Google (multiples pour redondance)
    { urls: 'stun:stun.l.google.com:19302' },
    { urls: 'stun:stun1.l.google.com:19302' },
    { urls: 'stun:stun2.l.google.com:19302' },
    { urls: 'stun:stun3.l.google.com:19302' },
    { urls: 'stun:stun4.l.google.com:19302' },
    
    // STUN alternatifs
    { urls: 'stun:stun.voipbuster.com:3478' },
    { urls: 'stun:stun.voipstunt.com:3478' },
    
    // ⚠️ TURN à configurer plus tard
    // {
    //   urls: 'turn:turn.nufotec.com:3478',
    //   username: 'nufotec',
    //   credential: 'votre_mot_de_passe'
    // }
  ],
  iceTransportPolicy: 'all',
  iceCandidatePoolSize: 10
};

// ============================================
// Configuration Socket.IO ULTIME
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
  upgradeTimeout: 10000,
  perMessageDeflate: {
    threshold: 1024
  },
  maxHttpBufferSize: 1e7,
  connectTimeout: 45000
});

// ============================================
// Statistiques en temps réel
// ============================================
const stats = {
  connections: 0,
  rooms: 0,
  startTime: Date.now()
};

io.engine.on('connection', () => {
  stats.connections++;
});

setInterval(() => {
  stats.rooms = io.engine.clientsCount;
  console.log(`📊 Stats: ${stats.connections} total, ${stats.rooms} actives`);
}, 30000);

// ============================================
// Routes API
// ============================================
app.get('/', (req, res) => {
  res.json({ 
    status: '🚀 SERVEUR NUFOTEC ULTIME',
    version: '3.0',
    uptime: Math.floor((Date.now() - stats.startTime) / 1000) + 's',
    connections: stats.rooms,
    features: {
      cors: true,
      websocket: true,
      stun: true,
      turn: false
    },
    transports: ['polling', 'websocket'],
    time: new Date().toISOString()
  });
});

app.get('/api/ice-servers', (req, res) => {
  res.json(TURN_CONFIG);
});

app.get('/api/stats', (req, res) => {
  res.json({
    connections: stats.rooms,
    totalConnections: stats.connections,
    uptime: Date.now() - stats.startTime,
    memory: process.memoryUsage(),
    cpu: os.loadavg()
  });
});

// ============================================
// Socket.IO Events
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

    // Heartbeat pour garder la connexion active
    socket.heartbeatTimer = setInterval(() => {
      socket.to(roomId).emit('heartbeat', { 
        id: socket.id, 
        time: Date.now() 
      });
    }, 15000);
  });

  // Événements WebRTC
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
    
    if (socket.heartbeatTimer) {
      clearInterval(socket.heartbeatTimer);
    }
    
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
// Démarrage
// ============================================
const port = process.env.PORT || 3002;

server.listen(port, () => {
  console.log(`
╔════════════════════════════════════════╗
║  🚀 NUFOTEC CONSULTATION ULTIME       ║
╠════════════════════════════════════════╣
║  📡 Port: ${String(port).padEnd(30)}║
║  🌐 URL: https://consultation.nufotec.com ║
║  🔧 Node: ${process.version.padEnd(29)}║
║  💻 Host: ${os.hostname().padEnd(29)}║
║  📊 Cores: ${os.cpus().length} CPU                      ║
║  🎯 STUN: 7 serveurs actifs           ║
╚════════════════════════════════════════╝
  `);
});

// Gestion propre de l'arrêt
process.on('SIGTERM', () => {
  console.log('SIGTERM reçu, arrêt gracieux...');
  server.close(() => process.exit(0));
});

process.on('uncaughtException', (err) => {
  console.error('❌ Exception:', err);
});

process.on('unhandledRejection', (reason) => {
  console.error('❌ Rejection:', reason);
});