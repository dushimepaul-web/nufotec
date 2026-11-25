# Nufotec API (FastAPI)

Réécriture en Python du backend PHP `nufotec-backend` (CodeIgniter), sous forme d'API REST.
Elle se branche sur la base MySQL existante **nufotec_db** sans modifier son schéma, et les
mots de passe sont compatibles avec ceux du site PHP (bcrypt `$2y$`).

## Installation

```bash
python -m venv .venv
.venv\Scripts\python -m pip install -r requirements.txt
copy .env.example .env    # puis mettre une vraie valeur dans JWT_SECRET et SMTP_PASSWORD
```

Tables ajoutées à nufotec_db par l'API (à exécuter une fois, dans l'ordre) :

```bash
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/001_password_resets.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/002_email_outbox.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/003_idempotency_keys.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/004_push_devices.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/005_consultations.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/006_consultations_pro.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/007_medecin_whatsapp.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/008_chatbot_rag.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/009_investors.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/010_investors_cleanup.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/011_shop.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/012_media.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/013_content.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/014_parametres.sql
mysql -uroot --default-character-set=utf8mb4 nufotec_db < sql/015_email_changes.sql
```

Puis, une fois : `python -m tools.migrate_chatbot_keys` (clés d'IA → stockage chiffré) et
`python -m tools.index_knowledge` (modèle d'embeddings + indexation) et
`python -m tools.import_static_pages` (pages et sections du PHP → base),
`python -m tools.process_media` (versions légères et formes d'onde des audios existants ; **ffmpeg requis sur le serveur**).
⚠️ Toujours passer `--default-character-set=utf8mb4` au client mysql, sinon les accents sont abîmés. `AI_ENCRYPTION_KEY` (.env) est à sauvegarder.

Les documents médicaux sont rangés dans `PRIVATE_DIR` (par défaut `C:\wamp64\nufotec-private`), **hors du dossier web**.

## Lancer

```bash
.venv\Scripts\fastapi dev app/main.py
```

- Documentation Swagger : http://127.0.0.1:8000/docs
- Santé : http://127.0.0.1:8000/api/v1/health (base comprise : 200 ou 503) et `/api/v1/health/live` (l'API seule)

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
  Un ancien `refresh_token` rejoué plus de 15 s après sa rotation ferme la session (vol présumé).
- Chaque réponse porte `X-Request-ID` ; une erreur 500 renvoie cet identifiant pour retrouver la trace.
- Erreur 429 : en-tête `Retry-After` (secondes).

## Pour l'app mobile

- **Dates** : toujours au format ISO avec fuseau du Burundi, ex. `2026-11-13T06:28:00+02:00`.
- **Idempotency-Key** : envoyer `Idempotency-Key: <uuid>` sur chaque POST/PATCH/DELETE. Si la requête est
  renvoyée (coupure réseau), l'API rejoue la première réponse (`Idempotent-Replayed: true`) sans rien refaire.
  Même clé avec un autre contenu → 422 `IDEMPOTENCY_KEY_REUSED` ; requête encore en cours → 409.
- **Langue** : `Accept-Language` (ou `?lang=xx`). 50 langues (`GET /api/v1/languages`), français par défaut.
  Seuls les messages changent : l'app doit s'appuyer sur les **codes** d'erreur.
- **Notifications push** : après la connexion, `POST /auth/push-devices` avec le jeton Expo
  (`ExponentPushToken[...]`) ; `POST /auth/push-devices/test` pour vérifier. Un appareil déconnecté ne reçoit plus rien.
  `PUSH_MODE=expo` dans `.env` pour envoyer réellement.
- **Emails** : mis en file (`email_outbox`) dans la même transaction que l'action, envoyés aussitôt, puis
  retentés après 1 min, 5 min, 30 min, 2 h et 6 h en cas d'échec. Le contenu est effacé une fois envoyé.

## Traductions (50 langues)

**Contenu du site** : rédigé en **français** (langue par défaut) et traduit **dans le navigateur avec Google Translate** ; rien n'est traduit ni stocké côté serveur. `GET /site/parametres` renvoie `langue_par_defaut` et la liste des 50 langues (codes Google) pour le widget.

**Messages de l'API** :

Les messages sont écrits en français ; l'anglais est relu à la main (`app/core/i18n_messages.py`) ;
les 48 autres langues sont dans `app/locales/<code>.json`, générées avec Google Translate (gratuit) :

```bash
.venv\Scripts\python -m tools.generate_translations
```

Seuls les textes manquants sont traduits : à relancer après avoir ajouté des messages. Si Google répond
« trop de requêtes », le script patiente puis s'arrête proprement ; relancez-le plus tard, il reprend.
Traduction absente → l'API répond en anglais, puis en français.

## Consultations

1. Le patient (compte obligatoire) choisit un médecin et un **créneau libre** (horaires de la semaine découpés
   selon la durée du médecin, moins les rendez-vous pris et ses absences). Le formulaire reprend celui du site :
   âge, pays, poids, taille, symptômes, WhatsApp, mode de paiement.
2. Le **prix est calculé par le serveur** : tarif BIF pour un résident du Burundi, USD sinon.
3. La réponse contient `whatsapp_medecin.url` : l'app l'ouvre, WhatsApp s'ouvre sur le **numéro du médecin
   choisi** avec le récapitulatif déjà rempli (numéro : fiche médecin → téléphone du compte → `contact_whatsapp`).
4. Le patient envoie sa preuve de paiement ; l'admin la valide. Le médecin confirme : une salle Jitsi au nom
   aléatoire est créée ; le lien n'est donné qu'au patient et au médecin, de 10 min avant à 30 min après, paiement validé.
5. Rappels la veille et 1 h avant ; une demande jamais confirmée expire à l'heure du rendez-vous.
6. **Secret médical** : l'admin ne voit ni les symptômes, ni le compte-rendu, ni les documents médicaux
   (seulement la preuve de paiement). Documents servis par liens signés de 5 minutes.

Statuts : `en_attente → confirmee → en_cours → terminee`, avec `refusee`, `annulee`, `absent` ; toute autre
transition est refusée (409 `INVALID_TRANSITION`) et chaque changement est historisé.

## Sécurité (lot B)

- **En-têtes de sécurité** sur toutes les réponses (nosniff, anti-iframe, CSP stricte ; HSTS en production).
- **Taille maximale** des requêtes : `MAX_REQUEST_MB` (60). Les médias passent par envoi en morceaux.
- **Limite de débit** : `RATE_LIMIT_IP_PER_MINUTE` (120, visiteurs) et `RATE_LIMIT_USER_PER_MINUTE` (300, comptes) ;
  réponse 429 avec `Retry-After`. Compteurs en mémoire (un seul serveur) ; à partager (Redis) si plusieurs serveurs.
- **Changement d'email** : `POST /auth/me/email` → lien à la nouvelle adresse (24 h), alerte à l'ancienne.
- **Alertes par email** : connexion depuis un nouvel appareil, mot de passe changé ou réinitialisé, changement
  d'email, suppression du compte.
- **Purge manuelle** des comptes supprimés par l'admin depuis plus de 30 jours : `POST /admin/users/purger`
  (aucune tâche planifiée).
- **Journal d'activité** : `GET /admin/journal` (tous les modules) et `GET /admin/journal/securite`.
- **Paiement** : aucun paiement en ligne. Le patient téléverse sa preuve dans l'app ; les autres échanges se font
  sur WhatsApp. Les comptes affichés (Lumicash, EcoCash…) se gèrent dans `/admin/modes-paiement`.
- Toutes les dates de l'API portent le fuseau du Burundi (+02:00), y compris dans les réponses libres.

## Disponibilité et rapidité

- **Base lente ou arrêtée** : délais limités (`DB_CONNECT_TIMEOUT`, `DB_READ_TIMEOUT`, `DB_POOL_TIMEOUT`), connexions
  coupées remplacées automatiquement (`pool_pre_ping`). L'API répond alors **503 `SERVICE_UNAVAILABLE`** avec
  `Retry-After: 5` au lieu de rester bloquée ; le site et l'app retentent seuls.
- **Surveillance** : `GET /api/v1/health` (200 si la base répond, 503 sinon, avec le temps de réponse de la base) pour
  un outil comme Uptime Kuma ou UptimeRobot ; `GET /api/v1/health/live` pour le superviseur qui redémarre l'API.
- **Redémarrage automatique** : lancer l'API comme service (NSSM sous Windows, systemd sous Linux) avec plusieurs
  processus : `uvicorn app.main:app --host 127.0.0.1 --port 8000 --workers 4`. Les rappels et emails ne partent
  qu'une fois même avec plusieurs processus (`SKIP LOCKED`).
  La limite de débit, elle, est comptée par processus (4 processus : limite réelle ×4) : diviser
  `RATE_LIMIT_*` d'autant, ou la partager avec Redis.
- **Sauvegardes** : `python -m tools.backup_db --prive` chaque nuit (Planificateur de tâches), puis copie sur un autre
  disque ou une autre machine. Restauration : `gunzip -c fichier.sql.gz | mysql --default-character-set=utf8mb4 nufotec_db`.
- **ETag / 304** : les réponses publiques (GET sans compte) portent une empreinte ; si rien n'a changé, l'API
  répond 304 sans corps. Avec la compression gzip, c'est l'essentiel du gain sur les connexions lentes.
- **Images WebP** : chaque image envoyée reçoit une version `photo.jpg.webp` (≤ 200 Ko, ≤ 1920 px), servie par Apache
  sous la même adresse aux navigateurs qui l'acceptent ; l'original n'est jamais modifié. Pour les images existantes :
  `python -m tools.optimize_images` (une fois) ; l'API refait la passe chaque nuit après 3 h (images du site PHP).
- **Apache** : `attachments/.htaccess` met en cache images et médias (30 jours) ; `frontend/public/.htaccess`
  (copié dans `dist/`) met en cache les fichiers du site, compresse et ajoute les en-têtes de sécurité.

## Sécurité du module Auth

- Blocage 15 min après 5 échecs sur un compte (30 par IP), compté dans la table `logs`.
- Temps de réponse identique que le compte existe ou non.
- Téléphones normalisés en E.164 (`+25779123456`, Burundi par défaut) ; les anciens formats de la base
  (« +257 79 123 456 ») restent reconnus sans être réécrits.
- Mot de passe : 8 caractères, une lettre, un chiffre, pas un mot de passe courant, pas l'email ni le nom.
- Codes de réinitialisation : 6 chiffres, 15 min, 5 essais, stockés hachés (HMAC), 3 par heure.
- Photos : contenu réellement vérifié (Pillow), ré-encodées sans EXIF, 400 px, dans `attachments/Users` (partagé avec le PHP).
- Activités dans `user_activities` (visibles dans l'admin PHP).
- En production (`APP_ENV=production`) : refuse de démarrer si `JWT_SECRET` est faible, `CORS_ORIGINS=*`,
  `DEBUG=true` ou `MAIL_MODE` différent de smtp ; `/docs` est masqué. Derrière nginx/Cloudflare,
  mettre leurs IP dans `TRUSTED_PROXIES`.

## Droits

Il y a exactement **3 rôles fixes** : `admin`, `medecin` et `user` (utilisateur simple). On ne peut ni en
créer ni en supprimer ; seuls leur nom affiché et leur description sont modifiables. Le rôle **découle du
type** : type `admin` → admin, `medecin` → médecin, tous les autres (patient, entreprise, investisseur,
partenaire, broker) → user. Modifier un ancien compte incohérent le réaligne automatiquement.
Les permissions (`users.read`…) sont définies dans `app/core/permissions.py` ; le rôle admin les a toutes.
Garde-fous : pas d'action sur soi-même (désactiver, supprimer, se retirer le rôle admin), le dernier admin
actif est protégé, changer de rôle ou désactiver ferme les sessions.

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
| Auth | fait | `register`, `login`, `refresh`, `logout`, `password/forgot`, `password/reset`, `change-password`, `verify-email`, `resend-verification`, `GET/PATCH/DELETE me`, `POST/DELETE me/photo`, `GET/DELETE sessions`, `DELETE sessions/{id}` |
| Utilisateurs et rôles | fait | `admin/users` : liste (recherche, filtres, tri, pagination), `stats`, `export.csv`, détail, création avec invitation, modification, `activate`/`deactivate`, `kyc`, `send-reset`, `photo`, `sessions`, `activities`, suppression/`restore` · `admin/roles` : liste, détail, nom affiché/description, `permissions` · `pays` (public) |
| Consultations et médecins | fait | public : `medecins`, `creneaux`, `modes-paiement` · patient : réserver (+ lien WhatsApp vers le médecin), suivre, documents, paiement, annuler, déplacer, rejoindre (Jitsi), avis, agenda .ics · médecin : agenda, confirmer/refuser/démarrer/terminer, patient absent, ordonnance, horaires, absences · admin : médecins, paiements, réattribution, annulation, stats |
| Chatbot | fait | widget (flux mot à mot, sources, transfert à un conseiller, avis) · admin : fournisseurs d'IA (clés chiffrées, test), réglages, articles, documents, **tables choisies par l'admin** (colonnes, filtres, aperçu), espace de test, conversations, statistiques |
| Investisseurs et courtiers | fait | public : phases, manifestation d'intérêt, candidature courtier · investisseur : dossier, NDA, data room · courtier : présentations (attribution 12 mois) · admin : pipeline (statuts, notes, responsable, relances), courtiers (revue AML/KYC), phases, data room |
| Commandes et produits | fait | public : catalogue (recherche, catégories, prix BIF/USD, avertissement santé), fiche produit, **commande sans panier → lien WhatsApp vers les ventes**, demande de prix · client : suivi (numéro + téléphone, ou compte) · admin : produits (images, stock facultatif, suppression douce), catégories unifiées, commandes (statuts, historique, contacté sur WhatsApp, stats, export) |
| Médias | fait | catégories = types (audio, video, image, fichier, link) · bibliothèque (tri récent/populaire/tendance/mieux notés), flux avec avance rapide (Range), version légère ~64 kbit/s, téléchargement, vues dédoublonnées · compte : réactions, notes, favoris, commentaires modérés, reprise de lecture · collections · admin : envoi par morceaux (reprise), traitement ffmpeg en tâche de fond, modération, stats |
| Contenu du site | fait | pages et sections modifiables (22 pages / 91 sections reprises du PHP), actualités (brouillon, programmées), FAQ, témoignages, partenaires, chiffres clés, réseaux sociaux, `site/parametres` (sans secret), contact, newsletter en double confirmation, visites anonymes, tableau de bord admin |


## Table `parametres`

L'ancienne table `configurations` s'appelle `parametres`. Une **vue** `configurations` la remplace pour le site PHP
(lecture et écriture), le temps de la transition. Les secrets (mots de passe SMTP, clés d'IA) n'y sont plus.
