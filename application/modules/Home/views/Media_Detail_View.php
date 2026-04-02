<?php 
// Fonction pour obtenir l'URL correcte du fichier selon son type
function getMediaFileUrl($fichier, $type) {
    if (empty($fichier)) return '';
    
    // Si c'est une URL YouTube ou externe
    if (preg_match('/^https?:\/\//', $fichier)) {
        return $fichier;
    }
    
    $base_url = base_url();
    
    // Construire l'URL selon le type
    switch($type) {
        case 'video':
            // Essayer d'abord les originals, puis encoded
            $url = $base_url . 'attachments/Video/Originals/' . $fichier;
            // Vérifier si le fichier existe (côté serveur)
            $file_path = FCPATH . 'attachments/Video/Originals/' . $fichier;
            if (!file_exists($file_path)) {
                $url = $base_url . 'attachments/Video/Encoded/' . $fichier;
            }
            return $url;
            
        case 'audio':
            $url = $base_url . 'attachments/Audio/Originals/' . $fichier;
            $file_path = FCPATH . 'attachments/Audio/Originals/' . $fichier;
            if (!file_exists($file_path)) {
                $url = $base_url . 'attachments/Audio/Converted/' . $fichier;
            }
            return $url;
            
        case 'image':
            return $base_url . 'attachments/Images/' . $fichier;
            
        case 'document':
            return $base_url . 'attachments/Documents/' . $fichier;
            
        default:
            return $base_url . $fichier;
    }
}

// Fonction pour vérifier si le fichier existe physiquement
function isMediaFileExists($fichier, $type) {
    if (empty($fichier)) return false;
    
    // Si c'est une URL externe, on considère qu'elle existe
    if (preg_match('/^https?:\/\//', $fichier)) {
        return true;
    }
    
    $base = FCPATH;
    
    switch($type) {
        case 'video':
            $path = $base . 'attachments/Video/Originals/' . $fichier;
            if (file_exists($path)) return true;
            $path = $base . 'attachments/Video/Encoded/' . $fichier;
            return file_exists($path);
            
        case 'audio':
            $path = $base . 'attachments/Audio/Originals/' . $fichier;
            if (file_exists($path)) return true;
            $path = $base . 'attachments/Audio/Converted/' . $fichier;
            return file_exists($path);
            
        case 'image':
            return file_exists($base . 'attachments/Images/' . $fichier);
            
        case 'document':
            return file_exists($base . 'attachments/Documents/' . $fichier);
            
        default:
            return file_exists($base . $fichier);
    }
}

