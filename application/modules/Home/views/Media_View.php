<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
    /* Variables de couleur personnalisées */
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
        --danger: #dc3545;
        --success: #28a745;
        --warning: #ffc107;
        --info: #17a2b8;
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

    /* Hero section avec les nouvelles couleurs */
    .hero-section {
        margin-top: 100px;
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
    .hero-section::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -5%;
        width: 250px;
        height: 250px;
        background: var(--accent-green);
        opacity: 0.1;
        border-radius: 50%;
    }
    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
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
    .hero-stats {
        display: flex;
        gap: 30px;
        justify-content: center;
        flex-wrap: wrap;
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

    /* Grille de cartes */
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
        padding: 0 24px 40px;
        max-width: 1600px;
        margin: 0 auto;
    }

    .media-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        border: 1px solid var(--border-light);
        display: flex;
        flex-direction: column;
        box-shadow: var(--shadow-soft);
    }

    .media-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-hover);
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

    .play-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.2);
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

    .card-stats span {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Badge de type */
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
    .type-badge.document { background: var(--warning); color: #333; }

    /* Lightbox style - corrigé pour un affichage YouTube-like */
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
    .lightbox-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-bottom: 1px solid var(--yt-light-gray);
    }
    .lightbox-header h2 {
        font-size: 1.3rem;
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
    }
    .lightbox-close:hover { color: var(--yt-text); }

    .lightbox-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        min-height: 0; /* Nécessaire pour que flex fonctionne correctement */
    }

    .video-container {
        background: #000;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        flex: 1 1 auto; /* Prend tout l'espace disponible */
        min-height: 0;   /* Permet la réduction si nécessaire */
    }

    .video-container iframe,
    .video-container video,
    .video-container img {
        width: 100%;
        height: 100%;
        object-fit: contain; /* Conserve les proportions */
        background: #000;
    }

    .video-info {
        padding: 20px 24px;
        border-bottom: 1px solid var(--yt-light-gray);
    }

    .video-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: var(--yt-text);
    }

    .video-meta {
        display: flex;
        gap: 20px;
        color: var(--yt-text-secondary);
        font-size: 0.95rem;
        flex-wrap: wrap;
    }

    .video-description {
        padding: 20px 24px;
        color: var(--yt-text-secondary);
        line-height: 1.6;
        white-space: pre-wrap;
        border-bottom: 1px solid var(--yt-light-gray);
    }

    .lightbox-actions {
        padding: 16px 24px;
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .btn-like, .btn-share {
        background: var(--yt-light-gray);
        border: none;
        color: var(--yt-text);
        padding: 10px 20px;
        border-radius: 24px;
        font-weight: 600;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-like:hover, .btn-share:hover {
        background: var(--yt-hover, #3a3a3a);
    }

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
        transition: background 0.2s;
    }
    .lightbox-nav:hover { background: rgba(255,255,255,0.2); }
    .nav-prev { left: 20px; }
    .nav-next { right: 20px; }

    /* Mobile adjustments */
    @media (max-width: 768px) {
        .hero-title { font-size: 2.2rem; }
        .hero-stat { padding: 8px 16px; font-size: 0.9rem; }
        .media-grid { grid-template-columns: 1fr; padding: 0 16px; }
        .video-title { font-size: 1.2rem; }
        .lightbox-nav { width: 40px; height: 40px; }
    }

    /* État vide */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: var(--text-muted);
    }
    .empty-icon { font-size: 5rem; margin-bottom: 20px; opacity: 0.5; color: var(--primary-green); }
</style>

<?php
// Fonction pour extraire l'ID YouTube
function get_youtube_id($url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches);
    return $matches[1] ?? null;
}

// Préparation des données
$galleryData = [];
if (!empty($medias)) {
    foreach ($medias as $media) {
        $item = (array)$media;
        $item['youtube_id'] = null;
        if ($item['type'] === 'link' && !empty($item['lien'])) {
            $item['youtube_id'] = get_youtube_id($item['lien']);
        }
        // Ajout de métadonnées fictives pour l'exemple (vous pouvez les remplacer par des données réelles)
        $item['views'] = rand(1000, 500000);
        $item['duration'] = $item['duree'] ?? sprintf('%d:%02d', rand(1, 15), rand(0, 59));
        $galleryData[] = $item;
    }
}
?>

