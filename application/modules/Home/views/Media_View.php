<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'NUFOTEC' ?> - Media</title>
    <link rel="icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* YouTube Dark Mode Colors */
        :root {
            --yt-black: #0f0f0f;
            --yt-white: #ffffff;
            --yt-gray-100: #f1f1f1;
            --yt-gray-200: #e5e5e5;
            --yt-gray-300: #d4d4d4;
            --yt-gray-400: #aaaaaa;
            --yt-gray-500: #717171;
            --yt-gray-600: #606060;
            --yt-gray-700: #3d3d3d;
            --yt-gray-800: #272727;
            --yt-gray-900: #212121;
            --yt-red: #ff0000;
            --yt-blue: #3ea6ff;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: var(--yt-black); 
            color: var(--yt-gray-100);
            overflow-x: hidden;
        }

        /* ============================================
           NAVBAR YOUTUBE STYLE - Responsive Search
           ============================================ */
        .navbar {
            background: var(--yt-black);
            border-bottom: 1px solid var(--yt-gray-800);
            padding: 0.5rem 1rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            height: 56px;
        }
        
        .navbar .container-fluid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
            gap: 1rem;
        }
        
        /* Logo Section */
        .logo-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        
        .menu-icon {
            background: transparent;
            border: none;
            color: var(--yt-gray-100);
            font-size: 1.5rem;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 50%;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        
        .menu-icon:hover {
            background: var(--yt-gray-800);
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            flex-shrink: 0;
        }
        
        .youtube-icon {
            color: var(--yt-red);
            font-size: 1.8rem;
        }
        
        .brand-name {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--yt-gray-100);
            letter-spacing: -0.5px;
            white-space: nowrap;
        }
        
        .brand-subname {
            font-size: 0.7rem;
            color: var(--yt-gray-500);
            margin-left: 0.25rem;
        }

        /* ============================================
           SEARCH BAR - Responsive Design
           ============================================ */
        .search-container {
            flex: 1;
            max-width: 640px;
            margin: 0 2rem;
            display: flex;
            justify-content: center;
        }
        
        .search-form {
            display: flex;
            width: 100%;
            max-width: 640px;
        }
        
        .search-input {
            background: var(--yt-gray-900);
            border: 1px solid var(--yt-gray-700);
            border-radius: 40px 0 0 40px;
            color: var(--yt-gray-100);
            padding: 0.5rem 1rem;
            width: 100%;
            font-size: 0.9rem;
            outline: none;
            height: 40px;
        }
        
        .search-input:focus {
            border-color: var(--yt-blue);
            background: var(--yt-black);
        }
        
        .search-btn {
            background: var(--yt-gray-800);
            border: 1px solid var(--yt-gray-700);
            border-left: none;
            border-radius: 0 40px 40px 0;
            color: var(--yt-gray-100);
            padding: 0 1.25rem;
            cursor: pointer;
            transition: background 0.2s;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .search-btn:hover {
            background: var(--yt-gray-700);
        }

        /* Search Toggle (Mobile) */
        .search-toggle {
            display: none;
            background: transparent;
            border: none;
            color: var(--yt-gray-100);
            font-size: 1.2rem;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 50%;
        }
        
        .search-toggle:hover {
            background: var(--yt-gray-800);
        }

        /* Mobile Search Overlay */
        .mobile-search-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--yt-black);
            padding: 0.5rem 1rem;
            z-index: 1002;
            align-items: center;
            gap: 0.5rem;
            height: 56px;
        }
        
        .mobile-search-overlay.active {
            display: flex;
        }
        
        .mobile-search-overlay .search-form {
            flex: 1;
        }
        
        .back-btn {
            background: transparent;
            border: none;
            color: var(--yt-gray-100);
            font-size: 1.5rem;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* Right Icons */
        .nav-icons {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        
        .nav-icon {
            background: transparent;
            border: none;
            color: var(--yt-gray-100);
            font-size: 1.2rem;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 50%;
            transition: background 0.2s;
        }
        
        .nav-icon:hover {
            background: var(--yt-gray-800);
        }

        /* ============================================
           SIDEBAR
           ============================================ */
        .sidebar { 
            position: fixed; 
            left: 0; 
            top: 56px; 
            width: 240px; 
            height: calc(100vh - 56px); 
            background: var(--yt-black); 
            overflow-y: auto; 
            padding: 0.75rem 0; 
            z-index: 999; 
            transition: transform 0.3s ease;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--yt-gray-700);
            border-radius: 4px;
        }
        
        .sidebar-item { 
            display: flex; 
            align-items: center; 
            gap: 1.5rem; 
            padding: 0.5rem 1.5rem; 
            color: var(--yt-gray-100); 
            text-decoration: none; 
            cursor: pointer; 
            transition: background 0.2s;
            font-size: 0.875rem;
        }
        
        .sidebar-item:hover, .sidebar-item.active { 
            background: var(--yt-gray-800); 
        }
        
        .sidebar-item i { 
            font-size: 1.25rem; 
            width: 24px; 
        }
        
        .sidebar-section { 
            padding: 0.5rem 0; 
            border-bottom: 1px solid var(--yt-gray-800); 
        }
        
        .sidebar-title { 
            padding: 0.5rem 1.5rem; 
            font-size: 0.75rem; 
            font-weight: 600; 
            color: var(--yt-gray-500); 
            text-transform: uppercase; 
        }
        
        /* ============================================
           MAIN CONTENT
           ============================================ */
        .main-content { 
            margin-left: 240px; 
            padding: 1.5rem; 
            min-height: calc(100vh - 56px); 
            transition: margin-left 0.3s ease;
        }
        
        /* Media Grid YouTube Style */
        .media-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 1rem; 
        }
        
        .media-card { 
            cursor: pointer; 
            transition: transform 0.2s; 
        }
        
        .media-card:hover {
            transform: translateY(-2px);
        }
        
        .thumbnail-container { 
            position: relative; 
            border-radius: 12px;
            overflow: hidden;
            background: var(--yt-gray-900);
            aspect-ratio: 16 / 9;
        }
        
        .thumbnail-img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }
        
        .duration-badge { 
            position: absolute; 
            bottom: 8px; 
            right: 8px; 
            background: rgba(0,0,0,0.85); 
            color: var(--yt-gray-100); 
            padding: 2px 6px; 
            border-radius: 4px; 
            font-size: 0.7rem; 
            font-weight: 500; 
            z-index: 10; 
        }
        
        .card-info { 
            padding: 0.75rem 0.25rem; 
            display: flex;
            gap: 0.75rem;
        }
        
        .channel-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--yt-gray-700);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-details {
            flex: 1;
            min-width: 0;
        }
        
        .card-title { 
            font-size: 0.9rem; 
            font-weight: 500; 
            margin-bottom: 0.25rem; 
            line-height: 1.3; 
            display: -webkit-box; 
            -webkit-line-clamp: 2; 
            -webkit-box-orient: vertical; 
            overflow: hidden;
            color: var(--yt-gray-100);
        }
        
        .card-meta { 
            color: var(--yt-gray-500); 
            font-size: 0.75rem; 
        }

        /* ============================================
           RESPONSIVE BREAKPOINTS - YouTube Style
           ============================================ */
        
        /* Desktop Large (1280px+) */
        @media (min-width: 1280px) {
            .media-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        /* Desktop (1024px - 1279px) */
        @media (max-width: 1279px) and (min-width: 1024px) {
            .media-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .search-container {
                margin: 0 1rem;
            }
        }
        
        /* Tablet (768px - 1023px) */
        @media (max-width: 1023px) and (min-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .media-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .search-container {
                margin: 0 1rem;
                max-width: 500px;
            }
        }
        
        /* Mobile Large (480px - 767px) */
        @media (max-width: 767px) and (min-width: 480px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            .media-grid {
                grid-template-columns: 1fr;
            }
            .search-container {
                display: none; /* Hide desktop search */
            }
            .search-toggle {
                display: flex; /* Show mobile search icon */
            }
            .brand-subname {
                display: none;
            }
            .navbar {
                padding: 0.5rem;
            }
        }
        
        /* Mobile Small (0 - 479px) */
        @media (max-width: 479px) {
            .sidebar {
                transform: translateX(-100%);
                width: 200px;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 0.75rem;
            }
            .media-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            .search-container {
                display: none;
            }
            .search-toggle {
                display: flex;
            }
            .brand-name {
                font-size: 1rem;
            }
            .brand-subname {
                display: none;
            }
            .youtube-icon {
                font-size: 1.5rem;
            }
            .menu-icon {
                padding: 0.4rem;
                font-size: 1.3rem;
            }
            .navbar {
                padding: 0.4rem 0.5rem;
            }
            .nav-icon {
                padding: 0.4rem;
                font-size: 1.1rem;
            }
        }

        /* ============================================
           AUDIO PLAYER BAR
           ============================================ */
        .audio-player-bar { 
            position: fixed; 
            bottom: 0; 
            left: 240px; 
            right: 0; 
            background: var(--yt-gray-900); 
            border-top: 1px solid var(--yt-gray-800); 
            padding: 0.5rem 1rem; 
            display: none; 
            align-items: center; 
            gap: 1rem; 
            z-index: 1001; 
            height: 64px; 
        }
        
        .audio-player-bar.active { 
            display: flex; 
        }
        
        @media (max-width: 1023px) {
            .audio-player-bar {
                left: 0;
            }
        }

        /* ============================================
           UTILITIES
           ============================================ */
        .search-header { 
            margin-bottom: 1.5rem; 
        }
        
        .toast-container { 
            position: fixed; 
            bottom: 80px; 
            right: 20px; 
            z-index: 9999; 
        }
        
        .empty-state { 
            text-align: center; 
            padding: 4rem 2rem; 
            color: var(--yt-gray-500); 
        }
        
        .empty-state i { 
            font-size: 4rem; 
            margin-bottom: 1rem; 
        }
    </style>
</head>
<body>

<!-- Mobile Search Overlay -->
<div class="mobile-search-overlay" id="mobileSearchOverlay">
    <button class="back-btn" onclick="closeMobileSearch()">
        <i class="bi bi-arrow-left"></i>
    </button>
    <form action="<?= base_url($lang . '/media/apiSearch') ?>" method="GET" class="search-form">
        <input type="text" name="q" class="search-input" placeholder="<?= t('search_placeholder') ?>" id="mobileSearchInput">
        <button class="search-btn" type="submit"><i class="bi bi-search"></i></button>
    </form>
</div>

<nav class="navbar">
    <div class="container-fluid">
        <div class="logo-wrapper">
            <button class="menu-icon d-md-none" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand" href="<?= base_url($lang . '/media') ?>">
                <?php 
                $site_logo = $this->Model->get_setting('site_logo');
                if (!empty($site_logo)): 
                ?>
                    <img src="<?= base_url('attachments/Configurations/' . $site_logo) ?>" alt="Logo NUFOTEC" class="logo-img" height="40">
                <?php endif; ?>
                <span class="brand-name"><?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC')) ?></span>
                <span class="brand-subname"><?= strtoupper($lang) ?></span>
            </a>
        </div>
        
        <!-- Desktop Search -->
        <div class="search-container">
            <form action="<?= base_url($lang . '/media/apiSearch') ?>" method="GET" class="search-form">
                <input type="text" name="q" class="search-input" placeholder="<?= t('search_placeholder') ?>" value="<?= isset($search_query) ? htmlspecialchars($search_query) : '' ?>">
                <button class="search-btn" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
        
        <div class="nav-icons">
            <!-- Mobile Search Toggle -->
            <button class="search-toggle" onclick="openMobileSearch()">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</nav>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-section">
        <a href="<?= base_url($lang . '/media') ?>" class="sidebar-item <?= empty($current_type) && empty($search_query) ? 'active' : '' ?>">
            <i class="bi bi-house-fill"></i><span><?= t('home') ?></span>
        </a>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-title"><?= t('categories') ?></div>
        
        <?php
        $types = [
            'video'    => ['icon' => 'camera-video-fill',   'label' => t('videos')],
            'audio'    => ['icon' => 'music-note-beamed',   'label' => t('audio')],
            'image'    => ['icon' => 'image-fill',          'label' => t('images')],
            'document' => ['icon' => 'file-earmark-text-fill', 'label' => t('documents')]
        ];
        ?>

        <?php foreach ($types as $type => $info): ?>
            <a href="javascript:void(0)" class="sidebar-item <?= (!empty($current_type) && $current_type === $type) ? 'active' : '' ?>" onclick="filterMedia('<?= $type ?>')">
                <i class="bi bi-<?= $info['icon'] ?>"></i><span><?= $info['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="sidebar-section">
        <a href="<?= base_url($lang) ?>" class="sidebar-item">
            <i class="bi bi-door-closed"></i><span><?= t('exit') ?></span>
        </a>
    </div>
</aside>

<main class="main-content">
    <?php if (!empty($search_query)): ?>
        <div class="search-header">
            <h5><?= t('results_for') ?> "<?= htmlspecialchars($search_query) ?>"</h5>
            <small class="text-secondary"><?= (int)$results_count ?> <?= t('videos_count') ?></small>
        </div>
    <?php endif; ?>

    <div class="media-grid">
        <?php if (!empty($medias)): ?>
            <?php foreach ($medias as $media): ?>
                <?= createMediaCard($media, $lang) ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="empty-state" style="display: <?= empty($medias) ? 'flex' : 'none' ?>; flex-direction: column; align-items: center;">
        <i class="bi bi-play-circle"></i>
        <h5><?= t('no_media') ?></h5>
        <small class="text-secondary"><?= t('no_media_subtitle') ?></small>
    </div>
</main>

<div class="audio-player-bar" id="audioPlayerBar">
    <div class="player-info">
        <img src="" alt="" class="player-thumb" id="playerThumb">
        <div class="player-details">
            <h4 id="playerTitle"><?= t('title') ?></h4>
        </div>
    </div>
    <div class="player-controls">
        <div class="control-buttons">
            <button class="control-btn" onclick="previousTrack()"><i class="bi bi-skip-start-fill"></i></button>
            <button class="control-btn play" onclick="togglePlay()" id="playPauseBtn"><i class="bi bi-play-fill"></i></button>
            <button class="control-btn" onclick="nextTrack()"><i class="bi bi-skip-end-fill"></i></button>
        </div>
        <div class="progress-container">
            <span id="currentTime">0:00</span>
            <div class="progress-bar" onclick="seekAudio(event)"><div class="progress-fill" id="progressFill"></div></div>
            <span id="totalTime">0:00</span>
        </div>
    </div>
    <button class="control-btn" onclick="toggleMute()"><i class="bi bi-volume-up-fill" id="volumeIcon"></i></button>
    <audio id="audioElement" preload="metadata"></audio>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let audioElement = document.getElementById('audioElement');
    let isPlaying = false;
    let mediaQueue = [];
    let currentQueueIndex = 0;

    if (audioElement) {
        audioElement.addEventListener('timeupdate', updateProgress);
        audioElement.addEventListener('ended', nextTrack);
        audioElement.addEventListener('loadedmetadata', function() {
            document.getElementById('totalTime').textContent = formatTime(audioElement.duration);
        });
    }

    function openMedia(mediaSlug) {
        window.location.href = '<?= base_url($lang . '/media/detail/') ?>' + mediaSlug;
    }

    function openMobileSearch() {
        document.getElementById('mobileSearchOverlay').classList.add('active');
        document.getElementById('mobileSearchInput').focus();
    }

    function closeMobileSearch() {
        document.getElementById('mobileSearchOverlay').classList.remove('active');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMobileSearch();
        }
    });

    function togglePlay() {
        if (!audioElement) return;
        if (isPlaying) audioElement.pause(); else audioElement.play();
        isPlaying = !isPlaying;
        updatePlayButton();
    }

    function updatePlayButton() {
        const btn = document.getElementById('playPauseBtn');
        if (btn) btn.innerHTML = isPlaying ? '<i class="bi bi-pause-fill"></i>' : '<i class="bi bi-play-fill"></i>';
    }

    function updateProgress() {
        if (!audioElement) return;
        const percent = (audioElement.currentTime / audioElement.duration) * 100;
        const fill = document.getElementById('progressFill');
        if (fill) fill.style.width = percent + '%';
        document.getElementById('currentTime').textContent = formatTime(audioElement.currentTime);
    }

    function seekAudio(e) {
        if (!audioElement) return;
        const rect = e.currentTarget.getBoundingClientRect();
        const x = e.clientX - rect.left;
        audioElement.currentTime = (x / rect.width) * audioElement.duration;
    }

    function previousTrack() { if (currentQueueIndex > 0) currentQueueIndex--; }
    function nextTrack() { if (currentQueueIndex < mediaQueue.length - 1) currentQueueIndex++; }
    function toggleMute() { if (!audioElement) return; audioElement.muted = !audioElement.muted; }

    function formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    function filterMedia(type) {
        window.location.href = type === 'all' ? '<?= base_url($lang . '/media') ?>' : '<?= base_url($lang . '/media/type/') ?>' + type;
    }

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
    }

    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'primary'} border-0`;
        toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
        container.appendChild(toast);
        new bootstrap.Toast(toast).show();
        setTimeout(() => toast.remove(), 3000);
    }
</script>
</body>
</html>

<?php
function createMediaCard($media, $lang) {
    $type = $media['type'];
    $identifier = !empty($media['slug']) ? $media['slug'] : $media['id_media'];
    
    // Extraction YouTube ID
    $youtubeId = $media['youtube_id'] ?? '';
    if (empty($youtubeId) && !empty($media['lien'])) {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $media['lien'], $matches);
        $youtubeId = $matches[1] ?? '';
    }
    
    // Miniature logic
    $thumbUrl = '';
    if (!empty($youtubeId)) {
        $thumbUrl = "https://img.youtube.com/vi/{$youtubeId}/mqdefault.jpg";
    } elseif (!empty($media['miniature']) && file_exists(FCPATH . $media['miniature'])) {
        $thumbUrl = base_url($media['miniature']);
    } elseif ($type === 'video' && !empty($media['fichier'])) {
        $thumb_path = FCPATH . 'attachments/Video/Thumbnails/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_thumb.jpg';
        $thumbUrl = file_exists($thumb_path) 
            ? base_url('attachments/Video/Thumbnails/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_thumb.jpg')
            : base_url('assets/images/video-default.jpg');
    } elseif ($type === 'audio' && !empty($media['fichier'])) {
        $cover_path = FCPATH . 'attachments/Audio/Covers/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_cover.jpg';
        $thumbUrl = file_exists($cover_path)
            ? base_url('attachments/Audio/Covers/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_cover.jpg')
            : base_url('assets/images/audio-default.png');
    } elseif ($type === 'image' && !empty($media['fichier_url'])) {
        $thumbUrl = $media['fichier_url'];
    } else {
        $defaults = [
            'video' => base_url('assets/images/video-default.jpg'),
            'audio' => base_url('assets/images/audio-default.png'),
            'image' => base_url('assets/images/image-default.jpg'),
            'document' => base_url('assets/images/document-default.jpg')
        ];
        $thumbUrl = $defaults[$type] ?? base_url('assets/images/default-thumbnail.jpg');
    }
    
    $duration = $media['duration_formatted'] ?? '0:00';
    $title = htmlspecialchars($media['titre'] ?? t('untitled'));
    $channel = htmlspecialchars($media['credits'] ?? $media['categorie'] ?? t('unknown_channel'));
    $views = number_format($media['views_count'] ?? 0);
    
    return '
    <div class="media-card" onclick="openMedia(\'' . addslashes($identifier) . '\')">
        <div class="thumbnail-container">
            <img src="' . $thumbUrl . '" class="thumbnail-img" loading="lazy" alt="' . $title . '">
            <span class="duration-badge">' . $duration . '</span>
        </div>
        <div class="card-info">
            <div class="channel-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="card-details">
                <div class="card-title">' . $title . '</div>
                <div class="card-meta">' . $channel . ' • ' . $views . ' ' . t('views') . '</div>
            </div>
        </div>
    </div>';
}
?>