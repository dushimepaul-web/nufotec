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

<script>
// Configuration - Chunk size 1.5 MB pour Whapi
const CHUNK_SIZE = 1.5 * 1024 * 1024; // 1.5 MB
let selectedFile = null;
let currentUploadId = null;
let isUploading = false;
let abortController = null;

// Variables pour l'enregistrement audio
let mediaRecorder = null;
let audioChunks = [];
let recordingStartTime = null;
let recordingTimer = null;
let audioBlob = null;
let isRecording = false;
let audioStream = null; // Stocker le stream pour le nettoyer

// État
let selectedGroupes = [];
let currentJobId = null;
let pollInterval = null;
let currentType = 'texte';

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
    
    // Afficher/masquer l'état vide
    const emptyState = document.getElementById('emptyState');
    const previewBubble = document.getElementById('previewBubble');
    if (emptyState) {
        if (count > 0 || selectedFile || audioBlob) {
            emptyState.style.display = 'none';
            if (document.getElementById('previewText')?.textContent) {
                previewBubble.style.display = 'block';
            }
        } else {
            emptyState.style.display = 'block';
            previewBubble.style.display = 'none';
        }
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
            if (!selectedGroupes.includes(cb.value)) selectedGroupes.push(cb.value);
        } else {
            item.classList.remove('selected');
        }
    });
    
    if (allChecked) selectedGroupes = [];
    updateCounter();
}

function filterGroupes() {
    const term = document.getElementById('searchGroupes').value.toLowerCase();
    document.querySelectorAll('.conversation-item').forEach(item => {
        const name = item.querySelector('.conversation-name').textContent.toLowerCase();
        item.style.display = name.includes(term) ? '' : 'none';
    });
}

// ==================== GESTION DES TYPES ====================

function setType(type, btn) {
    currentType = type;
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    const input = document.getElementById('messageInput');
    
    if (type === 'texte') {
        input.placeholder = "Tapez votre message...";
        clearFile();
        clearAudio();
    } else if (type === 'audio') {
        // Démarrer l'enregistrement directement
        startRecording();
    } else {
        const placeholders = {
            'video': "Légende de la vidéo (optionnel)...",
            'image': "Légende de l'image (optionnel)...",
            'document': "Commentaire (optionnel)..."
        };
        input.placeholder = placeholders[type] || "Ajouter une légende...";
        
        if (!selectedFile && !audioBlob) {
            document.getElementById('filePicker').accept = type === 'video' ? 'video/*' : 
                                                          type === 'image' ? 'image/*' : 
                                                          type === 'audio' ? 'audio/*' :
                                                          '*/*';
            document.getElementById('filePicker').click();
        }
    }
}

// ==================== GESTION DES FICHIERS ====================

function handleFileSelect(input) {
    if (!input.files || !input.files[0]) return;
    
    // Si on avait un audio, le supprimer
    clearAudio();
    
    selectedFile = input.files[0];
    const sizeMB = (selectedFile.size / 1024 / 1024).toFixed(2);
    
    log('Fichier sélectionné: ' + selectedFile.name + ' (' + sizeMB + ' MB) - Type: ' + selectedFile.type);
    
    // Détecter le type automatiquement
    if (selectedFile.type.startsWith('video/')) currentType = 'video';
    else if (selectedFile.type.startsWith('image/')) currentType = 'image';
    else if (selectedFile.type.startsWith('audio/')) currentType = 'audio';
    else currentType = 'document';
    
    // Mettre à jour l'UI des boutons
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    const btnId = currentType === 'video' ? 'btnVideo' : 
                  currentType === 'image' ? 'btnImage' : 
                  currentType === 'audio' ? 'btnAudio' : 'btnDocument';
    document.getElementById(btnId)?.classList.add('active');
    
    // UI updates
    showFilePreview(selectedFile.name, sizeMB + ' MB • ' + Math.ceil(selectedFile.size / CHUNK_SIZE) + ' chunks', currentType);
    
    updateCounter();
}

