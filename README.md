# Nufotec API (FastAPI)

Réécriture en Python du backend PHP `nufotec-backend` (CodeIgniter), sous forme d'API REST.
Elle se branche sur la base MySQL existante **nufotec_db** sans modifier son schéma, et les
mots de passe sont compatibles avec ceux du site PHP (bcrypt `$2y$`).

## Installation

```bash
python -m venv .venv
.venv\Scripts\python -m pip install -r requirements.txt
copy .env.example .env    # puis mettre une vraie valeur dans JWT_SECRET
```

## Lancer

```bash
.venv\Scripts\fastapi dev app/main.py
```

- Documentation Swagger : http://127.0.0.1:8000/docs
- Santé : http://127.0.0.1:8000/api/v1/health

## Tests

Les tests utilisent nufotec_db dans une transaction annulée : aucune donnée n'est modifiée.

```bash
.venv\Scripts\python -m pytest
```

## Conventions

- Préfixe : `/api/v1`
- Succès : `{"success": true, "message": "...", "data": ...}`
- Erreur : `{"success": false, "error": {"code": "EMAIL_TAKEN", "message": "...", "details": [...]}}`
- Authentification : `Authorization: Bearer <access_token>` (30 min). Le `refresh_token` (30 jours) est
  à usage unique : `/auth/refresh` en renvoie un nouveau. Chaque session est une ligne de `user_sessions`.

## Structure

```
app/
  core/        config, base de données, sécurité (bcrypt/JWT), enveloppe et erreurs, dépendances
  models/      modèles SQLAlchemy des tables existantes
  modules/     un dossier par module (router.py, schemas.py, service.py)
tests/
```

## Modules

| Module | État | Endpoints |
|---|---|---|
| Auth | fait | `POST /auth/register`, `POST /auth/login`, `POST /auth/refresh`, `POST /auth/logout`, `GET/PATCH /auth/me`, `POST /auth/change-password` |
| Utilisateurs et rôles | à faire | |
| Consultations et médecins | à faire | |
| Chatbot | à faire | |
| Investisseurs et courtiers | à faire | |
| Commandes et produits | à faire | |
| Médias | à faire | |
| Contenu du site | à faire | |
