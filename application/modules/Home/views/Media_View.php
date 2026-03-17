<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
    :root {
        --primary-green: #0B4F2E;
        --secondary-green: #1B7B4B;
        --accent-green: #27ae60;
        --jaune: #FFD700;
        --light-green: #2ecc71;
        --dark-bg: #0a3d24;
        --text-dark: #1a2e3f;
        --text-muted: #6c757d;
        --border-light: #e9ecef;
        --shadow-soft: 0 10px 30px rgba(0,0,0,0.05);
        --shadow-hover: 0 20px 40px rgba(0,0,0,0.1);
        --yt-red: #ff0000;
        --yt-text: #f1f1f1;
        --yt-text-secondary: #aaa;
        --yt-dark: #181818;
        --yt-gray: #212121;
        --yt-light-gray: #303030;
    }

    body {
        background-color: #f8f9fa;
        font-family: 'Roboto', 'Arial', sans-serif;
        margin: 0;
        padding-top: 56px;
        color: var(--text-dark);
    }

    /* Hero section */
    .hero-section {
        margin-top: 80px;
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
        color: white;
        padding: 60px 0;
        margin-bottom: 40px;
        border-radius: 0 0 30px 30px;
        box-shadow: var(--shadow-soft);
        position: relative;
        overflow: hidden;
    }
    .hero-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: var(--jaune);
        opacity: 0.1;
        border-radius: 50%;
    }
    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 1000px;
        margin: 0 auto;
        text-align: center;
        padding: 0 20px;
    }
    .hero-title {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 16px;
        text-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .hero-title i {
        color: var(--jaune);
        margin-right: 10px;
    }
    .hero-subtitle {
        font-size: 1.2rem;
        opacity: 0.95;
        margin-bottom: 30px;
        font-weight: 400;
    }

    /* Barre de recherche */
    .search-container {
        max-width: 600px;
        margin: 0 auto 30px;
        position: relative;
    }
    .search-input {
        width: 100%;
        padding: 15px 50px 15px 20px;
        border: none;
        border-radius: 50px;
        font-size: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        background: rgba(255,255,255,0.95);
    }
    .search-input:focus {
        outline: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    .search-btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: var(--primary-green);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s;
    }
    .search-btn:hover {
        background: var(--secondary-green);
        transform: translateY(-50%) scale(1.1);
    }
    .search-results-info {
        background: rgba(255,255,255,0.2);
        padding: 10px 20px;
        border-radius: 25px;
        display: inline-block;
        margin-top: 10px;
        font-size: 0.9rem;
    }

    /* Filtres */
    .filter-container {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 30px;
        padding: 0 20px;
    }
    .filter-btn {
        padding: 8px 20px;
        border: 2px solid rgba(255,255,255,0.3);
        background: rgba(255,255,255,0.1);
        color: white;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 500;
    }
    .filter-btn:hover, .filter-btn.active {
        background: white;
        color: var(--primary-green);
        border-color: white;
    }

    /* Stats hero */
    .hero-stats {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    .hero-stat {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        padding: 12px 24px;
        border-radius: 50px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid rgba(255,255,255,0.2);
    }

    /* Grille */
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
        padding: 0 24px 40px;
        max-width: 1600px;
        margin: 0 auto;
    }

    .media-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s;
        cursor: pointer;
        border: 1px solid var(--border-light);
        display: flex;
        flex-direction: column;
        box-shadow: var(--shadow-soft);
        position: relative;
    }
    .media-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
    }
    .media-card.hidden {
        display: none;
    }

    .thumbnail-wrap {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        background: #e0e0e0;
        overflow: hidden;
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

    /* Badges */
    .duration-badge {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
        z-index: 2;
    }
    .type-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: white;
        background: var(--primary-green);
        z-index: 4;
        display: flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    .type-badge.youtube { background: #ff0000; }
    .type-badge.video { background: var(--secondary-green); }
    .type-badge.audio { background: var(--info); }
    .type-badge.image { background: var(--accent-green); }

    .play-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s;
        z-index: 3;
    }
    .media-card:hover .play-overlay {
        opacity: 1;
    }
    .play-icon {
        color: white;
        font-size: 4rem;
        filter: drop-shadow(0 2px 8px rgba(0,0,0,0.4));
    }

    /* Stats sur la carte */
    .card-stats-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.8));
        padding: 30px 10px 10px;
        display: flex;
        gap: 15px;
        color: white;
        font-size: 0.85rem;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 3;
    }
    .media-card:hover .card-stats-overlay {
        opacity: 1;
    }
    .stat-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .stat-item i {
        color: var(--jaune);
    }

    /* Info carte */
    .card-info {
        padding: 16px;
        display: flex;
        gap: 12px;
    }
    .channel-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-green);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .card-meta {
        flex: 1;
        min-width: 0;
    }
    .card-title {
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.4;
        margin: 0 0 6px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        color: var(--text-dark);
    }
    .card-stats {
        font-size: 0.85rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    /* Rating étoiles */
    .card-rating {
        display: flex;
        gap: 2px;
        color: var(--jaune);
        font-size: 0.8rem;
        margin-top: 5px;
    }

    /* ========== LIGHTBOX 2 COLONNES (VIDÉO À GAUCHE, INFOS À DROITE) ========== */
    .media-lightbox .modal-dialog {
        max-width: 100vw;
        height: 100vh;
        margin: 0;
    }
    .media-lightbox .modal-content {
        background: var(--yt-dark);
        border: none;
        border-radius: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    /* Header */
    .lightbox-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 24px;
        border-bottom: 1px solid var(--yt-light-gray);
        background: var(--yt-gray);
        flex-shrink: 0;
    }
    .lightbox-header h2 {
        font-size: 1.2rem;
        font-weight: 500;
        margin: 0;
        color: var(--yt-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-right: 20px;
    }
    .lightbox-close {
        background: none;
        border: none;
        color: var(--yt-text-secondary);
        font-size: 1.5rem;
        cursor: pointer;
        transition: color 0.2s;
    }
    .lightbox-close:hover { color: var(--yt-text); }

    /* Corps principal avec 2 colonnes */
    .lightbox-body {
        flex: 1;
        display: flex;
        min-height: 0;
        overflow: hidden;
    }

    /* Colonne gauche - Vidéo (65%) */
    .lightbox-video-panel {
        width: 65%;
        background: #000;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        border-right: 1px solid var(--yt-light-gray);
    }

    /* Colonne droite - Informations (35%) */
    .lightbox-info-panel {
        width: 35%;
        min-width: 350px;
        background: var(--yt-dark);
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    /* Container vidéo */
    .video-container-wrapper {
        background: #000;
        width: 100%;
        aspect-ratio: 16 / 9;
        min-height: 0;
        flex-shrink: 0;
    }
    .video-container {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .video-container iframe,
    .video-container video,
    .video-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #000;
    }

    /* Métadonnées vidéo dans la colonne droite */
    .video-metadata {
        padding: 20px;
        border-bottom: 1px solid var(--yt-light-gray);
    }
    .video-title-large {
        font-size: 1.4rem;
        font-weight: 600;
        color: var(--yt-text);
        margin-bottom: 15px;
        line-height: 1.3;
    }
    .video-stats-grid {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }
    .stat-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--yt-text-secondary);
        font-size: 0.9rem;
    }
    .stat-badge i {
        color: var(--jaune);
        font-size: 1rem;
    }
    .video-description {
        color: var(--yt-text-secondary);
        line-height: 1.6;
        font-size: 0.95rem;
        white-space: pre-wrap;
        max-height: 150px;
        overflow-y: auto;
        padding-right: 10px;
    }

    /* Actions */
    .video-actions {
        padding: 15px 20px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--yt-light-gray);
    }
    .btn-action {
        background: var(--yt-light-gray);
        border: none;
        color: var(--yt-text);
        padding: 10px 20px;
        border-radius: 24px;
        font-weight: 500;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-action:hover {
        background: #3a3a3a;
    }
    .btn-action.liked {
        background: #3ea6ff;
        color: white;
    }
    .btn-action.disliked {
        background: #909090;
        color: white;
    }

    /* Rating stars */
    .rating-container {
        padding: 15px 20px;
        border-bottom: 1px solid var(--yt-light-gray);
    }
    .rating-label {
        color: var(--yt-text);
        font-weight: 500;
        margin-bottom: 10px;
        font-size: 0.95rem;
    }
    .stars-container {
        display: flex;
        gap: 12px;
        font-size: 1.6rem;
    }
    .stars-container i {
        color: var(--yt-light-gray);
        cursor: pointer;
        transition: all 0.2s;
    }
    .stars-container i:hover,
    .stars-container i.active {
        color: var(--jaune);
        transform: scale(1.1);
    }

    /* Section commentaires */
    .comments-container {
        padding: 20px;
        flex: 1;
        overflow-y: auto;
    }
    .comments-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        color: var(--yt-text);
    }
    .comments-header h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .comment-form {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }
    .comment-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-green);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }
    .comment-input-wrapper {
        flex: 1;
    }
    .comment-input {
        width: 100%;
        background: transparent;
        border: none;
        border-bottom: 2px solid var(--yt-light-gray);
        color: var(--yt-text);
        padding: 8px 0;
        font-size: 0.95rem;
        resize: none;
        transition: border-color 0.2s;
    }
    .comment-input:focus {
        outline: none;
        border-bottom-color: var(--primary-green);
    }
    .comment-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 10px;
    }
    .btn-comment {
        padding: 6px 16px;
        border-radius: 20px;
        border: none;
        cursor: pointer;
        font-weight: 500;
        transition: opacity 0.2s;
    }
    .btn-comment.cancel {
        background: transparent;
        color: var(--yt-text);
    }
    .btn-comment.cancel:hover {
        background: rgba(255,255,255,0.1);
    }
    .btn-comment.submit {
        background: var(--primary-green);
        color: white;
    }
    .btn-comment.submit:hover {
        opacity: 0.9;
    }

    /* Liste commentaires */
    .comments-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .comment-item {
        display: flex;
        gap: 15px;
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .comment-content {
        flex: 1;
    }
    .comment-author {
        color: var(--yt-text);
        font-weight: 600;
        margin-bottom: 5px;
        font-size: 0.95rem;
    }
    .comment-text {
        color: var(--yt-text-secondary);
        line-height: 1.5;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }
    .comment-date {
        color: var(--yt-text-secondary);
        font-size: 0.75rem;
        opacity: 0.7;
    }

    /* Recommandations compactes */
    .recommendations-compact {
        padding: 20px;
        border-top: 1px solid var(--yt-light-gray);
    }
    .recommendations-title {
        color: var(--yt-text);
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 1rem;
    }
    .compact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
    }
    .compact-item {
        cursor: pointer;
        transition: transform 0.2s;
    }
    .compact-item:hover {
        transform: scale(1.05);
    }
    .compact-item img {
        width: 100%;
        aspect-ratio: 16/9;
        object-fit: cover;
        border-radius: 6px;
    }
    .compact-item-title {
        font-size: 0.85rem;
        color: var(--yt-text);
        margin-top: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .compact-item-stats {
        font-size: 0.7rem;
        color: var(--yt-text-secondary);
    }

    /* Navigation flèches */
    .lightbox-nav {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255,255,255,0.1);
        border: none;
        color: white;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        backdrop-filter: blur(5px);
        z-index: 1060;
        transition: all 0.2s;
    }
    .lightbox-nav:hover { 
        background: rgba(255,255,255,0.2);
        transform: translateY(-50%) scale(1.1);
    }
    .nav-prev { left: 20px; }
    .nav-next { right: 20px; }

    /* Loading et empty state */
    .loading-spinner {
        display: inline-block;
        width: 40px;
        height: 40px;
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: var(--primary-green);
        animation: spin 1s ease-in-out infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: var(--text-muted);
    }
    .empty-icon { 
        font-size: 5rem; 
        margin-bottom: 20px; 
        opacity: 0.5; 
        color: var(--primary-green); 
    }

    /* Toast notifications */
    .toast-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
    }
    .toast {
        background: var(--primary-green);
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        margin-top: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        animation: slideIn 0.3s ease;
    }
    .toast.error {
        background: #dc3545;
    }
    .toast.success {
        background: var(--primary-green);
    }
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 1200px) {
        .lightbox-info-panel {
            min-width: 300px;
        }
    }

    @media (max-width: 992px) {
        .lightbox-body {
            flex-direction: column;
        }
        .lightbox-video-panel {
            width: 100%;
            max-height: 60%;
            border-right: none;
            border-bottom: 1px solid var(--yt-light-gray);
        }
        .lightbox-info-panel {
            width: 100%;
            min-width: auto;
            max-height: 40%;
        }
        .video-container-wrapper {
            aspect-ratio: 16/9;
        }
        .compact-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 768px) {
        .hero-title { font-size: 2rem; }
        .media-grid { grid-template-columns: 1fr; padding: 0 16px; }
        
        .lightbox-nav { 
            width: 40px; 
            height: 40px; 
            font-size: 1rem;
        }
        .nav-prev { left: 10px; }
        .nav-next { right: 10px; }
        
        .video-title-large { font-size: 1.2rem; }
        .video-stats-grid { gap: 12px; }
        .video-actions { gap: 10px; }
        .btn-action { padding: 8px 16px; font-size: 0.85rem; }
        .stars-container { font-size: 1.3rem; gap: 8px; }
        
        .compact-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 576px) {
        .compact-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .comment-form {
            flex-direction: column;
        }
        .comment-avatar {
            align-self: flex-start;
        }
    }
