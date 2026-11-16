-- Appareils qui reçoivent les notifications push, liés à une session : un appareil déconnecté
-- (session fermée) ne reçoit plus rien.
CREATE TABLE IF NOT EXISTS `push_devices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `session_id` int unsigned DEFAULT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` enum('expo','fcm') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'expo',
  `platform` enum('android','ios','web') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_version` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `last_seen_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_devices_token_unique` (`token`),
  KEY `push_devices_user_id_index` (`user_id`),
  CONSTRAINT `push_devices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `push_devices_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `user_sessions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