function showFilePreview(name, sizeInfo, type) {
    document.getElementById('filePreview').classList.add('active');
    document.getElementById('fileName').textContent = name;
    document.getElementById('fileSize').textContent = sizeInfo;
    
    // Icône selon type
    const icons = {
        'video': 'bi-camera-video-fill',
        'image': 'bi-image-fill',
        'audio': 'bi-mic-fill',
        'document': 'bi-file-earmark-text-fill'
    };
    document.getElementById('fileIcon').className = 'bi ' + (icons[type] || 'bi-file-earmark');
}

function clearFile() {
    document.getElementById('filePicker').value = '';
    selectedFile = null;
    document.getElementById('filePreview').classList.remove('active');
    if (currentType !== 'texte' && !audioBlob) {
        setType('texte', document.getElementById('btnTexte'));
    }
}

// ==================== ENREGISTREMENT AUDIO - CORRIGÉ ====================

function initWaveBars() {
    const container = document.getElementById('waveContainer');
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
        // Vérifier que l'API est disponible
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Votre navigateur ne supporte pas l\'enregistrement audio');
            setType('texte', document.getElementById('btnTexte'));
            return;
        }
        
        // Configuration audio optimale pour WhatsApp
        const audioConstraints = {
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true,
                sampleRate: 44100,
                channelCount: 1 // Mono pour réduire la taille
            }
        };
        
        // Demander permission microphone
        audioStream = await navigator.mediaDevices.getUserMedia(audioConstraints);
        
        // Détecter le meilleur format supporté
        // Priorité: audio/ogg (Opus) > audio/webm > audio/mp4
        const mimeTypes = [
            'audio/ogg;codecs=opus',
            'audio/webm;codecs=opus',
            'audio/webm',
            'audio/mp4'
        ];
        
        let selectedMime = '';
        for (let type of mimeTypes) {
            if (MediaRecorder.isTypeSupported(type)) {
                selectedMime = type;
                log('Format audio sélectionné: ' + type);
                break;
            }
        }
        
        if (!selectedMime) {
            alert('Aucun format audio supporté par ce navigateur');
            setType('texte', document.getElementById('btnTexte'));
            return;
        }
        
        // Configurer le recorder avec le format optimal
        const options = {
            mimeType: selectedMime,
            audioBitsPerSecond: 128000 // 128 kbps qualité voix
        };
        
        mediaRecorder = new MediaRecorder(audioStream, options);
        audioChunks = [];
        
        mediaRecorder.ondataavailable = (e) => {
            if (e.data && e.data.size > 0) {
                audioChunks.push(e.data);
                log('Chunk audio reçu: ' + e.data.size + ' bytes');
            }
        };
        
        mediaRecorder.onstop = () => {
            // Créer le blob avec le type exact utilisé
            const actualMimeType = mediaRecorder.mimeType || selectedMime;
            audioBlob = new Blob(audioChunks, { type: actualMimeType });
            
            // Déterminer l'extension selon le MIME type
            let extension = 'webm';
            if (actualMimeType.includes('ogg')) extension = 'ogg';
            if (actualMimeType.includes('mp4') || actualMimeType.includes('m4a')) extension = 'm4a';
            
            // Créer le fichier avec le bon type MIME
            const audioFile = new File([audioBlob], 'note_vocale_' + Date.now() + '.' + extension, { 
                type: actualMimeType 
            });
            
            // Stocker comme fichier sélectionné
            selectedFile = audioFile;
            currentType = 'audio';
            
            // Afficher dans le preview
            const duration = Math.floor((Date.now() - recordingStartTime) / 1000);
            const mins = Math.floor(duration / 60).toString().padStart(2, '0');
            const secs = (duration % 60).toString().padStart(2, '0');
            
            showFilePreview(
                'Note vocale (' + extension.toUpperCase() + ')', 
                `Durée: ${mins}:${secs} • ${(audioFile.size/1024).toFixed(1)} KB • ${actualMimeType}`, 
                'audio'
            );
            
            log('Audio enregistré: ' + audioFile.size + ' bytes, type: ' + actualMimeType + ', ext: ' + extension);
            
            // Nettoyer le stream
            if (audioStream) {
                audioStream.getTracks().forEach(track => track.stop());
                audioStream = null;
            }
        };
        
        mediaRecorder.onerror = (e) => {
            console.error('Erreur MediaRecorder:', e);
            alert('Erreur lors de l\'enregistrement audio');
            cancelRecording();
        };
        
        // Démarrer l'enregistrement
        mediaRecorder.start(100); // Collecte toutes les 100ms
        recordingStartTime = Date.now();
        isRecording = true;
        
        // Basculer l'interface
        document.getElementById('normalInputArea').style.display = 'none';
        document.getElementById('recordingInputArea').style.display = 'flex';
        
        // Initialiser les vagues
        initWaveBars();
        
        // Démarrer le timer
        updateRecordingTime();
        recordingTimer = setInterval(updateRecordingTime, 1000);
        
        // Mettre à jour le bouton audio
        document.getElementById('btnAudio').classList.add('active');
        
        log('Enregistrement audio démarré avec format: ' + selectedMime);
        
    } catch (err) {
        console.error('Erreur microphone:', err);
        alert('Erreur d\'accès au microphone: ' + err.message);
        setType('texte', document.getElementById('btnTexte'));
        
        // Nettoyer en cas d'erreur
        if (audioStream) {
            audioStream.getTracks().forEach(track => track.stop());
            audioStream = null;
        }
    }
}