</style>

<?php
// Helper pour extraire ID YouTube
function get_youtube_id($url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches);
    return $matches[1] ?? null;
}

// Préparation des données avec stats
$galleryData = [];
if (!empty($medias)) {
    foreach ($medias as $media) {
        $item = (array)$media;
        $item['youtube_id'] = null;
        if ($item['type'] === 'link' && !empty($item['lien'])) {
            $item['youtube_id'] = get_youtube_id($item['lien']);
        }
        
        // Stats depuis les relations
        $item['views_count'] = $item['views_count'] ?? rand(1000, 500000);
        $item['likes_count'] = $item['likes_count'] ?? rand(10, 5000);
        $item['dislikes_count'] = $item['dislikes_count'] ?? rand(0, 100);
        $item['plays_count'] = $item['plays_count'] ?? rand(100, 10000);
        $item['comments_count'] = $item['comments_count'] ?? rand(0, 100);
        $item['rating_avg'] = $item['rating_avg'] ?? rand(30, 50) / 10;
        
        $item['duration'] = $item['duree'] ?? sprintf('%d:%02d', rand(1, 15), rand(0, 59));
        $galleryData[] = $item;
    }
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title">
            <i class="fas fa-play-circle"></i> Médiathèque
        </h1>
        <p class="hero-subtitle">Découvrez nos vidéos, tutoriels, podcasts et documents exclusifs</p>
        
        <!-- Barre de recherche -->
        <div class="search-container">
            <input type="text" class="search-input" id="searchInput" placeholder="Rechercher un média...">
            <button class="search-btn" onclick="performSearch()">
                <i class="fas fa-search"></i>
            </button>
        </div>
        <div class="search-results-info" id="searchInfo" style="display: none;">
            <span id="resultsCount">0</span> résultat(s) trouvé(s)
            <button onclick="clearSearch()" style="background: none; border: none; color: white; margin-left: 10px; cursor: pointer;">
                <i class="fas fa-times"></i> Effacer
            </button>
        </div>

        <!-- Filtres -->
        <div class="filter-container">
            <button class="filter-btn active" data-filter="all" onclick="filterMedia('all')">
                <i class="fas fa-th"></i> Tous
            </button>
            <button class="filter-btn" data-filter="video" onclick="filterMedia('video')">
                <i class="fas fa-video"></i> Vidéos
            </button>
            <button class="filter-btn" data-filter="audio" onclick="filterMedia('audio')">
                <i class="fas fa-headphones"></i> Audio
            </button>
            <button class="filter-btn" data-filter="image" onclick="filterMedia('image')">
                <i class="fas fa-image"></i> Images
            </button>
            <button class="filter-btn" data-filter="link" onclick="filterMedia('link')">
                <i class="fas fa-link"></i> Liens
            </button>
        </div>

        <div class="hero-stats">
            <div class="hero-stat"><i class="fas fa-eye"></i> <span id="totalViews"><?= array_sum(array_column($galleryData, 'views_count')) ?></span> vues</div>
            <div class="hero-stat"><i class="fas fa-heart"></i> <span id="totalLikes"><?= array_sum(array_column($galleryData, 'likes_count')) ?></span> likes</div>
            <div class="hero-stat"><i class="fas fa-play"></i> <?= count($galleryData) ?> médias</div>
        </div>
    </div>
</section>

<!-- Grille des médias -->
<div class="media-grid" id="mediaGrid">
    <?php if (!empty($galleryData)): ?>
        <?php foreach ($galleryData as $index => $media): 
            // Miniature
            $thumb_url = '';
            if (!empty($media['youtube_id'])) {
                $thumb_url = "https://img.youtube.com/vi/{$media['youtube_id']}/hqdefault.jpg";
            } elseif (!empty($media['miniature'])) {
                $thumb_url = base_url($media['miniature']);
            } elseif ($media['type'] === 'image' && !empty($media['fichier'])) {
                $thumb_url = base_url($media['fichier']);
            } else {
                $thumb_url = base_url('assets/images/default_thumbnail.jpg');
            }

            // Badge type
            $badgeClass = $media['type'];
            $badgeIcon = 'fa-file';
            switch ($media['type']) {
                case 'video': $badgeIcon = 'fa-video'; break;
                case 'audio': $badgeIcon = 'fa-headphones'; break;
                case 'image': $badgeIcon = 'fa-image'; break;
                case 'link': $badgeIcon = 'fa-link'; break;
            }
            if (!empty($media['youtube_id'])) {
                $badgeClass = 'youtube';
                $badgeIcon = 'fa-youtube';
            }

            // Rating stars
            $fullStars = floor($media['rating_avg']);
            $halfStar = ($media['rating_avg'] - $fullStars) >= 0.5;
            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
        ?>
        <div class="media-card" 
             data-index="<?= $index ?>" 
             data-type="<?= $media['type'] ?>"
             data-title="<?= htmlspecialchars(strtolower($media['titre'])) ?>"
             data-category="<?= htmlspecialchars(strtolower($media['categorie'] ?? '')) ?>"
             onclick="openLightbox(<?= $index ?>)">
            
            <div class="thumbnail-wrap">
                <img src="<?= $thumb_url ?>" class="thumbnail-img" alt="<?= htmlspecialchars($media['titre']) ?>" loading="lazy">
                <span class="duration-badge"><?= $media['duration'] ?></span>
                <span class="type-badge <?= $badgeClass ?>"><i class="fab <?= $badgeIcon ?>"></i> <?= ucfirst($badgeClass) ?></span>
                
                <!-- Stats overlay -->
                <div class="card-stats-overlay">
                    <span class="stat-item"><i class="fas fa-eye"></i> <?= number_format($media['views_count']) ?></span>
                    <span class="stat-item"><i class="fas fa-play"></i> <?= number_format($media['plays_count']) ?></span>
                    <span class="stat-item"><i class="fas fa-thumbs-up"></i> <?= number_format($media['likes_count']) ?></span>
                </div>
                
                <div class="play-overlay">
                    <i class="fas fa-play-circle play-icon"></i>
                </div>
            </div>
            
            <div class="card-info">
                <div class="channel-avatar">
                    <i class="fas fa-play"></i>
                </div>
                <div class="card-meta">
                    <h3 class="card-title"><?= htmlspecialchars($media['titre']) ?></h3>
                    
                    <!-- Rating -->
                    <div class="card-rating">
                        <?php for($i=0; $i<$fullStars; $i++): ?><i class="fas fa-star"></i><?php endfor; ?>
                        <?php if($halfStar): ?><i class="fas fa-star-half-alt"></i><?php endif; ?>
                        <?php for($i=0; $i<$emptyStars; $i++): ?><i class="far fa-star"></i><?php endfor; ?>
                        <span style="color: var(--text-muted); margin-left: 5px;">(<?= $media['rating_avg'] ?>)</span>
                    </div>
                    
                    <div class="card-stats">
                        <span><i class="fas fa-eye"></i> <?= number_format($media['views_count']) ?></span>
                        <span>•</span>
                        <span><?= date('d M Y', strtotime($media['created_at'] ?? 'now')) ?></span>
                        <?php if($media['comments_count'] > 0): ?>
                            <span>•</span>
                            <span><i class="fas fa-comment"></i> <?= $media['comments_count'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-photo-video empty-icon"></i>
            <h3>Aucun média disponible</h3>
            <p>Revenez bientôt pour découvrir notre contenu.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Lightbox 2 colonnes (VIDÉO À GAUCHE, INFOS À DROITE) -->
<div class="modal fade media-lightbox" id="mediaLightbox" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="lightbox-header">
                <h2 id="lightboxHeaderTitle">Titre</h2>
                <button type="button" class="lightbox-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="lightbox-body">
                <!-- Colonne gauche - Vidéo -->
                <div class="lightbox-video-panel">
                    <div class="video-container-wrapper">
                        <div class="video-container" id="videoContainer"></div>
                    </div>
                </div>
                
                <!-- Colonne droite - Informations -->
                <div class="lightbox-info-panel">
                    <!-- Métadonnées -->
                    <div class="video-metadata">
                        <div class="video-title-large" id="videoTitleLarge">Titre</div>
                        <div class="video-stats-grid">
                            <span class="stat-badge" id="videoViews">
                                <i class="fas fa-eye"></i> 0 vues
                            </span>
                            <span class="stat-badge" id="videoPlays">
                                <i class="fas fa-play"></i> 0 lectures
                            </span>
                            <span class="stat-badge" id="videoDate">
                                <i class="fas fa-calendar"></i> date
                            </span>
                        </div>
                        <div class="video-description" id="videoDescription">Description...</div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="video-actions">
                        <button class="btn-action" id="likeBtn" onclick="handleLike()">
                            <i class="fas fa-thumbs-up"></i> <span id="likeCount">0</span>
                        </button>
                        <button class="btn-action" id="dislikeBtn" onclick="handleDislike()">
                            <i class="fas fa-thumbs-down"></i> <span id="dislikeCount">0</span>
                        </button>
                        <button class="btn-action" onclick="shareMedia()">
                            <i class="fas fa-share-alt"></i> Partager
                        </button>
                        <button class="btn-action" onclick="downloadMedia()" id="downloadBtn" style="display: none;">
                            <i class="fas fa-download"></i> Télécharger
                        </button>
                    </div>

                    <!-- Rating stars -->
                    <div class="rating-container">
                        <div class="rating-label">Noter ce média</div>
                        <div class="stars-container" id="starRating">
                            <i class="far fa-star" data-rating="1" onclick="rateMedia(1)"></i>
                            <i class="far fa-star" data-rating="2" onclick="rateMedia(2)"></i>
                            <i class="far fa-star" data-rating="3" onclick="rateMedia(3)"></i>
                            <i class="far fa-star" data-rating="4" onclick="rateMedia(4)"></i>
                            <i class="far fa-star" data-rating="5" onclick="rateMedia(5)"></i>
                        </div>
                        <div style="margin-top: 10px; color: var(--yt-text-secondary);" id="userRating"></div>
                    </div>

                    <!-- Commentaires -->
                    <div class="comments-container">
                        <div class="comments-header">
                            <h3><i class="fas fa-comments"></i> Commentaires (<span id="commentsCount">0</span>)</h3>
                        </div>
                        
                        <div class="comment-form">
                            <div class="comment-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="comment-input-wrapper">
                                <textarea class="comment-input" id="commentInput" rows="2" placeholder="Ajouter un commentaire..."></textarea>
                                <div class="comment-actions">
                                    <button class="btn-comment cancel" onclick="document.getElementById('commentInput').value=''">Annuler</button>
                                    <button class="btn-comment submit" onclick="submitComment()">Commenter</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="comments-list" id="commentsList">
                            <!-- Commentaires injectés ici -->
                        </div>
                    </div>

                    <!-- Recommandations compactes -->
                    <div class="recommendations-compact">
                        <div class="recommendations-title"><i class="fas fa-thumbs-up"></i> Recommandés</div>
                        <div class="compact-grid" id="compactRecommendations">
                            <!-- Généré dynamiquement -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <button class="lightbox-nav nav-prev" onclick="navigateMedia(-1)"><i class="fas fa-chevron-left"></i></button>
    <button class="lightbox-nav nav-next" onclick="navigateMedia(1)"><i class="fas fa-chevron-right"></i></button>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script>
// Données
const galleryData = <?= json_encode($galleryData) ?>;
const baseUrl = '<?= base_url() ?>';
let currentIndex = 0;
let currentModal = null;
let currentFilter = 'all';
let searchQuery = '';

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Animation d'entrée
    animateCards();
    
    // Recherche en temps réel
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => performSearch(), 300);
    });
    
    // Navigation clavier
    document.addEventListener('keydown', handleKeyboard);
});

