-- Codes de réinitialisation du mot de passe (seule table ajoutée par l'API à nufotec_db).
-- Le site PHP prévoyait déjà cette table (« Table password_resets (à créer si nécessaire) »).
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `code_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'HMAC-SHA256 du code, jamais le code en clair',
  `expires_at` datetime NOT NULL,
  `attempts` smallint unsigned NOT NULL DEFAULT '0',
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `password_resets_user_id_index` (`user_id`, `created_at`),
  CONSTRAINT `password_resets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
