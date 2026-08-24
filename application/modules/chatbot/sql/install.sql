-- =========================================================
-- Chatbot Module - Database Schema (MySQL)
-- Compatible with CodeIgniter 3 HMVC projects
-- =========================================================

CREATE TABLE IF NOT EXISTS `chatbot_conversations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `session_id` VARCHAR(64) NOT NULL,
  `user_name` VARCHAR(150) DEFAULT NULL,
  `user_email` VARCHAR(150) DEFAULT NULL,
  `status` ENUM('bot','pending_human','human','closed') NOT NULL DEFAULT 'bot',
  `agent_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_session` (`session_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `chatbot_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT UNSIGNED NOT NULL,
  `sender` ENUM('user','bot','agent','system') NOT NULL,
  `message` TEXT NOT NULL,
  `meta` TEXT DEFAULT NULL COMMENT 'JSON: KB sources, score, etc.',
  `created_at` DATETIME NOT NULL,
  KEY `idx_conversation` (`conversation_id`),
  CONSTRAINT `fk_msg_conv` FOREIGN KEY (`conversation_id`)
    REFERENCES `chatbot_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `chatbot_kb` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `category` VARCHAR(120) DEFAULT NULL,
  `embedding` LONGTEXT DEFAULT NULL COMMENT 'JSON array (embedding vector)',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  FULLTEXT KEY `ft_content` (`title`, `content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `chatbot_handoff_requests` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT UNSIGNED NOT NULL,
  `reason` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('open','assigned','closed') NOT NULL DEFAULT 'open',
  `agent_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_handoff_conv` FOREIGN KEY (`conversation_id`)
    REFERENCES `chatbot_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