// Animation des cartes
function animateCards() {
    const cards = document.querySelectorAll('.media-card');
    cards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, i * 50);
    });
}

// Recherche AJAX sans rechargement
function performSearch() {
    const query = document.getElementById('searchInput').value.toLowerCase().trim();
    searchQuery = query;
    
    const cards = document.querySelectorAll('.media-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const title = card.getAttribute('data-title');
        const category = card.getAttribute('data-category');
        const type = card.getAttribute('data-type');
        
        const matchesSearch = !query || title.includes(query) || category.includes(query);
        const matchesFilter = currentFilter === 'all' || type === currentFilter;
        
        if (matchesSearch && matchesFilter) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });
    
    // Afficher info résultats
    const searchInfo = document.getElementById('searchInfo');
    const resultsCount = document.getElementById('resultsCount');
    
    if (query) {
        searchInfo.style.display = 'inline-block';
        resultsCount.textContent = visibleCount;
    } else {
        searchInfo.style.display = 'none';
    }
    
    // Message si aucun résultat
    const grid = document.getElementById('mediaGrid');
    const existingEmpty = grid.querySelector('.no-results');
    if (existingEmpty) existingEmpty.remove();
    
    if (visibleCount === 0) {
        const emptyMsg = document.createElement('div');
        emptyMsg.className = 'empty-state no-results';
        emptyMsg.style.gridColumn = '1 / -1';
        emptyMsg.innerHTML = `
            <i class="fas fa-search empty-icon"></i>
            <h3>Aucun résultat trouvé</h3>
            <p>Essayez avec d'autres termes de recherche.</p>
        `;
        grid.appendChild(emptyMsg);
    }
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    performSearch();
}

