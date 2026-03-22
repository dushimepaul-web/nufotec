<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($media['titre'] ?? 'Détail du média') ?> - <?= $this->Model->get_setting('site_name', 'NUFOTEC') ?></title>
    <meta property="og:title" content="<?= htmlspecialchars($media['titre'] ?? '') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($media['description'] ?? $media['credits'] ?? '') ?>">
    <meta property="og:image" content="<?= $media['thumbnail_url'] ?? base_url('assets/images/default-share.jpg') ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:type" content="article">
    <meta name="twitter:card" content="summary_large_image">
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
        body { font-family: 'Inter', sans-serif; background: var(--bg-dark); color: var(--text-primary); }
        
        .navbar {
            background: rgba(15,15,15,0.98);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #ff0000;
            text-decoration: none;
        }
        .navbar-brand:hover { color: #ff0000; }
        
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .media-container {
            background: var(--bg-card);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }
        
        .media-viewer {
            background: #000;
            min-height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .video-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 500px;
            background: #000;
        }
        
        .video-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .media-viewer video {
            width: 100%;
            height: 100%;
            min-height: 500px;
            object-fit: contain;
            background: #000;
        }
        
        .media-viewer img {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
        }
        
        .audio-container {
            text-align: center;
            padding: 3rem;
            background: linear-gradient(135deg, #1db954 0%, #169c46 100%);
        }
        
        .audio-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .audio-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .audio-btn.play-pause {
            width: 60px;
            height: 60px;
            background: white;
            color: #1db954;
        }
        
        .audio-btn:hover {
            transform: scale(1.05);
        }
        
        .progress-bar-custom {
            max-width: 500px;
            margin: 1rem auto;
        }
        
        .media-info {
            padding: 2rem;
        }
        
        .media-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .media-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-secondary);
        }
        
        .media-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .media-description {
            margin-bottom: 1.5rem;
            line-height: 1.6;
            color: var(--text-secondary);
        }
        
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1rem 0;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            background: var(--bg-hover);
            border: none;
            border-radius: 30px;
            color: var(--text-primary);
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            background: #3a3a3a;
            transform: translateY(-2px);
        }
        
        .action-btn.active {
            background: #3ea6ff;
            color: white;
        }
        
        .action-btn.disliked {
            background: #ff6b6b;
            color: white;
        }
        
        .rating-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .rating-stars {
            display: flex;
            gap: 0.3rem;
            font-size: 1.5rem;
            color: #ffc107;
            cursor: pointer;
        }
        
        .rating-stars i {
            transition: transform 0.2s;
        }
        
        .rating-stars i:hover {
            transform: scale(1.1);
        }
        
        .rating-average {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--bg-hover);
            border-radius: 30px;
        }
        
        .share-section {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }
        
        .share-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .share-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .share-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: 30px;
            color: white;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        .share-btn:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .share-whatsapp { background: #25d366; }
        .share-facebook { background: #1877f2; }
        .share-twitter { background: #1da1f2; }
        .share-linkedin { background: #0077b5; }
        .share-copy { background: #666; }
        
        .share-link-container {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .share-link-input {
            flex: 1;
            background: var(--bg-hover);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.6rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .copy-btn {
            padding: 0.6rem 1.2rem;
            background: var(--bg-hover);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .copy-btn:hover {
            background: #3a3a3a;
        }
        
        .comments-section {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            border: 1px solid var(--border-color);
        }
        
        .comments-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .comment-form {
            margin-bottom: 2rem;
        }
        
        .comment-input {
            width: 100%;
            background: var(--bg-hover);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            resize: vertical;
        }
        
        .comment-submit {
            background: #3ea6ff;
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .comment-list {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .comment-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            gap: 1rem;
        }
        
        .comment-avatar {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            background: var(--bg-hover);
        }
        
        .comment-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .comment-avatar .default-avatar {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .comment-content {
            flex: 1;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        
        .comment-author {
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .comment-author-badge {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 12px;
            background: #3ea6ff;
            color: white;
        }
        
        .comment-text {
            color: var(--text-primary);
            line-height: 1.5;
        }
        
        .related-section {
            margin-top: 2rem;
        }
        
        .related-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .related-card {
            background: var(--bg-card);
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s;
            border: 1px solid var(--border-color);
        }
        
        .related-card:hover {
            transform: translateY(-4px);
        }
        
        .related-thumb {
            height: 120px;
            background-size: cover;
            background-position: center;
            background-color: #2a2a2a;
        }
        
        .related-info {
            padding: 0.75rem;
        }
        
        .related-title-sm {
            font-size: 0.85rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.3;
        }
        
        .related-meta {
            font-size: 0.7rem;
            color: var(--text-secondary);
            margin-top: 0.3rem;
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
        
        @media (max-width: 768px) {
            .main-content { padding: 1rem; }
            .media-title { font-size: 1.3rem; }
            .action-buttons { gap: 0.5rem; }
            .action-btn { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
            .share-buttons { gap: 0.5rem; }
            .share-btn { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
            .comment-item { flex-direction: column; gap: 0.5rem; }
            .comment-avatar { width: 32px; height: 32px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= base_url('media') ?>">
            <i class="bi bi-play-circle"></i> <?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC')) ?>
        </a>
        <a href="<?= base_url('media') ?>" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <?php if (isset($user) && $user): ?>
        <div class="dropdown">
            <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-dark">
                <li><a class="dropdown-item" href="<?= base_url('profile') ?>"><i class="bi bi-person"></i> Mon profil</a></li>
                <li><a class="dropdown-item" href="<?= base_url('media/favorites') ?>"><i class="bi bi-bookmark"></i> Mes favoris</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= base_url('auth/logout') ?>"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
            </ul>
        </div>
        <?php else: ?>
        <a href="<?= base_url('auth/login') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-box-arrow-in-right"></i> Connexion
        </a>
        <?php endif; ?>
    </div>
</nav>

<main class="main-content">
    <?php if ($media): 
        $mediaSlug = !empty($media['slug']) ? $media['slug'] : $media['id_media'];
        $type = $media['type'] ?? 'autre';
        $sous_type = $media['sous_type'] ?? '';
        $fichier = $media['fichier_url'] ?? '';
        $youtube_id = $media['youtube_id'] ?? '';
        $lien = $media['lien'] ?? '';
        
        if (!empty($fichier) && !preg_match('/^https?:\/\//', $fichier)) {
            $fichier = base_url($fichier);
        }
        
        // Extraire l'ID YouTube du lien si nécessaire (pour tous les types)
        if (empty($youtube_id) && !empty($lien)) {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $lien, $matches);
            $youtube_id = $matches[1] ?? '';
        }
        
        // Détecter si c'est un lien YouTube (peu importe le type)
        $is_youtube_link = !empty($youtube_id);
    ?>
        <div class="media-container">
            <div class="media-viewer" id="mediaViewer">
                <?php 
                // PRIORITÉ ABSOLUE : Si c'est un lien YouTube (quel que soit le type), on affiche l'iframe
                if ($is_youtube_link): ?>
                    <div class="video-wrapper">
                        <iframe 
                            src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($youtube_id) ?>?autoplay=1&rel=0&modestbranding=1&showinfo=0&controls=1&fs=1&disablekb=1&iv_load_policy=3&color=white&theme=dark&hl=fr"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            title="<?= htmlspecialchars($media['titre']) ?>">
                        </iframe>
                    </div>
                <?php 
                // Sinon, on traite selon le type normal
                else:
                    switch($type):
                        case 'video':
                            if (!empty($fichier)): ?>
                                <video controls autoplay>
                                    <source src="<?= htmlspecialchars($fichier) ?>" type="video/mp4">
                                    Votre navigateur ne supporte pas la lecture vidéo.
                                </video>
                            <?php else: ?>
                                <div class="text-center p-5">
                                    <i class="bi bi-play-circle" style="font-size: 4rem;"></i>
                                    <h4 class="mt-3">Vidéo non disponible</h4>
                                </div>
                            <?php endif;
                            break;
                            
                        case 'audio':
                            if (!empty($fichier)): ?>
                                <div class="audio-container">
                                    <i class="bi bi-music-note-beamed" style="font-size: 4rem;"></i>
                                    <h3 class="mt-3"><?= htmlspecialchars($media['titre']) ?></h3>
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
                                        <div class="d-flex justify-content-between mb-1">
                                            <span id="currentTime">0:00</span>
                                            <span id="totalTime">0:00</span>
                                        </div>
                                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.3); cursor: pointer;" onclick="seekAudio(event)">
                                            <div class="progress-bar bg-white" id="progressFill" style="width: 0%;"></div>
                                        </div>
                                    </div>
                                    <audio id="audioElement" src="<?= htmlspecialchars($fichier) ?>" preload="metadata"></audio>
                                </div>
                            <?php endif;
                            break;
                            
                        case 'image':
                            if (!empty($fichier)): ?>
                                <img src="<?= htmlspecialchars($fichier) ?>" alt="<?= htmlspecialchars($media['titre']) ?>">
                            <?php endif;
                            break;
                            
                        case 'autre':
                            if ($sous_type === 'book' || str_ends_with($fichier, '.pdf')): ?>
                                <div class="text-center p-5">
                                    <i class="bi bi-file-earmark-pdf" style="font-size: 4rem; color: #e74c3c;"></i>
                                    <h3 class="mt-3"><?= htmlspecialchars($media['titre']) ?></h3>
                                    <a href="<?= htmlspecialchars($fichier) ?>" target="_blank" class="btn btn-danger mt-3">
                                        <i class="bi bi-download"></i> Télécharger le PDF
                                    </a>
                                </div>
                            <?php elseif (!empty($fichier)): ?>
                                <div class="text-center p-5">
                                    <i class="bi bi-file-earmark" style="font-size: 4rem;"></i>
                                    <h3 class="mt-3"><?= htmlspecialchars($media['titre']) ?></h3>
                                    <a href="<?= htmlspecialchars($fichier) ?>" target="_blank" class="btn btn-primary mt-3">
                                        <i class="bi bi-box-arrow-up-right"></i> Ouvrir le fichier
                                    </a>
                                </div>
                            <?php endif;
                            break;
                            
                        case 'link':
                            if (!empty($lien)): ?>
                                <div class="text-center p-5">
                                    <i class="bi bi-link-45deg" style="font-size: 4rem;"></i>
                                    <h3 class="mt-3"><?= htmlspecialchars($media['titre']) ?></h3>
                                    <a href="<?= htmlspecialchars($lien) ?>" target="_blank" class="btn btn-primary mt-3">
                                        <i class="bi bi-box-arrow-up-right"></i> Ouvrir le lien
                                    </a>
                                </div>
                            <?php endif;
                            break;
                            
                        default:
                            if (!empty($lien)): ?>
                                <div class="text-center p-5">
                                    <i class="bi bi-link-45deg" style="font-size: 4rem;"></i>
                                    <h3 class="mt-3"><?= htmlspecialchars($media['titre']) ?></h3>
                                    <a href="<?= htmlspecialchars($lien) ?>" target="_blank" class="btn btn-primary mt-3">
                                        <i class="bi bi-box-arrow-up-right"></i> Ouvrir le lien
                                    </a>
                                </div>
                            <?php endif;
                    endswitch;
                endif; ?>
            </div>
            
            <div class="media-info">
                <h1 class="media-title"><?= htmlspecialchars($media['titre']) ?></h1>
                
                <div class="media-meta">
                    <span class="media-meta-item"><i class="bi bi-eye"></i> <?= number_format($media['views_count'] ?? 0) ?> vues</span>
                    <span class="media-meta-item"><i class="bi bi-hand-thumbs-up"></i> <?= number_format($media['likes_count'] ?? 0) ?></span>
                    <span class="media-meta-item"><i class="bi bi-chat"></i> <?= number_format($media['comments_count'] ?? 0) ?> commentaires</span>
                    <?php if (!empty($media['credits'])): ?>
                        <span class="media-meta-item"><i class="bi bi-person"></i> <?= htmlspecialchars($media['credits']) ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($media['description'])): ?>
                    <div class="media-description"><?= nl2br(htmlspecialchars($media['description'])) ?></div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <button class="action-btn <?= ($media['user_like_action'] ?? '') === 'like' ? 'active' : '' ?>" id="likeBtn" onclick="toggleLike(<?= (int)$media['id_media'] ?>)">
                        <i class="bi bi-hand-thumbs-up"></i> <span id="likeCount"><?= (int)($media['likes_count'] ?? 0) ?></span>
                    </button>
                    <button class="action-btn <?= ($media['user_like_action'] ?? '') === 'dislike' ? 'disliked' : '' ?>" id="dislikeBtn" onclick="toggleDislike(<?= (int)$media['id_media'] ?>)">
                        <i class="bi bi-hand-thumbs-down"></i> <span id="dislikeCount"><?= (int)($media['dislikes_count'] ?? 0) ?></span>
                    </button>
                    <button class="action-btn <?= ($media['is_favorite'] ?? 0) ? 'active' : '' ?>" id="favoriteBtn" onclick="toggleFavorite(<?= (int)$media['id_media'] ?>)">
                        <i class="bi bi-bookmark"></i> Favoris
                    </button>
                    <button class="action-btn" onclick="openShareModal()">
                        <i class="bi bi-share"></i> Partager
                    </button>
                </div>
                
                <div class="rating-section">
                    <div class="rating-stars" id="ratingStars">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star <?= ($media['user_rating'] ?? 0) >= $i ? 'bi-star-fill' : 'bi-star' ?>" onclick="rateMedia(<?= $i ?>, <?= (int)$media['id_media'] ?>)"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="rating-average">
                        <i class="bi bi-star-fill text-warning"></i>
                        <span id="ratingAverage"><?= number_format($media['rating_avg'] ?? 0, 1) ?></span>
                        <span>(<?= number_format($media['total_ratings'] ?? 0) ?> votes)</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="share-section" id="shareSection">
            <div class="share-title"><i class="bi bi-share-fill"></i> Partager ce média</div>
            <div class="share-buttons">
                <button class="share-btn share-whatsapp" onclick="shareOnWhatsApp()"><i class="bi bi-whatsapp"></i> WhatsApp</button>
                <button class="share-btn share-facebook" onclick="shareOnFacebook()"><i class="bi bi-facebook"></i> Facebook</button>
                <button class="share-btn share-twitter" onclick="shareOnTwitter()"><i class="bi bi-twitter"></i> Twitter</button>
                <button class="share-btn share-linkedin" onclick="shareOnLinkedIn()"><i class="bi bi-linkedin"></i> LinkedIn</button>
                <button class="share-btn share-copy" onclick="copyToClipboard()"><i class="bi bi-link-45deg"></i> Copier le lien</button>
            </div>
            <div class="share-link-container">
                <input type="text" class="share-link-input" id="shareLink" value="<?= base_url('media/detail/' . $mediaSlug) ?>" readonly>
                <button class="copy-btn" onclick="copyToClipboard()"><i class="bi bi-clipboard"></i> Copier</button>
            </div>
        </div>
        
        <div class="comments-section">
            <div class="comments-title"><i class="bi bi-chat-dots"></i> Commentaires (<?= (int)($media['comments_count'] ?? 0) ?>)</div>
            
            <?php if (isset($user) && $user): ?>
            <div class="comment-form">
                <textarea class="comment-input" id="commentText" rows="3" placeholder="Ajouter un commentaire..."></textarea>
                <button class="comment-submit" onclick="addComment(<?= (int)$media['id_media'] ?>)">
                    <i class="bi bi-send"></i> Publier
                </button>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> <a href="<?= base_url('auth/login') ?>" class="alert-link">Connectez-vous</a> pour laisser un commentaire.
            </div>
            <?php endif; ?>
            
            <div class="comment-list">
                <?php if (!empty($comments)): ?>
                    <?php foreach($comments as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-avatar">
                                <?php if (!empty($comment['photo']) && $comment['photo'] != 'default-avatar.png'): ?>
                                    <img src="<?= base_url('uploads/users/' . $comment['photo']) ?>" alt="<?= htmlspecialchars($comment['prenom'] ?? '') ?>">
                                <?php elseif (!empty($comment['user_id'])): ?>
                                    <div class="default-avatar">
                                        <?= strtoupper(substr($comment['prenom'] ?? 'V', 0, 1) . substr($comment['nom'] ?? 'i', 0, 1)) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="default-avatar">
                                        <i class="bi bi-person"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <div class="comment-author">
                                        <?php if (!empty($comment['user_id'])): ?>
                                            <strong><?= htmlspecialchars(($comment['prenom'] ?? '') . ' ' . ($comment['nom'] ?? '')) ?></strong>
                                            <?php if (isset($comment['type_utilisateur']) && $comment['type_utilisateur'] === 'admin'): ?>
                                                <span class="comment-author-badge">Admin</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <strong><?= htmlspecialchars($comment['author_name'] ?? 'Visiteur') ?></strong>
                                        <?php endif; ?>
                                    </div>
                                    <span><?= $comment['created_at_formatted'] ?? date('d/m/Y H:i', strtotime($comment['created_at'])) ?></span>
                                </div>
                                <div class="comment-text"><?= nl2br(htmlspecialchars($comment['comment'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-secondary p-4">
                        <i class="bi bi-chat display-4"></i>
                        <p>Soyez le premier à commenter !</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($recommended)): ?>
            <div class="related-section">
                <div class="related-title"><i class="bi bi-collection-play"></i> Vous aimerez aussi</div>
                <div class="related-grid">
                    <?php foreach($recommended as $related): 
                        $relatedSlug = !empty($related['slug']) ? $related['slug'] : $related['id_media'];
                    ?>
                        <div class="related-card" onclick="window.location.href='<?= base_url('media/detail/'.$relatedSlug) ?>'">
                            <div class="related-thumb" style="background-image: url('<?= htmlspecialchars($related['thumbnail_url'] ?? base_url('assets/images/default-thumbnail.jpg')) ?>')"></div>
                            <div class="related-info">
                                <p class="related-title-sm"><?= htmlspecialchars($related['titre']) ?></p>
                                <div class="related-meta"><i class="bi bi-eye"></i> <?= number_format($related['views_count'] ?? 0) ?> vues</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="text-center p-5">
            <i class="bi bi-exclamation-triangle display-1 mb-3"></i>
            <h3>Média non trouvé</h3>
            <a href="<?= base_url('media') ?>" class="btn btn-primary mt-3">Retour à l'accueil</a>
        </div>
    <?php endif; ?>
</main>

<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let audioElement = document.getElementById('audioElement');
let isPlaying = false;
let mediaQueue = [];
let currentQueueIndex = 0;
const mediaSlug = '<?= htmlspecialchars($mediaSlug ?? '') ?>';

if (audioElement) {
    audioElement.addEventListener('timeupdate', updateProgress);
    audioElement.addEventListener('ended', nextTrack);
    audioElement.addEventListener('loadedmetadata', function() {
        document.getElementById('totalTime').textContent = formatTime(audioElement.duration);
    });
}

function togglePlay() {
    if (!audioElement) return;
    if (isPlaying) {
        audioElement.pause();
        document.getElementById('playPauseBtn').innerHTML = '<i class="bi bi-play-fill"></i>';
    } else {
        audioElement.play();
        document.getElementById('playPauseBtn').innerHTML = '<i class="bi bi-pause-fill"></i>';
    }
    isPlaying = !isPlaying;
}

function updateProgress() {
    if (!audioElement) return;
    const percent = (audioElement.currentTime / audioElement.duration) * 100;
    document.getElementById('progressFill').style.width = percent + '%';
    document.getElementById('currentTime').textContent = formatTime(audioElement.currentTime);
}

function formatTime(seconds) {
    if (isNaN(seconds)) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}

function seekAudio(e) {
    if (!audioElement) return;
    const rect = e.currentTarget.getBoundingClientRect();
    const x = e.clientX - rect.left;
    audioElement.currentTime = (x / rect.width) * audioElement.duration;
}

function previousTrack() { if (currentQueueIndex > 0) currentQueueIndex--; }
function nextTrack() { if (currentQueueIndex < mediaQueue.length - 1) currentQueueIndex++; }

function toggleLike(mediaId) {
    const btn = document.getElementById('likeBtn');
    const isLiked = btn.classList.contains('active');
    fetch('<?= base_url('media/apiToggleLike') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}&action=${isLiked ? 'remove' : 'like'}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('likeCount').textContent = data.likes;
            document.getElementById('dislikeCount').textContent = data.dislikes;
            btn.classList.toggle('active', !isLiked);
            document.getElementById('dislikeBtn').classList.remove('disliked');
            showToast(isLiked ? 'Like retiré' : 'Like ajouté', 'success');
        }
    })
    .catch(() => showToast('Erreur', 'error'));
}

function toggleDislike(mediaId) {
    const btn = document.getElementById('dislikeBtn');
    const isDisliked = btn.classList.contains('disliked');
    fetch('<?= base_url('media/apiToggleLike') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}&action=${isDisliked ? 'remove' : 'dislike'}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('likeCount').textContent = data.likes;
            document.getElementById('dislikeCount').textContent = data.dislikes;
            btn.classList.toggle('disliked', !isDisliked);
            document.getElementById('likeBtn').classList.remove('active');
            showToast(isDisliked ? 'Dislike retiré' : 'Dislike ajouté', 'success');
        }
    })
    .catch(() => showToast('Erreur', 'error'));
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
            const btn = document.getElementById('favoriteBtn');
            btn.classList.toggle('active', data.is_favorite);
            showToast(data.message, 'success');
        } else if (data.need_login) {
            showToast('Veuillez vous connecter', 'warning');
            setTimeout(() => window.location.href = '<?= base_url('auth/login') ?>', 2000);
        }
    })
    .catch(() => showToast('Erreur', 'error'));
}

function rateMedia(rating, mediaId) {
    fetch('<?= base_url('media/apiRateMedia') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}&rating=${rating}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const stars = document.querySelectorAll('#ratingStars i');
            stars.forEach((s, i) => s.className = i < rating ? 'bi bi-star-fill' : 'bi bi-star');
            document.getElementById('ratingAverage').textContent = data.average;
            showToast('Merci !', 'success');
        }
    })
    .catch(() => showToast('Erreur', 'error'));
}

function addComment(mediaId) {
    const commentText = document.getElementById('commentText').value.trim();
    if (!commentText) return showToast('Veuillez écrire un commentaire', 'warning');
    
    fetch('<?= base_url('media/apiAddComment') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}&comment=${encodeURIComponent(commentText)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Commentaire ajouté !', 'success');
            document.getElementById('commentText').value = '';
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Erreur', 'error');
        }
    })
    .catch(() => showToast('Erreur', 'error'));
}

function getShareUrl() { return encodeURIComponent('<?= base_url('media/detail/') ?>' + mediaSlug); }
function getShareTitle() { return encodeURIComponent('<?= htmlspecialchars($media['titre'] ?? '') ?>'); }
function shareOnWhatsApp() { window.open(`https://wa.me/?text=${getShareTitle()}%20-%20${getShareUrl()}`, '_blank'); }
function shareOnFacebook() { window.open(`https://www.facebook.com/sharer/sharer.php?u=${getShareUrl()}`, '_blank'); }
function shareOnTwitter() { window.open(`https://twitter.com/intent/tweet?text=${getShareTitle()}&url=${getShareUrl()}`, '_blank'); }
function shareOnLinkedIn() { window.open(`https://www.linkedin.com/shareArticle?mini=true&url=${getShareUrl()}&title=${getShareTitle()}`, '_blank'); }
function copyToClipboard() { document.getElementById('shareLink').select(); document.execCommand('copy'); showToast('Lien copié !', 'success'); }
function openShareModal() { document.getElementById('shareSection').scrollIntoView({ behavior: 'smooth' }); }

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'warning' === type ? 'warning' : 'primary'} border-0`;
    toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    container.appendChild(toast);
    new bootstrap.Toast(toast).show();
    setTimeout(() => toast.remove(), 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    const currentRating = parseFloat('<?= $media['user_rating'] ?? 0 ?>');
    if (currentRating > 0) {
        const stars = document.querySelectorAll('#ratingStars i');
        stars.forEach((star, index) => star.className = index < currentRating ? 'bi bi-star-fill' : 'bi bi-star');
    }
});
</script>
</body>
</html>