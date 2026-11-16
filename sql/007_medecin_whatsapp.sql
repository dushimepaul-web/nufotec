-- Numéro WhatsApp du médecin : après une demande de consultation, le patient lui envoie le récapitulatif
-- par WhatsApp. Vide → téléphone du compte, puis numéro central (configurations.contact_whatsapp).
ALTER TABLE `medecins`
  ADD COLUMN `whatsapp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `langues_parlees`;
