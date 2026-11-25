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
En production, le serveur web doit faire la même chose. Avec Apache, c'est fait par `public/.htaccess`, copié dans `dist/` à chaque `npm run build` (il ajoute aussi le cache, la compression et les en-têtes de sécurité) :

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(admin|medecin|login)(/.*)?$ /admin.html [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ /index.html [L]
```

## Rapidité

- **Pages à la demande** : seule la page d'accueil est dans le fichier principal ; les autres se chargent à l'ouverture.
- **Images** : `loading="lazy"` sur toutes les images hors du haut de page ; les drapeaux des menus de langues ne se
  chargent qu'à la première ouverture d'un menu. Versions WebP de `public/` : `python -m tools.optimize_images --dossier ..\frontend\public`
  (depuis `backend/`).
- **Icônes** : `src/styles/icons/` contient une petite police (~20 Ko) avec les icônes utilisées par le code ; la police
  complète (131 Ko) n'est téléchargée que si une page affiche une autre icône (choisie dans l'administration).
  Après avoir ajouté des icônes `bi-…` au code : `pip install fonttools brotli` puis `python scripts/build_icons.py`.
