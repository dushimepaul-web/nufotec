-- Valeurs vides enregistrées par le PHP dans des listes fermées (MySQL non strict) : '' → NULL.
UPDATE `investors` SET `commitment_range` = NULL WHERE `commitment_range` = '';
UPDATE `investors` SET `timeline` = NULL WHERE `timeline` = '';
UPDATE `brokers` SET `regulatory_status` = NULL WHERE `regulatory_status` = '';
UPDATE `brokers` SET `engagement_model` = NULL WHERE `engagement_model` = '';
