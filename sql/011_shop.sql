-- Produits et commandes (sans panier : la commande ouvre WhatsApp vers le service des ventes).

-- 1. Encodage : latin1 → utf8mb4 (les accents sont stockés en vrai latin1 : conversion sans perte).
ALTER TABLE `advertise_product` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `order_requests` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `product_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Produits : prix en chiffres, stock facultatif, produit de santé (avertissement), position.
ALTER TABLE `advertise_product`
  ADD COLUMN `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `id`,
  ADD COLUMN `prix_usd` decimal(10,2) DEFAULT NULL AFTER `price`,
  ADD COLUMN `prix_bif` decimal(12,2) DEFAULT NULL AFTER `prix_usd`,
  ADD COLUMN `stock_quantite` int DEFAULT NULL COMMENT 'NULL = stock non suivi' AFTER `prix_bif`,
  ADD COLUMN `est_produit_sante` tinyint(1) NOT NULL DEFAULT '0' AFTER `stock_quantite`,
  ADD UNIQUE KEY `advertise_product_uuid_unique` (`uuid`);

-- Reprise des prix texte : « 7$ = 21000fbu » → 7 USD et 21 000 BIF.
UPDATE `advertise_product` SET `uuid` = UUID() WHERE `uuid` IS NULL;
UPDATE `advertise_product` SET
  `prix_usd` = NULLIF(CAST(REGEXP_REPLACE(REGEXP_SUBSTR(`price`, '[0-9]+([.,][0-9]+)?[ ]*[$]'), '[^0-9.]', '') AS DECIMAL(10,2)), 0),
  `prix_bif` = NULLIF(CAST(REGEXP_REPLACE(REGEXP_SUBSTR(LOWER(`price`), '[0-9][0-9 .]*[ ]*(fbu|bif)'), '[^0-9]', '') AS DECIMAL(12,2)), 0)
WHERE `prix_usd` IS NULL AND `prix_bif` IS NULL;
-- Les produits de la catégorie « PRODUIT MEDICALE » portent l'avertissement santé.
UPDATE `advertise_product` SET `est_produit_sante` = 1 WHERE `category_id` = 1;

-- 3. Galerie d'images (l'image principale reste dans advertise_product.main_image).
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_index` (`product_id`, `position`),
  CONSTRAINT `product_images_product_foreign` FOREIGN KEY (`product_id`) REFERENCES `advertise_product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Catégories unifiées : product_categories devient l'arbre unique ; les 7 gammes (A–G) de `categories`
--    y sont copiées comme catégories principales. La table `categories` reste intacte pour le PHP.
ALTER TABLE `product_categories`
  ADD COLUMN `parent_id` int DEFAULT NULL,
  ADD COLUMN `code` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `slug` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `description` text COLLATE utf8mb4_unicode_ci,
  ADD COLUMN `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `position` smallint unsigned NOT NULL DEFAULT '0',
  ADD COLUMN `is_active` tinyint(1) NOT NULL DEFAULT '1',
  ADD COLUMN `legacy_categorie_id` int DEFAULT NULL COMMENT 'id dans la table categories (gammes)',
  ADD UNIQUE KEY `product_categories_slug_unique` (`slug`),
  ADD CONSTRAINT `product_categories_parent_foreign` FOREIGN KEY (`parent_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL;

UPDATE `product_categories` SET `slug` = 'produits-medicaux', `position` = 10 WHERE `id` = 1 AND `slug` IS NULL;
UPDATE `product_categories` SET `slug` = 'produits-des-plantes', `position` = 20 WHERE `id` = 2 AND `slug` IS NULL;
INSERT INTO `product_categories` (`name`, `code`, `slug`, `description`, `image`, `position`, `is_active`, `legacy_categorie_id`)
SELECT TRIM(c.`nom_categorie`), c.`code_categorie`,
       LOWER(CONCAT('gamme-', c.`code_categorie`, '-', TRIM(BOTH '-' FROM REGEXP_REPLACE(LOWER(TRIM(c.`nom_categorie`)), '[^a-z0-9]+', '-')))),
       COALESCE(c.`description_courte`, c.`description_longue`), c.`image_url`, 100 + c.`id_categorie`, c.`is_active`, c.`id_categorie`
FROM `categories` c
WHERE NOT EXISTS (SELECT 1 FROM `product_categories` p WHERE p.`legacy_categorie_id` = c.`id_categorie`);

-- 5. Commandes : l'historique survit à la suppression d'un produit, prix figé, suivi.
ALTER TABLE `order_requests` DROP FOREIGN KEY `fk_order_product`;
ALTER TABLE `order_requests`
  MODIFY `product_id` int DEFAULT NULL,
  ADD COLUMN `numero` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `id`,
  ADD COLUMN `user_id` int unsigned DEFAULT NULL AFTER `product_id`,
  ADD COLUMN `customer_email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `customer_phone`,
  ADD COLUMN `quantite` int unsigned NOT NULL DEFAULT '1' AFTER `product_price`,
  ADD COLUMN `prix_unitaire` decimal(12,2) DEFAULT NULL AFTER `quantite`,
  ADD COLUMN `devise` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `prix_unitaire`,
  ADD COLUMN `total` decimal(14,2) DEFAULT NULL AFTER `devise`,
  ADD UNIQUE KEY `order_requests_numero_unique` (`numero`),
  ADD KEY `order_requests_status_index` (`order_status`),
  ADD CONSTRAINT `fk_order_product` FOREIGN KEY (`product_id`) REFERENCES `advertise_product` (`id`) ON DELETE SET NULL;

UPDATE `order_requests` SET `numero` = CONCAT('CMD-', DATE_FORMAT(`created_at`, '%Y%m%d'), '-', LPAD(`id`, 6, '0'))
WHERE `numero` IS NULL;

CREATE TABLE IF NOT EXISTS `order_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `from_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `author_id` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_history_order_index` (`order_id`),
  CONSTRAINT `order_history_order_foreign` FOREIGN KEY (`order_id`) REFERENCES `order_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
