-- Changement d'adresse email par l'utilisateur : confirmé par un lien envoyé à la NOUVELLE adresse,
-- alerte envoyée à l'ancienne. Le jeton n'est stocké qu'haché.
CREATE TABLE IF NOT EXISTS `email_changes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `ancien_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nouvel_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_changes_token_unique` (`token_hash`),
  KEY `email_changes_user_index` (`user_id`, `created_at`),
  CONSTRAINT `email_changes_user_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
