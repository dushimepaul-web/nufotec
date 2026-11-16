# NUFOTEC BURUNDI

Un seul dépôt pour tout le projet :

| Dossier | Contenu |
|---|---|
| `backend/` | API FastAPI (Python) sur la base MySQL `nufotec_db` — voir `backend/README.md` |
| `frontend/` | Site public, administration (`/admin`) et espace expert (`/medecin`) en React + Vite — voir `frontend/README.md` |

## Démarrer en local

```bash
# API (port 8000)
cd backend
.venv/Scripts/python -m uvicorn app.main:app --port 8000

# Site + administration (port 5173)
cd frontend
npm install
npm run dev
```

## Fichiers envoyés (images, médias, dossiers patients)

Ils sont dans `backend/attachments/`, servis par Apache à l'adresse `http://localhost/nufotec/backend/attachments/`
(réglages `UPLOADS_DIR` et `MEDIA_BASE_URL`, voir `backend/.env.example`). Ils ne sont pas enregistrés dans git.

Le fichier `.htaccess` à la racine interdit à Apache de servir le reste du dossier (`.git`, `.env`, code) ;
seul `backend/attachments/` est public. Ne pas le supprimer.
