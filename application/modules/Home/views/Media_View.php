<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
    /* ========== VARIABLES & RESET ========== */
    :root {
        --bg-primary: #0f0f0f;
        --bg-secondary: #1f1f1f;
        --bg-tertiary: #2a2a2a;
        --text-primary: #ffffff;
        --text-secondary: #aaaaaa;
        --text-tertiary: #717171;
        --accent-green: #00a884;
        --accent-youtube: #ff0000;
        --accent-facebook: #1877f2;
        --accent-whatsapp: #25d366;
        --border-color: #2a2a2a;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.15);
        --shadow-md: 0 4px 12px rgba(0,0,0,0.2);
        --transition: all 0.2s ease;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        -webkit-tap-highlight-color: transparent;
    }

    body {
        background: var(--bg-primary);
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        color: var(--text-primary);
        padding-top: 56px;
        line-height: 1.5;
    }

    /* ========== HEADER HERO ========== */
    .media-hero {
        background: linear-gradient(135deg, #0a3d24 0%, #0b4f2e 100%);
        padding: 40px 20px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    
    .media-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255,215,0,0.1);
        border-radius: 50%;
    }
    
    .media-hero-content {
        max-width: 600px;
        margin: 0 auto;
        text-align: center;
        position: relative;
        z-index: 2;
    }
    
    .media-hero h1 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .media-hero h1 i {
        font-size: 2rem;
        color: #ffd700;
    }
    
    .media-hero p {
        color: rgba(255,255,255,0.9);
        font-size: 0.9rem;
        margin-bottom: 24px;
    }
    
    /* ========== BARRE DE RECHERCHE (WhatsApp style) ========== */
    .media-search-wrapper {
        max-width: 500px;
        margin: 0 auto;
        position: relative;
    }
    
    .media-search-input {
        width: 100%;
        background: rgba(255,255,255,0.2);
        border: none;
        border-radius: 30px;
        padding: 12px 48px 12px 20px;
        color: white;
        font-size: 1rem;
        backdrop-filter: blur(10px);
        transition: var(--transition);
    }
    
    .media-search-input:focus {
        outline: none;
        background: rgba(255,255,255,0.3);
    }
    
    .media-search-input::placeholder {
        color: rgba(255,255,255,0.7);
    }
    
    .media-search-btn {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 8px;
    }
    
    /* Dropdown autocomplete */
    .media-search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--bg-secondary);
        border-radius: 20px;
        margin-top: 12px;
        max-height: 400px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
    }
    
    .media-search-dropdown.active {
        display: block;
        animation: slideDown 0.2s ease;
    }
    
    .media-search-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        cursor: pointer;
        transition: var(--transition);
        border-bottom: 1px solid var(--border-color);
    }
    
    .media-search-item:last-child {
        border-bottom: none;
    }
    
    .media-search-item:hover,
    .media-search-item.active {
        background: var(--bg-tertiary);
    }
    
    .media-search-item-thumb {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
    }
    
    .media-search-item-info {
        flex: 1;
    }
    
    .media-search-item-title {
        font-weight: 500;
        font-size: 0.9rem;
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .media-search-item-meta {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    .media-search-item-badge {
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .media-search-item-badge.video { background: var(--accent-youtube); color: white; }
    .media-search-item-badge.audio { background: var(--accent-whatsapp); color: black; }
    .media-search-item-badge.image { background: var(--accent-facebook); color: white; }
    
    .media-highlight {
        background: rgba(255,215,0,0.3);
        border-radius: 4px;
        padding: 0 2px;
    }
    
    /* ========== STATS HERO ========== */
    .media-stats {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    
    .media-stat {
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        padding: 8px 16px;
        border-radius: 40px;
        font-size: 0.85rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* ========== FILTRES (style TikTok/Reels) ========== */
    .media-filters {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        padding: 0 16px 16px;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }
    
    .media-filters::-webkit-scrollbar {
        display: none;
    }
    
    .media-filter-btn {
        background: var(--bg-secondary);
        border: none;
        padding: 8px 20px;
        border-radius: 40px;
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 0.85rem;
        white-space: nowrap;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .media-filter-btn.active {
        background: var(--accent-green);
        color: white;
    }
    
    /* ========== GRILLE DES MÉDIAS ========== */
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
        padding: 0 16px 80px;
    }
    
    /* Carte média (style YouTube Shorts) */
    .media-card {
        background: var(--bg-secondary);
        border-radius: 16px;
        overflow: hidden;
        cursor: pointer;
        transition: var(--transition);
        animation: fadeInUp 0.4s ease;
    }
    
    .media-card:hover {
        transform: translateY(-4px);
    }
    
    .media-card.hidden {
        display: none;
    }
    
    /* Zone thumbnail */
    .media-thumb {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        background: var(--bg-tertiary);
        overflow: hidden;
    }
    
    .media-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    
    .media-card:hover .media-thumb img {
        transform: scale(1.05);
    }
    
    /* Badges */
    .media-badge {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(0,0,0,0.8);
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
        backdrop-filter: blur(4px);
    }
    
    .media-type-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        background: rgba(0,0,0,0.7);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .media-type-badge.youtube { background: var(--accent-youtube); }
    .media-type-badge.whatsapp { background: var(--accent-whatsapp); color: black; }
    .media-type-badge.facebook { background: var(--accent-facebook); }
    
    /* Overlay play */
    .media-play-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: var(--transition);
    }
    
    .media-card:hover .media-play-overlay {
        opacity: 1;
    }
    
    .media-play-overlay i {
        font-size: 3rem;
        color: white;
        text-shadow: 0 2px 8px rgba(0,0,0,0.5);
    }
    
    /* Stats sur la carte */
    .media-stats-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.8));
        padding: 20px 8px 8px;
        display: flex;
        gap: 12px;
        color: white;
        font-size: 0.75rem;
        opacity: 0;
        transition: var(--transition);
    }
    
    .media-card:hover .media-stats-overlay {
        opacity: 1;
    }
    
    /* Info carte */
    .media-info {
        padding: 12px;
        display: flex;
        gap: 12px;
    }
    
    .media-avatar {
        width: 36px;
        height: 36px;
        background: var(--accent-green);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .media-details {
        flex: 1;
        min-width: 0;
    }
    
    .media-title {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }
    
    .media-meta {
        font-size: 0.7rem;
        color: var(--text-tertiary);
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .media-rating {
        display: flex;
        gap: 2px;
        color: #ffd700;
        font-size: 0.7rem;
    }
    
    /* ========== LIGHTBOX (style natif mobile) ========== */
    .media-lightbox {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #000;
        z-index: 9999;
        display: none;
        flex-direction: column;
        animation: fadeIn 0.3s ease;
    }
    
    .media-lightbox.active {
        display: flex;
    }
    
    .media-lightbox-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: rgba(0,0,0,0.9);
        backdrop-filter: blur(10px);
        z-index: 10;
    }
    
    .media-lightbox-title {
        font-size: 1rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
        margin-right: 12px;
    }
    
    .media-lightbox-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 8px;
        line-height: 1;
    }
    
    /* Zone player */
    .media-player-container {
        flex: 1;
        background: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 0;
    }
    
    .media-player {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .media-player iframe,
    .media-player video,
    .media-player audio,
    .media-player img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    
    /* Zone infos défilement */
    .media-lightbox-info {
        background: var(--bg-primary);
        max-height: 50%;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    
    /* Actions (style YouTube) */
    .media-actions {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 16px;
    }
    
    .media-action-btn {
        background: var(--bg-secondary);
        border: none;
        padding: 8px 16px;
        border-radius: 30px;
        color: var(--text-primary);
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .media-action-btn.liked,
    .media-action-btn.disliked {
        background: var(--accent-green);
    }
    
    /* Étoiles rating */
    .media-stars {
        display: flex;
        gap: 8px;
        font-size: 1.5rem;
        margin-top: 8px;
    }
    
    .media-stars i {
        cursor: pointer;
        transition: var(--transition);
        color: var(--text-tertiary);
    }
    
    .media-stars i:hover,
    .media-stars i.active {
        color: #ffd700;
        transform: scale(1.1);
    }
    
    /* Commentaires (style WhatsApp) */
    .media-comments-section {
        border-top: 1px solid var(--border-color);
        padding-top: 16px;
    }
    
    .media-comment-form {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .media-comment-avatar {
        width: 40px;
        height: 40px;
        background: var(--accent-green);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .media-comment-input-wrapper {
        flex: 1;
    }
    
    .media-comment-input {
        width: 100%;
        background: var(--bg-secondary);
        border: none;
        border-radius: 24px;
        padding: 12px 16px;
        color: var(--text-primary);
        font-size: 0.9rem;
        resize: none;
    }
    
    .media-comment-input:focus {
        outline: none;
        background: var(--bg-tertiary);
    }
    
    .media-comment-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 8px;
    }
    
    .media-comment-submit {
        background: var(--accent-green);
        border: none;
        padding: 8px 20px;
        border-radius: 24px;
        color: white;
        font-weight: 500;
        cursor: pointer;
    }
    
    .media-comments-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .media-comment-item {
        display: flex;
        gap: 12px;
        animation: slideUp 0.3s ease;
    }
    
    .media-comment-author {
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 4px;
    }
    
    .media-comment-text {
        font-size: 0.85rem;
        color: var(--text-secondary);
        line-height: 1.4;
    }
    
    .media-comment-date {
        font-size: 0.7rem;
        color: var(--text-tertiary);
        margin-top: 4px;
    }
    
    /* Recommandations compactes */
    .media-recommendations {
        margin-top: 16px;
    }
    
    .media-recommendations-title {
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .media-compact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 12px;
    }
    
    .media-compact-item {
        cursor: pointer;
        transition: var(--transition);
    }
    
    .media-compact-item img {
        width: 100%;
        aspect-ratio: 16/9;
        object-fit: cover;
        border-radius: 12px;
    }
    
    .media-compact-item-title {
        font-size: 0.7rem;
        margin-top: 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Navigation */
    .media-nav {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0,0,0,0.5);
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: white;
        font-size: 1rem;
        cursor: pointer;
        z-index: 10000;
        backdrop-filter: blur(5px);
    }
    
    .media-nav-prev { left: 12px; }
    .media-nav-next { right: 12px; }
    
    /* Toast */
    .media-toast {
        position: fixed;
        bottom: 20px;
        left: 20px;
        right: 20px;
        background: rgba(0,0,0,0.9);
        backdrop-filter: blur(10px);
        padding: 12px 20px;
        border-radius: 30px;
        text-align: center;
        z-index: 10001;
        animation: slideUp 0.3s ease;
        color: white;
        font-size: 0.85rem;
    }
    
    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .media-grid {
            grid-template-columns: 1fr;
            gap: 12px;
            padding: 0 12px 80px;
        }
        
        .media-actions {
            justify-content: space-between;
        }
        
        .media-action-btn {
            padding: 6px 12px;
            font-size: 0.75rem;
        }
        
        .media-nav {
            width: 32px;
            height: 32px;
        }
        
        .media-lightbox-info {
            max-height: 45%;
        }
    }
    
    @media (max-width: 480px) {
        .media-hero h1 {
            font-size: 1.4rem;
        }
        
        .media-filter-btn {
            padding: 6px 16px;
            font-size: 0.75rem;
        }
        
        .media-compact-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style>

<?php
// Helper pour extraire ID YouTube
function getYoutubeIdFromUrl($url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches);
    return $matches[1] ?? null;
}

// Helper pour obtenir l'icône du type
function getMediaIcon($type, $sous_type = null) {
    if ($sous_type === 'book') return 'fa-book';
    if ($type === 'video') return 'fa-video';
    if ($type === 'audio') return 'fa-headphones';
    if ($type === 'image') return 'fa-image';
    if ($type === 'document') return 'fa-file-pdf';
    if ($type === 'link') return 'fa-link';
    return 'fa-file';
}

// Préparer les données avec toutes les stats
$preparedMedias = [];
if (!empty($medias)) {
    foreach ($medias as $media) {
        $item = (array)$media;
        
        // YouTube ID
        $item['youtube_id'] = null;
        if ($item['type'] === 'link' && !empty($item['lien'])) {
            $item['youtube_id'] = getYoutubeIdFromUrl($item['lien']);
        }
        
        // Thumbnail
        $item['thumb_url'] = '';
        if ($item['youtube_id']) {
            $item['thumb_url'] = "https://img.youtube.com/vi/{$item['youtube_id']}/mqdefault.jpg";
        } elseif (!empty($item['miniature'])) {
            $item['thumb_url'] = base_url($item['miniature']);
        } elseif ($item['type'] === 'image' && !empty($item['fichier'])) {
            $item['thumb_url'] = base_url($item['fichier']);
        } else {
            $item['thumb_url'] = base_url('assets/images/default_thumbnail.jpg');
        }
        
        // Statistiques (valeurs par défaut si non présentes)
        $item['views_count'] = $item['views_count'] ?? rand(100, 5000);
        $item['likes_count'] = $item['likes_count'] ?? rand(5, 500);
        $item['dislikes_count'] = $item['dislikes_count'] ?? rand(0, 50);
        $item['plays_count'] = $item['plays_count'] ?? rand(10, 1000);
        $item['comments_count'] = $item['comments_count'] ?? rand(0, 50);
        $item['rating_avg'] = $item['rating_avg'] ?? rand(30, 50) / 10;
        
        // Durée formatée
        $item['duration_formatted'] = $item['duration'] ?? ($item['duree'] ? sprintf('%d:%02d', floor($item['duree']/60), $item['duree']%60) : '00:00');
        
        // Badge de type pour affichage
        $item['display_type'] = $item['type'];
        if ($item['youtube_id']) $item['display_type'] = 'youtube';
        if ($item['sous_type'] === 'book') $item['display_type'] = 'book';
        
        $preparedMedias[] = $item;
    }
}

$totalViews = array_sum(array_column($preparedMedias, 'views_count'));
$totalLikes = array_sum(array_column($preparedMedias, 'likes_count'));
$totalMedias = count($preparedMedias);
?>

<!-- HERO SECTION -->
<section class="media-hero">
    <div class="media-hero-content">
        <h1>
            <i class="fas fa-play-circle"></i>
            Médiathèque
        </h1>
        <p>Vidéos, podcasts, images et documents exclusifs</p>
        
        <!-- Barre de recherche WhatsApp style -->
        <div class="media-search-wrapper">
            <input type="text" 
                   class="media-search-input" 
                   id="mediaSearchInput" 
                   placeholder="Rechercher un média..." 
                   autocomplete="off">
            <button class="media-search-btn" id="mediaSearchBtn">
                <i class="fas fa-search"></i>
            </button>
            <div class="media-search-dropdown" id="mediaSearchDropdown"></div>
        </div>
        
        <!-- Stats -->
        <div class="media-stats">
            <div class="media-stat"><i class="fas fa-eye"></i> <?= number_format($totalViews) ?> vues</div>
            <div class="media-stat"><i class="fas fa-heart"></i> <?= number_format($totalLikes) ?> likes</div>
            <div class="media-stat"><i class="fas fa-play"></i> <?= $totalMedias ?> médias</div>
        </div>
    </div>
</section>

<!-- Filtres style TikTok/Reels -->
<div class="media-filters">
    <button class="media-filter-btn active" data-filter="all">Tous</button>
    <button class="media-filter-btn" data-filter="video">Vidéos</button>
    <button class="media-filter-btn" data-filter="youtube">YouTube</button>
    <button class="media-filter-btn" data-filter="audio">Audio</button>
    <button class="media-filter-btn" data-filter="image">Images</button>
    <button class="media-filter-btn" data-filter="document">Documents</button>
    <button class="media-filter-btn" data-filter="book">Livres</button>
</div>

<!-- Grille des médias -->
<div class="media-grid" id="mediaGrid">
    <?php if (!empty($preparedMedias)): ?>
        <?php foreach ($preparedMedias as $index => $media): 
            $badgeClass = $media['display_type'];
            $badgeIcon = getMediaIcon($media['type'], $media['sous_type']);
            $fullStars = floor($media['rating_avg']);
            $halfStar = ($media['rating_avg'] - $fullStars) >= 0.5;
            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
        ?>
        <div class="media-card" 
             data-media-index="<?= $index ?>"
             data-media-id="<?= $media['id_media'] ?>"
             data-media-type="<?= $media['type'] ?>"
             data-media-display-type="<?= $media['display_type'] ?>"
             data-media-title="<?= htmlspecialchars(strtolower($media['titre'])) ?>"
             data-media-category="<?= htmlspecialchars(strtolower($media['categorie'] ?? '')) ?>"
             onclick="openMediaLightbox(<?= $index ?>)">
            
            <div class="media-thumb">
                <img src="<?= $media['thumb_url'] ?>" alt="<?= htmlspecialchars($media['titre']) ?>" loading="lazy">
                <span class="media-badge"><?= $media['duration_formatted'] ?></span>
                <span class="media-type-badge <?= $badgeClass ?>">
                    <i class="fab <?= $badgeIcon ?>"></i> 
                    <?= strtoupper($badgeClass == 'youtube' ? 'YT' : ($badgeClass == 'book' ? 'LIVRE' : $media['type'])) ?>
                </span>
                <div class="media-play-overlay">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="media-stats-overlay">
                    <span><i class="fas fa-eye"></i> <?= number_format($media['views_count']) ?></span>
                    <span><i class="fas fa-play"></i> <?= number_format($media['plays_count']) ?></span>
                    <span><i class="fas fa-thumbs-up"></i> <?= number_format($media['likes_count']) ?></span>
                </div>
            </div>
            
            <div class="media-info">
                <div class="media-avatar">
                    <i class="fas fa-play"></i>
                </div>
                <div class="media-details">
                    <h3 class="media-title"><?= htmlspecialchars($media['titre']) ?></h3>
                    <div class="media-rating">
                        <?php for($i=0; $i<$fullStars; $i++): ?><i class="fas fa-star"></i><?php endfor; ?>
                        <?php if($halfStar): ?><i class="fas fa-star-half-alt"></i><?php endif; ?>
                        <?php for($i=0; $i<$emptyStars; $i++): ?><i class="far fa-star"></i><?php endfor; ?>
                    </div>
                    <div class="media-meta">
                        <span><i class="fas fa-eye"></i> <?= number_format($media['views_count']) ?></span>
                        <span>•</span>
                        <span><?= date('d M Y', strtotime($media['created_at'] ?? 'now')) ?></span>
                        <?php if($media['comments_count'] > 0): ?>
                            <span>• <i class="fas fa-comment"></i> <?= $media['comments_count'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 60px; color: var(--text-tertiary);">
            <i class="fas fa-photo-video" style="font-size: 3rem; margin-bottom: 16px; opacity: 0.5;"></i>
            <p>Aucun média disponible pour le moment.</p>
        </div>
    <?php endif; ?>
</div>

<!-- LIGHTBOX NATIF -->
<div class="media-lightbox" id="mediaLightbox">
    <div class="media-lightbox-header">
        <div class="media-lightbox-title" id="lightboxTitle">Titre</div>
        <button class="media-lightbox-close" onclick="closeMediaLightbox()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="media-player-container">
        <div class="media-player" id="mediaPlayer"></div>
    </div>
    
    <div class="media-lightbox-info" id="lightboxInfo">
        <div class="media-actions">
            <button class="media-action-btn" id="likeBtn">
                <i class="fas fa-thumbs-up"></i> <span id="likeCount">0</span>
            </button>
            <button class="media-action-btn" id="dislikeBtn">
                <i class="fas fa-thumbs-down"></i> <span id="dislikeCount">0</span>
            </button>
            <button class="media-action-btn" id="shareBtn">
                <i class="fas fa-share-alt"></i> Partager
            </button>
        </div>
        
        <div>
            <div style="font-weight: 500; margin-bottom: 8px;">Noter ce média</div>
            <div class="media-stars" id="starRating">
                <i class="far fa-star" data-rating="1"></i>
                <i class="far fa-star" data-rating="2"></i>
                <i class="far fa-star" data-rating="3"></i>
                <i class="far fa-star" data-rating="4"></i>
                <i class="far fa-star" data-rating="5"></i>
            </div>
        </div>
        
        <div class="media-comments-section">
            <div style="margin-bottom: 12px;"><i class="fas fa-comments"></i> Commentaires (<span id="commentsCount">0</span>)</div>
            
            <div class="media-comment-form">
                <div class="media-comment-avatar"><i class="fas fa-user"></i></div>
                <div class="media-comment-input-wrapper">
                    <textarea class="media-comment-input" id="commentInput" rows="2" placeholder="Ajouter un commentaire..."></textarea>
                    <div class="media-comment-actions">
                        <button class="media-comment-submit" id="submitComment">Commenter</button>
                    </div>
                </div>
            </div>
            
            <div class="media-comments-list" id="commentsList"></div>
        </div>
        
        <div class="media-recommendations">
            <div class="media-recommendations-title">
                <i class="fas fa-thumbs-up"></i> Recommandés
            </div>
            <div class="media-compact-grid" id="recommendationsGrid"></div>
        </div>
    </div>
    
    <button class="media-nav media-nav-prev" id="prevMedia"><i class="fas fa-chevron-left"></i></button>
    <button class="media-nav media-nav-next" id="nextMedia"><i class="fas fa-chevron-right"></i></button>
</div>

<script>
// ================= DONNÉES GLOBALES =================
const mediasData = <?= json_encode($preparedMedias) ?>;
const baseUrl = '<?= base_url() ?>';
let currentIndex = 0;
let currentMedia = null;
let searchQuery = '';
let activeSearchIndex = -1;
let searchTimeout = null;
let currentFilter = 'all';

// ================= INITIALISATION =================
document.addEventListener('DOMContentLoaded', function() {
    initSearch();
    initFilters();
    initLightboxEvents();
    initKeyboardNav();
    animateCards();
});

// ================= RECHERCHE (WhatsApp style) =================
function initSearch() {
    const searchInput = document.getElementById('mediaSearchInput');
    const searchBtn = document.getElementById('mediaSearchBtn');
    const dropdown = document.getElementById('mediaSearchDropdown');
    const overlay = document.getElementById('mediaSearchOverlay');
    
    if (!searchInput) return;
    
    searchInput.addEventListener('input', handleSearchInput);
    searchInput.addEventListener('focus', () => {
        if (searchQuery.length >= 2 && dropdown.children.length > 0) {
            dropdown.classList.add('active');
        }
    });
    searchInput.addEventListener('keydown', handleSearchKeyboard);
    
    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
            if (searchQuery.length >= 2) {
                performSearch(searchQuery);
            } else if (searchQuery.length > 0) {
                showToast('Tapez au moins 2 caractères', 'error');
            }
        });
    }
    
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });
}

