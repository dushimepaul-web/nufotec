<?php defined('BASEPATH') OR exit('No direct script access allowed'); 

// Définir les variables par défaut si elles n'existent pas
if (!isset($resultat)) {
    $resultat = null;
}
if (!isset($message)) {
    $message = '';
}
if (!isset($type_envoi)) {
    $type_envoi = 'texte';
}
if (!isset($groupes_info)) {
    $groupes_info = array();
}
if (!isset($total_groupes)) {
    $total_groupes = 0;
}
if (!isset($groupes)) {
    $groupes = array();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #dddbd1;
            background-image: linear-gradient(180deg, #dddbd1, #d2dbdc);
            height: 100vh;
            overflow: hidden;
        }
        
        /* Container principal comme WhatsApp Web */
        .app-container {
            display: flex;
            height: 100vh;
            max-width: 1600px;
            margin: 0 auto;
            background: #f0f2f5;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,0.06), 0 2px 5px 0 rgba(0,0,0,0.2);
        }
        
        /* Sidebar gauche (liste des conversations) */
        .sidebar {
            width: 30%;
            min-width: 300px;
            max-width: 420px;
            background: #fff;
            border-right: 1px solid #e9edef;
            display: flex;
            flex-direction: column;
        }
        
        /* Header de la sidebar */
        .sidebar-header {
            background: #f0f2f5;
            padding: 10px 16px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e9edef;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #dfe5e7;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #54656f;
            font-size: 20px;
            cursor: pointer;
        }
        
        .header-icons {
            display: flex;
            gap: 8px;
        }
        
        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: transparent;
            color: #54656f;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .icon-btn:hover {
            background: #d1d7db;
        }
        
        /* Barre de recherche */
        .search-container {
            background: #f0f2f5;
            padding: 8px 16px;
            border-bottom: 1px solid #e9edef;
        }
        
        .search-box {
            background: #fff;
            border-radius: 8px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: none;
        }
        
        .search-box i {
            color: #54656f;
            font-size: 16px;
        }
        
        .search-box input {
            border: none;
            outline: none;
            flex: 1;
            font-size: 14px;
            color: #3b4a54;
        }
        
        /* Liste des conversations */
        .conversations-list {
            flex: 1;
            overflow-y: auto;
            background: #fff;
        }
        
        .conversation-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f0f2f5;
            transition: background 0.2s;
        }
        
        .conversation-item:hover {
            background: #f5f6f6;
        }
        
        .conversation-item.selected {
            background: #f0f2f5;
        }
        
        .conversation-avatar {
            width: 49px;
            height: 49px;
            border-radius: 50%;
            background: #dfe5e7;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 24px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .conversation-avatar.group {
            background: linear-gradient(135deg, #00bfa5, #00897b);
        }
        
        .conversation-info {
            flex: 1;
            min-width: 0;
        }
        
        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }
        
        .conversation-name {
            font-size: 16px;
            font-weight: 400;
            color: #111b21;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .conversation-time {
            font-size: 12px;
            color: #667781;
        }
        
        .conversation-preview {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .conversation-message {
            font-size: 14px;
            color: #667781;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .checkbox-select {
            width: 18px;
            height: 18px;
            margin-right: 12px;
            accent-color: #00a884;
        }
        
        /* Zone de chat (droite) */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #efeae2;
            background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH5QUbCTk4U3d0fQAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAAJklEQVQ4y2NgYGD4z0ABYBw1gGE0DqNhNIyG0TAaRsNoGA2jYQgAASYgCw+4S6UAAAAASUVORK5CYII=');
            background-repeat: repeat;
        }
        
        /* Header du chat */
        .chat-header {
            background: #f0f2f5;
            padding: 10px 16px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 1px solid #e9edef;
        }
        
        .chat-header-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .chat-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00bfa5, #00897b);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
        }
        
        .chat-title {
            display: flex;
            flex-direction: column;
        }
        
        .chat-name {
            font-size: 16px;
            font-weight: 500;
            color: #111b21;
        }
        
        .chat-status {
            font-size: 13px;
            color: #667781;
        }
        
        /* Messages area */
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .message-bubble {
            max-width: 65%;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 12px;
            position: relative;
            word-wrap: break-word;
        }
        
        .message-bubble.sent {
            background: #d9fdd3;
            align-self: flex-end;
            border-top-right-radius: 0;
        }
        
        .message-bubble.received {
            background: #fff;
            align-self: flex-start;
            border-top-left-radius: 0;
            box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
        }
        
        .message-text {
            font-size: 14.2px;
            line-height: 19px;
            color: #111b21;
        }
        
        .message-time {
            font-size: 11px;
            color: #667781;
            text-align: right;
            margin-top: 4px;
        }
        
        /* Input area */
        .input-container {
            background: #f0f2f5;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 1px solid #e9edef;
        }
        
        .input-icons {
            display: flex;
            gap: 8px;
        }
        
        .input-icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: transparent;
            color: #54656f;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .input-icon-btn:hover {
            color: #00a884;
        }
        
        .message-input-wrapper {
            flex: 1;
            background: #fff;
            border-radius: 8px;
            padding: 9px 12px;
            display: flex;
            align-items: center;
        }
        
        .message-input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 15px;
            color: #111b21;
            resize: none;
            max-height: 100px;
            min-height: 20px;
        }
        
        .send-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: #00a884;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .send-btn:hover {
            background: #008f72;
        }
        
        .send-btn:disabled {
            background: #8696a0;
            cursor: not-allowed;
        }
        
        /* File preview */
        .file-preview-container {
            background: #f0f2f5;
            padding: 10px 16px;
            border-left: 1px solid #e9edef;
            display: none;
        }
        
        .file-preview-container.active {
            display: block;
        }
        
        .file-preview-box {
            background: #fff;
            border-radius: 8px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .file-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: #e7fce3;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #00a884;
            font-size: 24px;
        }
        
        .file-info {
            flex: 1;
        }
        
        .file-name {
            font-size: 14px;
            color: #111b21;
            font-weight: 500;
        }
        
        .file-size {
            font-size: 12px;
            color: #667781;
        }
        
        .file-remove {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background: transparent;
            color: #8696a0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .file-remove:hover {
            background: #f0f2f5;
            color: #ea0038;
        }
        
        /* Overlay compression */
        .compression-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.85);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .compression-box {
            background: #fff;
            padding: 32px;
            border-radius: 16px;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        
        .compression-spinner {
            width: 64px;
            height: 64px;
            border: 4px solid #f0f2f5;
            border-top: 4px solid #00a884;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .compression-title {
            font-size: 20px;
            font-weight: 500;
            color: #111b21;
            margin-bottom: 8px;
        }
        
        .compression-text {
            font-size: 14px;
            color: #667781;
            margin-bottom: 20px;
        }
        
        .compression-progress {
            height: 6px;
            background: #e9edef;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        
        .compression-progress-bar {
            height: 100%;
            background: #00a884;
            width: 0%;
            transition: width 0.3s;
        }
        
        .compression-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .btn-compress {
            background: #00a884;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 24px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .btn-compress:hover {
            background: #008f72;
        }
        
        .btn-cancel {
            background: transparent;
            color: #00a884;
            border: 1px solid #00a884;
            padding: 12px 24px;
            border-radius: 24px;
            font-size: 14px;
            cursor: pointer;
        }
        
        /* Empty state */
        .empty-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f0f2f5;
            border-left: 1px solid #e9edef;
        }
        
        .empty-icon {
            width: 320px;
            height: 200px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><rect fill="%23f0f2f5" width="200" height="200"/><circle cx="100" cy="80" r="40" fill="%23e9edef"/><path d="M70 120 Q100 150 130 120" stroke="%23d1d7db" stroke-width="3" fill="none"/></svg>');
            margin-bottom: 40px;
        }
        
        .empty-title {
            font-size: 32px;
            font-weight: 300;
            color: #41525d;
            margin-bottom: 16px;
        }
        
        .empty-text {
            font-size: 14px;
            color: #667781;
            text-align: center;
            max-width: 450px;
            line-height: 20px;
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Type selector */
        .type-selector {
            display: flex;
            gap: 8px;
            padding: 8px 16px;
            background: #f0f2f5;
            border-left: 1px solid #e9edef;
        }
        
        .type-btn {
            padding: 8px 16px;
            border-radius: 18px;
            border: none;
            background: #fff;
            color: #54656f;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .type-btn.active {
            background: #d9fdd3;
            color: #008f72;
        }
        
        .type-btn:hover:not(.active) {
            background: #f5f6f6;
        }
        
        /* Selection counter badge */
        .selection-badge {
            background: #00a884;
            color: #fff;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* Alertes */
        .alert-compact {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<!-- Overlay de compression -->
<div class="compression-overlay" id="compressionOverlay">
    <div class="compression-box">
        <div class="compression-spinner"></div>
        <div class="compression-title" id="compressionTitle">Fichier trop volumineux</div>
        <div class="compression-text" id="compressionText">Ce fichier dépasse 16 MB</div>
        <div class="compression-progress">
            <div class="compression-progress-bar" id="compressionProgress"></div>
        </div>
        <div class="compression-actions" id="compressionActions">
            <button class="btn-compress" onclick="compressAndSend()">
                <i class="bi bi-magic me-2"></i>Compresser
            </button>
            <button class="btn-cancel" onclick="sendWithoutCompression()">
                Envoyer quand même
            </button>
        </div>
    </div>
</div>

<!-- Container principal -->
<div class="app-container">
    
    <!-- Sidebar (Liste des groupes) -->
    <div class="sidebar">
        <!-- Header -->
        <div class="sidebar-header">
            <div class="user-avatar">
                <i class="bi bi-person"></i>
            </div>
            <div class="header-icons">
                <button class="icon-btn" title="Nouveau groupe">
                    <i class="bi bi-people"></i>
                </button>
                <button class="icon-btn" title="Menu">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
            </div>
        </div>
        
        <!-- Search -->
        <div class="search-container">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchGroupes" placeholder="Rechercher ou démarrer une nouvelle discussion" onkeyup="filterGroupes()">
            </div>
        </div>
        
        <!-- Liste des conversations -->
        <div class="conversations-list" id="conversationsList">
            <?php 
            $selectedCount = 0;
            foreach ($groupes as $index => $groupe): 
                $isSelected = isset($selected_groupes) && in_array($groupe['groupe_id'], $selected_groupes);
                if ($isSelected) $selectedCount++;
            ?>
            <div class="conversation-item <?php echo $isSelected ? 'selected' : ''; ?>" onclick="toggleSelection(this, '<?php echo htmlspecialchars($groupe['groupe_id']); ?>')">
                <input type="checkbox" class="checkbox-select" name="groupes_ids[]" value="<?php echo htmlspecialchars($groupe['groupe_id']); ?>" <?php echo $isSelected ? 'checked' : ''; ?> onchange="updateCounter()" onclick="event.stopPropagation()">
                <div class="conversation-avatar group">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="conversation-info">
                    <div class="conversation-header">
                        <span class="conversation-name"><?php echo htmlspecialchars($groupe['nom']); ?></span>
                        <span class="conversation-time"><?php echo date('H:i'); ?></span>
                    </div>
                    <div class="conversation-preview">
                        <i class="bi bi-check2-all" style="color: #53bdeb; font-size: 12px;"></i>
                        <span class="conversation-message">Cliquez pour sélectionner ce groupe</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($groupes)): ?>
            <div style="padding: 40px; text-align: center; color: #667781;">
                <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 16px;"></i>
                <p>Aucun groupe disponible</p>
                <a href="<?php echo site_url('whatsapp/synchroniser'); ?>" style="color: #00a884; text-decoration: none;">
                    Synchroniser les groupes
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Zone de chat -->
    <div class="chat-area">
        <?php if ($resultat !== null): ?>
            <!-- Affichage des résultats -->
            <div class="chat-header">
                <div class="chat-header-info">
                    <div class="chat-avatar">
                        <i class="bi bi-check-all"></i>
                    </div>
                    <div class="chat-title">
                        <span class="chat-name">Résultat de l'envoi</span>
                        <span class="chat-status"><?php echo $reussis; ?>/<?php echo $total; ?> messages envoyés</span>
                    </div>
                </div>
                <div class="header-icons">
                    <a href="<?php echo site_url('whatsapp/envoyer'); ?>" class="icon-btn" title="Nouvel envoi">
                        <i class="bi bi-plus-lg"></i>
                    </a>
                </div>
            </div>
            
            <div class="messages-container">
                <?php 
                $stats = isset($resultat['response']) ? $resultat['response'] : $resultat;
                $total = isset($stats['total']) ? $stats['total'] : 0;
                $reussis = isset($stats['reussis']) ? $stats['reussis'] : 0;
                $echoues = isset($stats['echoues']) ? $stats['echoues'] : 0;
                $details = isset($stats['details']) ? $stats['details'] : array();
                ?>
                
                <div class="message-bubble sent" style="align-self: center; background: #fff; max-width: 80%;">
                    <div style="text-align: center; padding: 20px;">
                        <div style="font-size: 48px; margin-bottom: 16px;">
                            <?php echo ($reussis > 0) ? '✅' : '❌'; ?>
                        </div>
                        <div style="font-size: 24px; font-weight: 500; color: #111b21; margin-bottom: 8px;">
                            <?php echo ($reussis > 0) ? 'Envoi terminé' : 'Échec de l\'envoi'; ?>
                        </div>
                        <div style="color: #667781; margin-bottom: 20px;">
                            <?php echo $reussis; ?> succès, <?php echo $echoues; ?> échecs sur <?php echo $total; ?> groupes
                        </div>
                        
                        <?php if (!empty($details)): ?>
                        <div style="text-align: left; max-height: 300px; overflow-y: auto;">
                            <?php foreach ($details as $detail): 
                                $isSuccess = (isset($detail['statut']) && $detail['statut'] === 'succès');
                            ?>
                            <div style="padding: 8px; border-bottom: 1px solid #f0f2f5; display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-<?php echo $isSuccess ? 'check-circle-fill text-success' : 'x-circle-fill text-danger'; ?>"></i>
                                <span style="flex: 1; font-size: 14px;"><?php echo substr($detail['destinataire_id'], 0, 30); ?>...</span>
                                <?php if (!$isSuccess): ?>
                                <small class="text-danger"><?php echo htmlspecialchars($detail['erreur'] ?? 'Erreur'); ?></small>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Formulaire d'envoi -->
            <form action="<?php echo site_url('whatsapp/traiter_envoi'); ?>" method="post" enctype="multipart/form-data" id="envoiForm" style="display: flex; flex-direction: column; height: 100%;">
                
                <!-- Header -->
                <div class="chat-header">
                    <div class="chat-header-info">
                        <div class="chat-avatar">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="chat-title">
                            <span class="chat-name">Nouvelle diffusion</span>
                            <span class="chat-status" id="selectionStatus">Sélectionnez des groupes</span>
                        </div>
                    </div>
                    <div class="header-icons">
                        <button type="button" class="icon-btn" onclick="selectAllGroupes()" title="Tout sélectionner">
                            <i class="bi bi-check-all"></i>
                        </button>
                        <a href="<?php echo site_url('whatsapp'); ?>" class="icon-btn" title="Retour">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Type selector -->
                <div class="type-selector">
                    <button type="button" class="type-btn active" onclick="setType('texte', this)" id="btnTexte">
                        <i class="bi bi-chat-text"></i>
                        <span>Message</span>
                    </button>
                    <button type="button" class="type-btn" onclick="setType('fichier', this)" id="btnFichier">
                        <i class="bi bi-paperclip"></i>
                        <span>Fichier</span>
                    </button>
                    <input type="hidden" name="type_envoi" id="typeEnvoi" value="texte">
                </div>
                
                <!-- Messages area -->
                <div class="messages-container" id="messagesContainer">
                    <!-- Preview du message -->
                    <div class="message-bubble sent" id="messagePreview">
                        <div class="message-text">Votre message apparaîtra ici...</div>
                        <div class="message-time"><?php echo date('H:i'); ?></div>
                    </div>
                </div>
                
                <!-- File preview -->
                <div class="file-preview-container" id="filePreviewContainer">
                    <div class="file-preview-box">
                        <div class="file-icon">
                            <i class="bi bi-file-earmark" id="fileIcon"></i>
                        </div>
                        <div class="file-info">
                            <div class="file-name" id="fileName">Aucun fichier</div>
                            <div class="file-size" id="fileSize">-</div>
                        </div>
                        <button type="button" class="file-remove" onclick="clearFile()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Input area -->
                <div class="input-container">
                    <div class="input-icons">
                        <button type="button" class="input-icon-btn" onclick="document.getElementById('fileInput').click()">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <input type="file" id="fileInput" name="fichier" style="display: none;" onchange="handleFileSelect(this)" accept="video/*,audio/*,image/*,.pdf,.doc,.docx">
                    </div>
                    
                    <div class="message-input-wrapper">
                        <textarea class="message-input" id="messageInput" name="message" placeholder="Tapez un message" rows="1" oninput="updatePreview()"></textarea>
                    </div>
                    
                    <button type="submit" class="send-btn" id="sendBtn" disabled>
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
                
            </form>
        <?php endif; ?>
    </div>
    
</div>

<script>
let selectedFile = null;
let compressedFile = null;
let selectedGroupes = [];

function toggleSelection(element, groupeId) {
    const checkbox = element.querySelector('.checkbox-select');
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        element.classList.add('selected');
        if (!selectedGroupes.includes(groupeId)) {
            selectedGroupes.push(groupeId);
        }
    } else {
        element.classList.remove('selected');
        selectedGroupes = selectedGroupes.filter(id => id !== groupeId);
    }
    
    updateCounter();
}

function updateCounter() {
    const checkboxes = document.querySelectorAll('input[name="groupes_ids[]"]:checked');
    const count = checkboxes.length;
    
    const statusEl = document.getElementById('selectionStatus');
    const sendBtn = document.getElementById('sendBtn');
    
    if (statusEl) {
        if (count === 0) {
            statusEl.textContent = 'Sélectionnez des groupes';
        } else if (count === 1) {
            statusEl.textContent = '1 groupe sélectionné';
        } else {
            statusEl.textContent = count + ' groupes sélectionnés';
        }
    }
    
    if (sendBtn) {
        sendBtn.disabled = count === 0;
    }
}

function selectAllGroupes() {
    const checkboxes = document.querySelectorAll('input[name="groupes_ids[]"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
        const item = cb.closest('.conversation-item');
        if (!allChecked) {
            item.classList.add('selected');
        } else {
            item.classList.remove('selected');
        }
    });
    
    updateCounter();
}

function filterGroupes() {
    const term = document.getElementById('searchGroupes').value.toLowerCase();
    const items = document.querySelectorAll('.conversation-item');
    
    items.forEach(item => {
        const name = item.querySelector('.conversation-name').textContent.toLowerCase();
        item.style.display = name.includes(term) ? '' : 'none';
    });
}

function setType(type, btn) {
    document.getElementById('typeEnvoi').value = type;
    
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    const input = document.getElementById('messageInput');
    if (type === 'fichier') {
        input.placeholder = "Ajoutez une légende (optionnel)...";
        document.getElementById('fileInput').click();
    } else {
        input.placeholder = "Tapez un message";
    }
}

function handleFileSelect(input) {
    if (!input.files || !input.files[0]) return;
    
    selectedFile = input.files[0];
    const sizeMB = (selectedFile.size / 1024 / 1024).toFixed(2);
    
    // Afficher preview
    document.getElementById('filePreviewContainer').classList.add('active');
    document.getElementById('fileName').textContent = selectedFile.name;
    document.getElementById('fileSize').textContent = sizeMB + ' MB';
    
    // Icône selon type
    const icon = document.getElementById('fileIcon');
    if (selectedFile.type.startsWith('video/')) icon.className = 'bi bi-camera-video-fill';
    else if (selectedFile.type.startsWith('audio/')) icon.className = 'bi bi-mic-fill';
    else if (selectedFile.type.startsWith('image/')) icon.className = 'bi bi-image-fill';
    else icon.className = 'bi bi-file-earmark';
    
    // Si > 16MB, proposer compression
    if (selectedFile.size > 16 * 1024 * 1024) {
        showCompressionDialog();
    } else {
        compressedFile = selectedFile;
    }
    
    // Changer le type en fichier
    setType('fichier', document.getElementById('btnFichier'));
}

function showCompressionDialog() {
    document.getElementById('compressionOverlay').style.display = 'flex';
    document.getElementById('compressionTitle').textContent = 'Fichier volumineux';
    document.getElementById('compressionText').textContent = 'Ce fichier fait ' + (selectedFile.size / 1024 / 1024).toFixed(2) + ' MB. Compresser avant envoi ?';
    document.getElementById('compressionActions').style.display = 'flex';
}

async function compressAndSend() {
    document.getElementById('compressionActions').style.display = 'none';
    document.getElementById('compressionTitle').textContent = 'Compression...';
    document.getElementById('compressionText').textContent = 'Veuillez patienter';
    
    const progress = document.getElementById('compressionProgress');
    progress.style.width = '30%';
    
    try {
        // Compression image simple via canvas
        if (selectedFile.type.startsWith('image/')) {
            compressedFile = await compressImage(selectedFile);
        } else {
            // Pour vidéo/audio, on ne peut pas compresser sans FFmpeg
            // On propose de réduire la qualité ou d'envoyer tel quel
            progress.style.width = '100%';
            compressedFile = selectedFile;
        }
        
        const originalSize = (selectedFile.size / 1024 / 1024).toFixed(2);
        const newSize = (compressedFile.size / 1024 / 1024).toFixed(2);
        
        document.getElementById('fileSize').innerHTML = 
            '<span style="text-decoration: line-through; color: #8696a0;">' + originalSize + ' MB</span> ' +
            '<span style="color: #00a884; font-weight: 500;">' + newSize + ' MB</span>';
        
        document.getElementById('compressionOverlay').style.display = 'none';
        
    } catch (e) {
        console.error(e);
        compressedFile = selectedFile;
        document.getElementById('compressionOverlay').style.display = 'none';
    }
}

function compressImage(file) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const url = URL.createObjectURL(file);
        
        img.onload = () => {
            URL.revokeObjectURL(url);
            
            let width = img.width;
            let height = img.height;
            const maxDim = 1280;
            
            if (width > maxDim || height > maxDim) {
                if (width > height) {
                    height = (height / width) * maxDim;
                    width = maxDim;
                } else {
                    width = (width / height) * maxDim;
                    height = maxDim;
                }
            }
            
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
            
            canvas.toBlob((blob) => {
                const compressed = new File([blob], 'compressed_' + file.name, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });
                resolve(compressed);
            }, 'image/jpeg', 0.7);
        };
        
        img.onerror = reject;
        img.src = url;
    });
}

