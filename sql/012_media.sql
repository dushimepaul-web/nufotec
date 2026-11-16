-- Médias : catégories, collections, reprise de lecture, envois par morceaux, modération, traitement ffmpeg.

ALTER TABLE `media_downloads` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Médias : identifiant public, version légère, état du traitement.
--    Les catégories sont les types : audio, vidéo, image, fichier (document/autre), lien.
ALTER TABLE `galerie_medias`
  ADD COLUMN `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `id_media`,
  ADD COLUMN `version_legere` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'audio ~64 kbit/s pour économiser les données mobiles',
  ADD COLUMN `traitement` enum('aucun','en_attente','en_cours','pret','erreur') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aucun',
  ADD COLUMN `traitement_erreur` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD UNIQUE KEY `galerie_medias_uuid_unique` (`uuid`),
  ADD KEY `galerie_medias_type_index` (`type`, `est_actif`);

UPDATE `galerie_medias` SET `uuid` = UUID() WHERE `uuid` IS NULL;
UPDATE `galerie_medias` SET `traitement` = 'en_attente' WHERE `type` IN ('audio', 'video') AND `fichier` IS NOT NULL;

-- 3. Réactions et notes : une par COMPTE (et non plus une par adresse IP, partagée sur mobile).
ALTER TABLE `media_likes` DROP INDEX `unique_user_media`, ADD UNIQUE KEY `media_likes_media_user_unique` (`id_media`, `user_id`);
ALTER TABLE `media_ratings` DROP INDEX `unique_user_rating`, ADD UNIQUE KEY `media_ratings_media_user_unique` (`id_media`, `user_id`);
ALTER TABLE `media_views` ADD KEY `media_views_dedupe_index` (`id_media`, `viewed_at`);

-- 4. Commentaires : en attente de modération par défaut (0 = en attente, 1 = publié, -1 = refusé).
ALTER TABLE `media_comments`
  MODIFY `is_approved` tinyint NOT NULL DEFAULT '0',
  ADD COLUMN `moderated_by` int unsigned DEFAULT NULL,
  ADD COLUMN `moderated_at` datetime DEFAULT NULL,
  ADD KEY `media_comments_moderation_index` (`is_approved`, `created_at`);

-- 5. Reprise de lecture.
CREATE TABLE IF NOT EXISTS `media_progress` (
  `user_id` int unsigned NOT NULL,
  `id_media` int NOT NULL,
  `position_secondes` int unsigned NOT NULL DEFAULT '0',
  `duree_secondes` int unsigned DEFAULT NULL,
  `termine` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`user_id`, `id_media`),
  KEY `media_progress_recent_index` (`user_id`, `updated_at`),
  CONSTRAINT `media_progress_media_foreign` FOREIGN KEY (`id_media`) REFERENCES `galerie_medias` (`id_media`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Collections (séries ordonnées, composées par l'admin).
CREATE TABLE IF NOT EXISTS `media_collections` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `couverture_media_id` int DEFAULT NULL,
  `position` smallint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_collections_uuid_unique` (`uuid`),
  UNIQUE KEY `media_collections_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `media_collection_items` (
  `collection_id` int unsigned NOT NULL,
  `id_media` int NOT NULL,
  `position` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`collection_id`, `id_media`),
  CONSTRAINT `media_collection_items_collection_foreign` FOREIGN KEY (`collection_id`) REFERENCES `media_collections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `media_collection_items_media_foreign` FOREIGN KEY (`id_media`) REFERENCES `galerie_medias` (`id_media`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Envois par morceaux (reprise possible après une coupure réseau).
CREATE TABLE IF NOT EXISTS `media_uploads` (
  `id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_id` int unsigned NOT NULL,
  `nom_fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taille` bigint unsigned NOT NULL,
  `taille_morceau` int unsigned NOT NULL,
  `morceaux_recus` text COLLATE utf8mb4_unicode_ci COMMENT 'indices reçus (JSON)',
  `statut` enum('en_cours','termine','abandonne') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_cours',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