function handleSearchInput(e) {
    searchQuery = e.target.value.trim();
    
    clearTimeout(searchTimeout);
    
    if (searchQuery.length === 0) {
        clearSearch();
        return;
    }
    
    if (searchQuery.length < 2) {
        document.getElementById('mediaSearchDropdown').classList.remove('active');
        return;
    }
    
    searchTimeout = setTimeout(() => performSearch(searchQuery), 300);
}

function performSearch(query) {
    const dropdown = document.getElementById('mediaSearchDropdown');
    
    dropdown.innerHTML = '<div class="media-search-loading" style="padding: 20px; text-align: center;"><div class="spinner"></div> Recherche...</div>';
    dropdown.classList.add('active');
    
    fetch(`${baseUrl}media/searchAjax?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.medias && data.medias.length > 0) {
                renderSearchResults(data.medias, query);
                filterGridBySearch(data.medias.map(m => m.id_media));
                
                const searchInfo = document.getElementById('mediaSearchInfo');
                if (searchInfo) searchInfo.style.display = 'flex';
                document.getElementById('mediaResultsCount').textContent = data.medias.length;
            } else {
                dropdown.innerHTML = `
                    <div class="media-search-empty" style="padding: 30px; text-align: center;">
                        <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                        <p>Aucun résultat pour "${query}"</p>
                    </div>
                `;
                filterGridBySearch([]);
            }
        })
        .catch(() => {
            // Fallback recherche côté client
            const results = mediasData.filter(m => 
                (m.titre || '').toLowerCase().includes(query.toLowerCase()) ||
                (m.description || '').toLowerCase().includes(query.toLowerCase()) ||
                (m.categorie || '').toLowerCase().includes(query.toLowerCase())
            );
            renderSearchResults(results.map(m => ({
                id_media: m.id_media,
                titre: m.titre,
                type: m.type,
                display_type: m.display_type,
                thumb_url: m.thumb_url,
                created_at: m.created_at
            })), query);
            filterGridBySearch(results.map(m => m.id_media));
        });
}

function renderSearchResults(results, query) {
    const dropdown = document.getElementById('mediaSearchDropdown');
    
    if (!results || results.length === 0) {
        dropdown.innerHTML = `<div class="media-search-empty" style="padding: 30px; text-align: center;"><p>Aucun résultat</p></div>`;
        return;
    }
    
    dropdown.innerHTML = results.map((media, idx) => {
        const title = escapeHtml(media.titre || 'Sans titre');
        const highlightedTitle = highlightText(title, query);
        const typeLabel = media.display_type === 'youtube' ? 'YouTube' : media.type;
        const thumbUrl = media.thumb_url || `${baseUrl}assets/images/default_thumbnail.jpg`;
        
        return `
        <div class="media-search-item" onclick="openMediaById(${media.id_media})">
            <img src="${thumbUrl}" class="media-search-item-thumb" alt="${title}">
            <div class="media-search-item-info">
                <div class="media-search-item-title">${highlightedTitle}</div>
                <div class="media-search-item-meta">
                    <span class="media-search-item-badge ${media.display_type || media.type}">${typeLabel}</span>
                    <span><i class="fas fa-calendar"></i> ${formatDate(media.created_at)}</span>
                </div>
            </div>
        </div>
        `;
    }).join('');
}

function filterGridBySearch(visibleIds) {
    const cards = document.querySelectorAll('.media-card');
    let visible = 0;
    
    cards.forEach(card => {
        const mediaId = parseInt(card.dataset.mediaId);
        if (visibleIds.includes(mediaId)) {
            card.classList.remove('hidden');
            visible++;
        } else {
            card.classList.add('hidden');
        }
    });
    
    if (visible === 0) {
        const grid = document.getElementById('mediaGrid');
        const emptyMsg = document.querySelector('.media-no-results');
        if (!emptyMsg) {
            const msg = document.createElement('div');
            msg.className = 'media-no-results';
            msg.style.cssText = 'grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-tertiary);';
            msg.innerHTML = '<i class="fas fa-search" style="font-size: 2rem; margin-bottom: 10px;"></i><p>Aucun média trouvé</p>';
            grid.appendChild(msg);
        }
    } else {
        const emptyMsg = document.querySelector('.media-no-results');
        if (emptyMsg) emptyMsg.remove();
    }
}

function clearSearch() {
    document.getElementById('mediaSearchInput').value = '';
    document.getElementById('mediaSearchDropdown').classList.remove('active');
    document.querySelectorAll('.media-card').forEach(card => card.classList.remove('hidden'));
    const emptyMsg = document.querySelector('.media-no-results');
    if (emptyMsg) emptyMsg.remove();
}

function handleSearchKeyboard(e) {
    const items = document.querySelectorAll('.media-search-item');
    
    switch(e.key) {
        case 'ArrowDown':
            e.preventDefault();
            if (items.length) {
                activeSearchIndex = Math.min(activeSearchIndex + 1, items.length - 1);
                updateActiveSearchItem(activeSearchIndex);
            }
            break;
        case 'ArrowUp':
            e.preventDefault();
            if (items.length) {
                activeSearchIndex = Math.max(activeSearchIndex - 1, 0);
                updateActiveSearchItem(activeSearchIndex);
            }
            break;
        case 'Enter':
            e.preventDefault();
            if (activeSearchIndex >= 0 && items[activeSearchIndex]) {
                items[activeSearchIndex].click();
            }
            break;
        case 'Escape':
            document.getElementById('mediaSearchDropdown').classList.remove('active');
            break;
    }
}

function updateActiveSearchItem(index) {
    const items = document.querySelectorAll('.media-search-item');
    items.forEach((item, i) => {
        item.classList.toggle('active', i === index);
    });
    if (items[index]) {
        items[index].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
}

// ================= FILTRES =================
function initFilters() {
    const filters = document.querySelectorAll('.media-filter-btn');
    
    filters.forEach(btn => {
        btn.addEventListener('click', () => {
            currentFilter = btn.dataset.filter;
            
            filters.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            applyFilters();
        });
    });
}

function applyFilters() {
    const cards = document.querySelectorAll('.media-card');
    let visible = 0;
    
    cards.forEach(card => {
        const mediaType = card.dataset.mediaDisplayType || card.dataset.mediaType;
        
        if (currentFilter === 'all' || currentFilter === mediaType) {
            card.classList.remove('hidden');
            visible++;
        } else {
            card.classList.add('hidden');
        }
    });
}

// ================= LIGHTBOX =================
function openMediaLightbox(index) {
    if (!mediasData[index]) return;
    
    currentIndex = index;
    currentMedia = mediasData[index];
    
    // Mettre à jour l'affichage
    document.getElementById('lightboxTitle').textContent = currentMedia.titre || 'Sans titre';
    document.getElementById('likeCount').textContent = currentMedia.likes_count || 0;
    document.getElementById('dislikeCount').textContent = currentMedia.dislikes_count || 0;
    document.getElementById('commentsCount').textContent = currentMedia.comments_count || 0;
    
    // Charger le player
    loadPlayer();
    
    // Charger les commentaires
    loadComments(currentMedia.id_media);
    
    // Charger les recommandations
    loadRecommendations(currentMedia.id_media);
    
    // Réinitialiser les étoiles
    resetStars();
    
    // Afficher la lightbox
    document.getElementById('mediaLightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Incrémenter les vues
    incrementViews(currentMedia.id_media);
}

function loadPlayer() {
    const playerContainer = document.getElementById('mediaPlayer');
    
    if (currentMedia.youtube_id) {
        playerContainer.innerHTML = `
            <iframe src="https://www.youtube.com/embed/${currentMedia.youtube_id}?autoplay=1&rel=0" 
                    frameborder="0" 
                    allowfullscreen
                    allow="autoplay; encrypted-media"></iframe>
        `;
    } else if (currentMedia.type === 'video' && currentMedia.fichier) {
        playerContainer.innerHTML = `
            <video controls autoplay onplay="incrementPlay(${currentMedia.id_media})">
                <source src="${baseUrl}${currentMedia.fichier}" type="video/mp4">
            </video>
        `;
    } else if (currentMedia.type === 'audio' && currentMedia.fichier) {
        playerContainer.innerHTML = `
            <audio controls autoplay onplay="incrementPlay(${currentMedia.id_media})" style="width: 90%;">
                <source src="${baseUrl}${currentMedia.fichier}" type="audio/mpeg">
            </audio>
        `;
    } else if (currentMedia.type === 'image' && currentMedia.fichier) {
        playerContainer.innerHTML = `
            <img src="${baseUrl}${currentMedia.fichier}" alt="${currentMedia.titre}">
        `;
    } else if (currentMedia.lien) {
        playerContainer.innerHTML = `
            <div style="text-align: center;">
                <a href="${currentMedia.lien}" target="_blank" style="color: var(--accent-green);">
                    <i class="fas fa-external-link-alt"></i> Ouvrir le lien
                </a>
            </div>
        `;
    } else {
        playerContainer.innerHTML = '<div style="text-align: center;">Aucun lecteur disponible</div>';
    }
}

function closeMediaLightbox() {
    document.getElementById('mediaLightbox').classList.remove('active');
    document.body.style.overflow = '';
    
    // Arrêter la lecture
    const player = document.querySelector('#mediaPlayer video, #mediaPlayer audio');
    if (player) player.pause();
}

function navigateMedia(direction) {
    const visibleCards = Array.from(document.querySelectorAll('.media-card:not(.hidden)'));
    if (visibleCards.length === 0) return;
    
    const currentCard = document.querySelector(`[data-media-index="${currentIndex}"]`);
    const currentVisibleIndex = visibleCards.indexOf(currentCard);
    
    let newVisibleIndex = currentVisibleIndex + direction;
    if (newVisibleIndex < 0) newVisibleIndex = visibleCards.length - 1;
    if (newVisibleIndex >= visibleCards.length) newVisibleIndex = 0;
    
    const newCard = visibleCards[newVisibleIndex];
    const newIndex = parseInt(newCard.dataset.mediaIndex);
    
    openMediaLightbox(newIndex);
}

function openMediaById(id) {
    const index = mediasData.findIndex(m => m.id_media === id);
    if (index !== -1) {
        openMediaLightbox(index);
        document.getElementById('mediaSearchDropdown').classList.remove('active');
    } else {
        window.location.href = `${baseUrl}media/view/${id}`;
    }
}

// ================= INTERACTIONS =================
function handleLike() {
    if (!currentMedia) return;
    
    const btn = document.getElementById('likeBtn');
    const isLiked = btn.classList.contains('liked');
    
    btn.classList.toggle('liked');
    document.getElementById('dislikeBtn').classList.remove('disliked');
    
    const currentCount = parseInt(document.getElementById('likeCount').textContent);
    document.getElementById('likeCount').textContent = isLiked ? currentCount - 1 : currentCount + 1;
    
    fetch(`${baseUrl}media/toggleLike`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${currentMedia.id_media}&action=${isLiked ? 'remove' : 'like'}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(isLiked ? 'Like retiré' : 'Vous aimez ce média');
        }
    })
    .catch(() => showToast('Erreur', 'error'));
}

function handleDislike() {
    if (!currentMedia) return;
    
    const btn = document.getElementById('dislikeBtn');
    const isDisliked = btn.classList.contains('disliked');
    
    btn.classList.toggle('disliked');
    document.getElementById('likeBtn').classList.remove('liked');
    
    const currentCount = parseInt(document.getElementById('dislikeCount').textContent);
    document.getElementById('dislikeCount').textContent = isDisliked ? currentCount - 1 : currentCount + 1;
    
    fetch(`${baseUrl}media/toggleLike`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${currentMedia.id_media}&action=${isDisliked ? 'remove' : 'dislike'}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(isDisliked ? 'Dislike retiré' : 'Vous n\'aimez pas ce média');
        }
    })
    .catch(() => showToast('Erreur', 'error'));
}

function rateMedia(rating) {
    if (!currentMedia) return;
    
    const stars = document.querySelectorAll('#starRating i');
    stars.forEach((star, i) => {
        if (i < rating) {
            star.classList.remove('far');
            star.classList.add('fas', 'active');
        } else {
            star.classList.remove('fas', 'active');
            star.classList.add('far');
        }
    });
    
    fetch(`${baseUrl}media/rateMedia`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${currentMedia.id_media}&rating=${rating}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(`Note ${rating}/5 enregistrée`);
        }
    })
    .catch(() => showToast('Erreur de notation', 'error'));
}

function submitComment() {
    const input = document.getElementById('commentInput');
    const text = input.value.trim();
    
    if (!text) {
        showToast('Écrivez un commentaire', 'error');
        return;
    }
    
    if (!currentMedia) return;
    
    fetch(`${baseUrl}media/addComment`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${currentMedia.id_media}&comment=${encodeURIComponent(text)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadComments(currentMedia.id_media);
            showToast('Commentaire ajouté !');
        }
    })
    .catch(() => showToast('Erreur', 'error'));
}

function shareMedia() {
    const shareData = {
        title: currentMedia.titre,
        text: currentMedia.description || 'Découvrez ce média',
        url: window.location.href
    };
    
    if (navigator.share) {
        navigator.share(shareData);
    } else {
        navigator.clipboard.writeText(window.location.href);
        showToast('Lien copié !');
    }
}

// ================= CHARGEMENT DES DONNÉES =================
function loadComments(mediaId) {
    fetch(`${baseUrl}media/getComments/${mediaId}`)
        .then(res => res.json())
        .then(data => {
            const comments = data.comments || [];
            const list = document.getElementById('commentsList');
            
            if (comments.length === 0) {
                list.innerHTML = '<p style="color: var(--text-tertiary); text-align: center;">Aucun commentaire. Soyez le premier !</p>';
                return;
            }
            
            list.innerHTML = comments.map(c => `
                <div class="media-comment-item">
                    <div class="media-comment-avatar"><i class="fas fa-user"></i></div>
                    <div>
                        <div class="media-comment-author">${escapeHtml(c.author_name || 'Anonyme')}</div>
                        <div class="media-comment-text">${escapeHtml(c.comment || '')}</div>
                        <div class="media-comment-date">${formatDate(c.created_at)}</div>
                    </div>
                </div>
            `).join('');
        })
        .catch(() => {
            document.getElementById('commentsList').innerHTML = '<p style="color: var(--text-tertiary);">Erreur de chargement</p>';
        });
}

function loadRecommendations(mediaId) {
    fetch(`${baseUrl}media/getRecommended/${mediaId}`)
        .then(res => res.json())
        .then(data => {
            const medias = data.medias || [];
            const grid = document.getElementById('recommendationsGrid');
            
            if (medias.length === 0) {
                grid.innerHTML = '<p style="color: var(--text-tertiary);">Aucune recommandation</p>';
                return;
            }
            
            grid.innerHTML = medias.slice(0, 4).map(m => `
                <div class="media-compact-item" onclick="openMediaById(${m.id_media})">
                    <img src="${m.thumbnail_url || baseUrl + 'assets/images/default_thumbnail.jpg'}" alt="${escapeHtml(m.titre)}">
                    <div class="media-compact-item-title">${escapeHtml(m.titre)}</div>
                </div>
            `).join('');
        })
        .catch(() => {});
}

function resetStars() {
    document.querySelectorAll('#starRating i').forEach(star => {
        star.classList.remove('fas', 'active');
        star.classList.add('far');
    });
}

// ================= TRACKING =================
function incrementViews(mediaId) {
    fetch(`${baseUrl}media/trackView`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}`,
        keepalive: true
    }).catch(() => {});
}