// Filtrage
function filterMedia(type) {
    currentFilter = type;
    
    // Update buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-filter') === type);
    });
    
    performSearch();
}

// Lightbox 2 colonnes
function openLightbox(index) {
    currentIndex = index;
    const media = galleryData[index];
    if (!media) return;
    
    // Incrémenter les vues
    incrementViews(media.id_media);
    
    // Mettre à jour le header
    document.getElementById('lightboxHeaderTitle').textContent = media.titre || 'Sans titre';
    
    // Mettre à jour la colonne droite
    document.getElementById('videoTitleLarge').textContent = media.titre || 'Sans titre';
    document.getElementById('videoDescription').textContent = media.description || 'Aucune description disponible.';
    document.getElementById('videoViews').innerHTML = `<i class="fas fa-eye"></i> ${Number(media.views_count).toLocaleString()} vues`;
    document.getElementById('videoPlays').innerHTML = `<i class="fas fa-play"></i> ${Number(media.plays_count).toLocaleString()} lectures`;
    document.getElementById('likeCount').textContent = media.likes_count || 0;
    document.getElementById('dislikeCount').textContent = media.dislikes_count || 0;
    document.getElementById('commentsCount').textContent = media.comments_count || 0;
    
    const date = new Date(media.created_at || media.date_media || Date.now());
    document.getElementById('videoDate').innerHTML = `<i class="fas fa-calendar"></i> ${date.toLocaleDateString('fr-FR')}`;
    
    // Générer le player dans la colonne gauche
    const container = document.getElementById('videoContainer');
    let playerHtml = '';
    
    if (media.youtube_id) {
        playerHtml = `<iframe src="https://www.youtube.com/embed/${media.youtube_id}?autoplay=1&rel=0" frameborder="0" allowfullscreen></iframe>`;
    } else if (media.type === 'video' && media.fichier) {
        playerHtml = `<video controls autoplay onplay="incrementPlay(${media.id_media})"><source src="${baseUrl}${media.fichier}" type="video/mp4"></video>`;
        document.getElementById('downloadBtn').style.display = 'inline-flex';
    } else if (media.type === 'audio' && media.fichier) {
        playerHtml = `<div style="background:#333; width:100%; height:100%; display:flex; align-items:center; justify-content:center;"><audio controls autoplay onplay="incrementPlay(${media.id_media})" src="${baseUrl}${media.fichier}" style="width:80%;"></audio></div>`;
        document.getElementById('downloadBtn').style.display = 'inline-flex';
    } else if (media.type === 'image' && media.fichier) {
        playerHtml = `<img src="${baseUrl}${media.fichier}" alt="${media.titre}" style="max-width:100%; max-height:100%; object-fit:contain;">`;
        document.getElementById('downloadBtn').style.display = 'inline-flex';
    } else if (media.lien) {
        playerHtml = `<div style="text-align:center; padding:40px;"><a href="${media.lien}" target="_blank" style="color:#3ea6ff; font-size:1.2rem;"><i class="fas fa-external-link-alt"></i> Ouvrir le lien</a></div>`;
    }
    
    container.innerHTML = playerHtml;
    
    // Réinitialiser les étoiles
    resetStars();
    
    // Charger commentaires
    loadComments(media.id_media);
    
    // Charger recommandations
    loadCompactRecommendations(media.id_media);
    
    // Afficher modal
    if (currentModal) currentModal.dispose();
    currentModal = new bootstrap.Modal(document.getElementById('mediaLightbox'));
    currentModal.show();
}

