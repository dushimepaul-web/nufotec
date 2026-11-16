-- La table `configurations` s'appelle désormais `parametres`.
-- Une vue `configurations` est créée pour que le site PHP continue de fonctionner sans modification
-- (vue simple sur une seule table : MySQL y autorise lecture, ajout, modification et suppression).
RENAME TABLE `configurations` TO `parametres`;
CREATE OR REPLACE VIEW `configurations` AS SELECT * FROM `parametres`;