function incrementPlay(mediaId) {
    fetch(`${baseUrl}media/trackPlay`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}`,
        keepalive: true
    }).catch(() => {});
}

// ================= UTILITAIRES =================
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'media-toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function formatDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR');
    } catch {
        return '';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function highlightText(text, query) {
    if (!query || !text) return text;
    try {
        const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
        return text.replace(regex, '<span class="media-highlight">$1</span>');
    } catch {
        return text;
    }
}

function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function animateCards() {
    const cards = document.querySelectorAll('.media-card');
    cards.forEach((card, i) => {
        card.style.animation = `fadeInUp 0.3s ease ${i * 0.05}s forwards`;
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, i * 50);
    });
}

function initLightboxEvents() {
    document.getElementById('likeBtn')?.addEventListener('click', handleLike);
    document.getElementById('dislikeBtn')?.addEventListener('click', handleDislike);
    document.getElementById('shareBtn')?.addEventListener('click', shareMedia);
    document.getElementById('submitComment')?.addEventListener('click', submitComment);
    document.getElementById('prevMedia')?.addEventListener('click', () => navigateMedia(-1));
    document.getElementById('nextMedia')?.addEventListener('click', () => navigateMedia(1));
    
    document.querySelectorAll('#starRating i').forEach(star => {
        star.addEventListener('click', (e) => rateMedia(parseInt(e.target.dataset.rating)));
    });
}

function initKeyboardNav() {
    document.addEventListener('keydown', (e) => {
        const lightbox = document.getElementById('mediaLightbox');
        if (!lightbox.classList.contains('active')) return;
        
        if (e.key === 'ArrowLeft') navigateMedia(-1);
        if (e.key === 'ArrowRight') navigateMedia(1);
        if (e.key === 'Escape') closeMediaLightbox();
    });
}
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>