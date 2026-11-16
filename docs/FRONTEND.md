# Brancher le frontend sur l'API Nufotec

- **Adresse** : `http://127.0.0.1:8000/api/v1` en local (`.venv\Scripts\fastapi dev app/main.py`).
- **Documentation interactive** : `http://127.0.0.1:8000/docs` (essayer chaque endpoint) ;
  description complète : [`openapi.json`](openapi.json) — à importer dans Postman ou un générateur de client.
- **CORS** : `CORS_ORIGINS` dans `.env` (`*` en local ; mettre l'adresse exacte du site en production).

## Format de toutes les réponses

```json
{ "success": true,  "message": "…", "data": { … } }
{ "success": false, "error": { "code": "EMAIL_TAKEN", "message": "…", "details": [ {"field": "email", "message": "…"} ] } }
```

- Listes paginées : `data = { "items": [...], "meta": { "page", "per_page", "total", "pages" } }` (`?page=1&per_page=20`).
- **Toujours s'appuyer sur `error.code`**, jamais sur le texte (le texte dépend de la langue).
- Dates : ISO 8601 avec fuseau du Burundi, ex. `2026-11-13T06:28:00+02:00`.
- Erreur 429 : attendre `Retry-After` secondes. Erreur 500 : afficher `details.request_id` pour le support.

## Connexion

1. `POST /auth/login` `{login, password}` (email ou téléphone) → `data.tokens.access_token` (30 min) et `refresh_token`.
2. Envoyer `Authorization: Bearer <access_token>` sur les routes protégées.
3. Sur `401` avec `TOKEN_EXPIRED` : `POST /auth/refresh` `{refresh_token}` → nouveaux jetons (l'ancien refresh ne sert plus).
   `TOKEN_REUSED` = session fermée par sécurité → renvoyer vers la connexion.
4. Profil : `GET /auth/me`. Rôles : `data.role.slug` = `admin`, `medecin` ou `user`.

## Langue et traduction

- Le site est **en français** (langue par défaut). La traduction en 50 langues se fait **dans le navigateur avec
  Google Translate** : `GET /site/parametres` → `traduction.langues` (codes Google) et `langue_de_la_page: "fr"`.
- Envoyer `Accept-Language` (ou `?lang=xx`) : seuls les **messages** de l'API changent de langue.

## Points d'attention

- **Créations/modifications depuis le mobile** : ajouter `Idempotency-Key: <uuid>` (pas de doublon si le réseau coupe).
- **WhatsApp** : réserver une consultation (`POST /consultations`) ou commander (`POST /produits/{slug}/commander`)
  renvoie `whatsapp_medecin.url` / `whatsapp.url` → **ouvrir ce lien** : WhatsApp s'ouvre avec le message prêt.
- **Paiement** : aucun paiement en ligne. Le patient téléverse sa preuve (`POST /consultations/{n}/paiement`,
  multipart) ; tout le reste se passe sur WhatsApp.
- **Fichiers privés** (documents médicaux, data room) : demander un lien (`…/lien`) valable 5 minutes, puis l'ouvrir.
- **Médias** : lecture avec `GET /medias/{slug}/flux` (version légère par défaut, avance rapide gérée) ;
  enregistrer la position avec `PUT /medias/{slug}/progression` pour « Reprendre la lecture ».
- **Envoi de gros fichiers (admin)** : `POST /admin/medias/envois` → `PUT …/morceaux/{i}` (5 Mo) → `POST …/terminer`.
- **Formulaires publics** : prévoir un champ caché `site_web` (anti-robots), laissé vide.
- **Notifications push** : après connexion, `POST /auth/push-devices` avec le jeton Expo.
- **Statistiques** : `POST /stats/visite {page}` à chaque page vue (anonyme).

## Écrans → endpoints principaux

| Écran | Endpoints |
|---|---|
| Accueil, pages | `GET /pages` (menu), `GET /pages/{slug}`, `GET /site/parametres`, `GET /chiffres-cles`, `GET /partenaires`, `GET /temoignages` |
| Actualités, FAQ, contact | `GET /actualites`, `GET /actualites/{slug}`, `GET /faq`, `POST /contact`, `POST /newsletter/inscription` |
| Médecins, consultation | `GET /medecins`, `GET /medecins/{uuid}/creneaux`, `POST /consultations`, `GET /consultations`, `GET /consultations/{n}/rejoindre` |
| Espace médecin | `GET /medecin/consultations`, `POST /medecin/consultations/{n}/confirmer|terminer`, `PUT /medecin/horaires` |
| Boutique | `GET /produits`, `GET /produits/{slug}`, `POST /produits/{slug}/commander`, `GET /commandes/suivi` |
| Médias | `GET /medias`, `GET /medias/{slug}`, `GET /medias/{slug}/flux`, `GET /medias/collections` |
| Investir | `GET /investissement/phases`, `POST /investisseurs/manifestation`, `POST /courtiers/candidature` |
| Mon compte | `GET/PATCH /auth/me`, `POST /auth/me/email`, `GET /auth/sessions`, `DELETE /auth/me` |
| Administration | `GET /admin/tableau-de-bord`, puis `/admin/users`, `/admin/consultations`, `/admin/commandes`, `/admin/medias`, `/admin/contenu/…`, `/admin/investisseurs`, `/admin/journal` |
