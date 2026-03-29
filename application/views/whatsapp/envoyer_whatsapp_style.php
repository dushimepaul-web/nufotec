<?php defined('BASEPATH') OR exit('No direct script access allowed'); 

if (!isset($resultat)) $resultat = null;
if (!isset($message)) $message = '';
if (!isset($type_envoi)) $type_envoi = 'texte';
if (!isset($groupes_info)) $groupes_info = array();
if (!isset($total_groupes)) $total_groupes = 0;
if (!isset($groupes)) $groupes = array();
if (!isset($job_id)) $job_id = null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp - Diffusion Groupes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #dddbd1;
            height: 100vh;
            overflow: hidden;
        }
        
        .app-container {
            display: flex;
            height: 100vh;
            max-width: 1600px;
            margin: 0 auto;
            background: #f0f2f5;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,0.06), 0 2px 5px 0 rgba(0,0,0,0.2);
        }
        
        .sidebar {
            width: 30%;
            min-width: 300px;
            max-width: 420px;
            background: #fff;
            border-right: 1px solid #e9edef;
            display: flex;
            flex-direction: column;
        }
        
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
            background: #00a884;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
        }
        
        .header-icons { display: flex; gap: 8px; }
        
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
        
        .icon-btn:hover { background: #d1d7db; }
        
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
        }
        
        .search-box i { color: #54656f; font-size: 16px; }
        
        .search-box input {
            border: none;
            outline: none;
            flex: 1;
            font-size: 14px;
            color: #3b4a54;
        }
        
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
        
        .conversation-item:hover { background: #f5f6f6; }
        .conversation-item.selected { background: #f0f2f5; }
        
        .conversation-avatar {
            width: 49px;
            height: 49px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00a884, #008f72);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 24px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .conversation-info { flex: 1; min-width: 0; }
        
        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }
        
        .conversation-name {
            font-size: 16px;
            font-weight: 500;
            color: #111b21;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .conversation-time { font-size: 12px; color: #667781; }
        
        .checkbox-select {
            width: 18px;
            height: 18px;
            margin-right: 12px;
            accent-color: #00a884;
            cursor: pointer;
        }
        
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #efeae2;
            background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH5QUbCTk4U3d0fQAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAAJklEQVQ4y2NgYGD4z0ABYBw1gGE0DqNhNIyG0TAaRsNoGA2jYQgAASYgCw+4S6UAAAAASUVORK5CYII=');
            background-repeat: repeat;
            position: relative;
        }
        
        .chat-header {
            background: #f0f2f5;
            padding: 10px 16px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 1px solid #e9edef;
            z-index: 10;
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
            background: linear-gradient(135deg, #00a884, #008f72);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
        }
        
        .chat-title { display: flex; flex-direction: column; }
        .chat-name { font-size: 16px; font-weight: 600; color: #111b21; }
        .chat-status { font-size: 13px; color: #667781; }
        
        /* Upload Overlay */
        .upload-overlay {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.85);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        .upload-overlay.active { display: flex; }
        
        .upload-box {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            min-width: 350px;
            max-width: 90%;
        }
        
        .upload-icon {
            width: 80px;
            height: 80px;
            background: #e7fce3;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #00a884;
            font-size: 40px;
        }
        
        .upload-title {
            font-size: 22px;
            font-weight: 600;
            color: #111b21;
            margin-bottom: 8px;
        }
        
        .upload-text {
            font-size: 14px;
            color: #667781;
            margin-bottom: 20px;
        }
        
        .upload-stats {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #667781;
            margin-bottom: 8px;
        }
        
        .upload-bar {
            height: 6px;
            background: #e9edef;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        
        .upload-fill {
            height: 100%;
            background: #00a884;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .upload-percent {
            font-size: 16px;
            color: #00a884;
            font-weight: 600;
        }
        
        .upload-cancel {
            margin-top: 20px;
            padding: 10px 24px;
            border: none;
            background: #f0f2f5;
            color: #54656f;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .upload-cancel:hover { background: #e9edef; }
        
        /* Messages */
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
        
        .message-text { font-size: 14.2px; line-height: 19px; color: #111b21; }
        .message-time { font-size: 11px; color: #667781; text-align: right; margin-top: 4px; }
        
        /* Input Area */
        .input-area {
            background: #f0f2f5;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 1px solid #e9edef;
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
        
        .input-icon-btn:hover { color: #00a884; }
        
        .input-icon-btn.recording {
            background: #ea0038;
            color: #fff;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
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
        
        .send-btn:hover { background: #008f72; }
        .send-btn:disabled { background: #8696a0; cursor: not-allowed; }
        
        /* Audio Recording Interface - Style WhatsApp */
        .audio-recording {
            display: none;
            align-items: center;
            gap: 12px;
            flex: 1;
            background: #fff;
            border-radius: 8px;
            padding: 8px 16px;
        }
        
        .audio-recording.active { display: flex; }
        
        .recording-wave {
            flex: 1;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
        }
        
        .wave-bar {
            width: 4px;
            background: #ea0038;
            border-radius: 2px;
            animation: wave 0.5s ease-in-out infinite;
        }
        
        @keyframes wave {
            0%, 100% { height: 20%; }
            50% { height: 100%; }
        }
        
        .recording-time {
            font-size: 16px;
            color: #ea0038;
            font-weight: 600;
            min-width: 60px;
            text-align: center;
        }
        
        .recording-status {
            font-size: 13px;
            color: #667781;
            margin-left: 8px;
        }
        
        .recording-cancel {
            color: #ea0038;
            font-size: 20px;
            cursor: pointer;
        }
        
        .recording-send {
            color: #00a884;
            font-size: 24px;
            cursor: pointer;
        }
        
        /* File Preview */
        .file-preview-area {
            background: #f0f2f5;
            padding: 8px 16px;
            border-left: 1px solid #e9edef;
            display: none;
        }
        
        .file-preview-area.active { display: block; }
        
        .file-box {
            background: #fff;
            border-radius: 8px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
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
        
        .file-info { flex: 1; }
        .file-name { font-size: 14px; color: #111b21; font-weight: 500; }
        .file-size { font-size: 12px; color: #667781; }
        
        .file-remove {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background: transparent;
            color: #8696a0;
            cursor: pointer;
        }
        
        .file-remove:hover { background: #f0f2f5; color: #ea0038; }
        
        /* Type Selector */
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
        
        /* Result View */
        .result-container {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            margin: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .result-header {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .result-header.success { background: #d9fdd3; color: #008f72; }
        .result-header.error { background: #ffe4e6; color: #ea0038; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f0f2f5;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #00a884;
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
        
        /* Group Counter Badge */
        .group-counter {
            background: #00a884;
            color: #fff;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* Audio Preview in File Box */
        .audio-preview {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .audio-play-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #00a884;
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .audio-waveform {
            flex: 1;
            height: 30px;
            background: #e9edef;
            border-radius: 4px;
            position: relative;
            overflow: hidden;
        }
        
        .audio-waveform-fill {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            background: #00a884;
            width: 0%;
            transition: width 0.1s;
        }
    </style>
</head>
<body>

<!-- Upload Progress Overlay -->
<div class="upload-overlay" id="uploadOverlay">
    <div class="upload-box">
        <div class="upload-icon" id="uploadIcon">
            <i class="bi bi-cloud-arrow-up"></i>
        </div>
        <div class="upload-title" id="uploadTitle">Envoi en cours...</div>
        <div class="upload-text" id="uploadText">Préparation du fichier</div>
        <div class="upload-stats">
            <span id="uploadChunkInfo">0 / 0 MB</span>
            <span id="uploadSpeed">0 MB/s</span>
        </div>
        <div class="upload-bar">
            <div class="upload-fill" id="uploadProgress"></div>
        </div>
        <div class="upload-percent" id="uploadPercent">0%</div>
        <button class="upload-cancel" onclick="cancelUpload()" id="cancelBtn">Annuler</button>
    </div>
</div>

<div class="app-container">
    
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar"><i class="bi bi-whatsapp"></i></div>
            <div class="header-icons">
                <button type="button" class="icon-btn" onclick="selectAllGroupes()" title="Tout sélectionner">
                    <i class="bi bi-check-all"></i>
                </button>
                <a href="<?php echo site_url('whatsapp/synchroniser'); ?>" class="icon-btn" title="Synchroniser groupes">
                    <i class="bi bi-arrow-repeat"></i>
                </a>
            </div>
        </div>
        
        <div class="search-container">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchGroupes" placeholder="Rechercher un groupe..." onkeyup="filterGroupes()">
            </div>
        </div>
        
        <form id="envoiForm" style="display: flex; flex-direction: column; height: 100%;">
            <div class="conversations-list" id="conversationsList">
                <?php foreach ($groupes as $groupe): ?>
                <div class="conversation-item" onclick="toggleSelection(this, '<?php echo htmlspecialchars($groupe['groupe_id']); ?>')">
                    <input type="checkbox" class="checkbox-select" name="groupes_ids[]" 
                           value="<?php echo htmlspecialchars($groupe['groupe_id']); ?>" 
                           id="cb_<?php echo htmlspecialchars($groupe['groupe_id']); ?>"
                           onchange="updateCounter()" onclick="event.stopPropagation()">
                    <div class="conversation-avatar">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-header">
                            <span class="conversation-name"><?php echo htmlspecialchars($groupe['nom']); ?></span>
                            <span class="conversation-time"><?php echo date('H:i', strtotime($groupe['date_modification'] ?? 'now')); ?></span>
                        </div>
                        <div class="conversation-preview">
                            <span class="conversation-message">Cliquez pour sélectionner ce groupe</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
    
    <!-- Chat Area -->
    <div class="chat-area">
        
        <?php if ($resultat !== null): ?>
            <!-- AFFICHAGE RÉSULTAT -->
            <div class="chat-header">
                <div class="chat-header-info">
                    <div class="chat-avatar"><i class="bi bi-check-all"></i></div>
                    <div class="chat-title">
                        <span class="chat-name">Résultat de la diffusion</span>
                        <span class="chat-status">
                            <?php 
                            $stats = isset($resultat['response']) ? $resultat['response'] : $resultat;
                            echo ($stats['reussis'] ?? 0) . '/' . ($stats['total'] ?? 0) . ' groupes envoyés';
                            ?>
                        </span>
                    </div>
                </div>
                <a href="<?php echo site_url('whatsapp/envoyer'); ?>" class="icon-btn" title="Nouvelle diffusion">
                    <i class="bi bi-plus-lg"></i>
                </a>
            </div>
            
            <div class="messages-container" style="justify-content: center;">
                <div class="result-container" style="max-width: 600px; align-self: center;">
                    <?php 
                    $stats = isset($resultat['response']) ? $resultat['response'] : $resultat;
                    $success = ($stats['reussis'] ?? 0) > 0;
                    ?>
                    <div class="result-header <?php echo $success ? 'success' : 'error'; ?>">
                        <i class="bi bi-<?php echo $success ? 'check-circle-fill' : 'x-circle-fill'; ?>" style="font-size: 48px;"></i>
                        <h4 style="margin-top: 16px;">
                            <?php echo $success ? 'Diffusion terminée !' : 'Échec de la diffusion'; ?>
                        </h4>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
                            <small>Groupes total</small>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" style="color: #00a884;"><?php echo $stats['reussis'] ?? 0; ?></div>
                            <small>Envoyés avec succès</small>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" style="color: #ea0038;"><?php echo $stats['echoues'] ?? 0; ?></div>
                            <small>Échecs</small>
                        </div>
                    </div>
                    
                    <?php if (!empty($stats['details'])): ?>
                    <div style="margin-top: 20px; max-height: 300px; overflow-y: auto;">
                        <h6 style="margin-bottom: 10px; color: #667781;">Détails par groupe:</h6>
                        <?php foreach ($stats['details'] as $detail): ?>
                        <div style="padding: 8px; border-bottom: 1px solid #f0f2f5; font-size: 13px;">
                            <span style="color: <?php echo $detail['statut'] === 'succès' ? '#00a884' : '#ea0038'; ?>">
                                <i class="bi bi-<?php echo $detail['statut'] === 'succès' ? 'check-circle' : 'x-circle'; ?>"></i>
                            </span>
                            <?php echo htmlspecialchars(substr($detail['destinataire_id'], 0, 20)) . '...'; ?>
                            <?php if (!empty($detail['erreur'])): ?>
                                <div style="color: #ea0038; font-size: 11px; margin-left: 20px;"><?php echo htmlspecialchars($detail['erreur']); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php else: ?>
            <!-- INTERFACE D'ENVOI -->
            <div class="chat-header">
                <div class="chat-header-info">
                    <div class="chat-avatar"><i class="bi bi-people-fill"></i></div>
                    <div class="chat-title">
                        <span class="chat-name">Nouvelle diffusion WhatsApp</span>
                        <span class="chat-status" id="selectionStatus">Aucun groupe sélectionné</span>
                    </div>
                </div>
                <div class="header-icons">
                    <a href="<?php echo site_url('whatsapp'); ?>" class="icon-btn" title="Retour">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                </div>
            </div>
            
            <!-- Type Selector -->
            <div class="type-selector">
                <button type="button" class="type-btn active" onclick="setType('texte', this)" id="btnTexte">
                    <i class="bi bi-chat-text"></i> <span>Message texte</span>
                </button>
                <button type="button" class="type-btn" onclick="setType('video', this)" id="btnVideo">
                    <i class="bi bi-camera-video"></i> <span>Vidéo</span>
                </button>
                <button type="button" class="type-btn" onclick="setType('image', this)" id="btnImage">
                    <i class="bi bi-image"></i> <span>Image</span>
                </button>
                <button type="button" class="type-btn" onclick="setType('audio', this)" id="btnAudio">
                    <i class="bi bi-mic"></i> <span>Audio</span>
                </button>
                <button type="button" class="type-btn" onclick="setType('document', this)" id="btnDocument">
                    <i class="bi bi-file-earmark"></i> <span>Document</span>
                </button>
            </div>
            
            <!-- File Preview (pour fichiers sélectionnés) -->
            <div class="file-preview-area" id="filePreview">
                <div class="file-box" id="fileBox">
                    <div class="file-icon" id="fileIconBox">
                        <i class="bi bi-file-earmark" id="fileIcon"></i>
                    </div>
                    <div class="file-info">
                        <div class="file-name" id="fileName">-</div>
                        <div class="file-size" id="fileSize">-</div>
                    </div>
                    <button type="button" class="file-remove" onclick="clearFile()" title="Supprimer">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            
            <!-- Messages Preview -->
            <div class="messages-container" id="messagesContainer">
                <div class="message-bubble sent" id="previewBubble" style="display: none;">
                    <div class="message-text" id="previewText"></div>
                    <div class="message-time"><?php echo date('H:i'); ?></div>
                </div>
                <div style="align-self: center; color: #667781; margin-top: 40px; text-align: center;" id="emptyState">
                    <i class="bi bi-whatsapp" style="font-size: 64px; opacity: 0.3;"></i>
                    <p style="margin-top: 16px;">Sélectionnez des groupes et choisissez un contenu<br> pour commencer la diffusion</p>
                </div>
            </div>
            
            <!-- Input Area - Mode Normal -->
            <div class="input-area" id="normalInputArea">
                <div id="normalInput" style="display: flex; align-items: center; gap: 10px; flex: 1;">
                    <button type="button" class="input-icon-btn" onclick="document.getElementById('filePicker').click()" title="Joindre un fichier">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <input type="file" id="filePicker" style="display: none;" 
                           onchange="handleFileSelect(this)" 
                           accept="video/*,image/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                    
                    <div class="message-input-wrapper">
                        <textarea class="message-input" id="messageInput" 
                                  placeholder="Ajouter une légende (optionnel)..." rows="1" 
                                  oninput="updatePreview()"></textarea>
                    </div>
                    
                    <!-- MICRO POUR AUDIO - Style WhatsApp -->
                    <button type="button" class="input-icon-btn" id="micBtn" onclick="startRecording()" title="Enregistrer un message vocal">
                        <i class="bi bi-mic-fill"></i>
                    </button>
                </div>
                
                <button type="button" class="send-btn" id="sendBtn" onclick="submitForm()" title="Envoyer à tous les groupes sélectionnés">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
            
            <!-- Input Area - Mode Enregistrement Audio (caché par défaut) -->
            <div class="input-area" id="recordingInputArea" style="display: none;">
                <div class="audio-recording active" style="display: flex; flex: 1;">
                    <button type="button" class="input-icon-btn recording-cancel" onclick="cancelRecording()" title="Annuler">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                    
                    <div class="recording-wave" id="waveContainer">
                        <!-- Les barres d'onde seront générées par JS -->
                    </div>
                    
                    <div class="recording-time" id="recordingTime">00:00</div>
                    <span class="recording-status">Enregistrement...</span>
                    
                    <button type="button" class="input-icon-btn recording-send" onclick="stopRecordingAndSend()" title="Envoyer">
                        <i class="bi bi-check-lg"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Dans le <head> de envoyer_whatsapp_style.php -->
<script src="https://cdn.jsdelivr.net/npm/lamejs@1.2.1/lame.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/audiobuffer-to-wav@1.0.0/index.js"></script>
<script>
/// ==================== CONFIGURATION GLOBALE ====================
const CHUNK_SIZE = 1.5 * 1024 * 1024; // 1.5 MB pour Whapi
let selectedFile = null;
let currentUploadId = null;
let isUploading = false;
let abortController = null;
let selectedGroupes = [];
let currentJobId = null;
let pollInterval = null;
let currentType = 'texte';

// ==================== VARIABLES AUDIO (UNIQUES) ====================
let audioContext = null;
let audioRecorder = null;
let audioRecordedChunks = [];
let audioStream = null;
let recordingStartTime = null;
let recordingTimer = null;
let isRecording = false;

function log(msg) {
    console.log('[WhatsApp]', msg);
}

// ==================== GESTION DES GROUPES ====================

function toggleSelection(element, groupeId) {
    const checkbox = document.getElementById('cb_' + groupeId);
    if (!checkbox) return;
    
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        element.classList.add('selected');
        if (!selectedGroupes.includes(groupeId)) selectedGroupes.push(groupeId);
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
    
    if (statusEl) {
        if (count === 0) {
            statusEl.innerHTML = 'Aucun groupe sélectionné';
        } else {
            statusEl.innerHTML = `<span class="group-counter">${count}</span> groupe${count > 1 ? 's' : ''} sélectionné${count > 1 ? 's' : ''}`;
        }
    }
    
    const emptyState = document.getElementById('emptyState');
    const previewBubble = document.getElementById('previewBubble');
    if (emptyState) {
        const hasContent = count > 0 || selectedFile || (currentType === 'audio' && audioRecordedChunks.length > 0);
        emptyState.style.display = hasContent ? 'none' : 'block';
        if (previewBubble && document.getElementById('previewText')?.textContent) {
            previewBubble.style.display = hasContent ? 'block' : 'none';
        }
    }
}

function selectAllGroupes() {
    const checkboxes = document.querySelectorAll('input[name="groupes_ids[]"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
        const item = cb.closest('.conversation-item');
        if (item) {
            if (!allChecked) {
                item.classList.add('selected');
                if (!selectedGroupes.includes(cb.value)) selectedGroupes.push(cb.value);
            } else {
                item.classList.remove('selected');
            }
        }
    });
    
    if (allChecked) selectedGroupes = [];
    updateCounter();
}

function filterGroupes() {
    const searchInput = document.getElementById('searchGroupes');
    if (!searchInput) return;
    const term = searchInput.value.toLowerCase();
    document.querySelectorAll('.conversation-item').forEach(item => {
        const nameEl = item.querySelector('.conversation-name');
        if (nameEl) {
            const name = nameEl.textContent.toLowerCase();
            item.style.display = name.includes(term) ? '' : 'none';
        }
    });
}

// ==================== GESTION DES TYPES ====================

function setType(type, btn) {
    currentType = type;
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    
    const input = document.getElementById('messageInput');
    if (!input) return;
    
    const placeholders = {
        'texte': "Tapez votre message...",
        'video': "Légende de la vidéo (optionnel)...",
        'image': "Légende de l'image (optionnel)...",
        'document': "Commentaire (optionnel)...",
        'audio': "Enregistrement audio..."
    };
    
    input.placeholder = placeholders[type] || "Ajouter une légende...";
    
    if (type === 'texte') {
        clearFile();
        clearAudio();
    } else if (type === 'audio') {
        startRecording();
    } else if (!selectedFile) {
        const filePicker = document.getElementById('filePicker');
        if (filePicker) {
            filePicker.accept = type === 'video' ? 'video/*' : 
                               type === 'image' ? 'image/*' : '*/*';
            filePicker.click();
        }
    }
}

// ==================== GESTION DES FICHIERS ====================

function handleFileSelect(input) {
    if (!input.files || !input.files[0]) return;
    
    clearAudio();
    
    selectedFile = input.files[0];
    const sizeMB = (selectedFile.size / 1024 / 1024).toFixed(2);
    
    log('Fichier sélectionné: ' + selectedFile.name + ' (' + sizeMB + ' MB) - Type: ' + selectedFile.type);
    
    // Détection auto du type
    if (selectedFile.type.startsWith('video/')) currentType = 'video';
    else if (selectedFile.type.startsWith('image/')) currentType = 'image';
    else if (selectedFile.type.startsWith('audio/')) currentType = 'audio';
    else currentType = 'document';
    
    // Mise à jour UI boutons
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    const btnMap = {
        'video': 'btnVideo',
        'image': 'btnImage', 
        'audio': 'btnAudio',
        'document': 'btnDocument'
    };
    const btnId = btnMap[currentType];
    if (btnId) {
        const btn = document.getElementById(btnId);
        if (btn) btn.classList.add('active');
    }
    
    const chunksCount = Math.ceil(selectedFile.size / CHUNK_SIZE);
    showFilePreview(selectedFile.name, `${sizeMB} MB • ${chunksCount} chunks`, currentType);
    updateCounter();
}

function showFilePreview(name, sizeInfo, type) {
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const fileIcon = document.getElementById('fileIcon');
    
    if (filePreview) filePreview.classList.add('active');
    if (fileName) fileName.textContent = name;
    if (fileSize) fileSize.textContent = sizeInfo;
    
    const icons = {
        'video': 'bi-camera-video-fill',
        'image': 'bi-image-fill',
        'audio': 'bi-mic-fill',
        'document': 'bi-file-earmark-text-fill'
    };
    if (fileIcon) fileIcon.className = 'bi ' + (icons[type] || 'bi-file-earmark');
}

function clearFile() {
    const filePicker = document.getElementById('filePicker');
    if (filePicker) filePicker.value = '';
    selectedFile = null;
    const filePreview = document.getElementById('filePreview');
    if (filePreview) filePreview.classList.remove('active');
    
    if (currentType !== 'texte' && currentType !== 'audio') {
        setType('texte', document.getElementById('btnTexte'));
    }
}

// ==================== ENREGISTREMENT AUDIO ====================

function initWaveBars() {
    const container = document.getElementById('waveContainer');
    if (!container) return;
    container.innerHTML = '';
    for (let i = 0; i < 30; i++) {
        const bar = document.createElement('div');
        bar.className = 'wave-bar';
        bar.style.height = Math.random() * 30 + 20 + '%';
        bar.style.animationDelay = (i * 0.05) + 's';
        bar.style.animationDuration = (0.5 + Math.random() * 0.5) + 's';
        container.appendChild(bar);
    }
}

async function startRecording() {
    try {
        if (!navigator.mediaDevices?.getUserMedia) {
            alert('Votre navigateur ne supporte pas l\'enregistrement audio');
            return;
        }

        // Nettoyage si ancien recording actif
        if (isRecording) {
            await cancelRecording();
        }

        audioContext = new (window.AudioContext || window.webkitAudioContext)({
            sampleRate: 44100
        });

        const stream = await navigator.mediaDevices.getUserMedia({ 
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true,
                channelCount: 1
            } 
        });

        const source = audioContext.createMediaStreamSource(stream);
        audioRecorder = audioContext.createScriptProcessor(4096, 1, 1);
        audioRecordedChunks = [];
        
        audioRecorder.onaudioprocess = function(e) {
            if (!isRecording) return;
            const channelData = e.inputBuffer.getChannelData(0);
            audioRecordedChunks.push(new Float32Array(channelData));
        };

        source.connect(audioRecorder);
        audioRecorder.connect(audioContext.destination);

        audioStream = stream;
        recordingStartTime = Date.now();
        isRecording = true;

        // UI Recording
        const normalArea = document.getElementById('normalInputArea');
        const recordingArea = document.getElementById('recordingInputArea');
        if (normalArea) normalArea.style.display = 'none';
        if (recordingArea) recordingArea.style.display = 'flex';
        
        initWaveBars();
        updateRecordingTime();
        recordingTimer = setInterval(updateRecordingTime, 1000);
        
        const btnAudio = document.getElementById('btnAudio');
        if (btnAudio) btnAudio.classList.add('active');

        log('Enregistrement audio démarré');

    } catch (err) {
        console.error('Erreur:', err);
        alert('Erreur microphone: ' + err.message);
        resetAudioState();
    }
}

function updateRecordingTime() {
    if (!recordingStartTime) return;
    const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
    const mins = Math.floor(elapsed / 60).toString().padStart(2, '0');
    const secs = (elapsed % 60).toString().padStart(2, '0');
    const timeEl = document.getElementById('recordingTime');
    if (timeEl) timeEl.textContent = `${mins}:${secs}`;
}

async function stopRecordingAndSend() {
    if (!isRecording) return;
    
    isRecording = false;
    clearInterval(recordingTimer);
    
    // Arrêt propre du recorder
    if (audioRecorder) {
        try { audioRecorder.disconnect(); } catch(e) {}
        audioRecorder = null;
    }
    
    if (audioStream) {
        audioStream.getTracks().forEach(track => track.stop());
        audioStream = null;
    }
    
    if (audioContext) {
        try { await audioContext.close(); } catch(e) {}
        audioContext = null;
    }

    log('Conversion en MP3...');

    try {
        const mp3Blob = await convertToMp3(audioRecordedChunks);
        const duration = Math.floor((Date.now() - recordingStartTime) / 1000);
        const mins = Math.floor(duration / 60).toString().padStart(2, '0');
        const secs = (duration % 60).toString().padStart(2, '0');
        
        const mp3File = new File([mp3Blob], `note_vocale_${Date.now()}.mp3`, { 
            type: 'audio/mpeg' 
        });
        
        selectedFile = mp3File;
        currentType = 'audio';
        
        showFilePreview('Note vocale (MP3)', `Durée: ${mins}:${secs} • ${(mp3File.size/1024).toFixed(1)} KB`, 'audio');
        log('MP3 créé: ' + mp3File.size + ' bytes');

    } catch (err) {
        console.error('Erreur conversion MP3:', err);
        alert('Erreur conversion audio. Essayez avec un fichier audio plus court.');
    }

    resetAudioUI();
    audioRecordedChunks = [];
    recordingStartTime = null;
}

function cancelRecording() {
    isRecording = false;
    clearInterval(recordingTimer);
    
    if (audioRecorder) {
        try { audioRecorder.disconnect(); } catch(e) {}
        audioRecorder = null;
    }
    
    if (audioStream) {
        audioStream.getTracks().forEach(track => track.stop());
        audioStream = null;
    }
    
    if (audioContext) {
        try { audioContext.close(); } catch(e) {}
        audioContext = null;
    }
    
    resetAudioState();
    log('Enregistrement annulé');
}

function resetAudioState() {
    audioRecordedChunks = [];
    recordingStartTime = null;
    isRecording = false;
    resetAudioUI();
    
    const btnAudio = document.getElementById('btnAudio');
    if (btnAudio) btnAudio.classList.remove('active');
    
    setType('texte', document.getElementById('btnTexte'));
}

function resetAudioUI() {
    const normalArea = document.getElementById('normalInputArea');
    const recordingArea = document.getElementById('recordingInputArea');
    if (normalArea) normalArea.style.display = 'flex';
    if (recordingArea) recordingArea.style.display = 'none';
}

function clearAudio() {
    if (isRecording) {
        cancelRecording();
        return;
    }
    selectedFile = null;
    if (currentType === 'audio') {
        document.getElementById('filePreview')?.classList.remove('active');
        setType('texte', document.getElementById('btnTexte'));
    }
}

function convertToMp3(chunks) {
    return new Promise((resolve, reject) => {
        try {
            if (!chunks || chunks.length === 0) {
                reject(new Error('Aucune donnée audio'));
                return;
            }

            const totalLength = chunks.reduce((acc, chunk) => acc + chunk.length, 0);
            const audioData = new Float32Array(totalLength);
            
            let offset = 0;
            chunks.forEach(chunk => {
                audioData.set(chunk, offset);
                offset += chunk.length;
            });

            // Conversion PCM 16-bit
            const samples = new Int16Array(audioData.length);
            for (let i = 0; i < audioData.length; i++) {
                const s = Math.max(-1, Math.min(1, audioData[i]));
                samples[i] = s < 0 ? s * 0x8000 : s * 0x7FFF;
            }

            // Encodage MP3 avec lamejs [^9^]
            const mp3Encoder = new lamejs.Mp3Encoder(1, 44100, 128);
            const mp3Data = [];
            const sampleBlockSize = 1152;
            
            for (let i = 0; i < samples.length; i += sampleBlockSize) {
                const sampleChunk = samples.subarray(i, i + sampleBlockSize);
                const mp3buf = mp3Encoder.encodeBuffer(sampleChunk);
                if (mp3buf.length > 0) mp3Data.push(mp3buf);
            }
            
            const mp3buf = mp3Encoder.flush();
            if (mp3buf.length > 0) mp3Data.push(mp3buf);

            resolve(new Blob(mp3Data, { type: 'audio/mpeg' }));
            
        } catch (err) {
            reject(err);
        }
    });
}

// ==================== PRÉVISUALISATION ====================

function updatePreview() {
    const input = document.getElementById('messageInput');
    if (!input) return;
    
    const text = input.value.trim();
    const bubble = document.getElementById('previewBubble');
    const previewText = document.getElementById('previewText');
    
    if (previewText) {
        previewText.textContent = text || '(Aucune légende)';
    }
    if (bubble) {
        bubble.style.display = text ? 'block' : 'none';
    }
    
    updateCounter();
}

// ==================== ENVOI PAR CHUNKS ====================

async function submitForm() {
    const checkboxes = document.querySelectorAll('input[name="groupes_ids[]"]:checked');
    
    if (checkboxes.length === 0) {
        alert('Veuillez sélectionner au moins un groupe WhatsApp');
        return;
    }
    
    if (currentType !== 'texte' && !selectedFile) {
        alert('Veuillez sélectionner un fichier ou enregistrer un audio');
        return;
    }
    
    const messageInput = document.getElementById('messageInput');
    const message = messageInput ? messageInput.value.trim() : '';
    const groupesIds = Array.from(checkboxes).map(cb => cb.value);
    
    const sendBtn = document.getElementById('sendBtn');
    if (sendBtn) sendBtn.disabled = true;
    isUploading = true;
    
    try {
        if (currentType === 'texte') {
            await envoyerTexte(groupesIds, message);
        } else {
            const typeToSend = selectedFile?.type?.startsWith('audio/') ? 'audio' : currentType;
            log('Envoi fichier type: ' + typeToSend + ', MIME: ' + selectedFile?.type);
            await envoyerFichierChunks(groupesIds, message, typeToSend, selectedFile);
        }
    } catch (error) {
        log('Erreur: ' + error.message);
        alert('Erreur: ' + error.message);
        hideUploadProgress();
    } finally {
        if (sendBtn) sendBtn.disabled = false;
        isUploading = false;
    }
}

async function envoyerTexte(groupesIds, message) {
    showUploadProgress(0, 'Envoi des messages...', 'bi-chat-text-fill');
    
    const formData = new FormData();
    groupesIds.forEach(id => formData.append('groupes_ids[]', id));
    formData.append('type_envoi', 'texte');
    formData.append('message', message);
    formData.append('delai', '1000');
    
    const response = await fetch('<?php echo site_url('whatsapp/traiter_envoi'); ?>', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    if (!result.success) throw new Error(result.error);
    
    currentJobId = result.job_id;
    startPolling(result.job_id);
}

async function envoyerFichierChunks(groupesIds, message, type, file) {
    const totalSize = file.size;
    const totalChunks = Math.ceil(totalSize / CHUNK_SIZE);
    const uploadId = 'whapi_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    currentUploadId = uploadId;
    
    abortController = new AbortController();
    
    log(`Upload démarré: ${totalChunks} chunks, type: ${type}, mime: ${file.type}`);
    showUploadProgress(0, 'Préparation...', 'bi-cloud-arrow-up');
    
    // Initialisation
    const initResponse = await fetch('<?php echo site_url('whatsapp/init_chunk_upload'); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            upload_id: uploadId,
            filename: file.name,
            filesize: totalSize,
            filetype: file.type,
            total_chunks: totalChunks,
            groupes_ids: groupesIds,
            message: message,
            type_envoi: type
        }),
        signal: abortController.signal
    });
    
    const initResult = await initResponse.json();
    if (!initResult.success) throw new Error(initResult.error);
    
    // Envoi des chunks
    let uploadedSize = 0;
    let lastTime = Date.now();
    
    for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
        if (!isUploading) throw new Error('Upload annulé');
        
        const start = chunkIndex * CHUNK_SIZE;
        const end = Math.min(start + CHUNK_SIZE, totalSize);
        const chunk = file.slice(start, end);
        
        const chunkFormData = new FormData();
        chunkFormData.append('upload_id', uploadId);
        chunkFormData.append('chunk_index', chunkIndex);
        chunkFormData.append('total_chunks', totalChunks);
        chunkFormData.append('chunk_data', chunk, `${file.name}.part${chunkIndex}`);
        
        // Stats
        const now = Date.now();
        const elapsed = (now - lastTime) / 1000;
        const chunkSizeMB = (end - start) / 1024 / 1024;
        const speed = elapsed > 0 ? (chunkSizeMB / elapsed).toFixed(1) : 0;
        lastTime = now;
        
        uploadedSize = end;
        const percent = Math.round((uploadedSize / totalSize) * 100);
        
        updateUploadProgress(
            percent,
            `Upload chunk ${chunkIndex + 1}/${totalChunks}`,
            `${(uploadedSize/1024/1024).toFixed(1)} / ${(totalSize/1024/1024).toFixed(1)} MB`,
            `${speed} MB/s`
        );
        
        // Retry logic
        let retries = 3;
        while (retries > 0) {
            try {
                const chunkResponse = await fetch('<?php echo site_url('whatsapp/upload_chunk'); ?>', {
                    method: 'POST',
                    body: chunkFormData,
                    signal: abortController.signal
                });
                
                if (!chunkResponse.ok) throw new Error('HTTP ' + chunkResponse.status);
                
                const chunkResult = await chunkResponse.json();
                if (!chunkResult.success) throw new Error(chunkResult.error);
                
                break;
                
            } catch (err) {
                retries--;
                log(`Retry chunk ${chunkIndex + 1}, reste: ${retries}`);
                if (retries === 0) throw err;
                await new Promise(r => setTimeout(r, 1000));
            }
        }
        
        if (chunkIndex < totalChunks - 1) {
            await new Promise(r => setTimeout(r, 50));
        }
    }
    
    // Finalisation
    updateUploadProgress(100, 'Assemblage...', 'Finalisation', '');
    
    const finalizeFormData = new FormData();
    finalizeFormData.append('upload_id', uploadId);
    
    const finalizeResponse = await fetch('<?php echo site_url('whatsapp/finalize_and_send'); ?>', {
        method: 'POST',
        body: finalizeFormData,
        signal: abortController.signal
    });
    
    const finalizeResult = await finalizeResponse.json();
    if (!finalizeResult.success) throw new Error(finalizeResult.error);
    
    currentJobId = finalizeResult.job_id;
    startPolling(finalizeResult.job_id);
}

function cancelUpload() {
    if (abortController) abortController.abort();
    isUploading = false;
    hideUploadProgress();
    log('Upload annulé');
}

// ==================== UI PROGRESS ====================

function showUploadProgress(percent, title, iconClass) {
    const overlay = document.getElementById('uploadOverlay');
    const icon = document.getElementById('uploadIcon');
    const titleEl = document.getElementById('uploadTitle');
    const progress = document.getElementById('uploadProgress');
    const percentEl = document.getElementById('uploadPercent');
    
    if (overlay) overlay.classList.add('active');
    if (icon) icon.innerHTML = `<i class="bi ${iconClass}"></i>`;
    if (titleEl) titleEl.textContent = title;
    if (progress) progress.style.width = percent + '%';
    if (percentEl) percentEl.textContent = percent + '%';
}

function updateUploadProgress(percent, title, chunkInfo, speed) {
    const titleEl = document.getElementById('uploadTitle');
    const progress = document.getElementById('uploadProgress');
    const percentEl = document.getElementById('uploadPercent');
    const chunkEl = document.getElementById('uploadChunkInfo');
    const speedEl = document.getElementById('uploadSpeed');
    
    if (titleEl) titleEl.textContent = title;
    if (progress) progress.style.width = percent + '%';
    if (percentEl) percentEl.textContent = percent + '%';
    if (chunkEl) chunkEl.textContent = chunkInfo;
    if (speedEl) speedEl.textContent = speed;
}

function hideUploadProgress() {
    const overlay = document.getElementById('uploadOverlay');
    if (overlay) overlay.classList.remove('active');
}

// ==================== POLLING ====================

function startPolling(jobId) {
    let attempts = 0;
    
    pollInterval = setInterval(() => {
        attempts++;
        
        fetch('<?php echo site_url('whatsapp/check_status/'); ?>' + jobId)
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    clearInterval(pollInterval);
                    hideUploadProgress();
                    alert('Erreur: ' + data.error);
                    return;
                }
                
                if (data.status === 'completed') {
                    clearInterval(pollInterval);
                    window.location.href = '<?php echo site_url('whatsapp/resultat/'); ?>' + jobId;
                } else {
                    const progress = data.progress || Math.min(10 + attempts * 2, 95);
                    const text = data.current_group 
                        ? `Envoi à ${data.current_group}...` 
                        : 'Envoi en cours via Whapi...';
                    
                    updateUploadProgress(
                        progress,
                        text,
                        `${data.result?.reussis || 0} / ${data.result?.total || '?'} groupes`,
                        ''
                    );
                }
            })
            .catch(err => log('Polling error: ' + err));
    }, 2000);
}

// ==================== EVENT LISTENERS ====================

document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            updatePreview();
        });
    }
});
</script>

</body>
</html>