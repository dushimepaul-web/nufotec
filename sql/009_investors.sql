-- Investisseurs et courtiers : encodage, liaison stricte aux comptes, pipeline, revue des courtiers,
-- data room avec accord de confidentialité (NDA).

-- 1. Encodage : latin1 → utf8mb4 (noms en toutes langues, emojis).
ALTER TABLE `investors` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `brokers` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Investisseurs : identifiant public, compte lié, pipeline de suivi, source, consentement.
ALTER TABLE `investors`
  ADD COLUMN `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `id`,
  ADD COLUMN `user_id` int unsigned DEFAULT NULL AFTER `uuid`,
  ADD COLUMN `statut` enum('nouveau','contacte','qualifie','due_diligence','engage','perdu')
    COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'nouveau',
  ADD COLUMN `assigned_to` int unsigned DEFAULT NULL COMMENT 'admin responsable',
  ADD COLUMN `prochaine_relance` datetime DEFAULT NULL,
  ADD COLUMN `relance_envoyee_at` datetime DEFAULT NULL,
  ADD COLUMN `source` enum('direct','courtier') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'direct',
  ADD COLUMN `broker_id` bigint DEFAULT NULL COMMENT 'courtier qui l''a présenté',
  ADD COLUMN `consent_at` datetime DEFAULT NULL,
  ADD COLUMN `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD UNIQUE KEY `investors_uuid_unique` (`uuid`),
  ADD KEY `investors_user_index` (`user_id`),
  ADD KEY `investors_email_index` (`email`),
  ADD KEY `investors_statut_index` (`statut`);

-- 3. Courtiers : identifiant public, compte lié, revue (AML/KYC) par l'administration.
ALTER TABLE `brokers`
  ADD COLUMN `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `id`,
  ADD COLUMN `user_id` int unsigned DEFAULT NULL AFTER `uuid`,
  ADD COLUMN `statut` enum('en_attente','approuve','refuse','suspendu') COLLATE utf8mb4_unicode_ci
    NOT NULL DEFAULT 'en_attente',
  ADD COLUMN `motif_decision` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `decision_at` datetime DEFAULT NULL,
  ADD COLUMN `decision_par` int unsigned DEFAULT NULL,
  ADD COLUMN `consent_at` datetime DEFAULT NULL,
  ADD COLUMN `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD UNIQUE KEY `brokers_uuid_unique` (`uuid`),
  ADD KEY `brokers_user_index` (`user_id`),
  ADD KEY `brokers_email_index` (`email`);

-- 4. Investisseurs présentés par un courtier : lien vers le pipeline et durée d'attribution.
ALTER TABLE `broker_investors`
  ADD COLUMN `investor_id` bigint DEFAULT NULL AFTER `broker_id`,
  ADD COLUMN `attribution_until` datetime DEFAULT NULL,
  ADD KEY `broker_investors_email_index` (`email`);

-- Reprise des données existantes : identifiants publics, liaison par email VÉRIFIÉ uniquement (jamais par nom).
UPDATE `investors` SET `uuid` = UUID() WHERE `uuid` IS NULL;
UPDATE `brokers` SET `uuid` = UUID() WHERE `uuid` IS NULL;
UPDATE `investors` i JOIN `users` u ON LOWER(u.email) = LOWER(i.email)
  AND u.email_verified_at IS NOT NULL AND u.deleted_at IS NULL SET i.user_id = u.id WHERE i.user_id IS NULL;
UPDATE `brokers` b JOIN `users` u ON LOWER(u.email) = LOWER(b.email)
  AND u.email_verified_at IS NOT NULL AND u.deleted_at IS NULL SET b.user_id = u.id WHERE b.user_id IS NULL;
UPDATE `investors` SET `consent_at` = `created_at` WHERE `agree_contact` = 1 AND `consent_at` IS NULL;
UPDATE `broker_investors` SET `attribution_until` = DATE_ADD(`created_at`, INTERVAL 12 MONTH)
  WHERE `attribution_until` IS NULL;

-- 5. Suivi : notes, changements de statut, relances, assignations.
CREATE TABLE IF NOT EXISTS `investor_notes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `investor_id` bigint NOT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `kind` enum('note','statut','relance','assignation','manifestation') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'note',
  `from_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `investor_notes_investor_index` (`investor_id`),
  CONSTRAINT `investor_notes_investor_foreign` FOREIGN KEY (`investor_id`) REFERENCES `investors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Data room : documents réservés aux investisseurs qualifiés ayant accepté le NDA.
CREATE TABLE IF NOT EXISTS `data_room_documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `stored_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_bytes` int unsigned NOT NULL,
  `visibilite` enum('tous_qualifies','selection') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tous_qualifies',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `data_room_documents_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `data_room_access` (
  `document_id` int unsigned NOT NULL,
  `investor_id` bigint NOT NULL,
  PRIMARY KEY (`document_id`, `investor_id`),
  CONSTRAINT `data_room_access_document_foreign` FOREIGN KEY (`document_id`) REFERENCES `data_room_documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `data_room_access_investor_foreign` FOREIGN KEY (`investor_id`) REFERENCES `investors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `data_room_downloads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `document_id` int unsigned NOT NULL,
  `investor_id` bigint DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `data_room_downloads_document_index` (`document_id`),
  CONSTRAINT `data_room_downloads_document_foreign` FOREIGN KEY (`document_id`) REFERENCES `data_room_documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nda_acceptances` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `investor_id` bigint NOT NULL,
  `user_id` int unsigned NOT NULL,
  `nda_version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accepted_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nda_acceptances_investor_version_unique` (`investor_id`, `nda_version`),
  CONSTRAINT `nda_acceptances_investor_foreign` FOREIGN KEY (`investor_id`) REFERENCES `investors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
