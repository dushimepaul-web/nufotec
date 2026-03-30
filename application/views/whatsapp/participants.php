<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!isset($participants)) $participants = array();
if (!isset($groupes)) $groupes = array();
if (!isset($resultat)) $resultat = null;
if (!isset($message)) $message = '';
if (!isset($type_envoi)) $type_envoi = 'texte';
if (!isset($job_id)) $job_id = null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp - Envoi aux Participants</title>
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
            width: 35%;
            min-width: 350px;
            max-width: 450px;
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
            transition: all 0.2s;
        }
        
        .icon-btn:hover { background: #d1d7db; }
        
        .stats-bar {
            background: #fff;
            padding: 12px 16px;
            border-bottom: 1px solid #e9edef;
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: #667781;
        }
        
        .stats-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .stats-item i { color: #00a884; }
        
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
        
        .filter-tabs {
            display: flex;
            gap: 8px;
            padding: 8px 16px;
            background: #fff;
            border-bottom: 1px solid #e9edef;
        }
        
        .filter-btn {
            padding: 6px 12px;
            border-radius: 16px;
            border: none;
            background: #f0f2f5;
            color: #54656f;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .filter-btn.active {
            background: #d9fdd3;
            color: #008f72;
        }
        
        .filter-btn:hover:not(.active) { background: #e9edef; }
        
        .participants-list {
            flex: 1;
            overflow-y: auto;
            background: #fff;
        }
        
        .participant-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f0f2f5;
            transition: background 0.2s;
        }
        
        .participant-item:hover { background: #f5f6f6; }
        .participant-item.selected { background: #f0f2f5; }
        
        .checkbox-select {
            width: 18px;
            height: 18px;
            margin-right: 12px;
            accent-color: #00a884;
            cursor: pointer;
        }
        
        .participant-avatar {
            width: 49px;
            height: 49px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00a884, #008f72);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            margin-right: 15px;
            flex-shrink: 0;
            position: relative;
        }
        
        .participant-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .admin-badge {
            position: absolute;
            bottom: -2px;
            right: -2px;
            background: #ffd700;
            color: #fff;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            border: 2px solid #fff;
        }
        
        .creator-badge {
            background: #ff6b6b;
        }
        
        .participant-info { flex: 1; min-width: 0; }
        
        .participant-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }
        
        .participant-name {
            font-size: 16px;
            font-weight: 500;
            color: #111b21;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .participant-phone {
            font-size: 14px;
            color: #667781;
        }
        
        .participant-meta {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-top: 2px;
        }
        
        .meta-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
            background: #e9edef;
            color: #54656f;
        }
        
        .meta-badge.admin {
            background: #d9fdd3;
            color: #008f72;
        }
        
        .meta-badge.creator {
            background: #ffe4e6;
            color: #ea0038;
        }
        
        .group-tag {
            font-size: 11px;
            color: #00a884;
            background: #e7fce3;
            padding: 2px 8px;
            border-radius: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
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
        
        /* Type Selector */
        .type-selector {
            display: flex;
            gap: 8px;
            padding: 8px 16px;
            background: #f0f2f5;
            border-left: 1px solid #e9edef;
            overflow-x: auto;
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
            white-space: nowrap;
            transition: all 0.2s;
        }
        
        .type-btn.active {
            background: #d9fdd3;
            color: #008f72;
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
            transition: all 0.2s;
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
        
        /* Group Counter Badge */
        .participant-counter {
            background: #00a884;
            color: #fff;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* Empty State */
        .empty-state {
            align-self: center;
            color: #667781;
            margin-top: 40px;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 64px;
            opacity: 0.3;
            margin-bottom: 16px;
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
        
        /* Selection Actions */
        .selection-actions {
            display: none;
            position: absolute;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 8px 16px;
            border-radius: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            gap: 8px;
            align-items: center;
        }
        
        .selection-actions.active {
            display: flex;
        }
        
        .action-btn {
            padding: 8px 16px;
            border-radius: 18px;
            border: none;
            background: #f0f2f5;
            color: #54656f;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .action-btn.primary {
            background: #00a884;
            color: #fff;
        }
        
        /* Phone highlight */
        .phone-highlight {
            background: #e7fce3;
            color: #008f72;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { width: 100%; max-width: none; }
            .chat-area { display: none; }
            .chat-area.active { display: flex; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 100; }
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
            <div class="user-avatar"><i class="bi bi-people-fill"></i></div>
            <div class="header-icons">
                <button type="button" class="icon-btn" onclick="selectAllParticipants()" title="Tout sélectionner">
                    <i class="bi bi-check-all"></i>
                </button>
                <button type="button" class="icon-btn" onclick="syncParticipants()" title="Synchroniser participants">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
                <a href="<?php echo site_url('whatsapp/envoyer'); ?>" class="icon-btn" title="Retour aux groupes">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
        
        <div class="stats-bar">
            <div class="stats-item">
                <i class="bi bi-people"></i>
                <span id="totalCount"><?php echo count($participants); ?> participants</span>
            </div>
            <div class="stats-item">
                <i class="bi bi-check-circle"></i>
                <span id="selectedCount">0 sélectionnés</span>
            </div>
        </div>
        
        <div class="search-container">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchParticipants" placeholder="Rechercher par nom ou numéro..." onkeyup="filterParticipants()">
            </div>
        </div>
        
        <div class="filter-tabs">
            <button class="filter-btn active" onclick="filterBy('all', this)">Tous</button>
            <button class="filter-btn" onclick="filterBy('admin', this)">Admins</button>
            <button class="filter-btn" onclick="filterBy('creator', this)">Créateurs</button>
            <button class="filter-btn" onclick="filterBy('member', this)">Membres</button>
        </div>
        
        <form id="envoiForm" style="display: flex; flex-direction: column; height: 100%;">
            <div class="participants-list" id="participantsList">
                <?php foreach ($participants as $p): ?>
                <div class="participant-item" 
                     data-phone="<?php echo htmlspecialchars($p->phone); ?>"
                     data-name="<?php echo htmlspecialchars($p->profile_name ?? ''); ?>"
                     data-rank="<?php echo $p->rank; ?>"
                     data-groupe="<?php echo htmlspecialchars($p->groupe_id); ?>"
                     onclick="toggleSelection(this, '<?php echo htmlspecialchars($p->phone); ?>')">
                    
                    <input type="checkbox" class="checkbox-select" name="phones[]" 
                           value="<?php echo htmlspecialchars($p->phone); ?>" 
                           id="cb_<?php echo htmlspecialchars(str_replace(['+', '@'], '', $p->phone)); ?>"
                           onchange="updateCounter()" onclick="event.stopPropagation()">
                    
                    <div class="participant-avatar">
                        <?php if (!empty($p->profile_name)): ?>
                            <span><?php echo strtoupper(substr($p->profile_name, 0, 2)); ?></span>
                        <?php else: ?>
                            <i class="bi bi-person"></i>
                        <?php endif; ?>
                        
                        <?php if ($p->is_creator): ?>
                            <span class="admin-badge creator-badge"><i class="bi bi-star-fill"></i></span>
                        <?php elseif ($p->is_admin): ?>
                            <span class="admin-badge"><i class="bi bi-shield-fill"></i></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="participant-info">
                        <div class="participant-header">
                            <span class="participant-name">
                                <?php echo htmlspecialchars($p->profile_name ?? 'Sans nom'); ?>
                            </span>
                        </div>
                        <div class="participant-phone">
                            <span class="phone-highlight"><?php echo htmlspecialchars($p->phone_formatted ?? $p->phone); ?></span>
                        </div>
                        <div class="participant-meta">
                            <span class="meta-badge <?php echo $p->rank; ?>">
                                <?php 
                                $rankLabels = ['creator' => 'Créateur', 'admin' => 'Admin', 'member' => 'Membre'];
                                echo $rankLabels[$p->rank] ?? $p->rank;
                                ?>
                            </span>
                            <?php 
                            // Trouver le nom du groupe
                            $groupName = '';
                            foreach ($groupes as $g) {
                                if ($g['groupe_id'] === $p->groupe_id) {
                                    $groupName = $g['nom'];
                                    break;
                                }
                            }
                            ?>
                            <span class="group-tag" title="<?php echo htmlspecialchars($p->groupe_id); ?>">
                                <i class="bi bi-people-fill"></i> <?php echo htmlspecialchars($groupName ?? 'Groupe inconnu'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($participants)): ?>
                <div class="empty-state" style="padding: 40px;">
                    <i class="bi bi-inbox"></i>
                    <p>Aucun participant trouvé</p>
                    <small style="color: #8696a0;">Synchronisez d'abord vos groupes WhatsApp</small>
                </div>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Actions flottantes -->
        <div class="selection-actions" id="selectionActions">
            <span id="selectionText" style="color: #667781; font-size: 13px;">0 sélectionnés</span>
            <button class="action-btn" onclick="clearSelection()">
                <i class="bi bi-x-lg"></i> Annuler
            </button>
            <button class="action-btn primary" onclick="scrollToChat()">
                <i class="bi bi-chat-dots"></i> Envoyer
            </button>
        </div>
    </div>
    
    <!-- Chat Area -->
    <div class="chat-area" id="chatArea">
        
        <?php if ($resultat !== null): ?>
            <!-- AFFICHAGE RÉSULTAT -->
            <div class="chat-header">
                <div class="chat-header-info">
                    <div class="chat-avatar"><i class="bi bi-check-all"></i></div>
                    <div class="chat-title">
                        <span class="chat-name">Résultat de l'envoi</span>
                        <span class="chat-status">
                            <?php 
                            $stats = isset($resultat['response']) ? $resultat['response'] : $resultat;
                            echo ($stats['reussis'] ?? 0) . '/' . ($stats['total'] ?? 0) . ' messages envoyés';
                            ?>
                        </span>
                    </div>
                </div>
                <a href="<?php echo site_url('whatsapp/participants_envoyer'); ?>" class="icon-btn" title="Nouvel envoi">
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
                            <?php echo $success ? 'Envoi terminé !' : 'Échec de l\'envoi'; ?>
                        </h4>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
                            <small>Destinataires</small>
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
                        <h6 style="margin-bottom: 10px; color: #667781;">Détails par destinataire:</h6>
                        <?php foreach ($stats['details'] as $detail): ?>
                        <div style="padding: 8px; border-bottom: 1px solid #f0f2f5; font-size: 13px;">
                            <span style="color: <?php echo $detail['statut'] === 'succès' ? '#00a884' : '#ea0038'; ?>">
                                <i class="bi bi-<?php echo $detail['statut'] === 'succès' ? 'check-circle' : 'x-circle'; ?>"></i>
                            </span>
                            <?php echo htmlspecialchars($detail['destinataire_id']); ?>
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
                    <div class="chat-avatar"><i class="bi bi-send-fill"></i></div>
                    <div class="chat-title">
                        <span class="chat-name">Envoi aux participants</span>
                        <span class="chat-status" id="selectionStatus">Aucun destinataire sélectionné</span>
                    </div>
                </div>
                <div class="header-icons">
                    <button type="button" class="icon-btn" onclick="showMobileSidebar()" title="Retour">
                        <i class="bi bi-arrow-left"></i>
                    </button>
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
            
            <!-- File Preview -->
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
                <div class="empty-state" id="emptyState">
                    <i class="bi bi-whatsapp"></i>
                    <p>Sélectionnez des participants dans la liste<br>pour commencer l'envoi</p>
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
                                  placeholder="Tapez votre message..." rows="1" 
                                  oninput="updatePreview()"></textarea>
                    </div>
                    
                    <button type="button" class="input-icon-btn" id="micBtn" onclick="startRecording()" title="Enregistrer un message vocal">
                        <i class="bi bi-mic-fill"></i>
                    </button>
                </div>
                
                <button type="button" class="send-btn" id="sendBtn" onclick="submitForm()" title="Envoyer aux participants sélectionnés">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
            
            <!-- Input Area - Mode Enregistrement Audio -->
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

<script src="https://cdn.jsdelivr.net/npm/opus-recorder@8.0.5/dist/recorder.min.js"></script>
<script>
// ==================== CONFIGURATION GLOBALE ====================
const CHUNK_SIZE = 1.5 * 1024 * 1024; // 1.5 MB
let selectedFile = null;
let currentUploadId = null;
let isUploading = false;
let abortController = null;
let selectedPhones = [];
let currentJobId = null;
let pollInterval = null;
let currentType = 'texte';
let currentFilter = 'all';

// ==================== VARIABLES AUDIO OGG OPUS ====================
let opusRecorder = null;
let audioChunks = [];
let recordingStartTime = null;
let recordingTimer = null;
let isRecording = false;
let audioStream = null;

function log(msg) {
    console.log('[WhatsApp Participants]', msg);
}

// ==================== GESTION DES PARTICIPANTS ====================

function toggleSelection(element, phone) {
    const checkbox = document.getElementById('cb_' + phone.replace(/[+@]/g, ''));
    if (!checkbox) return;
    
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        element.classList.add('selected');
        if (!selectedPhones.includes(phone)) selectedPhones.push(phone);
    } else {
        element.classList.remove('selected');
        selectedPhones = selectedPhones.filter(p => p !== phone);
    }
    
    updateCounter();
    updateSelectionActions();
}

function updateCounter() {
    const checkboxes = document.querySelectorAll('input[name="phones[]"]:checked');
    const count = checkboxes.length;
    
    // Mettre à jour les compteurs
    const selectedCountEl = document.getElementById('selectedCount');
    const selectionStatus = document.getElementById('selectionStatus');
    const selectionText = document.getElementById('selectionText');
    
    if (selectedCountEl) selectedCountEl.textContent = count + ' sélectionné' + (count > 1 ? 's' : '');
    if (selectionText) selectionText.textContent = count + ' sélectionné' + (count > 1 ? 's' : '');
    
    if (selectionStatus) {
        if (count === 0) {
            selectionStatus.innerHTML = 'Aucun destinataire sélectionné';
        } else {
            selectionStatus.innerHTML = `<span class="participant-counter">${count}</span> destinataire` + (count > 1 ? 's' : '') + ' sélectionné' + (count > 1 ? 's' : '');
        }
    }
    
    // Mettre à jour l'état vide
    const emptyState = document.getElementById('emptyState');
    const previewBubble = document.getElementById('previewBubble');
    if (emptyState) {
        const hasContent = count > 0 || selectedFile || (currentType === 'audio' && audioChunks.length > 0);
        emptyState.style.display = hasContent ? 'none' : 'block';
        if (previewBubble && document.getElementById('previewText')?.textContent) {
            previewBubble.style.display = hasContent ? 'block' : 'none';
        }
    }
}

function updateSelectionActions() {
    const actions = document.getElementById('selectionActions');
    const checkboxes = document.querySelectorAll('input[name="phones[]"]:checked');
    
    if (actions) {
        if (checkboxes.length > 0) {
            actions.classList.add('active');
        } else {
            actions.classList.remove('active');
        }
    }
}

function selectAllParticipants() {
    const visibleItems = document.querySelectorAll('.participant-item:not([style*="display: none"])');
    const allChecked = Array.from(visibleItems).every(item => {
        const cb = item.querySelector('.checkbox-select');
        return cb && cb.checked;
    });
    
    visibleItems.forEach(item => {
        const cb = item.querySelector('.checkbox-select');
        if (cb) {
            cb.checked = !allChecked;
            if (!allChecked) {
                item.classList.add('selected');
                if (!selectedPhones.includes(cb.value)) selectedPhones.push(cb.value);
            } else {
                item.classList.remove('selected');
            }
        }
    });
    
    if (allChecked) selectedPhones = [];
    updateCounter();
    updateSelectionActions();
}

function clearSelection() {
    document.querySelectorAll('input[name="phones[]"]').forEach(cb => {
        cb.checked = false;
        cb.closest('.participant-item')?.classList.remove('selected');
    });
    selectedPhones = [];
    updateCounter();
    updateSelectionActions();
}

function filterParticipants() {
    const searchInput = document.getElementById('searchParticipants');
    if (!searchInput) return;
    
    const term = searchInput.value.toLowerCase();
    const items = document.querySelectorAll('.participant-item');
    
    items.forEach(item => {
        const phone = item.getAttribute('data-phone')?.toLowerCase() || '';
        const name = item.getAttribute('data-name')?.toLowerCase() || '';
        const groupe = item.getAttribute('data-groupe')?.toLowerCase() || '';
        
        const matchesSearch = phone.includes(term) || name.includes(term) || groupe.includes(term);
        const matchesFilter = currentFilter === 'all' || item.getAttribute('data-rank') === currentFilter;
        
        item.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
    });
}

function filterBy(rank, btn) {
    currentFilter = rank;
    
    // Mettre à jour les boutons
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    
    // Appliquer le filtre
    filterParticipants();
}

function syncParticipants() {
    if (confirm('Synchroniser tous les participants avec l\'API WhatsApp ?')) {
        window.location.href = '<?php echo site_url('whatsapp/synchroniser_participants'); ?>';
    }
}

function scrollToChat() {
    const chatArea = document.getElementById('chatArea');
    if (chatArea && window.innerWidth <= 768) {
        chatArea.classList.add('active');
    }
    document.getElementById('messageInput')?.focus();
}

function showMobileSidebar() {
    const chatArea = document.getElementById('chatArea');
    if (chatArea) chatArea.classList.remove('active');
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
    
    log('Fichier sélectionné: ' + selectedFile.name + ' (' + sizeMB + ' MB)');
    
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

// ==================== ENREGISTREMENT AUDIO OGG OPUS ====================

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

        if (isRecording) {
            await cancelRecording();
        }

        audioChunks = [];
        audioStream = await navigator.mediaDevices.getUserMedia({ 
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true,
                channelCount: 1,
                sampleRate: 48000
            } 
        });

        opusRecorder = new Recorder({
            encoderPath: 'https://cdn.jsdelivr.net/npm/opus-recorder@8.0.5/dist/encoderWorker.min.js',
            encoderSampleRate: 48000,
            encoderApplication: 2048,
            encoderBitRate: 16000,
            encoderFrameSize: 20,
            numberOfChannels: 1,
            streamPages: true,
            maxFramesPerPage: 40
        });

        opusRecorder.ondataavailable = function(chunk) {
            audioChunks.push(chunk);
            log('Chunk OGG reçu: ' + chunk.byteLength + ' bytes');
        };

        opusRecorder.onstart = function() {
            log('Enregistrement OGG Opus démarré');
        };

        opusRecorder.onstop = function() {
            log('Enregistrement arrêté, total chunks: ' + audioChunks.length);
        };

        opusRecorder.onerror = function(err) {
            log('Erreur recorder: ' + err.message);
        };

        await opusRecorder.start(audioStream);
        
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

        log('Enregistrement OGG Opus actif');

    } catch (err) {
        console.error('Erreur:', err);
        log('Erreur microphone: ' + err.message);
        alert('Erreur microphone: ' + err.message);
        
        if (audioStream) {
            audioStream.getTracks().forEach(track => track.stop());
            audioStream = null;
        }
        
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
    if (!isRecording || !opusRecorder) return;
    
    isRecording = false;
    clearInterval(recordingTimer);
    
    try {
        await opusRecorder.stop();
        
        if (audioStream) {
            audioStream.getTracks().forEach(track => track.stop());
            audioStream = null;
        }
        
        const duration = Math.floor((Date.now() - recordingStartTime) / 1000);
        const mins = Math.floor(duration / 60).toString().padStart(2, '0');
        const secs = (duration % 60).toString().padStart(2, '0');
        
        if (audioChunks.length === 0) {
            throw new Error('Aucune donnée audio enregistrée');
        }
        
        const oggBlob = new Blob(audioChunks, { type: 'audio/ogg; codecs=opus' });
        const oggFile = new File([oggBlob], `note_vocale_${Date.now()}.ogg`, { 
            type: 'audio/ogg; codecs=opus' 
        });
        
        selectedFile = oggFile;
        currentType = 'audio';
        
        showFilePreview('Note vocale (OGG Opus)', `Durée: ${mins}:${secs} • ${(oggFile.size/1024).toFixed(1)} KB`, 'audio');
        log(`OGG Opus créé: ${oggFile.size} bytes, ${duration}s`);

    } catch (err) {
        console.error('Erreur OGG Opus:', err);
        log('Erreur conversion audio: ' + err.message);
        alert('Erreur conversion audio: ' + err.message);
    }

    resetAudioUI();
    audioChunks = [];
    recordingStartTime = null;
    opusRecorder = null;
}

function cancelRecording() {
    isRecording = false;
    clearInterval(recordingTimer);
    
    if (opusRecorder) {
        try {
            opusRecorder.stop();
        } catch(e) {}
        opusRecorder = null;
    }
    
    if (audioStream) {
        audioStream.getTracks().forEach(track => track.stop());
        audioStream = null;
    }
    
    resetAudioState();
    log('Enregistrement annulé');
}

function resetAudioState() {
    audioChunks = [];
    recordingStartTime = null;
    isRecording = false;
    opusRecorder = null;
    audioStream = null;
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

// ==================== PRÉVISUALISATION ====================

function updatePreview() {
    const input = document.getElementById('messageInput');
    if (!input) return;
    
    const text = input.value.trim();
    const bubble = document.getElementById('previewBubble');
    const previewText = document.getElementById('previewText');
    
    if (previewText) {
        previewText.textContent = text || '(Aucun message)';
    }
    if (bubble) {
        bubble.style.display = text ? 'block' : 'none';
    }
    
    updateCounter();
}

// ==================== ENVOI PAR CHUNKS ====================

async function submitForm() {
    const checkboxes = document.querySelectorAll('input[name="phones[]"]:checked');
    
    if (checkboxes.length === 0) {
        alert('Veuillez sélectionner au moins un participant');
        return;
    }
    
    if (currentType !== 'texte' && !selectedFile) {
        alert('Veuillez sélectionner un fichier ou enregistrer un audio');
        return;
    }
    
    const messageInput = document.getElementById('messageInput');
    const message = messageInput ? messageInput.value.trim() : '';
    const phones = Array.from(checkboxes).map(cb => cb.value);
    
    const sendBtn = document.getElementById('sendBtn');
    if (sendBtn) sendBtn.disabled = true;
    isUploading = true;
    
    try {
        if (currentType === 'texte') {
            await envoyerTexte(phones, message);
        } else {
            const typeToSend = selectedFile?.type?.includes('ogg') ? 'audio' : currentType;
            log('Envoi fichier type: ' + typeToSend + ', MIME: ' + selectedFile?.type);
            await envoyerFichierChunks(phones, message, typeToSend, selectedFile);
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

async function envoyerTexte(phones, message) {
    showUploadProgress(0, 'Envoi des messages...', 'bi-chat-text-fill');
    
    const formData = new FormData();
    phones.forEach(phone => formData.append('phones[]', phone));
    formData.append('type_envoi', 'texte');
    formData.append('message', message);
    formData.append('delai', '1000');
    
    const response = await fetch('<?php echo site_url('whatsapp/traiter_envoi_participants'); ?>', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    if (!result.success) throw new Error(result.error);
    
    currentJobId = result.job_id;
    startPolling(result.job_id);
}

async function envoyerFichierChunks(phones, message, type, file) {
    const totalSize = file.size;
    const totalChunks = Math.ceil(totalSize / CHUNK_SIZE);
    const uploadId = 'whapi_participants_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    currentUploadId = uploadId;
    
    abortController = new AbortController();
    
    log(`Upload démarré: ${totalChunks} chunks, type: ${type}`);
    showUploadProgress(0, 'Préparation...', 'bi-cloud-arrow-up');
    
    const isVoiceMessage = file.type.includes('ogg') && type === 'audio';
    
    const initResponse = await fetch('<?php echo site_url('whatsapp/init_chunk_upload_participants'); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            upload_id: uploadId,
            filename: file.name,
            filesize: totalSize,
            filetype: file.type,
            total_chunks: totalChunks,
            phones: phones,
            message: message,
            type_envoi: type,
            voice: isVoiceMessage
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
                const chunkResponse = await fetch('<?php echo site_url('whatsapp/upload_chunk_participants'); ?>', {
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
    
    const finalizeResponse = await fetch('<?php echo site_url('whatsapp/finalize_and_send_participants'); ?>', {
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
        fetch('<?php echo site_url('whatsapp/check_status_participants/'); ?>' + jobId)
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
                    window.location.href = '<?php echo site_url('whatsapp/resultat_participants/'); ?>' + jobId;
                } else {
                    const progress = data.progress || Math.min(10 + attempts * 2, 95);
                    const text = data.current_phone 
                        ? `Envoi à ${data.current_phone}...` 
                        : 'Envoi en cours...';
                    
                    updateUploadProgress(
                        progress,
                        text,
                        `${data.result?.reussis || 0} / ${data.result?.total || '?'} destinataires`,
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
    
    // Initialiser le compteur
    updateCounter();
});
</script>

</body>
</html>