<!-- Hero Section aux couleurs personnalisées -->
<section class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title">
            <i class="fas fa-play-circle"></i> Médiathèque
        </h1>
        <p class="hero-subtitle">Vidéos, tutoriels, podcasts et documents à portée de clic</p>
        <div class="hero-stats">
            <div class="hero-stat"><i class="fas fa-video"></i> <?= count(array_filter($galleryData, fn($m) => $m['type'] === 'video')) ?> Vidéos</div>
            <div class="hero-stat"><i class="fas fa-headphones"></i> <?= count(array_filter($galleryData, fn($m) => $m['type'] === 'audio')) ?> Audio</div>
            <div class="hero-stat"><i class="fas fa-image"></i> <?= count(array_filter($galleryData, fn($m) => $m['type'] === 'image')) ?> Photos</div>
            <div class="hero-stat"><i class="fas fa-file-alt"></i> <?= count($galleryData) ?> Total</div>
        </div>
    </div>
</section>

<!-- Grille des médias -->
<div class="media-grid" id="mediaGrid">
    <?php if (!empty($galleryData)): ?>
        <?php foreach ($galleryData as $index => $media): 
            // Déterminer la miniature
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

            // Définir la classe du badge de type
            $badgeClass = '';
            $badgeIcon = '';
            switch ($media['type']) {
                case 'video': $badgeClass = 'video'; $badgeIcon = 'fa-video'; break;
                case 'audio': $badgeClass = 'audio'; $badgeIcon = 'fa-headphones'; break;
                case 'image': $badgeClass = 'image'; $badgeIcon = 'fa-image'; break;
                case 'document': $badgeClass = 'document'; $badgeIcon = 'fa-file-alt'; break;
                case 'link': $badgeClass = 'youtube'; $badgeIcon = 'fa-link'; break;
                default: $badgeClass = ''; $badgeIcon = 'fa-file';
            }
            if (!empty($media['youtube_id'])) {
                $badgeClass = 'youtube';
                $badgeIcon = 'fa-youtube';
            }
        ?>
        <div class="media-card" data-index="<?= $index ?>" onclick="openLightbox(<?= $index ?>)">
            <div class="thumbnail-wrap">
                <img src="<?= $thumb_url ?>" class="thumbnail-img" alt="<?= htmlspecialchars($media['titre']) ?>" loading="lazy">
                <span class="duration-badge"><?= $media['duration'] ?></span>
                <span class="type-badge <?= $badgeClass ?>"><i class="fab <?= $badgeIcon ?>"></i> <?= ucfirst($badgeClass) ?></span>
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
                    <div class="card-stats">
                        <span><i class="fas fa-eye"></i> <?= number_format($media['views']) ?> vues</span>
                        <span>•</span>
                        <span><?= date('d M Y', strtotime($media['created_at'] ?? $media['date_media'] ?? 'now')) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-photo-video empty-icon"></i>
            <h3>Aucun média pour le moment</h3>
            <p>Revenez bientôt, nous ajoutons régulièrement du contenu.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Lightbox -->
<div class="modal fade media-lightbox" id="mediaLightbox" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="lightbox-header">
                <h2 id="lightboxHeaderTitle">Titre</h2>
                <button type="button" class="lightbox-close" data-bs-dismiss="modal" aria-label="Fermer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="lightbox-body" id="lightboxBody">
                <div class="video-container" id="videoContainer"></div>
                <div class="video-info">
                    <div class="video-title" id="videoTitle">Titre</div>
                    <div class="video-meta">
                        <span id="videoViews"><i class="fas fa-eye"></i> 0 vues</span>
                        <span id="videoDate"><i class="fas fa-calendar-alt"></i> date</span>
                    </div>
                </div>
                <div class="video-description" id="videoDescription">Description...</div>
                <div class="lightbox-actions">
                    <button class="btn-like" id="likeButton"><i class="fas fa-thumbs-up"></i> J'aime</button>
                    <button class="btn-share" id="shareButton"><i class="fas fa-share-alt"></i> Partager</button>
                </div>
            </div>
        </div>
    </div>
    <button class="lightbox-nav nav-prev" onclick="navigateMedia(-1)"><i class="fas fa-chevron-left"></i></button>
    <button class="lightbox-nav nav-next" onclick="navigateMedia(1)"><i class="fas fa-chevron-right"></i></button>
</div>

<script>
// Données PHP passées à JavaScript
const galleryData = <?= json_encode($galleryData) ?>;
let currentIndex = 0;
let currentModal = null;