function updateRecordingTime() {
    const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
    const mins = Math.floor(elapsed / 60).toString().padStart(2, '0');
    const secs = (elapsed % 60).toString().padStart(2, '0');
    document.getElementById('recordingTime').textContent = `${mins}:${secs}`;
}

function cancelRecording() {
    // Arrêter le recorder si actif
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        try {
            mediaRecorder.stop();
        } catch (e) {
            console.error('Erreur arrêt recorder:', e);
        }
    }
    
    // Nettoyer le stream
    if (audioStream) {
        audioStream.getTracks().forEach(track => track.stop());
        audioStream = null;
    }
    
    clearInterval(recordingTimer);
    audioChunks = [];
    audioBlob = null;
    isRecording = false;
    mediaRecorder = null;
    
    // Réinitialiser l'interface
    document.getElementById('normalInputArea').style.display = 'flex';
    document.getElementById('recordingInputArea').style.display = 'none';
    
    // Réinitialiser le type
    setType('texte', document.getElementById('btnTexte'));
    
    log('Enregistrement annulé');
}

function stopRecordingAndSend() {
    if (!mediaRecorder || mediaRecorder.state === 'inactive') {
        log('Recorder déjà inactif');
        return;
    }
    
    log('Arrêt de l\'enregistrement...');
    
    // Arrêter l'enregistrement - le onstop handler va créer le fichier
    mediaRecorder.stop();
    
    clearInterval(recordingTimer);
    isRecording = false;
    
    // Réinitialiser l'interface
    document.getElementById('normalInputArea').style.display = 'flex';
    document.getElementById('recordingInputArea').style.display = 'none';
    
    log('Enregistrement terminé, fichier prêt à envoyer');
}

function clearAudio() {
    if (isRecording) {
        cancelRecording();
    }
    audioBlob = null;
    if (currentType === 'audio' && !selectedFile) {
        document.getElementById('filePreview').classList.remove('active');
    }
}

function updatePreview() {
    const text = document.getElementById('messageInput').value.trim();
    const bubble = document.getElementById('previewBubble');
    const previewText = document.getElementById('previewText');
    
    if (text) {
        previewText.textContent = text;
        bubble.style.display = 'block';
    } else {
        previewText.textContent = '(Aucune légende)';
    }
    
    updateCounter();
}

// ==================== ENVOI PAR CHUNKS 1.5MB ====================

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
    
    const message = document.getElementById('messageInput').value.trim();
    const groupesIds = Array.from(checkboxes).map(cb => cb.value);
    
    // Désactiver le bouton
    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = true;
    isUploading = true;
    
    try {
        if (currentType === 'texte') {
            // Envoi texte simple
            await envoyerTexte(groupesIds, message);
        } else {
            // Envoi fichier par chunks (y compris audio)
            // FORCER le type audio si c'est un fichier audio
            const typeToSend = selectedFile.type.startsWith('audio/') ? 'audio' : currentType;
            log('Envoi fichier type: ' + typeToSend + ', MIME: ' + selectedFile.type);
            await envoyerFichierChunks(groupesIds, message, typeToSend, selectedFile);
        }
    } catch (error) {
        log('Erreur: ' + error.message);
        alert('Erreur: ' + error.message);
        hideUploadProgress();
    } finally {
        sendBtn.disabled = false;
        isUploading = false;
    }
}

