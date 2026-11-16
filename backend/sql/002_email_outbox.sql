-- File d'attente des emails : écrite dans la même transaction que l'action (inscription, code…),
-- puis envoyée avec nouvelles tentatives. Le contenu est effacé une fois l'email envoyé ou abandonné
-- (il peut contenir un code de réinitialisation).
CREATE TABLE IF NOT EXISTS `email_outbox` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `to_email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text_body` mediumtext COLLATE utf8mb4_unicode_ci,
  `html_body` mediumtext COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `attempts` smallint unsigned NOT NULL DEFAULT '0',
  `last_error` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_attempt_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_outbox_due_index` (`status`, `next_attempt_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