// Ouvrir la lightbox
function openLightbox(index) {
    currentIndex = index;
    const media = galleryData[index];
    if (!media) return;

    document.getElementById('lightboxHeaderTitle').textContent = media.titre || 'Sans titre';
    document.getElementById('videoTitle').textContent = media.titre || 'Sans titre';
    document.getElementById('videoDescription').textContent = media.description || 'Aucune description disponible.';
    document.getElementById('videoViews').innerHTML = `<i class="fas fa-eye"></i> ${Number(media.views).toLocaleString()} vues`;
    const date = new Date(media.created_at || media.date_media || Date.now());
    document.getElementById('videoDate').innerHTML = `<i class="fas fa-calendar-alt"></i> ${date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })}`;

    // Générer le lecteur
    const container = document.getElementById('videoContainer');
    let playerHtml = '';

    if (media.youtube_id) {
        // YouTube
        playerHtml = `<iframe src="https://www.youtube.com/embed/${media.youtube_id}?autoplay=1&rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>`;
    } else if (media.type === 'video' && media.fichier) {
        // Vidéo locale (chemin complet stocké)
        playerHtml = `<video controls autoplay><source src="<?= base_url('') ?>${media.fichier}" type="video/mp4">Votre navigateur ne supporte pas la vidéo.</video>`;
    } else if (media.type === 'audio' && media.fichier) {
        // Audio local
        playerHtml = `<div style="background:#333; width:100%; padding:40px; text-align:center;"><audio controls src="<?= base_url('') ?>${media.fichier}" style="width:100%;"></audio></div>`;
    } else if (media.type === 'image' && media.fichier) {
        // Image (plus de style inline, le CSS gère le dimensionnement)
        playerHtml = `<img src="<?= base_url('') ?>${media.fichier}" alt="${media.titre}">`;
    } else if (media.type === 'document' && media.fichier) {
        // Document
        playerHtml = `<div style="text-align:center; padding:40px;"><a href="<?= base_url('') ?>${media.fichier}" target="_blank" style="color:#3ea6ff;">Télécharger le document</a></div>`;
    } else if (media.type === 'link' && media.lien && !media.youtube_id) {
        // Autre lien externe
        playerHtml = `<div style="text-align:center; padding:40px;"><a href="${media.lien}" target="_blank" style="color:#3ea6ff;">Ouvrir le lien externe</a></div>`;
    } else {
        playerHtml = `<div style="color:#fff; padding:40px;">Aucun aperçu disponible.</div>`;
    }

    container.innerHTML = playerHtml;

    // Afficher la modale
    if (currentModal) currentModal.dispose();
    currentModal = new bootstrap.Modal(document.getElementById('mediaLightbox'));
    currentModal.show();

    // Bouton partage
    document.getElementById('shareButton').onclick = () => shareMedia(media);
}

// Navigation
function navigateMedia(direction) {
    let newIndex = currentIndex + direction;
    if (newIndex < 0) newIndex = galleryData.length - 1;
    if (newIndex >= galleryData.length) newIndex = 0;
    openLightbox(newIndex);
}

// Partage
function shareMedia(media) {
    const shareData = {
        title: media.titre || 'Média',
        text: media.description || 'Découvrez ce média',
        url: media.lien || window.location.href
    };
    if (navigator.share) {
        navigator.share(shareData).catch(() => {});
    } else {
        navigator.clipboard.writeText(shareData.url).then(() => {
            alert('Lien copié dans le presse-papier !');
        });
    }
}

// Gestion clavier
document.addEventListener('keydown', (e) => {
    if (!currentModal || !document.getElementById('mediaLightbox').classList.contains('show')) return;
    if (e.key === 'ArrowLeft') navigateMedia(-1);
    if (e.key === 'ArrowRight') navigateMedia(1);
    if (e.key === 'Escape') currentModal.hide();
});

// Nettoyage
document.getElementById('mediaLightbox').addEventListener('hidden.bs.modal', function () {
    document.getElementById('videoContainer').innerHTML = '';
});

// Animation d'apparition
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.media-card');
    cards.forEach((card, i) => {
        card.style.animation = `fadeInUp 0.4s ease ${i * 0.05}s forwards`;
    });
});

// Style animation
const style = document.createElement('style');
style.innerHTML = `
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.media-card { opacity: 0; animation-fill-mode: forwards; }`;
document.head.appendChild(style);
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>