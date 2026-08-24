<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Chatbot Module Configuration
|--------------------------------------------------------------------------
| Copy this file to application/config/chatbot.php and set your values.
| Use environment variables (getenv()) in production.
*/

// AI provider: 'openai' (embeddings + chat)
$config['chatbot_ai_provider']   = 'openai';

// OpenAI API key (set via env var OPENAI_API_KEY or paste here)
$config['chatbot_openai_api_key'] = getenv('OPENAI_API_KEY') ?: 'METTEZ_VOTRE_CLE_API_ICI';

// Models
$config['chatbot_chat_model']       = 'gpt-4o-mini';
$config['chatbot_embedding_model']  = 'text-embedding-3-small';

// Number of KB articles to inject into AI context
$config['chatbot_kb_top_n'] = 3;

// Minimum cosine similarity score (0-1) for a KB article to be considered relevant
$config['chatbot_kb_min_score'] = 0.72;

// If max similarity score is below this threshold, suggest human escalation
$config['chatbot_escalate_score_threshold'] = 0.55;

// Keywords that trigger explicit human transfer request
$config['chatbot_escalation_keywords'] = array(
    'agent', 'humain', 'conseiller', 'parler a quelqu\'un',
    'service client', 'assistance humaine', 'appeler', 'appelez-moi'
);

// System prompt sent to the chat model
$config['chatbot_system_prompt'] =
"Tu es l'assistant virtuel officiel. ".
"Reponds uniquement en te basant sur le CONTEXTE fourni ci-dessous, ".
"issu de la base de connaissances. ".
"Si le contexte ne contient pas la reponse, dis clairement que tu ne sais pas ".
"et propose de transferrer la conversation a un agent humain. ".
"Reponds de facon claire, concise et polie, en francais, sauf si l'utilisateur ecrit dans une autre langue.";

// Email(s) to notify on human handoff request
$config['chatbot_handoff_notify_email'] = 'support@nufotec.bi';

// Chatbot site name (used in widget header)
$config['chatbot_site_name'] = 'Assistant';

// Chatbot greeting message
$config['chatbot_greeting'] = 'Bonjour ! Comment puis-je vous aider ?';
