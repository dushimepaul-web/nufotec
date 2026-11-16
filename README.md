# React + Vite

This template provides a minimal setup to get React working in Vite with HMR and some Oxlint rules.

Currently, two official plugins are available:

- [@vitejs/plugin-react](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react) uses [Oxc](https://oxc.rs)
- [@vitejs/plugin-react-swc](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react-swc) uses [SWC](https://swc.rs/)

## React Compiler

The React Compiler is not enabled on this template because of its impact on dev & build performances. To add it, see [this documentation](https://react.dev/learn/react-compiler/installation).

## Expanding the Oxlint configuration

If you are developing a production application, we recommend using TypeScript with type-aware lint rules enabled. Check out the [TS template](https://github.com/vitejs/vite/tree/main/packages/create-vite/template-react-ts) for information on how to integrate TypeScript and Oxlint's TypeScript related rules in your project.

## Administration et espace expert (même projet)

Le projet a deux points d'entrée :

- `index.html` : site public ;
- `admin.html` : administration (`/admin/…`), espace expert (`/medecin/…`) et leur connexion (`/login`).
  Code dans `src/admin/` (repris de l'ancien projet `nufotec-admin-app`), styles NiceAdmin chargés seulement ici.

En développement (`npm run dev`), ces adresses servent `admin.html` automatiquement (voir `vite.config.js`).
En production, le serveur web doit faire la même chose, par exemple avec Apache (`.htaccess` dans `dist/`) :

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(admin|medecin|login)(/.*)?$ /admin.html [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ /index.html [L]
```
