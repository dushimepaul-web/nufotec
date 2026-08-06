<?php
/**
 * env.example.php — MODÈLE pour env.php (NON commité, sans secrets)
 * ----------------------------------------------------------------
 * Copiez ce fichier en « env.php », puis remplissez les valeurs.
 * env.php est exclu de Git (voir .gitignore) et chargé par index.php.
 * ----------------------------------------------------------------
 */

/* ──────────── BASE DE DONNÉES (serveur distant) ──────────── */
putenv('DB_USERNAME=nufotec_nufotec');
putenv('DB_PASSWORD=CHANGEZ_MOI');
putenv('DB_DATABASE=nufotec_db');

/* ──────────── SMTP / EMAIL ──────────── */
putenv('SMTP_USER=info@nufotec.com');
putenv('SMTP_PASS=CHANGEZ_MOI');