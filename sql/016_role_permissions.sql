-- Rôles dynamiques : chaque rôle peut recevoir une liste de permissions (JSON), par module et par action.
-- NULL = aucun droit propre. Le rôle admin garde toujours tous les droits (calculés par le code).
ALTER TABLE `roles` ADD COLUMN `permissions` TEXT NULL DEFAULT NULL AFTER `niveau`;