// Fonction pour obtenir la miniature
function getMediaThumbnail($media) {
    if (!empty($media['youtube_id'])) {
        return "https://img.youtube.com/vi/{$media['youtube_id']}/hqdefault.jpg";
    }
    
    if (!empty($media['miniature']) && file_exists(FCPATH . $media['miniature'])) {
        return base_url($media['miniature']);
    }
    
    // Pour les vidéos
    if ($media['type'] === 'video' && !empty($media['fichier'])) {
        $thumb_path = FCPATH . 'attachments/Video/Thumbnails/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_thumb.jpg';
        if (file_exists($thumb_path)) {
            return base_url('attachments/Video/Thumbnails/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_thumb.jpg');
        }
    }
    
    // Pour les audios
    if ($media['type'] === 'audio' && !empty($media['fichier'])) {
        $cover_path = FCPATH . 'attachments/Audio/Covers/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_cover.jpg';
        if (file_exists($cover_path)) {
            return base_url('attachments/Audio/Covers/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_cover.jpg');
        }
    }
    
    // Images par défaut
    $defaults = [
        'audio' => base_url('assets/images/audio-default.png'),
        'video' => base_url('assets/images/video-default.jpg'),
        'image' => base_url('assets/images/image-default.jpg'),
        'document' => base_url('assets/images/document-default.jpg'),
        'default' => base_url('assets/images/default-thumbnail.jpg')
    ];
    
    return $defaults[$media['type']] ?? $defaults['default'];
}
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($media['titre'] ?? 'Détail du média') ?> - <?= $this->Model->get_setting('site_name', 'NUFOTEC') ?></title>
    <meta property="og:title" content="<?= htmlspecialchars($media['titre'] ?? '') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($media['description'] ?? $media['credits'] ?? '') ?>">
    <meta property="og:image" content="<?= $media['thumbnail_url'] ?? base_url('assets/images/default-share.jpg') ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:type" content="article">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#0f0f0f">
    <link rel="icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* YouTube Dark Mode Variables */
        :root {
            --yt-bg-dark: #0f0f0f;
            --yt-bg-card: #212121;
            --yt-bg-hover: #2a2a2a;
            --yt-bg-header: #202020;
            --yt-text-primary: #ffffff;
            --yt-text-secondary: #aaaaaa;
            --yt-border: #3d3d3d;
            --yt-red: #ff0000;
            --yt-blue: #3ea6ff;
            --yt-gray: #606060;
            --safe-area-inset-bottom: env(safe-area-inset-bottom, 0px);
            --safe-area-inset-top: env(safe-area-inset-top, 0px);
        }
        
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        
        body { 
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: var(--yt-bg-dark); 
            color: var(--yt-text-primary);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        
        /* YouTube Style Navbar */
        .navbar {
            background: var(--yt-bg-dark);
            border-bottom: 1px solid var(--yt-border);
            padding: 0.5rem 1rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--yt-text-primary);
            text-decoration: none;
        }
        
        .navbar-brand i {
            color: var(--yt-red);
            font-size: 1.5rem;
        }
        
        /* Main Layout - YouTube Style */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem;
        }
        
        .two-column-layout {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        @media (min-width: 1024px) {
            .two-column-layout {
                flex-direction: row;
            }
            .main-column {
                flex: 2;
                min-width: 0;
            }
            .sidebar-column {
                flex: 1;
                min-width: 0;
            }
        }
        
        /* Video Container - YouTube Style */
        .video-container {
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
        }
        
        .video-container iframe,
        .video-container video {
            width: 100%;
            height: 100%;
            border: none;
            object-fit: contain;
        }
        
        .video-container .download-floating {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            z-index: 10;
            opacity: 0;
            transition: opacity 0.2s;
            background: rgba(0,0,0,0.7);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
        }
        
        .video-container:hover .download-floating {
            opacity: 1;
        }
        
        .video-container .download-floating:hover {
            background: var(--yt-blue);
            transform: scale(1.05);
        }
        
        /* Media Info - YouTube Style */
        .media-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0.75rem 0 0.5rem 0;
            line-height: 1.3;
        }
        
        .media-meta-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--yt-border);
            margin-bottom: 1rem;
        }
        
        .media-stats {
            display: flex;
            gap: 1rem;
            color: var(--yt-text-secondary);
            font-size: 0.875rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: transparent;
            border: none;
            border-radius: 40px;
            color: var(--yt-text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .action-btn:hover {
            background: var(--yt-bg-hover);
            color: var(--yt-text-primary);
        }
        
        .action-btn.active {
            color: var(--yt-blue);
        }
        
        .action-btn.disliked {
            color: var(--yt-blue);
        }
        
        .action-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Description Box */
        .description-box {
            background: var(--yt-bg-card);
            border-radius: 12px;
            padding: 1rem;
            margin: 1rem 0;
            cursor: pointer;
        }
        
        .description-box .channel-name {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .description-text {
            color: var(--yt-text-secondary);
            font-size: 0.875rem;
            line-height: 1.4;
        }
        
        .description-text.expanded {
            white-space: normal;
        }
        
        .description-text:not(.expanded) {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Comments Section */
        .comments-section {
            margin-top: 1.5rem;
        }
        
        .comments-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .comment-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--yt-bg-hover);
            flex-shrink: 0;
            overflow: hidden;
        }
        
        .comment-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .comment-input-wrapper {
            flex: 1;
        }
        
        .comment-input {
            width: 100%;
            background: transparent;
            border: none;
            border-bottom: 1px solid var(--yt-border);
            color: var(--yt-text-primary);
            padding: 0.5rem 0;
            resize: vertical;
            font-size: 0.875rem;
        }
        
        .comment-input:focus {
            outline: none;
            border-bottom-color: var(--yt-blue);
        }
        
        .comment-submit {
            background: var(--yt-blue);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 18px;
            cursor: pointer;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            transition: opacity 0.2s;
        }
        
        .comment-submit:hover {
            opacity: 0.9;
        }
        
        .comment-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }
        
        .comment-author {
            font-weight: 500;
            font-size: 0.8125rem;
            margin-bottom: 0.25rem;
        }
        
        .comment-text {
            font-size: 0.875rem;
            line-height: 1.4;
            word-break: break-word;
        }
        
        /* Sidebar Related Videos */
        .related-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .related-item {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: background 0.2s;
            padding: 0.5rem;
            border-radius: 8px;
        }
        
        .related-item:hover {
            background: var(--yt-bg-hover);
        }
        
        .related-thumb {
            width: 168px;
            height: 94px;
            border-radius: 8px;
            background-size: cover;
            background-position: center;
            flex-shrink: 0;
        }
        
        .related-info {
            flex: 1;
        }
        
        .related-title-sm {
            font-size: 0.875rem;
            font-weight: 500;
            margin: 0 0 0.25rem 0;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .related-meta {
            font-size: 0.75rem;
            color: var(--yt-text-secondary);
        }
        
        /* Audio Player */
        .audio-player {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
        }
        
        .audio-cover {
            width: 200px;
            height: 200px;
            border-radius: 12px;
            margin: 0 auto 1rem;
            background-size: cover;
            background-position: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        
        .audio-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .audio-artist {
            color: var(--yt-text-secondary);
            margin-bottom: 1rem;
        }
        
        .audio-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .audio-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .audio-btn:hover {
            transform: scale(1.05);
            background: rgba(255,255,255,0.2);
        }
        
        .audio-btn.play-pause {
            width: 56px;
            height: 56px;
            background: var(--yt-blue);
        }
        
        .progress-bar-custom {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .progress {
            cursor: pointer;
        }
        
        /* Image Viewer */
        .image-viewer {
            text-align: center;
            background: #000;
            border-radius: 12px;
            padding: 1rem;
            position: relative;
        }
        
        .image-viewer img {
            max-width: 100%;
            max-height: 500px;
            border-radius: 8px;
        }
        
        .image-viewer .download-floating {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(0,0,0,0.7);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .image-viewer .download-floating:hover {
            background: var(--yt-blue);
            transform: scale(1.05);
        }
        
        /* Toast Notifications */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast-custom {
            background: var(--yt-bg-card);
            border: 1px solid var(--yt-border);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-top: 0.5rem;
            animation: slideIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .spin {
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .related-item {
                flex-direction: column;
            }
            .related-thumb {
                width: 100%;
                aspect-ratio: 16/9;
            }
            .action-btn span:not(.count) {
                display: none;
            }
            .action-btn {
                padding: 0.5rem;
            }
            .audio-cover {
                width: 150px;
                height: 150px;
            }
        }
        
        /* Alert */
        .alert-secondary {
            background: var(--yt-bg-card);
            border: 1px solid var(--yt-border);
            color: var(--yt-text-secondary);
            padding: 1rem;
            border-radius: 12px;
        }
        
        .alert-secondary a {
            color: var(--yt-blue);
        }
        
        .text-secondary {
            color: var(--yt-text-secondary) !important;
        }
        
        .bg-dark {
            background: var(--yt-bg-card) !important;
        }
        
        .rounded-3 {
            border-radius: 12px !important;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container-fluid d-flex justify-content-between align-items-center">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= base_url('media') ?>">
        <?php 
        $site_logo = $this->Model->get_setting('site_logo');
        if (!empty($site_logo)): 
        ?>
            <img src="<?= base_url('attachments/Configurations/' . $site_logo) ?>" alt="Logo NUFOTEC" class="logo-img" height="35">
        <?php else: ?>
            <i class="bi bi-youtube"></i>
        <?php endif; ?>
        <span><?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC')) ?></span>
    </a>
    <div class="d-flex gap-2">
        <a href="<?= base_url('media') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-house"></i> <span class="d-none d-sm-inline">Accueil</span>
        </a>
    </div>
</div>
</nav>

<main class="main-content">
    <?php 
    // Fonction helper pour formater la taille
    function formatFileSize($bytes) {
        if (!$bytes) return '';
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 1) . ' Go';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' Mo';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' Ko';
        }
        return $bytes . ' octets';
    }
    
    if ($media): 
        $mediaSlug = !empty($media['slug']) ? $media['slug'] : $media['id_media'];
        $type = $media['type'] ?? 'autre';
        $sous_type = $media['sous_type'] ?? '';
        $fichier = $media['fichier_url'] ?? '';
        $youtube_id = $media['youtube_id'] ?? '';
        $lien = $media['lien'] ?? '';
        
        if (!empty($fichier) && !preg_match('/^https?:\/\//', $fichier)) {
            $fichier = base_url($fichier);
        }
        
        if (empty($youtube_id) && !empty($lien)) {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $lien, $matches);
            $youtube_id = $matches[1] ?? '';
        }
        
        $is_youtube_link = !empty($youtube_id);
        $is_downloadable = in_array($type, ['video', 'audio', 'image', 'document']) && !empty($media['fichier']);
    ?>
        <div class="two-column-layout">
            <div class="main-column">
                <!-- Media Player -->
                <?php if ($is_youtube_link): ?>
                    <div class="video-container">
                        <iframe 
                            src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($youtube_id) ?>?autoplay=1&rel=0&modestbranding=1&showinfo=0&controls=1&fs=1"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen>
                        </iframe>
                    </div>
                    
                <?php elseif ($type === 'video' && !empty($fichier)): ?>
                    <div class="video-container">
                        <video controls autoplay playsinline>
                            <source src="<?= htmlspecialchars($fichier) ?>" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture vidéo.
                        </video>
                        <button class="download-floating" onclick="downloadMedia('<?= htmlspecialchars($mediaSlug) ?>')">
                            <i class="bi bi-download"></i>
                        </button>
                    </div>
                    
                <?php elseif ($type === 'audio' && !empty($fichier)): ?>
                    <div class="audio-player">
                        <div class="audio-cover" style="background-image: url('<?= htmlspecialchars($media['cover_url'] ?? base_url('assets/images/audio-default.png')) ?>')"></div>
                        <div class="audio-title"><?= htmlspecialchars($media['titre']) ?></div>
                        <div class="audio-artist"><?= htmlspecialchars($media['artist'] ?? $media['credits'] ?? 'Artiste inconnu') ?></div>
                        <button class="btn btn-outline-light btn-sm mb-3" onclick="downloadMedia('<?= htmlspecialchars($mediaSlug) ?>')">
                            <i class="bi bi-download"></i> Télécharger
                        </button>
                        
                        <audio id="audioElement" src="<?= htmlspecialchars($fichier) ?>" preload="metadata"></audio>
                        <div class="audio-controls">
                            <button class="audio-btn" onclick="previousTrack()">
                                <i class="bi bi-skip-backward-fill"></i>
                            </button>
                            <button class="audio-btn play-pause" id="playPauseBtn" onclick="togglePlay()">
                                <i class="bi bi-play-fill"></i>
                            </button>
                            <button class="audio-btn" onclick="nextTrack()">
                                <i class="bi bi-skip-forward-fill"></i>
                            </button>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="d-flex justify-content-between small mb-1">
                                <span id="currentTime">0:00</span>
                                <span id="totalTime">0:00</span>
                            </div>
                            <div class="progress" style="height: 4px; background: rgba(255,255,255,0.3); cursor: pointer;" onclick="seekAudio(event)">
                                <div class="progress-bar bg-primary" id="progressFill" style="width: 0%;"></div>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($type === 'image' && !empty($fichier)): ?>
                    <div class="image-viewer">
                        <img src="<?= htmlspecialchars($fichier) ?>" alt="<?= htmlspecialchars($media['titre']) ?>" loading="lazy">
                        <button class="download-floating" onclick="downloadMedia('<?= htmlspecialchars($mediaSlug) ?>')">
                            <i class="bi bi-download"></i>
                        </button>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center p-5 bg-dark rounded-3">
                        <i class="bi bi-file-earmark" style="font-size: 4rem;"></i>
                        <h4 class="mt-2"><?= htmlspecialchars($media['titre']) ?></h4>
                        <?php if (!empty($lien) || !empty($fichier)): ?>
                            <a href="<?= htmlspecialchars($lien ?: $fichier) ?>" target="_blank" class="btn btn-primary mt-3">
                                <i class="bi bi-box-arrow-up-right"></i> Ouvrir
                            </a>
                        <?php endif; ?>
                        <?php if ($is_downloadable): ?>
                            <button class="action-btn" onclick="downloadMedia('<?= htmlspecialchars($mediaSlug) ?>')">
                                <i class="bi bi-download"></i> Télécharger
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Media Info -->
                <h1 class="media-title"><?= htmlspecialchars($media['titre']) ?></h1>
                
                <div class="media-meta-bar">
                    <div class="media-stats">
                        <span><i class="bi bi-eye"></i> <?= number_format($media['views_count'] ?? 0) ?> vues</span>
                        <span><i class="bi bi-hand-thumbs-up"></i> <?= number_format($media['likes_count'] ?? 0) ?></span>
                        <span><i class="bi bi-chat"></i> <?= number_format($media['comments_count'] ?? 0) ?> commentaires</span>
                        <?php if (!empty($media['telechargements'])): ?>
                            <span><i class="bi bi-download"></i> <?= number_format($media['telechargements']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="action-buttons">
                        <button class="action-btn <?= ($media['user_like_action'] ?? '') === 'like' ? 'active' : '' ?>" onclick="toggleLike(<?= (int)$media['id_media'] ?>)">
                            <i class="bi bi-hand-thumbs-up"></i> <span id="likeCount" class="count"><?= (int)($media['likes_count'] ?? 0) ?></span>
                        </button>
                        <button class="action-btn <?= ($media['user_like_action'] ?? '') === 'dislike' ? 'disliked' : '' ?>" onclick="toggleDislike(<?= (int)$media['id_media'] ?>)">
                            <i class="bi bi-hand-thumbs-down"></i> <span id="dislikeCount" class="count"><?= (int)($media['dislikes_count'] ?? 0) ?></span>
                        </button>
                        <button class="action-btn <?= ($media['is_favorite'] ?? 0) ? 'active' : '' ?>" onclick="toggleFavorite(<?= (int)$media['id_media'] ?>)">
                            <i class="bi bi-bookmark"></i> <span class="d-none d-md-inline">Favoris</span>
                        </button>
                        <button class="action-btn" onclick="shareMedia()">
                            <i class="bi bi-share"></i> <span class="d-none d-md-inline">Partager</span>
                        </button>
                        <?php if ($is_downloadable && $type !== 'audio'): ?>
                            <button class="action-btn" id="downloadBtn" onclick="downloadMedia('<?= htmlspecialchars($mediaSlug) ?>')">
                                <i class="bi bi-download"></i> <span class="d-none d-md-inline">Télécharger</span>
                                <?php if (!empty($media['taille'])): ?>
                                    <small class="text-secondary ms-1">(<?= formatFileSize($media['taille']) ?>)</small>
                                <?php endif; ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="description-box" onclick="toggleDescription()">
                    <div class="channel-name">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($media['credits'] ?? 'Chaîne inconnue') ?>
                    </div>
                    <div class="description-text" id="descriptionText">
                        <?= nl2br(htmlspecialchars($media['description'] ?? 'Aucune description disponible pour ce média.')) ?>
                    </div>
                    <small class="text-secondary mt-1 d-block" id="descriptionToggle">Afficher plus</small>
                </div>
                
                <!-- Comments -->
                <div class="comments-section">
                    <div class="comments-title"><i class="bi bi-chat-dots"></i> <?= (int)($media['comments_count'] ?? 0) ?> commentaires</div>
                    
                    <?php if (isset($user) && $user): ?>
                    <div class="comment-form">
                        <div class="comment-avatar">
                            <?php if (!empty($user['photo'])): ?>
                                <img src="<?= base_url('uploads/users/' . $user['photo']) ?>" alt="Avatar">
                            <?php else: ?>
                                <div class="default-avatar w-100 h-100 rounded-circle bg-primary d-flex align-items-center justify-content-center">
                                    <?= strtoupper(substr($user['prenom'] ?? 'U', 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="comment-input-wrapper">
                            <textarea class="comment-input" id="commentText" rows="1" placeholder="Ajouter un commentaire..."></textarea>
                            <button class="comment-submit d-none" id="commentSubmit" onclick="addComment(<?= (int)$media['id_media'] ?>)">
                                Commenter
                            </button>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-secondary">
                        <i class="bi bi-info-circle"></i> 
                        <a href="<?= base_url('Auth') ?>" class="text-decoration-none">Connectez-vous</a> pour laisser un commentaire.
                    </div>
                    <?php endif; ?>
                    
                    <div class="comment-list" id="commentList">
                        <?php if (!empty($comments)): ?>
                            <?php foreach($comments as $comment): ?>
                                <div class="comment-item">
                                    <div class="comment-avatar">
                                        <?php if (!empty($comment['photo'])): ?>
                                            <img src="<?= base_url('uploads/users/' . $comment['photo']) ?>" alt="Avatar">
                                        <?php else: ?>
                                            <div class="default-avatar w-100 h-100 rounded-circle bg-secondary d-flex align-items-center justify-content-center">
                                                <?= strtoupper(substr($comment['prenom'] ?? $comment['author_name'] ?? 'V', 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-content">
                                        <div class="comment-author">
                                            <?= htmlspecialchars(($comment['prenom'] ?? '') . ' ' . ($comment['nom'] ?? $comment['author_name'] ?? 'Visiteur')) ?>
                                            <span class="text-secondary ms-2"><?= $comment['created_at_formatted'] ?? date('d/m/Y H:i', strtotime($comment['created_at'])) ?></span>
                                        </div>
                                        <div class="comment-text"><?= nl2br(htmlspecialchars($comment['comment'])) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-secondary py-4">
                                <i class="bi bi-chat display-6"></i>
                                <p class="mt-2">Soyez le premier à commenter</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="sidebar-column">
                <div class="related-title"><i class="bi bi-collection-play"></i> À regarder ensuite</div>
                <?php if (!empty($recommended)): ?>
                    <?php foreach($recommended as $related): 
                        $relatedSlug = !empty($related['slug']) ? $related['slug'] : $related['id_media'];
                    ?>
                        <div class="related-item" onclick="window.location.href='<?= base_url('media/detail/'.$relatedSlug) ?>'">
                            <div class="related-thumb" style="background-image: url('<?= htmlspecialchars($related['thumbnail_url'] ?? base_url('assets/images/default-thumbnail.jpg')) ?>')"></div>
                            <div class="related-info">
                                <p class="related-title-sm"><?= htmlspecialchars($related['titre']) ?></p>
                                <div class="related-meta">
                                    <?= number_format($related['views_count'] ?? 0) ?> vues
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-secondary py-4">
                        <i class="bi bi-collection-play display-6"></i>
                        <p class="mt-2">Aucune recommandation pour le moment</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    <?php else: ?>
        <div class="text-center p-5">
            <i class="bi bi-exclamation-triangle display-1"></i>
            <h3 class="mt-3">Média non trouvé</h3>
            <p class="text-secondary">Le média que vous recherchez n'existe pas ou a été supprimé.</p>
            <a href="<?= base_url('media') ?>" class="btn btn-primary mt-3">
                <i class="bi bi-house"></i> Retour à l'accueil
            </a>
        </div>
    <?php endif; ?>
</main>

<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Configuration
const mediaId = <?= (int)($media['id_media'] ?? 0) ?>;
const mediaSlug = '<?= htmlspecialchars($mediaSlug ?? '') ?>';

// Audio Player
let audioElement = document.getElementById('audioElement');
let isPlaying = false;

if (audioElement) {
    audioElement.addEventListener('timeupdate', updateProgress);
    audioElement.addEventListener('ended', () => { 
        isPlaying = false; 
        updatePlayButton(); 
    });
    audioElement.addEventListener('loadedmetadata', () => {
        const totalTimeSpan = document.getElementById('totalTime');
        if (totalTimeSpan) {
            totalTimeSpan.textContent = formatTime(audioElement.duration);
        }
    });
}

function togglePlay() {
    if (!audioElement) return;
    if (isPlaying) {
        audioElement.pause();
    } else {
        audioElement.play();
    }
    isPlaying = !isPlaying;
    updatePlayButton();
}

function updatePlayButton() {
    const btn = document.getElementById('playPauseBtn');
    if (btn) {
        btn.innerHTML = isPlaying ? '<i class="bi bi-pause-fill"></i>' : '<i class="bi bi-play-fill"></i>';
    }
}

function updateProgress() {
    if (!audioElement) return;
    const percent = (audioElement.currentTime / audioElement.duration) * 100;
    const fill = document.getElementById('progressFill');
    if (fill) fill.style.width = percent + '%';
    const currentTimeSpan = document.getElementById('currentTime');
    if (currentTimeSpan) {
        currentTimeSpan.textContent = formatTime(audioElement.currentTime);
    }
}

function formatTime(seconds) {
    if (isNaN(seconds) || !isFinite(seconds)) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}

function seekAudio(e) {
    if (!audioElement) return;
    const rect = e.currentTarget.getBoundingClientRect();
    const percent = (e.clientX - rect.left) / rect.width;
    audioElement.currentTime = percent * audioElement.duration;
}

function previousTrack() { 
    showToast('Fonctionnalité à venir', 'info'); 
}

function nextTrack() { 
    showToast('Fonctionnalité à venir', 'info'); 
}

// Fonction de téléchargement corrigée pour mobile
function downloadMedia(identifier) {
    // Déterminer si c'est un ID numérique ou un slug
    const isNumeric = !isNaN(identifier) && !isNaN(parseFloat(identifier));
    const paramName = isNumeric ? 'id' : 'slug';
    
    // Construire l'URL de téléchargement
    const downloadUrl = '<?= base_url("media/downloader") ?>?' + paramName + '=' + encodeURIComponent(identifier);
    
    // Créer un lien temporaire pour forcer le téléchargement
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.setAttribute('download', ''); // Force le téléchargement
    link.style.display = 'none';
    document.body.appendChild(link);
    
    // Simuler le clic
    link.click();
    
    // Nettoyer
    document.body.removeChild(link);
    
    // Afficher le toast
    showToast('Téléchargement démarré !', 'success');
}

// Likes & Dislikes
function toggleLike(mediaId) {
    const btn = document.querySelector('[onclick*="toggleLike"]');
    if (!btn) return;
    const isLiked = btn.classList.contains('active');
    
    fetch('<?= base_url('media/apiToggleLike') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}&action=${isLiked ? 'remove' : 'like'}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const likeCount = document.getElementById('likeCount');
            const dislikeCount = document.getElementById('dislikeCount');
            if (likeCount) likeCount.textContent = data.likes;
            if (dislikeCount) dislikeCount.textContent = data.dislikes;
            btn.classList.toggle('active', !isLiked);
            const dislikeBtn = document.querySelector('[onclick*="toggleDislike"]');
            if (dislikeBtn) dislikeBtn.classList.remove('disliked');
            showToast(isLiked ? 'Like retiré' : 'Like ajouté', 'success');
        }
    })
    .catch(() => showToast('Erreur lors de l\'opération', 'error'));
}

function toggleDislike(mediaId) {
    const btn = document.querySelector('[onclick*="toggleDislike"]');
    if (!btn) return;
    const isDisliked = btn.classList.contains('disliked');
    
    fetch('<?= base_url('media/apiToggleLike') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}&action=${isDisliked ? 'remove' : 'dislike'}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const likeCount = document.getElementById('likeCount');
            const dislikeCount = document.getElementById('dislikeCount');
            if (likeCount) likeCount.textContent = data.likes;
            if (dislikeCount) dislikeCount.textContent = data.dislikes;
            btn.classList.toggle('disliked', !isDisliked);
            const likeBtn = document.querySelector('[onclick*="toggleLike"]');
            if (likeBtn) likeBtn.classList.remove('active');
            showToast(isDisliked ? 'Dislike retiré' : 'Dislike ajouté', 'success');
        }
    })
    .catch(() => showToast('Erreur lors de l\'opération', 'error'));
}

function toggleFavorite(mediaId) {
    fetch('<?= base_url('media/apiToggleFavorite') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const btn = document.querySelector('[onclick*="toggleFavorite"]');
            if (btn) btn.classList.toggle('active', data.is_favorite);
            showToast(data.message, 'success');
        } else if (data.need_login) {
            showToast('Veuillez vous connecter', 'warning');
            setTimeout(() => window.location.href = '<?= base_url('Auth') ?>', 1500);
        }
    })
    .catch(() => showToast('Erreur', 'error'));
}

// Comments
function addComment(mediaId) {
    const commentText = document.getElementById('commentText');
    if (!commentText) return;
    const comment = commentText.value.trim();
    
    if (!comment) {
        showToast('Écrivez un commentaire', 'warning');
        return;
    }
    
    fetch('<?= base_url('media/apiAddComment') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}&comment=${encodeURIComponent(comment)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Commentaire ajouté !', 'success');
            commentText.value = '';
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Erreur', 'error');
        }
    })
    .catch(() => showToast('Erreur lors de l\'ajout', 'error'));
}

