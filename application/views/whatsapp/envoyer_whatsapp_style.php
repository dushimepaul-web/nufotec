<?php defined('BASEPATH') OR exit('No direct script access allowed'); 

// Définir les variables par défaut
if (!isset($resultat)) $resultat = null;
if (!isset($message)) $message = '';
if (!isset($type_envoi)) $type_envoi = 'texte';
if (!isset($groupes_info)) $groupes_info = array();
if (!isset($total_groupes)) $total_groupes = 0;
if (!isset($groupes)) $groupes = array();
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
        
        /* Sidebar */
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
            background: linear-gradient(135deg, #00bfa5, #00897b);
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
            font-weight: 400;
            color: #111b21;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .conversation-time { font-size: 12px; color: #667781; }
        
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
        
        /* Chat Area */
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
            background: linear-gradient(135deg, #00bfa5, #00897b);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
        }
        
        .chat-title { display: flex; flex-direction: column; }
        .chat-name { font-size: 16px; font-weight: 500; color: #111b21; }
        .chat-status { font-size: 13px; color: #667781; }
        
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
        
        .message-bubble.received {
            background: #fff;
            align-self: flex-start;
            border-top-left-radius: 0;
            box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
        }
        
        .message-text { font-size: 14.2px; line-height: 19px; color: #111b21; }
        .message-time { font-size: 11px; color: #667781; text-align: right; margin-top: 4px; }
        
        /* Upload Progress - Style WhatsApp */
        .upload-progress-container {
            display: none;
            background: rgba(0,0,0,0.6);
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 100;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        .upload-progress-container.active { display: flex; }
        
        .upload-box {
            background: #fff;
            padding: 32px;
            border-radius: 16px;
            text-align: center;
            min-width: 300px;
        }
        
        .upload-spinner {
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
        
        .upload-title {
            font-size: 20px;
            font-weight: 500;
            color: #111b21;
            margin-bottom: 8px;
        }
        
        .upload-text {
            font-size: 14px;
            color: #667781;
            margin-bottom: 16px;
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
            transition: width 0.3s;
        }
        
        .upload-percent {
            font-size: 14px;
            color: #00a884;
            font-weight: 500;
        }
        
        .upload-cancel {
            margin-top: 16px;
            padding: 8px 24px;
            border: none;
            background: transparent;
            color: #ea0038;
            cursor: pointer;
            font-size: 14px;
        }
        
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
            transition: all 0.2s;
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
        
        /* Audio Recording */
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
            height: 30px;
            display: flex;
            align-items: center;
            gap: 2px;
        }
        
        .wave-bar {
            width: 3px;
            background: #ea0038;
            border-radius: 2px;
            animation: wave 0.5s ease-in-out infinite;
        }
        
        @keyframes wave {
            0%, 100% { height: 10%; }
            50% { height: 100%; }
        }
        
        .recording-time {
            font-size: 14px;
            color: #ea0038;
            font-weight: 500;
            min-width: 50px;
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
        
        /* Empty State */
        .empty-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-left: 1px solid #e9edef;
        }
        
        .empty-icon {
            width: 300px;
            height: 200px;
            opacity: 0.5;
            margin-bottom: 32px;
        }
        
        .empty-title {
            font-size: 28px;
            font-weight: 300;
            color: #41525d;
            margin-bottom: 16px;
        }
        
        .empty-text {
            font-size: 14px;
            color: #667781;
            text-align: center;
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
        
        /* Alerts */
        .alert-floating {
            position: absolute;
            top: 70px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-width: 400px;
        }
    </style>
</head>
<body>

<!-- Upload Progress Overlay -->
<div class="upload-progress-container" id="uploadOverlay">
    <div class="upload-box">
        <div class="upload-spinner"></div>
        <div class="upload-title" id="uploadTitle">Envoi en cours...</div>
        <div class="upload-text" id="uploadText">Ne fermez pas cette page</div>
        <div class="upload-bar">
            <div class="upload-fill" id="uploadProgress"></div>
        </div>
        <div class="upload-percent" id="uploadPercent">0%</div>
        <button class="upload-cancel" onclick="cancelUpload()">Annuler</button>
    </div>
</div>

<div class="app-container">
    
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar"><i class="bi bi-person"></i></div>
            <div class="header-icons">
                <button class="icon-btn" onclick="selectAllGroupes()" title="Tout sélectionner">
                    <i class="bi bi-check-all"></i>
                </button>
                <a href="<?php echo site_url('whatsapp/synchroniser'); ?>" class="icon-btn" title="Synchroniser">
                    <i class="bi bi-arrow-repeat"></i>
                </a>
            </div>
        </div>
        
        <div class="search-container">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchGroupes" placeholder="Rechercher..." onkeyup="filterGroupes()">
            </div>
        </div>
        
        <div class="conversations-list" id="conversationsList">
            <?php foreach ($groupes as $groupe): ?>
            <div class="conversation-item" onclick="toggleSelection(this, '<?php echo htmlspecialchars($groupe['groupe_id']); ?>')">
                <input type="checkbox" class="checkbox-select" name="groupes_ids[]" 
                       value="<?php echo htmlspecialchars($groupe['groupe_id']); ?>" 
                       onchange="updateCounter()" onclick="event.stopPropagation()">
                <div class="conversation-avatar">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="conversation-info">
                    <div class="conversation-header">
                        <span class="conversation-name"><?php echo htmlspecialchars($groupe['nom']); ?></span>
                        <span class="conversation-time"><?php echo date('H:i'); ?></span>
                    </div>
                    <div class="conversation-preview">
                        <span class="conversation-message">Cliquez pour sélectionner</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Chat Area -->
    <div class="chat-area">
        
        <?php if ($resultat !== null): ?>
            <!-- Résultat -->
            <div class="chat-header">
                <div class="chat-header-info">
                    <div class="chat-avatar"><i class="bi bi-check-all"></i></div>
                    <div class="chat-title">
                        <span class="chat-name">Résultat</span>
                        <span class="chat-status"><?php echo $reussis ?? 0; ?>/<?php echo $total ?? 0; ?> envoyés</span>
                    </div>
                </div>
                <a href="<?php echo site_url('whatsapp/envoyer'); ?>" class="icon-btn">
                    <i class="bi bi-plus-lg"></i>
                </a>
            </div>
            
            <div class="messages-container">
                <div class="message-bubble sent" style="align-self: center; background: #fff; max-width: 80%;">
                    <div style="text-align: center; padding: 20px;">
                        <div style="font-size: 48px; margin-bottom: 16px;">
                            <?php echo (($reussis ?? 0) > 0) ? '✅' : '❌'; ?>
                        </div>
                        <div style="font-size: 20px; font-weight: 500; margin-bottom: 8px;">
                            <?php echo (($reussis ?? 0) > 0) ? 'Envoi terminé' : 'Échec'; ?>
                        </div>
                        <div style="color: #667781;">
                            <?php echo $reussis ?? 0; ?> succès, <?php echo $echoues ?? 0; ?> échecs
                        </div>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Formulaire -->
            <form id="envoiForm" style="display: flex; flex-direction: column; height: 100%;">
                
                <div class="chat-header">
                    <div class="chat-header-info">
                        <div class="chat-avatar"><i class="bi bi-people-fill"></i></div>
                        <div class="chat-title">
                            <span class="chat-name">Nouvelle diffusion</span>
                            <span class="chat-status" id="selectionStatus">0 groupe sélectionné</span>
                        </div>
                    </div>
                    <div class="header-icons">
                        <a href="<?php echo site_url('whatsapp'); ?>" class="icon-btn">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Alerts -->
                <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-floating">
                    <?php echo $this->session->flashdata('error'); ?>
                </div>
                <?php endif; ?>
                
                <div class="type-selector">
                    <button type="button" class="type-btn active" onclick="setType('texte', this)" id="btnTexte">
                        <i class="bi bi-chat-text"></i> <span>Message</span>
                    </button>
                    <button type="button" class="type-btn" onclick="setType('fichier', this)" id="btnFichier">
                        <i class="bi bi-paperclip"></i> <span>Fichier</span>
                    </button>
                    <button type="button" class="type-btn" onclick="setType('audio', this)" id="btnAudio">
                        <i class="bi bi-mic"></i> <span>Audio</span>
                    </button>
                    <input type="hidden" name="type_envoi" id="typeEnvoi" value="texte">
                </div>
                
                <!-- File Preview -->
                <div class="file-preview-area" id="filePreview">
                    <div class="file-box">
                        <div class="file-icon"><i class="bi bi-file-earmark" id="fileIcon"></i></div>
                        <div class="file-info">
                            <div class="file-name" id="fileName">-</div>
                            <div class="file-size" id="fileSize">-</div>
                        </div>
                        <button type="button" class="file-remove" onclick="clearFile()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Messages -->
                <div class="messages-container" id="messagesContainer">
                    <div class="message-bubble sent" id="previewBubble" style="display: none;">
                        <div class="message-text" id="previewText"></div>
                        <div class="message-time"><?php echo date('H:i'); ?></div>
                    </div>
                </div>
                
                <!-- Input Area -->
                <div class="input-area">
                    <!-- Normal Input -->
                    <div id="normalInput" style="display: flex; align-items: center; gap: 10px; flex: 1;">
                        <button type="button" class="input-icon-btn" onclick="document.getElementById('fileInput').click()">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <input type="file" id="fileInput" name="fichier" style="display: none;" 
                               onchange="handleFileSelect(this)" 
                               accept="video/*,audio/*,image/*,.pdf,.doc,.docx">
                        
                        <div class="message-input-wrapper">
                            <textarea class="message-input" id="messageInput" name="message" 
                                      placeholder="Tapez un message" rows="1" 
                                      oninput="updatePreview()"></textarea>
                        </div>
                        
                        <button type="button" class="input-icon-btn" id="micBtn" onclick="startRecording()">
                            <i class="bi bi-mic-fill"></i>
                        </button>
                    </div>
                    
                    <!-- Recording Interface -->
                    <div class="audio-recording" id="recordingInterface">
                        <button type="button" class="input-icon-btn" onclick="cancelRecording()" style="color: #ea0038;">
                            <i class="bi bi-trash"></i>
                        </button>
                        <div class="recording-wave" id="waveContainer">
                            <!-- Wave bars generated by JS -->
                        </div>
                        <div class="recording-time" id="recordingTime">00:00</div>
                        <button type="button" class="input-icon-btn" onclick="stopRecording()" style="color: #00a884;">
                            <i class="bi bi-check-lg"></i>
                        </button>
                    </div>
                    
                    <button type="button" class="send-btn" id="sendBtn" onclick="submitForm()">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
                
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
// Variables globales
let selectedGroupes = [];
let selectedFile = null;
let mediaRecorder = null;
let audioChunks = [];
let recordingStartTime = null;
let recordingTimer = null;
let uploadXHR = null;

// Initialiser les ondes audio
function initWaveBars() {
    const container = document.getElementById('waveContainer');
    container.innerHTML = '';
    for (let i = 0; i < 20; i++) {
        const bar = document.createElement('div');
        bar.className = 'wave-bar';
        bar.style.height = Math.random() * 100 + '%';
        bar.style.animationDelay = (i * 0.05) + 's';
        container.appendChild(bar);
    }
}

function toggleSelection(element, groupeId) {
    const checkbox = element.querySelector('.checkbox-select');
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
        statusEl.textContent = count + ' groupe' + (count > 1 ? 's' : '') + ' sélectionné' + (count > 1 ? 's' : '');
    }
}

function selectAllGroupes() {
    const checkboxes = document.querySelectorAll('input[name="groupes_ids[]"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
        const item = cb.closest('.conversation-item');
        if (!allChecked) item.classList.add('selected');
        else item.classList.remove('selected');
    });
    updateCounter();
}

function filterGroupes() {
    const term = document.getElementById('searchGroupes').value.toLowerCase();
    document.querySelectorAll('.conversation-item').forEach(item => {
        const name = item.querySelector('.conversation-name').textContent.toLowerCase();
        item.style.display = name.includes(term) ? '' : 'none';
    });
}

function setType(type, btn) {
    document.getElementById('typeEnvoi').value = type;
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    const input = document.getElementById('messageInput');
    const micBtn = document.getElementById('micBtn');
    
    if (type === 'audio') {
        document.getElementById('normalInput').style.display = 'none';
        startRecording();
    } else {
        document.getElementById('normalInput').style.display = 'flex';
        document.getElementById('recordingInterface').classList.remove('active');
        
        if (type === 'fichier') {
            input.placeholder = "Légende (optionnel)...";
            document.getElementById('fileInput').click();
        } else {
            input.placeholder = "Tapez un message";
        }
    }
}

function handleFileSelect(input) {
    if (!input.files || !input.files[0]) return;
    
    selectedFile = input.files[0];
    const sizeMB = (selectedFile.size / 1024 / 1024).toFixed(2);
    
    document.getElementById('filePreview').classList.add('active');
    document.getElementById('fileName').textContent = selectedFile.name;
    document.getElementById('fileSize').textContent = sizeMB + ' MB';
    
    // Icône selon type
    const icon = document.getElementById('fileIcon');
    if (selectedFile.type.startsWith('video/')) icon.className = 'bi bi-camera-video-fill';
    else if (selectedFile.type.startsWith('audio/')) icon.className = 'bi bi-mic-fill';
    else if (selectedFile.type.startsWith('image/')) icon.className = 'bi bi-image-fill';
    else icon.className = 'bi bi-file-earmark';
    
    setType('fichier', document.getElementById('btnFichier'));
}

function clearFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('filePreview').classList.remove('active');
    selectedFile = null;
    setType('texte', document.getElementById('btnTexte'));
}

function updatePreview() {
    const text = document.getElementById('messageInput').value.trim();
    const bubble = document.getElementById('previewBubble');
    const previewText = document.getElementById('previewText');
    
    if (text) {
        previewText.textContent = text;
        bubble.style.display = 'block';
    } else {
        bubble.style.display = 'none';
    }
}

// ==================== ENREGISTREMENT AUDIO ====================

async function startRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];
        
        mediaRecorder.ondataavailable = (e) => {
            if (e.data.size > 0) audioChunks.push(e.data);
        };
        
        mediaRecorder.onstop = () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            const audioFile = new File([audioBlob], 'audio_' + Date.now() + '.webm', { type: 'audio/webm' });
            
            // Simuler un fichier sélectionné
            const dt = new DataTransfer();
            dt.items.add(audioFile);
            document.getElementById('fileInput').files = dt.files;
            selectedFile = audioFile;
            
            document.getElementById('filePreview').classList.add('active');
            document.getElementById('fileName').textContent = 'Note vocale';
            document.getElementById('fileSize').textContent = (audioFile.size / 1024).toFixed(1) + ' KB';
            document.getElementById('fileIcon').className = 'bi bi-mic-fill';
            
            document.getElementById('normalInput').style.display = 'flex';
            document.getElementById('recordingInterface').classList.remove('active');
            
            // Arrêter tous les tracks
            stream.getTracks().forEach(track => track.stop());
        };
        
        mediaRecorder.start();
        recordingStartTime = Date.now();
        
        // UI
        document.getElementById('normalInput').style.display = 'none';
        document.getElementById('recordingInterface').classList.add('active');
        initWaveBars();
        
        // Timer
        recordingTimer = setInterval(updateRecordingTime, 1000);
        
    } catch (err) {
        alert('Erreur microphone: ' + err.message);
        setType('texte', document.getElementById('btnTexte'));
    }
}

