# Chatbot Module - CodeIgniter 3 HMVC

Module de chatbot RAG (Retrieval-Augmented Generation) avec transfert vers agent humain.
Concu pour etre reutilisable dans tout projet CodeIgniter 3 + HMVC (Wiredesignz MX).

## Fonctionnalites

- Widget chat frontend (bulle + fenetre)
- Base de connaissances geree via back-office admin
- Recherche RAG avec embeddings OpenAI (cosine similarity)
- Escalade automatique vers agent humain
- Interface admin pour gerer les conversations
- Notifications email lors des demandes de transfert

## Installation

### 1. Copier le module

Copiez le dossier `chatbot/` dans `application/modules/` de votre projet.

### 2. Installer la base de donnees

Executez le fichier `sql/install.sql` pour creer les 4 tables :
- `chatbot_conversations`
- `chatbot_messages`
- `chatbot_kb`
- `chatbot_handoff_requests`

### 3. Configurer

Copiez `config/chatbot.php` dans `application/config/chatbot.php` et definissez :
- `chatbot_openai_api_key` : Votre cle API OpenAI
- `chatbot_site_name` : Nom affiche dans le widget
- `chatbot_handoff_notify_email` : Email de notification

### 4. Ajouter les routes

Ajoutez dans `application/config/routes.php` :

```php
// Chatbot Module
$route['chatbot']                          = 'chatbot/Chatbot/index';
$route['chatbot/start']                    = 'chatbot/Chatbot/start';
$route['chatbot/send']                     = 'chatbot/Chatbot/send';
$route['chatbot/request_human']            = 'chatbot/Chatbot/request_human';
$route['chatbot/history/(:num)']           = 'chatbot/Chatbot/history/$1';
$route['admin/chatbot/kb']                 = 'chatbot/admin/Kb/index';
$route['admin/chatbot/kb/create']          = 'chatbot/admin/Kb/create';
$route['admin/chatbot/kb/edit/(:num)']     = 'chatbot/admin/Kb/edit/$1';
$route['admin/chatbot/kb/delete/(:num)']   = 'chatbot/admin/Kb/delete/$1';
$route['admin/chatbot/kb/regenerate_all_embeddings'] = 'chatbot/admin/Kb/regenerate_all_embeddings';
$route['admin/chatbot/conversations']             = 'chatbot/admin/Conversations/index';
$route['admin/chatbot/conversations/view/(:num)'] = 'chatbot/admin/Conversations/view/$1';
$route['admin/chatbot/conversations/assign/(:num)'] = 'chatbot/admin/Conversations/assign/$1';
$route['admin/chatbot/conversations/reply/(:num)']  = 'chatbot/admin/Conversations/reply/$1';
$route['admin/chatbot/conversations/close/(:num)']  = 'chatbot/admin/Conversations/close/$1';
```

### 5. Inclure le widget

Dans votre footer frontend (`application/views/includes/frontend/Footer.php`), ajoutez avant `</body>` :

```php
<?php $this->load->view('chatbot/chatbot/Widget_View'); ?>
```

### 6. Ajouter le menu admin (optionnel)

Dans `application/views/includes/backend/Sidebar.php`, ajoutez :

```php
<?php if (admin_view()): ?>
<li class="nav-header">Chatbot</li>
<?php $chatbot_active = in_array($this->uri->segment(2), ['kb', 'conversations']); ?>
<li class="nav-item <?= $chatbot_active ? 'menu-open' : '' ?>">
    <a href="#" class="nav-link <?= $chatbot_active ? 'active' : '' ?>">
        <i class="nav-icon bi bi-robot"></i>
        <p>Chatbot <i class="nav-arrow bi bi-chevron-right"></i></p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item"><a href="<?= base_url('admin/chatbot/kb') ?>" class="nav-link <?= $this->uri->segment(2) == 'kb' ? 'active' : '' ?>"><i class="nav-icon bi bi-book"></i><p>Base de connaissances</p></a></li>
        <li class="nav-item"><a href="<?= base_url('admin/chatbot/conversations') ?>" class="nav-link <?= $this->uri->segment(2) == 'conversations' ? 'active' : '' ?>"><i class="nav-icon bi bi-chat-dots"></i><p>Conversations</p></a></li>
    </ul>
</li>
<?php endif; ?>
```

## Structure du module

```
chatbot/
├── config/
│   └── chatbot.php                    # Configuration
├── controllers/
│   ├── Chatbot.php                    # API publique (widget)
│   └── admin/
│       ├── Kb.php                     # Admin: Base de connaissances
│       └── Conversations.php          # Admin: Conversations
├── libraries/
│   └── Ai_service.php                 # Logique IA (embeddings, RAG, chat)
├── models/
│   ├── Chatbot_model.php             # Conversations, messages, handoff
│   └── Kb_model.php                  # Articles base de connaissances
├── sql/
│   └── install.sql                    # Schema de base de donnees
└── views/
    ├── chatbot/
    │   └── Widget_View.php           # Widget frontend
    └── admin/
        └── chatbot/
            ├── kb_list.php            # Liste articles BC
            ├── kb_form.php            # Formulaire article BC
            ├── conversations_list.php # Liste conversations
            └── conversations_view.php # Detail conversation
```

## Dependances

- CodeIgniter 3.1.x
- Wiredesignz MX (HMVC)
- PHP 7.4+ avec curl
- MySQL 5.7+ ou MariaDB 10.3+
- Cle API OpenAI (pour les embeddings et le chat)

## Personnalisation

- Modifiez `chatbot_site_name` dans la config pour changer le nom affiche
- Modifiez `chatbot_greeting` pour le message d'accueil
- Modifiez `chatbot_system_prompt` pour adapter le comportement de l'IA
- Modifiez `chatbot_escalation_keywords` pour les mots-cles de transfert