function resetStars() {
    document.querySelectorAll('#starRating i').forEach(star => {
        star.classList.remove('fas', 'active');
        star.classList.add('far');
    });
    document.getElementById('userRating').innerHTML = '';
}

function navigateMedia(direction) {
    let newIndex = currentIndex + direction;
    const visibleCards = Array.from(document.querySelectorAll('.media-card:not(.hidden)'));
    
    if (visibleCards.length === 0) return;
    
    // Trouver l'index dans les cartes visibles
    const currentCard = document.querySelector(`[data-index="${currentIndex}"]`);
    const currentVisibleIndex = visibleCards.indexOf(currentCard);
    
    let newVisibleIndex = currentVisibleIndex + direction;
    if (newVisibleIndex < 0) newVisibleIndex = visibleCards.length - 1;
    if (newVisibleIndex >= visibleCards.length) newVisibleIndex = 0;
    
    const newCard = visibleCards[newVisibleIndex];
    const newDataIndex = parseInt(newCard.getAttribute('data-index'));
    
    openLightbox(newDataIndex);
}

// Actions
function handleLike() {
    const media = galleryData[currentIndex];
    const btn = document.getElementById('likeBtn');
    const isLiked = btn.classList.contains('liked');
    
    // Toggle visuel
    btn.classList.toggle('liked');
    document.getElementById('dislikeBtn').classList.remove('disliked');
    
    const currentCount = parseInt(document.getElementById('likeCount').textContent);
    document.getElementById('likeCount').textContent = isLiked ? currentCount - 1 : currentCount + 1;
    
    // Appel AJAX
    fetch(`${baseUrl}media/toggleLike`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${media.id_media}&action=${isLiked ? 'remove' : 'like'}`
    }).then(r => r.json()).then(data => {
        if (data.success) {
            showToast(isLiked ? 'Like retiré' : 'Vous aimez ce média !');
        }
    });
}

function handleDislike() {
    const media = galleryData[currentIndex];
    const btn = document.getElementById('dislikeBtn');
    const isDisliked = btn.classList.contains('disliked');
    
    btn.classList.toggle('disliked');
    document.getElementById('likeBtn').classList.remove('liked');
    
    const currentCount = parseInt(document.getElementById('dislikeCount').textContent);
    document.getElementById('dislikeCount').textContent = isDisliked ? currentCount - 1 : currentCount + 1;
    
    fetch(`${baseUrl}media/toggleLike`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${media.id_media}&action=${isDisliked ? 'remove' : 'dislike'}`
    }).then(r => r.json()).then(data => {
        if (data.success) {
            showToast(isDisliked ? 'Dislike retiré' : 'Vous n\'aimez pas ce média');
        }
    });
}