function sendWithoutCompression() {
    compressedFile = selectedFile;
    document.getElementById('compressionOverlay').style.display = 'none';
}

function clearFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('filePreviewContainer').classList.remove('active');
    selectedFile = null;
    compressedFile = null;
    setType('texte', document.getElementById('btnTexte'));
}

function updatePreview() {
    const input = document.getElementById('messageInput');
    const preview = document.getElementById('messagePreview');
    const text = input.value.trim();
    
    if (text) {
        preview.querySelector('.message-text').textContent = text;
        preview.style.display = 'block';
    } else {
        preview.querySelector('.message-text').textContent = 'Votre message apparaîtra ici...';
    }
}

// Validation formulaire
document.getElementById('envoiForm')?.addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('input[name="groupes_ids[]"]:checked');
    if (checkboxes.length === 0) {
        e.preventDefault();
        alert('Veuillez sélectionner au moins un groupe');
        return false;
    }
    
    const type = document.getElementById('typeEnvoi').value;
    const message = document.getElementById('messageInput').value.trim();
    const hasFile = document.getElementById('fileInput').files.length > 0;
    
    if (type === 'texte' && !message) {
        e.preventDefault();
        alert('Veuillez saisir un message');
        return false;
    }
    
    if (type === 'fichier' && !hasFile) {
        e.preventDefault();
        alert('Veuillez sélectionner un fichier');
        return false;
    }
    
    document.getElementById('sendBtn').innerHTML = '<i class="bi bi-hourglass-split"></i>';
});
</script>

</body>
</html>