// Description toggle
let descriptionExpanded = false;

function toggleDescription() {
    const desc = document.getElementById('descriptionText');
    const toggle = document.getElementById('descriptionToggle');
    if (!desc || !toggle) return;
    
    descriptionExpanded = !descriptionExpanded;
    if (descriptionExpanded) {
        desc.classList.add('expanded');
        toggle.textContent = 'Afficher moins';
    } else {
        desc.classList.remove('expanded');
        toggle.textContent = 'Afficher plus';
    }
}

// Share
function shareMedia() {
    if (navigator.share) {
        navigator.share({
            title: '<?= htmlspecialchars($media['titre'] ?? '') ?>',
            url: window.location.href
        }).catch(() => copyToClipboard());
    } else {
        copyToClipboard();
    }
}

function copyToClipboard() {
    navigator.clipboard.writeText(window.location.href);
    showToast('Lien copié !', 'success');
}

// Toast notifications
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = 'toast-custom';
    
    let icon = 'info-circle';
    let bgColor = '#212121';
    
    if (type === 'success') {
        icon = 'check-circle';
        bgColor = '#2e7d32';
    } else if (type === 'error') {
        icon = 'exclamation-triangle';
        bgColor = '#c62828';
    } else if (type === 'warning') {
        icon = 'exclamation-circle';
        bgColor = '#ed6c02';
    }
    
    toast.style.background = bgColor;
    toast.innerHTML = `
        <i class="bi bi-${icon}"></i>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Auto-show comment submit
const commentTextarea = document.getElementById('commentText');
if (commentTextarea) {
    commentTextarea.addEventListener('input', function() {
        const submit = document.getElementById('commentSubmit');
        if (submit) {
            submit.classList.toggle('d-none', !this.value.trim());
        }
    });
}

// Track view on load
if (mediaId) {
    fetch('<?= base_url('media/apiTrackView') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}`
    }).catch(() => {});
}
</script>
</body>
</html>