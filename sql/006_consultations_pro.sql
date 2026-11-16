-- Consultations : fonctions des plateformes professionnelles.

-- 1. Durée de consultation propre à chaque médecin (15 à 120 min).
ALTER TABLE `medecins`
  ADD COLUMN `duree_consultation_minutes` smallint unsigned NOT NULL DEFAULT 30 AFTER `est_disponible`;

-- 2. Absences / congés : aucun créneau n'est proposé pendant ces périodes.
CREATE TABLE IF NOT EXISTS `medecin_absences` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `medecin_id` int unsigned NOT NULL,
  `debut` datetime NOT NULL,
  `fin` datetime NOT NULL,
  `motif` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `medecin_absences_medecin_periode_index` (`medecin_id`, `debut`, `fin`),
  CONSTRAINT `medecin_absences_medecin_id_foreign` FOREIGN KEY (`medecin_id`) REFERENCES `medecins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Statut « absent » (le patient ne s'est pas présenté) et rappel de la veille.
ALTER TABLE `consultations`
  MODIFY `statut` enum('en_attente','confirmee','en_cours','terminee','annulee','refusee','absent')
    COLLATE utf8mb4_unicode_ci DEFAULT 'en_attente',
  ADD COLUMN `rappel_veille_at` datetime DEFAULT NULL AFTER `rappel_envoye_at`,
  ADD KEY `consultations_medecin_date_index` (`medecin_id`, `date_souhaitee`);
