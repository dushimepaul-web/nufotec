import { resolve } from 'node:path'
import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

// Un seul projet, deux points d'entrée :
//   index.html -> site public ;  admin.html -> administration (/admin) et espace médecin (/medecin), connexion (/login).
// Chacun garde ses propres styles (l'admin NiceAdmin ne touche jamais au design du site public).
const ADMIN_PATHS = /^\/(admin|medecin|login)(\/|$|\?)/

/** Développement : les adresses de l'admin servent admin.html (en production, même règle côté serveur web). */
function adminEntry() {
  const rewrite = (req, _res, next) => {
    const url = req.url || ''
    const accept = req.headers.accept || ''
    if (ADMIN_PATHS.test(url) && accept.includes('text/html')) req.url = '/admin.html'
    next()
  }
  return {
    name: 'nufotec-admin-entry',
    // corps en accolades : renvoyer une valeur ici serait pris par Vite pour un crochet « après »
    configureServer(server) { server.middlewares.use(rewrite) },
    configurePreviewServer(server) { server.middlewares.use(rewrite) },
  }
}

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), adminEntry()],
  build: {
    rollupOptions: {
      input: { main: resolve(import.meta.dirname, 'index.html'), admin: resolve(import.meta.dirname, 'admin.html') },
    },
  },
})