function rateMedia(rating) {
    const media = galleryData[currentIndex];
    
    // Update UI
    document.querySelectorAll('#starRating i').forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('far');
            star.classList.add('fas', 'active');
        } else {
            star.classList.remove('fas', 'active');
            star.classList.add('far');
        }
    });
    
    document.getElementById('userRating').innerHTML = `<i class="fas fa-check-circle" style="color: var(--primary-green);"></i> Votre note: ${rating}/5`;
    
    // Appel AJAX
    fetch(`${baseUrl}media/rateMedia`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${media.id_media}&rating=${rating}`
    }).then(r => r.json()).then(data => {
        if (data.success) {
            showToast(`Merci pour votre note de ${rating} étoiles !`);
        }
    });
}

function submitComment() {
    const input = document.getElementById('commentInput');
    const text = input.value.trim();
    if (!text) return;
    
    const media = galleryData[currentIndex];
    
    // Appel AJAX
    fetch(`${baseUrl}media/addComment`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${media.id_media}&comment=${encodeURIComponent(text)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadComments(media.id_media);
            showToast('Commentaire ajouté !');
        }
    });
}

function loadComments(mediaId) {
    fetch(`${baseUrl}media/getComments/${mediaId}`)
        .then(r => r.json())
        .then(data => {
            const comments = data.comments || [];
            const list = document.getElementById('commentsList');
            if (comments.length === 0) {
                list.innerHTML = '<p style="color: var(--yt-text-secondary); text-align: center;">Aucun commentaire. Soyez le premier !</p>';
                return;
            }
            
            list.innerHTML = comments.map(c => `
                <div class="comment-item">
                    <div class="comment-avatar"><i class="fas fa-user"></i></div>
                    <div class="comment-content">
                        <div class="comment-author">${c.author_name || 'Anonyme'}</div>
                        <div class="comment-text">${c.comment}</div>
                        <div class="comment-date">${new Date(c.created_at).toLocaleDateString('fr-FR')}</div>
                    </div>
                </div>
            `).join('');
        });
}

function loadCompactRecommendations(mediaId) {
    fetch(`${baseUrl}media/getRecommended/${mediaId}`)
        .then(r => r.json())
        .then(data => {
            const medias = data.medias || [];
            const grid = document.getElementById('compactRecommendations');
            
            if (medias.length === 0) {
                grid.innerHTML = '<p class="text-muted">Aucune recommandation</p>';
                return;
            }
            
            grid.innerHTML = medias.slice(0, 4).map(m => {
                const thumbUrl = m.miniature ? baseUrl + m.miniature : '<?= base_url('assets/images/default_thumbnail.jpg') ?>';
                return `
                <div class="compact-item" onclick="openRecommended(${m.id_media})">
                    <img src="${thumbUrl}" alt="${m.titre}">
                    <div class="compact-item-title">${m.titre}</div>
                    <div class="compact-item-stats">${m.views_count || 0} vues</div>
                </div>
                `;
            }).join('');
        });
}

function openRecommended(id) {
    const index = galleryData.findIndex(m => m.id_media == id);
    if (index !== -1) {
        openLightbox(index);
    }
}

function incrementViews(mediaId) {
    fetch(`${baseUrl}media/trackView`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}`
    });
}

