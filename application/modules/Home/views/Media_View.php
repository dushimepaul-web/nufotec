<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'NUFOTEC' ?> - Media</title>
    <!-- Favicône (affichée dans l’onglet du navigateur) -->
    <link rel="icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Tous vos styles existants restent identiques */
        :root {
            --bg-dark: #0f0f0f;
            --bg-card: #1a1a1a;
            --bg-hover: #2a2a2a;
            --text-primary: #ffffff;
            --text-secondary: #aaaaaa;
            --border-color: #333333;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-dark); color: var(--text-primary); overflow-x: hidden; }

        .navbar {
            background: rgba(15,15,15,0.98);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        
        .navbar .container-fluid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .logo-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }
        
        .site-logo {
            height: 40px;
            width: auto;
            object-fit: contain;
            border-radius: 4px;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #ff0000;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            margin: 0;
            padding: 0;
        }
        
        .brand-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        
        .brand-name {
            margin: 0;
            font-size: 0.9rem;
            color: #ffffff;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .brand-subname {
            font-size: 0.7rem;
            color: #aaaaaa;
            white-space: nowrap;
        }

        .search-container {
            position: relative;
            max-width: 600px;
            width: 100%;
            flex: 1;
            margin: 0 1rem;
        }
        
        .search-form {
            display: flex;
            width: 100%;
        }
        
        .search-input {
            background: #121212;
            border: 1px solid var(--border-color);
            border-radius: 20px 0 0 20px;
            color: white;
            padding: 0.5rem 1rem;
            width: 100%;
            font-size: 0.9rem;
            outline: none;
        }
        
        .search-input:focus {
            background: #121212;
            border-color: #3ea6ff;
            color: white;
            box-shadow: none;
        }
        
        .search-btn {
            background: #222;
            border: 1px solid var(--border-color);
            border-left: none;
            border-radius: 0 20px 20px 0;
            color: white;
            padding: 0.5rem 1.5rem;
            cursor: pointer;
        }
        
        .search-btn:hover {
            background: #333;
        }
        
        .menu-toggle {
            background: none;
            border: none;
            color: white;
            padding: 0.5rem;
            cursor: pointer;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar { 
            position: fixed; 
            left: 0; 
            top: 65px; 
            width: 240px; 
            height: calc(100vh - 65px); 
            background: var(--bg-dark); 
            border-right: 1px solid var(--border-color); 
            overflow-y: auto; 
            padding: 1rem 0; 
            z-index: 999; 
            transition: transform 0.3s ease;
        }
        
        .sidebar-item { 
            display: flex; 
            align-items: center; 
            gap: 1rem; 
            padding: 0.75rem 1.5rem; 
            color: var(--text-primary); 
            text-decoration: none; 
            cursor: pointer; 
            transition: 0.2s; 
        }
        
        .sidebar-item:hover, .sidebar-item.active { 
            background: var(--bg-hover); 
        }
        
        .sidebar-item i { 
            font-size: 1.25rem; 
            width: 24px; 
            text-align: center; 
        }
        
        .sidebar-section { 
            padding: 0.5rem 0; 
            border-bottom: 1px solid var(--border-color); 
        }
        
        .sidebar-title { 
            padding: 0.5rem 1.5rem; 
            font-size: 0.875rem; 
            font-weight: 600; 
            color: var(--text-secondary); 
            text-transform: uppercase; 
        }

        .main-content { 
            margin-left: 240px; 
            padding: 1.5rem; 
            min-height: calc(100vh - 65px); 
            transition: margin-left 0.3s ease;
        }

        .media-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 1.25rem; 
        }

        .media-card { 
            background: var(--bg-card); 
            border-radius: 12px; 
            overflow: hidden; 
            transition: transform 0.2s, box-shadow 0.2s; 
            cursor: pointer; 
            position: relative; 
            display: flex; 
            flex-direction: column; 
            height: 260px; 
            border: 1px solid var(--border-color); 
        }
        
        .media-card:hover { 
            transform: translateY(-4px); 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
            border-color: #555; 
        }

        .thumbnail-container { 
            position: relative; 
            height: 150px; 
            min-height: 150px; 
            overflow: hidden; 
            background: #000; 
            flex-shrink: 0; 
        }
        
        .thumbnail-img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: transform 0.3s; 
        }
        
        .media-card:hover .thumbnail-img { 
            transform: scale(1.05); 
        }

        .audio-thumb { 
            background: linear-gradient(135deg, #1db954 0%, #169c46 100%); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        
        .doc-thumb { 
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
        }
        
        .link-thumb { 
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
        }

        .audio-visualizer { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 3px; 
            height: 50px; 
        }
        
        .audio-bar { 
            width: 4px; 
            background: rgba(255,255,255,0.9); 
            border-radius: 2px; 
            animation: sound 1.2s ease-in-out infinite; 
        }
        
        .audio-bar:nth-child(1) { height: 20%; animation-delay: 0s; }
        .audio-bar:nth-child(2) { height: 35%; animation-delay: 0.1s; }
        .audio-bar:nth-child(3) { height: 50%; animation-delay: 0.2s; }
        .audio-bar:nth-child(4) { height: 65%; animation-delay: 0.3s; }
        .audio-bar:nth-child(5) { height: 80%; animation-delay: 0.4s; }
        
        @keyframes sound { 
            0%, 100% { transform: scaleY(0.6); } 
            50% { transform: scaleY(1); } 
        }

        .doc-icon, .link-icon { 
            font-size: 2.5rem; 
            color: white; 
            margin-bottom: 0.25rem; 
        }
        
        .doc-label, .link-label { 
            color: rgba(255,255,255,0.9); 
            font-size: 0.8rem; 
            font-weight: 500; 
        }

        .duration-badge { 
            position: absolute; 
            bottom: 8px; 
            right: 8px; 
            background: rgba(0,0,0,0.9); 
            color: white; 
            padding: 2px 6px; 
            border-radius: 4px; 
            font-size: 0.75rem; 
            font-weight: 600; 
            z-index: 10; 
        }
        
        .type-badge { 
            position: absolute; 
            top: 8px; 
            left: 8px; 
            background: rgba(0,0,0,0.85); 
            color: white; 
            padding: 3px 6px; 
            border-radius: 4px; 
            font-size: 0.65rem; 
            font-weight: 600; 
            text-transform: uppercase; 
            z-index: 10; 
        }

        .play-overlay { 
            position: absolute; 
            inset: 0; 
            background: rgba(0,0,0,0.6); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            opacity: 0; 
            transition: opacity 0.2s; 
            z-index: 5; 
        }
        
        .media-card:hover .play-overlay { 
            opacity: 1; 
        }
        
        .play-btn { 
            width: 44px; 
            height: 44px; 
            background: rgba(255,255,255,0.95); 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: #000; 
            font-size: 1.2rem; 
            transform: scale(0.8); 
            transition: transform 0.2s; 
        }
        
        .media-card:hover .play-btn { 
            transform: scale(1); 
        }

        .card-info { 
            padding: 0.75rem; 
            display: flex; 
            flex-direction: column; 
            flex: 1; 
            min-height: 0; 
        }
        
        .card-title { 
            font-size: 0.9rem; 
            font-weight: 600; 
            margin-bottom: 0.4rem; 
            line-height: 1.3; 
            height: 2.6em; 
            overflow: hidden; 
            text-overflow: ellipsis; 
            display: -webkit-box; 
            -webkit-line-clamp: 2; 
            -webkit-box-orient: vertical; 
            color: var(--text-primary); 
        }
        
        .card-meta { 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
            color: var(--text-secondary); 
            font-size: 0.75rem; 
            margin-bottom: 0.4rem; 
            height: 1.1em; 
            overflow: hidden; 
            white-space: nowrap; 
            text-overflow: ellipsis; 
        }
        
        .card-stats { 
            display: flex; 
            gap: 0.75rem; 
            margin-top: auto; 
            padding-top: 0.4rem; 
            font-size: 0.7rem; 
            color: var(--text-secondary); 
            border-top: 1px solid var(--border-color); 
        }
        
        .stat-item { 
            display: flex; 
            align-items: center; 
            gap: 0.2rem; 
        }

        .audio-player-bar { 
            position: fixed; 
            bottom: 0; 
            left: 240px; 
            right: 0; 
            background: #181818; 
            border-top: 1px solid var(--border-color); 
            padding: 0.75rem 1.5rem; 
            display: none; 
            align-items: center; 
            gap: 1.5rem; 
            z-index: 1001; 
            height: 70px; 
        }
        
        .audio-player-bar.active { 
            display: flex; 
        }
        
        .player-info { 
            display: flex; 
            align-items: center; 
            gap: 0.75rem; 
            width: 25%; 
            min-width: 180px; 
        }
        
        .player-thumb { 
            width: 44px; 
            height: 44px; 
            border-radius: 4px; 
            object-fit: cover; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            flex-shrink: 0; 
        }
        
        .player-details { 
            min-width: 0; 
            flex: 1; 
        }
        
        .player-details h4 { 
            font-size: 0.8rem; 
            margin: 0; 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
            color: white; 
        }
        
        .player-details p { 
            font-size: 0.65rem; 
            color: var(--text-secondary); 
            margin: 0; 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
        }
        
        .player-controls { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            gap: 0.2rem; 
            max-width: 500px; 
        }
        
        .control-buttons { 
            display: flex; 
            align-items: center; 
            gap: 0.75rem; 
        }
        
        .control-btn { 
            background: none; 
            border: none; 
            color: var(--text-secondary); 
            font-size: 1rem; 
            cursor: pointer; 
            transition: color 0.2s; 
            padding: 0.2rem; 
        }
        
        .control-btn:hover { 
            color: var(--text-primary); 
        }
        
        .control-btn.play { 
            width: 32px; 
            height: 32px; 
            background: white; 
            color: black; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        
        .progress-container { 
            width: 100%; 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
            font-size: 0.65rem; 
            color: var(--text-secondary); 
        }
        
        .progress-bar { 
            flex: 1; 
            height: 4px; 
            background: #4d4d4d; 
            border-radius: 2px; 
            cursor: pointer; 
            position: relative; 
        }
        
        .progress-fill { 
            height: 100%; 
            background: #1db954; 
            border-radius: 2px; 
            width: 0%; 
            position: relative; 
        }

        .search-header { 
            margin-bottom: 1.5rem; 
            padding: 1rem; 
            background: var(--bg-card); 
            border-radius: 12px; 
            border: 1px solid var(--border-color); 
        }
        
        .search-header h4 { 
            margin: 0; 
            color: var(--text-primary); 
        }
        
        .search-header span { 
            color: var(--text-secondary); 
            font-size: 0.9rem; 
        }

        .toast-container { 
            position: fixed; 
            top: 80px; 
            right: 20px; 
            z-index: 9999; 
        }
        
        .toast { 
            background: var(--bg-card); 
            border: 1px solid var(--border-color); 
            color: white; 
        }

        .empty-state { 
            text-align: center; 
            padding: 4rem 2rem; 
            color: var(--text-secondary); 
        }
        
        .empty-state i { 
            font-size: 4rem; 
            margin-bottom: 1rem; 
            opacity: 0.5; 
        }

        @media (max-width: 1200px) { 
            .sidebar { transform: translateX(-100%); } 
            .sidebar.open { transform: translateX(0); } 
            .main-content { margin-left: 0; } 
            .audio-player-bar { left: 0; }
        }
        
        @media (max-width: 768px) { 
            .media-grid { grid-template-columns: 1fr; } 
            .search-container { margin: 0 0.5rem; }
            .navbar .container-fluid { flex-wrap: wrap; }
            .logo-wrapper { order: 1; }
            .search-container { order: 3; margin: 0.5rem 0 0 0; max-width: 100%; }
            .menu-toggle { order: 2; }
        }
        
        @media (max-width: 480px) {
            .brand-name { font-size: 0.8rem; }
            .brand-subname { font-size: 0.6rem; }
            .site-logo { height: 32px; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="container-fluid">
            <div class="logo-wrapper">
                <img src="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'logo.png')) ?>" 
                     alt="<?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC')) ?>" 
                     class="site-logo">
                <a class="navbar-brand" href="<?= base_url('media') ?>">
                    <div class="brand-info">
                        <h1 class="brand-name"><?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC')) ?></h1>
                        <span class="brand-subname"><?= htmlspecialchars($this->Model->get_setting('span_site_name', 'Media Center')) ?></span>
                    </div>
                </a>
            </div>
            <div class="search-container">
                <form action="<?= base_url('media/apiSearch') ?>" method="GET" class="search-form">
                    <input type="text" name="q" class="search-input" placeholder="Rechercher des médias..." value="<?= isset($search_query) ? htmlspecialchars($search_query) : '' ?>">
                    <button class="search-btn" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <button class="menu-toggle" onclick="toggleSidebar()"><i class="bi bi-list fs-4"></i></button>
        </div>
    </nav>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-section">
            <a href="<?= base_url('media') ?>" class="sidebar-item <?= !isset($current_type) && !isset($search_query) ? 'active' : '' ?>">
                <i class="bi bi-house-fill"></i><span>Accueil</span>
            </a>
        </div>
        <div class="sidebar-section">
            <div class="sidebar-title">Catégories</div>
            <a href="#" class="sidebar-item <?= ($current_type ?? '') === 'video' ? 'active' : '' ?>" onclick="filterMedia('video')">
                <i class="bi bi-camera-video-fill text-danger"></i><span>Vidéos</span>
            </a>
            <a href="#" class="sidebar-item <?= ($current_type ?? '') === 'audio' ? 'active' : '' ?>" onclick="filterMedia('audio')">
                <i class="bi bi-music-note-beamed text-success"></i><span>Audio</span>
            </a>
            <a href="#" class="sidebar-item <?= ($current_type ?? '') === 'image' ? 'active' : '' ?>" onclick="filterMedia('image')">
                <i class="bi bi-image-fill text-warning"></i><span>Images</span>
            </a>
            <a href="#" class="sidebar-item <?= ($current_type ?? '') === 'document' ? 'active' : '' ?>" onclick="filterMedia('document')">
                <i class="bi bi-file-earmark-text-fill text-info"></i><span>Documents</span>
            </a>
            <a href="#" class="sidebar-item <?= ($current_type ?? '') === 'link' ? 'active' : '' ?>" onclick="filterMedia('link')">
                <i class="bi bi-link-45deg text-primary"></i><span>Liens</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
       <?php if (!empty($search_query)): ?>
    <div class="search-header">
        <h4>
            <i class="bi bi-search me-2"></i>
            Résultats pour "<?= htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') ?>"
        </h4>
        <span>
            <?= (int)$results_count ?> média<?= $results_count > 1 ? 's' : '' ?> trouvé<?= $results_count > 1 ? 's' : '' ?>
        </span>
    </div>
<?php endif; ?>

        <div class="media-grid">
            <?php if (!empty($medias)): ?>
                <?php foreach ($medias as $media): ?>
                    <?= createMediaCard($media) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="empty-state" style="display: <?= empty($medias) ? 'block' : 'none' ?>;">
            <i class="bi bi-inbox"></i>
            <h3><?= isset($search_query) ? 'Aucun résultat trouvé' : 'Aucun média disponible' ?></h3>
            <p><?= isset($search_query) ? 'Essayez avec d\'autres termes de recherche.' : 'Revenez plus tard pour découvrir du nouveau contenu.' ?></p>
        </div>
    </main>

    <div class="audio-player-bar" id="audioPlayerBar">
        <div class="player-info">
            <img src="" alt="" class="player-thumb" id="playerThumb">
            <div class="player-details">
                <h4 id="playerTitle">Titre</h4>
                <p id="playerArtist">Artiste</p>
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

        // Fonction qui redirige vers la page de détail avec le slug
        function openMedia(mediaSlug) {
            window.location.href = '<?= base_url('media/detail/') ?>' + mediaSlug;
        }

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
            window.location.href = type === 'all' ? '<?= base_url('media') ?>' : `<?= base_url('media/type/') ?>${type}`;
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
// Helper: Create media card - Utilise le slug pour le lien
function createMediaCard($media) {
    $type = $media['type'];
    $sous_type = $media['sous_type'] ?? null;
    
    // Utiliser le slug s'il existe, sinon l'ID
    $identifier = !empty($media['slug']) ? $media['slug'] : $media['id_media'];
    
    // Échapper le slug pour JavaScript (évite les problèmes avec les apostrophes, guillemets, etc.)
    $identifier_js = addslashes($identifier);
    
    // File URL
    $fileUrl = '';
    if (!empty($media['fichier_url'])) {
        $fileUrl = $media['fichier_url'];
    } elseif (!empty($media['fichier'])) {
        $fileUrl = base_url($media['fichier']);
    } elseif (!empty($media['lien'])) {
        $fileUrl = $media['lien'];
    }

    // YouTube ID
    $youtubeId = $media['youtube_id'] ?? '';
    if (empty($youtubeId) && !empty($media['lien'])) {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $media['lien'], $matches);
        $youtubeId = $matches[1] ?? '';
    }

    // Duration
    $duration = $media['duration_formatted'] ?? '0:00';
    if ($duration === '0:00' && !empty($media['duree'])) {
        $mins = floor($media['duree'] / 60);
        $secs = $media['duree'] % 60;
        $duration = sprintf('%d:%02d', $mins, $secs);
    }

    // Thumbnail content based on type
    $thumbClass = '';
    $typeLabel = '';
    $typeIcon = '';
    $thumbContent = '';
    $dataSousType = $sous_type ?? '';

    switch ($type) {
        case 'audio':
            $thumbClass = 'audio-thumb';
            $typeLabel = 'Audio';
            $typeIcon = 'music-note-beamed';
            $thumbContent = '<div class="audio-visualizer">' . str_repeat('<div class="audio-bar"></div>', 5) . '</div>';
            break;
            
        case 'video':
            $typeLabel = 'Vidéo';
            $typeIcon = 'camera-video';
            $thumbUrl = $youtubeId 
                ? "https://img.youtube.com/vi/{$youtubeId}/mqdefault.jpg"
                : ($media['thumbnail_url'] ?? base_url('assets/images/video-default.jpg'));
            $thumbContent = "<img src=\"{$thumbUrl}\" alt=\"\" class=\"thumbnail-img\" loading=\"lazy\">";
            break;
            
        case 'image':
            $typeLabel = 'Image';
            $typeIcon = 'image';
            $thumbUrl = $fileUrl ?: ($media['thumbnail_url'] ?? base_url('assets/images/image-default.jpg'));
            $thumbContent = "<img src=\"{$thumbUrl}\" alt=\"\" class=\"thumbnail-img\" loading=\"lazy\">";
            break;
            
        case 'autre':
            if ($sous_type === 'book' || (isset($media['fichier']) && str_ends_with($media['fichier'], '.pdf'))) {
                $thumbClass = 'doc-thumb';
                $typeLabel = 'PDF';
                $typeIcon = 'file-earmark-pdf';
                $thumbContent = '<i class="bi bi-file-earmark-pdf doc-icon"></i><span class="doc-label">PDF</span>';
                $dataSousType = 'book';
            } elseif ($sous_type === 'link') {
                $thumbClass = 'link-thumb';
                $typeLabel = 'Lien';
                $typeIcon = 'link-45deg';
                $thumbContent = '<i class="bi bi-link-45deg link-icon"></i><span class="link-label">Lien</span>';
            } else {
                $thumbClass = 'doc-thumb';
                $typeLabel = 'Fichier';
                $typeIcon = 'file-earmark';
                $thumbContent = '<i class="bi bi-file-earmark doc-icon"></i><span class="doc-label">Fichier</span>';
            }
            break;
            
        case 'link':
            $typeLabel = 'Lien';
            $typeIcon = 'link-45deg';
            $thumbUrl = $youtubeId 
                ? "https://img.youtube.com/vi/{$youtubeId}/mqdefault.jpg"
                : base_url('assets/images/link-default.jpg');
            $thumbContent = "<img src=\"{$thumbUrl}\" alt=\"\" class=\"thumbnail-img\" loading=\"lazy\">";
            break;
            
        default:
            $typeLabel = 'Média';
            $typeIcon = 'play-circle';
            $thumbContent = '<img src="' . ($media['thumbnail_url'] ?? base_url('assets/images/default-thumbnail.jpg')) . '" alt="" class=\"thumbnail-img\" loading=\"lazy\">';
    }

    $durationBadge = ($duration !== '0:00') ? "<span class=\"duration-badge\">{$duration}</span>" : '';
    
    $views = $media['views_count'] ?? 0;
    $likes = $media['likes_count'] ?? 0;
    $comments = $media['comments_count'] ?? 0;
    $rating = round($media['rating_avg'] ?? 0, 1);

    // CORRECTION IMPORTANTE : Utiliser addslashes pour échapper les caractères spéciaux
    return "
    <div class=\"media-card\" 
         data-id=\"{$media['id_media']}\"
         data-slug=\"" . htmlspecialchars($identifier) . "\"
         data-type=\"{$type}\"
         data-sous-type=\"{$dataSousType}\"
         data-title=\"" . htmlspecialchars($media['titre'] ?? 'Sans titre') . "\"
         data-artist=\"" . htmlspecialchars($media['credits'] ?? $media['artist'] ?? '') . "\"
         data-file=\"{$fileUrl}\"
         data-youtube-id=\"{$youtubeId}\"
         data-duration=\"{$duration}\"
         data-views=\"{$views}\"
         data-likes=\"{$likes}\"
         data-comments=\"{$comments}\"
         data-rating=\"{$rating}\"
         onclick=\"openMedia('" . addslashes($identifier) . "')\">
        
        <div class=\"thumbnail-container {$thumbClass}\">
            {$thumbContent}
            {$durationBadge}
            <div class=\"play-overlay\">
                <div class=\"play-btn\"><i class=\"bi bi-play-fill\"></i></div>
            </div>
            <span class=\"type-badge\"><i class=\"bi bi-{$typeIcon}\"></i> {$typeLabel}</span>
        </div>
        
        <div class=\"card-info\">
            <h3 class=\"card-title\">" . htmlspecialchars($media['titre'] ?? 'Sans titre') . "</h3>
            <div class=\"card-meta\">
                <span>" . htmlspecialchars($media['credits'] ?? $media['categorie'] ?? 'NUFOTEC') . "</span>
                <span>•</span>
                <span>" . number_format($views) . " vues</span>
            </div>
            <div class=\"card-stats\">
                <span class=\"stat-item\"><i class=\"bi bi-hand-thumbs-up\"></i> {$likes}</span>
                <span class=\"stat-item\"><i class=\"bi bi-chat\"></i> {$comments}</span>
                <span class=\"stat-item\"><i class=\"bi bi-star-fill\"></i> {$rating}</span>
            </div>
        </div>
    </div>";
}