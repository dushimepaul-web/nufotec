-- Contenu du site : pages et sections en base (CMS), actualités, newsletter avec
-- double confirmation, messages de contact, statistiques de visites respectueuses de la vie privée.
-- La traduction du site se fait côté navigateur avec Google Translate (rien n'est traduit en base) ;
-- la langue par défaut du site est le français.
-- À appliquer avec : mysql --default-character-set=utf8mb4

ALTER TABLE `faq` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `temoignages` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `partenaires` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `chiffres_cles` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 1. Pages et sections (le PHP les gardait en dur dans static_pages_helper / static_sections_helper).
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `menu_ordre` smallint NOT NULL DEFAULT '0',
  `afficher_menu` tinyint(1) NOT NULL DEFAULT '1',
  `est_publiee` tinyint(1) NOT NULL DEFAULT '1',
  `meta_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contenu` mediumtext COLLATE utf8mb4_unicode_ci,
  `template` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icone` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legacy_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`),
  UNIQUE KEY `pages_legacy_unique` (`legacy_id`),
  CONSTRAINT `pages_parent_foreign` FOREIGN KEY (`parent_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `page_sections` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int unsigned NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'texte',
  `titre` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sous_titre` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contenu` mediumtext COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_droite` tinyint(1) NOT NULL DEFAULT '0',
  `bouton_texte` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bouton_lien` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `classe` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `options_json` mediumtext COLLATE utf8mb4_unicode_ci,
  `ordre` smallint NOT NULL DEFAULT '0',
  `est_publiee` tinyint(1) NOT NULL DEFAULT '1',
  `legacy_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_sections_legacy_unique` (`legacy_id`),
  KEY `page_sections_page_index` (`page_id`, `ordre`),
  CONSTRAINT `page_sections_page_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Actualités : brouillon / publié.
ALTER TABLE `actualites_blog`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  ADD COLUMN `est_publie` tinyint(1) NOT NULL DEFAULT '0' AFTER `est_en_avant`;

-- 4. Newsletter : double confirmation, désinscription en un clic. Les inscrits actuels sont « à reconfirmer ».
ALTER TABLE `newsletter`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  ADD COLUMN `statut` enum('a_reconfirmer','en_attente','confirme','desinscrit') COLLATE utf8mb4_unicode_ci
    NOT NULL DEFAULT 'en_attente',
  ADD COLUMN `jeton` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `langue` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `consent_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `confirme_at` datetime DEFAULT NULL,
  ADD COLUMN `desinscrit_at` datetime DEFAULT NULL,
  ADD UNIQUE KEY `newsletter_email_unique` (`email`),
  ADD UNIQUE KEY `newsletter_jeton_unique` (`jeton`);
UPDATE `newsletter` SET `statut` = 'a_reconfirmer', `jeton` = SHA2(CONCAT(UUID(), RAND()), 256) WHERE `jeton` IS NULL;

-- 5. Messages de contact : réponse de l'admin ; sujet, lieu et téléphone facultatifs (téléphone : 12 → 20 caractères).
ALTER TABLE `contact_us`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  MODIFY `Subject` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  MODIFY `PhoneNumber` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  MODIFY `Location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `reponse` text COLLATE utf8mb4_unicode_ci,
  ADD COLUMN `repondu_at` datetime DEFAULT NULL,
  ADD COLUMN `repondu_par` int unsigned DEFAULT NULL;

-- 6. Visites : identifiant de visiteur haché, plus d'adresse IP ni de coordonnées GPS.
ALTER TABLE `visitors_logs`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  ADD COLUMN `visitor_hash` char(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `id`,
  ADD KEY `visitors_logs_date_index` (`visit_date`),
  ADD KEY `visitors_logs_page_index` (`page`(100));
UPDATE `visitors_logs` SET `visitor_hash` = LEFT(SHA2(CONCAT(COALESCE(`ip_address`, ''), '|', COALESCE(`user_agent`, '')), 256), 32)
  WHERE `visitor_hash` IS NULL;
UPDATE `visitors_logs` SET `ip_address` = NULL, `user_agent` = NULL, `latitude` = NULL, `longitude` = NULL;

-- 7. Secrets : les mots de passe SMTP n'ont rien à faire dans une table lisible par l'administration.
DELETE FROM `configurations` WHERE `cle` IN ('smtp_pass', 'smtp_password');
