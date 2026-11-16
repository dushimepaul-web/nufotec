-- Chatbot RAG : fournisseurs d'IA (clés chiffrées), réglages, sources de connaissance (articles,
-- documents, tables choisies par l'admin), passages indexés, consommation, avis.
-- Les tables existantes chatbot_kb / chatbot_conversations / chatbot_messages / chatbot_handoff_requests sont conservées.

CREATE TABLE IF NOT EXISTS `ai_providers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kind` enum('openai','groq','anthropic','gemini','mistral','ollama','openai_compatible') COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key_encrypted` text COLLATE utf8mb4_unicode_ci COMMENT 'Fernet (clé AI_ENCRYPTION_KEY du .env), jamais en clair',
  `api_key_hint` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ex. gsk_••••4f2a',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_test_at` datetime DEFAULT NULL,
  `last_test_ok` tinyint(1) DEFAULT NULL,
  `last_test_message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ai_providers_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chatbot_settings` (
  `cle` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valeur` text COLLATE utf8mb4_unicode_ci,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`cle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rag_sources` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `kind` enum('kb','document','table') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','indexing','ready','error') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chunks_count` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_indexed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rag_table_rules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `source_id` int unsigned NOT NULL,
  `table_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `columns_json` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'colonnes autorisées',
  `filters_json` text COLLATE utf8mb4_unicode_ci COMMENT '[{column, op, value}]',
  `template` text COLLATE utf8mb4_unicode_ci COMMENT 'ex. Produit {nom} : {description}',
  `title_column` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `schedule` enum('manual','nightly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'nightly',
  `max_rows` int unsigned NOT NULL DEFAULT '5000',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rag_table_rules_table_unique` (`table_name`),
  CONSTRAINT `rag_table_rules_source_foreign` FOREIGN KEY (`source_id`) REFERENCES `rag_sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rag_documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `source_id` int unsigned NOT NULL,
  `stored_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_bytes` int unsigned NOT NULL,
  `text_chars` int unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `rag_documents_source_foreign` FOREIGN KEY (`source_id`) REFERENCES `rag_sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rag_chunks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `source_id` int unsigned NOT NULL,
  `ref_key` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'article, ligne ou page d''origine',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `embedding` mediumblob COMMENT 'vecteur float32 normalisé',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rag_chunks_source_index` (`source_id`, `ref_key`),
  FULLTEXT KEY `rag_chunks_fulltext` (`title`, `content`),
  CONSTRAINT `rag_chunks_source_foreign` FOREIGN KEY (`source_id`) REFERENCES `rag_sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_usage` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provider_id` int unsigned DEFAULT NULL,
  `model` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'chat',
  `prompt_tokens` int unsigned NOT NULL DEFAULT '0',
  `completion_tokens` int unsigned NOT NULL DEFAULT '0',
  `ok` tinyint(1) NOT NULL DEFAULT '1',
  `conversation_id` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_usage_created_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chatbot_feedback` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int unsigned NOT NULL,
  `rating` tinyint NOT NULL COMMENT '1 = utile, -1 = pas utile',
  `comment` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chatbot_feedback_message_unique` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conversations : lien vers le compte (si connecté), jeton d'accès du visiteur haché, langue.
ALTER TABLE `chatbot_conversations`
  ADD COLUMN `user_id` int unsigned DEFAULT NULL AFTER `user_email`,
  ADD COLUMN `token_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `session_id`,
  ADD COLUMN `lang` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `status`,
  ADD COLUMN `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD UNIQUE KEY `chatbot_conversations_token_unique` (`token_hash`),
  ADD KEY `chatbot_conversations_user_index` (`user_id`);
