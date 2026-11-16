-- Module Consultations et médecins.
-- Ajouts compatibles avec le site PHP : colonnes nullables / générées et nouvelles tables.

-- 1. Tarifs en chiffres (les colonnes texte « 40.000BIF » restent pour le PHP) et présentation.
ALTER TABLE `medecins`
  ADD COLUMN `prix_usd` decimal(10,2) DEFAULT NULL COMMENT 'Tarif non-résidents (USD)' AFTER `honoraires_consultation`,
  ADD COLUMN `prix_bif` decimal(12,2) DEFAULT NULL COMMENT 'Tarif résidents du Burundi (BIF)' AFTER `prix_usd`,
  ADD COLUMN `bio` text COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `diplomes`;

-- Reprise des tarifs existants : honoraires (USD) et « 40.000BIF » → 40000.
UPDATE `medecins` SET
  `prix_usd` = COALESCE(`prix_usd`, `honoraires_consultation`),
  `prix_bif` = COALESCE(`prix_bif`, NULLIF(CAST(REGEXP_REPLACE(`prix_pour_residant_burundi`, '[^0-9]', '') AS UNSIGNED), 0));

-- 2. Un seul rendez-vous actif par médecin et par créneau (garantie en base, même en cas de clics simultanés),
--    et rappel envoyé une seule fois.
ALTER TABLE `consultations`
  ADD COLUMN `creneau_actif` varchar(40) COLLATE utf8mb4_unicode_ci
    GENERATED ALWAYS AS (IF(`statut` IN ('en_attente','confirmee','en_cours'),
                            CONCAT(`medecin_id`, '|', DATE_FORMAT(`date_souhaitee`, '%Y-%m-%d %H:%i')), NULL)) STORED,
  ADD COLUMN `rappel_envoye_at` datetime DEFAULT NULL,
  ADD UNIQUE KEY `consultations_creneau_actif_unique` (`creneau_actif`);

-- 3. Documents privés (hors du dossier public), servis par liens signés temporaires.
CREATE TABLE IF NOT EXISTS `consultation_documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `consultation_id` int unsigned NOT NULL,
  `uploaded_by` int unsigned DEFAULT NULL,
  `kind` enum('document','ordonnance','preuve_paiement') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'document',
  `stored_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_bytes` int unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `consultation_documents_stored_name_unique` (`stored_name`),
  KEY `consultation_documents_consultation_id_index` (`consultation_id`),
  CONSTRAINT `consultation_documents_consultation_id_foreign` FOREIGN KEY (`consultation_id`)
    REFERENCES `consultations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Historique des changements de statut.
CREATE TABLE IF NOT EXISTS `consultation_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `consultation_id` int unsigned NOT NULL,
  `from_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actor_id` int unsigned DEFAULT NULL,
  `actor_role` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `consultation_history_consultation_id_index` (`consultation_id`),
  CONSTRAINT `consultation_history_consultation_id_foreign` FOREIGN KEY (`consultation_id`)
    REFERENCES `consultations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Avis des patients (un par consultation terminée).
CREATE TABLE IF NOT EXISTS `consultation_avis` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `consultation_id` int unsigned NOT NULL,
  `patient_id` int unsigned NOT NULL,
  `medecin_id` int unsigned NOT NULL,
  `note` tinyint unsigned NOT NULL,
  `commentaire` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `consultation_avis_consultation_id_unique` (`consultation_id`),
  KEY `consultation_avis_medecin_id_index` (`medecin_id`),
  CONSTRAINT `consultation_avis_consultation_id_foreign` FOREIGN KEY (`consultation_id`)
    REFERENCES `consultations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
