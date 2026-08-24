INSERT INTO `configurations` (`cle`, `valeur`, `type`, `categorie`, `description`) VALUES
('chatbot_api_key', '', 'texte', 'chatbot', 'Cle API OpenAI pour le chatbot'),
('chatbot_chat_model', 'gpt-4o-mini', 'texte', 'chatbot', 'Modele de chat OpenAI'),
('chatbot_embedding_model', 'text-embedding-3-small', 'texte', 'chatbot', 'Modele d''embedding pour le RAG'),
('chatbot_site_name', 'Assistant', 'texte', 'chatbot', 'Nom affiche dans le widget chat'),
('chatbot_greeting', 'Bonjour ! Comment puis-je vous aider ?', 'texte', 'chatbot', 'Message d''accueil du bot'),
('chatbot_handoff_email', 'support@nufotec.bi', 'texte', 'chatbot', 'Email de notification pour transfert humain'),
('chatbot_kb_top_n', '3', 'nombre', 'chatbot', 'Nombre d''articles BC injectes dans le contexte IA'),
('chatbot_kb_min_score', '0.72', 'nombre', 'chatbot', 'Score minimum de similarite cosinus (0-1)'),
('chatbot_escalate_score', '0.55', 'nombre', 'chatbot', 'Seuil d''escalade automatique vers agent humain');