// Envoi texte simple
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

// Envoi fichier par chunks - Style WhatsApp
async function envoyerFichierChunks(groupesIds, message, type, file) {
    const totalSize = file.size;
    const totalChunks = Math.ceil(totalSize / CHUNK_SIZE);
    const uploadId = 'whapi_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    currentUploadId = uploadId;
    
    abortController = new AbortController();
    
    log(`Upload démarré: ${totalChunks} chunks de 1.5 MB, type: ${type}, mime: ${file.type}`);
    showUploadProgress(0, 'Préparation de l\'upload...', 'bi-cloud-arrow-up');
    
    // Étape 1: Initialiser
    const initResponse = await fetch('<?php echo site_url('whatsapp/init_chunk_upload'); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            upload_id: uploadId,
            filename: file.name,
            filesize: totalSize,
            filetype: file.type, // Envoyer le vrai MIME type
            total_chunks: totalChunks,
            groupes_ids: groupesIds,
            message: message,
            type_envoi: type // 'audio', 'video', 'image', 'document'
        }),
        signal: abortController.signal
    });
    
    const initResult = await initResponse.json();
    if (!initResult.success) throw new Error(initResult.error);
    
    // Étape 2: Envoyer les chunks un par un
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
        
        // Calculer la vitesse
        const now = Date.now();
        const elapsed = (now - lastTime) / 1000;
        const chunkSizeMB = (end - start) / 1024 / 1024;
        const speed = elapsed > 0 ? (chunkSizeMB / elapsed).toFixed(1) : 0;
        lastTime = now;
        
        // Mettre à jour la progression
        uploadedSize = end;
        const percent = Math.round((uploadedSize / totalSize) * 100);
        
        updateUploadProgress(
            percent,
            `Upload chunk ${chunkIndex + 1}/${totalChunks}`,
            `${(uploadedSize/1024/1024).toFixed(1)} / ${(totalSize/1024/1024).toFixed(1)} MB`,
            `${speed} MB/s`
        );
        
        // Envoi avec retry
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
                
                break; // Succès
                
            } catch (err) {
                retries--;
                log(`Retry chunk ${chunkIndex + 1}, tentatives restantes: ${retries}`);
                if (retries === 0) throw err;
                await new Promise(r => setTimeout(r, 1000));
            }
        }
        
        // Petite pause pour ne pas saturer
        if (chunkIndex < totalChunks - 1) {
            await new Promise(r => setTimeout(r, 50));
        }
    }
    
    // Étape 3: Finaliser et lancer l'envoi Whapi
    updateUploadProgress(100, 'Assemblage du fichier...', 'Finalisation', '');
    
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
    if (abortController) {
        abortController.abort();
    }
    isUploading = false;
    hideUploadProgress();
    log('Upload annulé par l\'utilisateur');
}

// UI Progress
function showUploadProgress(percent, title, iconClass) {
    document.getElementById('uploadOverlay').classList.add('active');
    document.getElementById('uploadIcon').innerHTML = `<i class="bi ${iconClass}"></i>`;
    document.getElementById('uploadTitle').textContent = title;
    document.getElementById('uploadProgress').style.width = percent + '%';
    document.getElementById('uploadPercent').textContent = percent + '%';
}

function updateUploadProgress(percent, title, chunkInfo, speed) {
    document.getElementById('uploadTitle').textContent = title;
    document.getElementById('uploadProgress').style.width = percent + '%';
    document.getElementById('uploadPercent').textContent = percent + '%';
    document.getElementById('uploadChunkInfo').textContent = chunkInfo;
    document.getElementById('uploadSpeed').textContent = speed;
}

function hideUploadProgress() {
    document.getElementById('uploadOverlay').classList.remove('active');
}

// Polling statut
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
                    // Redirection vers résultat
                    window.location.href = '<?php echo site_url('whatsapp/resultat/'); ?>' + jobId;
                } else {
                    // Progression de l'envoi aux groupes
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
            .catch(err => {
                log('Polling error: ' + err);
            });
    }, 2000);
}

// Auto-resize textarea
document.getElementById('messageInput')?.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    updatePreview();
});
</script>

</body>
</html>