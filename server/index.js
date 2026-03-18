const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const os = require('os');

const app = express();
const server = http.createServer(app);

// ============================================
// ⭐ CORS ULTRA-PERMISSIF - CORRECTION MAXIMALE
// ============================================
const cors = require('cors');

// Option 1: CORS complet pour tous les domaines
app.use(cors({
  origin: '*',
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With'],
  credentials: false,
  preflightContinue: false,
  optionsSuccessStatus: 204
}));

// Option 2: Middleware CORS manuel supplémentaire (double sécurité)
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
  res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
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
// ⭐ BODY PARSER (nécessaire pour POST/PUT)
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
    { urls: 'stun:stun2.l.google.com:19302' }
  ]
};

// ============================================
// ⭐ SOCKET.IO AVEC CORS CORRECT
// ============================================
const io = socketIo(server, {
  cors: { 
    origin: "*",
    methods: ["GET", "POST"],
    allowedHeaders: ["Content-Type"],
    credentials: false
  },
  pingTimeout: 60000,
  pingInterval: 25000,
  transports: ['polling', 'websocket'],
  allowUpgrades: true
});

// ============================================
// ⭐ ROUTES API
// ============================================

// Test simple
app.get('/', (req, res) => {
  res.json({ 
    status: 'OK',
    server: 'NUFOTEC Consultation',
    time: new Date().toISOString()
  });
});

// ICE servers - CORRIGÉ avec CORS explicite
app.get('/api/ice-servers', (req, res) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.json(TURN_CONFIG);
});

// Health check
app.get('/health', (req, res) => {
  res.json({ status: 'healthy', uptime: process.uptime() });
});

// ============================================
// ⭐ SOCKET.IO EVENTS
// ============================================
io.on('connection', (socket) => {
  console.log('✅ Connecté:', socket.id);

  socket.on('join-room', (roomId) => {
    const rooms = io.sockets.adapter.rooms;
    const room = rooms.get(roomId);
    const numClients = room ? room.size : 0;

    if (numClients >= 2) {
      socket.emit('room-full', { message: 'Salle pleine' });
      return;
    }

    socket.join(roomId);
    console.log(`📌 ${socket.id} → ${roomId}`);
    
    socket.to(roomId).emit('user-connected', {
      id: socket.id,
      timestamp: Date.now()
    });
  });

  socket.on('offer', (data) => {
    socket.to(data.target).emit('offer', { 
      sdp: data.sdp, 
      sender: socket.id 
    });
  });

  socket.on('answer', (data) => {
    socket.to(data.target).emit('answer', { 
      sdp: data.sdp, 
      sender: socket.id 
    });
  });

  socket.on('ice-candidate', (data) => {
    socket.to(data.target).emit('ice-candidate', { 
      candidate: data.candidate, 
      sender: socket.id 
    });
  });

  socket.on('disconnect', () => {
    console.log('❌ Déconnecté:', socket.id);
    socket.rooms.forEach(room => {
      if (room !== socket.id) {
        socket.to(room).emit('user-disconnected', { id: socket.id });
      }
    });
  });
});

// ============================================
// ⭐ DÉMARRAGE
// ============================================
const port = process.env.PORT || 3002;

server.listen(port, () => {
  console.log(`
╔════════════════════════════════════════╗
║  🚀 NUFOTEC CONSULTATION              ║
╠════════════════════════════════════════╣
║  Port: ${port}                          ║
║  CORS: * (tous domaines)               ║
╚════════════════════════════════════════╝
  `);
});