function updateRecordingTime() {
    const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
    const mins = Math.floor(elapsed / 60).toString().padStart(2, '0');
    const secs = (elapsed % 60).toString().padStart(2, '0');
    document.getElementById('recordingTime').textContent = `${mins}:${secs}`;
}

function stopRecording() {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
        clearInterval(recordingTimer);
    }
}

function cancelRecording() {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
        mediaRecorder = null;
    }
    clearInterval(recordingTimer);
    audioChunks = [];
    
    document.getElementById('normalInput').style.display = 'flex';
    document.getElementById('recordingInterface').classList.remove('active');
    setType('texte', document.getElementById('btnTexte'));
}

// ==================== ENVOI AVEC PROGRESS ====================

function submitForm() {
    const checkboxes = document.querySelectorAll('input[name="groupes_ids[]"]:checked');
    if (checkboxes.length === 0) {
        alert('Sélectionnez au moins un groupe');
        return;
    }
    
    const type = document.getElementById('typeEnvoi').value;
    const message = document.getElementById('messageInput').value.trim();
    const file = document.getElementById('fileInput').files[0];
    
    if (type === 'texte' && !message) {
        alert('Tapez un message');
        return;
    }
    
    if ((type === 'fichier' || type === 'audio') && !file) {
        alert('Aucun fichier sélectionné');
        return;
    }
    
    // Préparer FormData
    const formData = new FormData();
    formData.append('type_envoi', type);
    formData.append('message', message);
    formData.append('delai', '1000');
    
    checkboxes.forEach(cb => {
        formData.append('groupes_ids[]', cb.value);
    });
    
    if (file) {
        formData.append('fichier', file);
    }
    
    // Envoi avec XMLHttpRequest pour avoir la progression
    uploadXHR = new XMLHttpRequest();
    
    uploadXHR.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            showUploadProgress(percent);
        }
    });
    
    uploadXHR.addEventListener('load', () => {
        if (uploadXHR.status === 200) {
            // Succès - remplacer le contenu par la réponse
            document.open();
            document.write(uploadXHR.responseText);
            document.close();
        } else {
            hideUploadProgress();
            alert('Erreur serveur: ' + uploadXHR.status);
        }
    });
    
    uploadXHR.addEventListener('error', () => {
        hideUploadProgress();
        alert('Erreur réseau pendant l\'envoi');
    });
    
    uploadXHR.addEventListener('abort', () => {
        hideUploadProgress();
    });
    
    // Démarrer l'upload
    showUploadProgress(0);
    uploadXHR.open('POST', '<?php echo site_url('whatsapp/traiter_envoi'); ?>');
    uploadXHR.send(formData);
}

function showUploadProgress(percent) {
    const overlay = document.getElementById('uploadOverlay');
    const fill = document.getElementById('uploadProgress');
    const percentText = document.getElementById('uploadPercent');
    const title = document.getElementById('uploadTitle');
    
    overlay.classList.add('active');
    fill.style.width = percent + '%';
    percentText.textContent = percent + '%';
    
    if (percent < 100) {
        title.textContent = 'Envoi en cours...';
        document.getElementById('uploadText').textContent = 'Ne fermez pas cette page';
    } else {
        title.textContent = 'Traitement...';
        document.getElementById('uploadText').textContent = 'Envoi aux groupes WhatsApp';
    }
}

function hideUploadProgress() {
    document.getElementById('uploadOverlay').classList.remove('active');
}

function cancelUpload() {
    if (uploadXHR) {
        uploadXHR.abort();
    }
}

// Auto-resize textarea
document.getElementById('messageInput')?.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
});
</script>

</body>
</html>