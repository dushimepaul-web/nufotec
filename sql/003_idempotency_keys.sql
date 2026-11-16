-- Réponses mémorisées 24 h pour les requêtes envoyées avec un en-tête Idempotency-Key
-- (une app mobile qui renvoie la même requête après une coupure réseau ne crée pas de doublon).
CREATE TABLE IF NOT EXISTS `idempotency_keys` (
  `key_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA-256 de la clé + méthode + chemin + auteur',
  `request_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_code` smallint unsigned DEFAULT NULL COMMENT 'NULL = requête en cours',
  `response_body` mediumtext COLLATE utf8mb4_unicode_ci,
  `content_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`key_hash`),
  KEY `idempotency_keys_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