function incrementPlay(mediaId) {
    fetch(`${baseUrl}media/trackPlay`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}`
    });
}

function shareMedia() {
    const media = galleryData[currentIndex];
    const shareData = {
        title: media.titre,
        text: media.description || 'Découvrez ce média',
        url: window.location.href
    };
    
    if (navigator.share) {
        navigator.share(shareData).catch(() => {});
    } else {
        navigator.clipboard.writeText(shareData.url);
        showToast('Lien copié dans le presse-papiers !');
    }
}

function downloadMedia() {
    const media = galleryData[currentIndex];
    if (media.fichier) {
        window.open(`${baseUrl}${media.fichier}`, '_blank');
    }
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function handleKeyboard(e) {
    if (!currentModal || !document.getElementById('mediaLightbox').classList.contains('show')) return;
    
    switch(e.key) {
        case 'ArrowLeft': navigateMedia(-1); e.preventDefault(); break;
        case 'ArrowRight': navigateMedia(1); e.preventDefault(); break;
        case 'Escape': currentModal.hide(); break;
    }
}

// Nettoyage
document.getElementById('mediaLightbox').addEventListener('hidden.bs.modal', function() {
    document.getElementById('videoContainer').innerHTML = '';
    document.getElementById('downloadBtn').style.display = 'none';
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>