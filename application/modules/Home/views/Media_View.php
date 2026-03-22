<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
    :root {
        --nufotec-primary: #0B4F2E;
        --nufotec-secondary: #1B7B4B;
        --nufotec-accent: #27ae60;
        --nufotec-gold: #FFD700;
        --bg-primary: #f9f9f9;
        --bg-secondary: #ffffff;
        --text-primary: #030303;
        --text-secondary: #606060;
        --border-color: #e5e5e5;
        --shadow-sm: 0 1px 2px rgba(0,0,0,0.1);
        --shadow-md: 0 4px 12px rgba(0,0,0,0.15);
        --shadow-lg: 0 8px 24px rgba(0,0,0,0.2);
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --wa-green: #00a884;
        --wa-dark: #111b21;
        --yt-red: #ff0000;
        --book-paper: #f4ecd8;
        --book-sepia: #704214;
    }

    * { -webkit-tap-highlight-color: transparent; }

    body {
        font-family: 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
        background: var(--bg-primary);
        margin: 0;
        padding: 0;
        color: var(--text-primary);
        -webkit-font-smoothing: antialiased;
    }

    .nufotec-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 56px;
        background: rgba(255,255,255,0.98);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--border-color);
        z-index: 1000;
        display: flex;
        align-items: center;
        padding: 0 16px;
        gap: 12px;
    }

    .nufotec-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        color: var(--nufotec-primary);
        font-weight: 700;
        font-size: 1.2rem;
    }

    .nufotec-logo-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, var(--nufotec-primary), var(--nufotec-accent));
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .nufotec-search-container {
        flex: 1;
        max-width: 640px;
        position: relative;
    }

    .nufotec-search-wrapper {
        display: flex;
        align-items: center;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        padding: 0 4px 0 16px;
        height: 40px;
    }

    .nufotec-search-wrapper:focus-within {
        border-color: var(--nufotec-primary);
        background: white;
        box-shadow: 0 0 0 3px rgba(11, 79, 46, 0.1);
    }

    .nufotec-search-input {
        flex: 1;
        border: none;
        background: transparent;
        font-size: 0.95rem;
        outline: none;
    }

    .nufotec-search-btn {
        width: 36px;
        height: 36px;
        border: none;
        background: transparent;
        border-radius: 50%;
        cursor: pointer;
        color: var(--text-secondary);
    }

    .nufotec-search-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        background: white;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-lg);
        max-height: 480px;
        overflow-y: auto;
        display: none;
        z-index: 1001;
    }

    .nufotec-search-dropdown.active { display: block; }

    .nufotec-search-suggestion {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        cursor: pointer;
        transition: background 0.15s;
    }

    .nufotec-search-suggestion:hover { background: var(--bg-primary); }

    .nufotec-search-suggestion-thumb {
        width: 80px;
        height: 45px;
        border-radius: var(--radius-sm);
        object-fit: cover;
    }

    .nufotec-search-suggestion-info { flex: 1; min-width: 0; }

    .nufotec-search-suggestion-title {
        font-weight: 500;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nufotec-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nufotec-header-btn {
        width: 40px;
        height: 40px;
        border: none;
        background: transparent;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-primary);
        font-size: 1.1rem;
    }

    .nufotec-main {
        margin-top: 56px;
        min-height: calc(100vh - 56px);
        padding-bottom: 80px;
    }

    .nufotec-categories {
        position: sticky;
        top: 56px;
        background: rgba(255,255,255,0.98);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--border-color);
        z-index: 100;
        padding: 12px 16px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .nufotec-categories::-webkit-scrollbar { display: none; }

    .nufotec-categories-inner {
        display: flex;
        gap: 8px;
        width: max-content;
    }

    .nufotec-chip {
        padding: 8px 16px;
        border: 1px solid var(--border-color);
        background: var(--bg-primary);
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .nufotec-chip.active {
        background: var(--nufotec-primary);
        color: white;
        border-color: var(--nufotec-primary);
    }

    .nufotec-hero {
        position: relative;
        background: linear-gradient(135deg, var(--nufotec-primary) 0%, var(--nufotec-secondary) 100%);
        padding: 40px 24px;
        margin: 0 0 24px 0;
        overflow: hidden;
        color: white;
        text-align: center;
    }

    .nufotec-hero-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0 0 12px 0;
    }

    .nufotec-hero-subtitle {
        font-size: 1.1rem;
        opacity: 0.95;
        margin: 0 0 24px 0;
    }

    .nufotec-hero-stats {
        display: flex;
        justify-content: center;
        gap: 24px;
        flex-wrap: wrap;
    }

    .nufotec-hero-stat {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.15);
        padding: 8px 16px;
        border-radius: 24px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .nufotec-content {
        padding: 0 24px 40px;
        max-width: 1600px;
        margin: 0 auto;
    }

    .nufotec-section-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .nufotec-grid {
        display: grid;
        gap: 16px;
        margin-bottom: 40px;
    }

    .nufotec-grid--video { grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); }
    .nufotec-grid--audio { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
    .nufotec-grid--image { grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); }
    .nufotec-grid--document { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }

    /* YouTube Video Card */
    .nufotec-card--video {
        cursor: pointer;
        transition: transform 0.2s;
    }

    .nufotec-card--video:hover { transform: translateY(-4px); }

    .nufotec-video-thumb {
        position: relative;
        aspect-ratio: 16/9;
        border-radius: var(--radius-md);
        overflow: hidden;
        background: #000;
    }

    .nufotec-video-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .nufotec-card--video:hover .nufotec-video-thumb img { transform: scale(1.05); }

    .nufotec-video-duration {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 3px 6px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .nufotec-video-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .nufotec-card--video:hover .nufotec-video-overlay { opacity: 1; }

    .nufotec-video-play {
        width: 48px;
        height: 48px;
        background: rgba(255,255,255,0.95);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--yt-red);
        font-size: 1.2rem;
    }

    .nufotec-video-info {
        display: flex;
        gap: 12px;
        padding: 12px 0;
    }

    .nufotec-video-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--nufotec-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
    }

    .nufotec-video-meta { flex: 1; min-width: 0; }

    .nufotec-video-title {
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.4;
        margin: 0 0 6px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .nufotec-video-channel {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }

    .nufotec-video-stats {
        font-size: 0.8rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* WhatsApp Audio Card */
    .nufotec-card--audio {
        background: white;
        border-radius: var(--radius-md);
        padding: 16px;
        box-shadow: var(--shadow-sm);
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid var(--border-color);
    }

    .nufotec-card--audio:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .nufotec-audio-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .nufotec-audio-cover {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-sm);
        background: linear-gradient(135deg, var(--wa-green), var(--nufotec-accent));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .nufotec-audio-info { flex: 1; min-width: 0; }

    .nufotec-audio-title {
        font-weight: 600;
        font-size: 0.95rem;
        margin: 0 0 4px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nufotec-audio-artist {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .nufotec-audio-waveform {
        height: 40px;
        background: var(--bg-primary);
        border-radius: 20px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        padding: 0 16px;
        gap: 3px;
    }

    .nufotec-waveform-bar {
        flex: 1;
        background: var(--wa-green);
        border-radius: 2px;
        opacity: 0.6;
    }

    .nufotec-audio-controls {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .nufotec-audio-play {
        width: 40px;
        height: 40px;
        background: var(--wa-green);
        border: none;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .nufotec-audio-time {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .nufotec-audio-actions { display: flex; gap: 8px; }

    .nufotec-audio-btn {
        width: 32px;
        height: 32px;
        border: none;
        background: transparent;
        border-radius: 50%;
        color: var(--text-secondary);
        cursor: pointer;
    }

    /* Facebook Image Card */
    .nufotec-card--image {
        position: relative;
        border-radius: var(--radius-md);
        overflow: hidden;
        cursor: pointer;
        background: var(--bg-secondary);
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s;
    }

    .nufotec-card--image:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .nufotec-image-container {
        position: relative;
        aspect-ratio: 1;
        overflow: hidden;
    }

    .nufotec-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .nufotec-card--image:hover .nufotec-image-container img { transform: scale(1.05); }

    .nufotec-image-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 50%);
        opacity: 0;
        transition: opacity 0.2s;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 16px;
    }

    .nufotec-card--image:hover .nufotec-image-overlay { opacity: 1; }

    .nufotec-image-title {
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .nufotec-image-stats {
        display: flex;
        gap: 16px;
        color: rgba(255,255,255,0.9);
        font-size: 0.8rem;
    }

    .nufotec-image-stat {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* eBook Document Card */
    .nufotec-card--document {
        cursor: pointer;
        transition: transform 0.2s;
    }

    .nufotec-card--document:hover { transform: translateY(-4px); }

    .nufotec-book-cover {
        position: relative;
        aspect-ratio: 3/4;
        border-radius: var(--radius-sm);
        overflow: hidden;
        background: var(--book-paper);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1), 0 4px 8px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        padding: 20px;
    }

    .nufotec-book-spine {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 12px;
        background: linear-gradient(to right, rgba(0,0,0,0.2), transparent);
    }

    .nufotec-book-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
        border: 2px solid var(--book-sepia);
        border-radius: 4px;
        padding: 16px;
    }

    .nufotec-book-icon {
        font-size: 3rem;
        color: var(--book-sepia);
        margin-bottom: 12px;
        opacity: 0.8;
    }

    .nufotec-book-title {
        font-family: 'Georgia', serif;
        font-size: 0.9rem;
        font-weight: 600;
        color: #2c2c2c;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .nufotec-book-author {
        font-size: 0.75rem;
        color: var(--book-sepia);
        margin-top: 8px;
    }

    .nufotec-book-meta { padding: 12px 0 0 0; }

    .nufotec-book-filename {
        font-size: 0.85rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nufotec-book-type {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-top: 4px;
    }

    /* Lightbox */
    .nufotec-lightbox {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.95);
        z-index: 9999;
        display: none;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .nufotec-lightbox.active {
        display: flex;
        opacity: 1;
    }

    .nufotec-lightbox-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.1);
        border: none;
        border-radius: 50%;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        z-index: 10;
    }

    .nufotec-lightbox-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 60px 24px;
    }

    .nufotec-lightbox-media {
        max-width: 100%;
        max-height: 70vh;
        border-radius: var(--radius-md);
    }

    .nufotec-lightbox-info {
        color: white;
        text-align: center;
        margin-top: 24px;
        max-width: 600px;
    }

    .nufotec-lightbox-title {
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 8px;
    }

    /* Audio Player */
    .nufotec-audio-player {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: var(--wa-dark);
        padding: 16px 24px;
        z-index: 1000;
        transform: translateY(100%);
        transition: transform 0.3s ease;
    }

    .nufotec-audio-player.active { transform: translateY(0); }

    .nufotec-audio-player-inner {
        max-width: 800px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .nufotec-player-cover {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-sm);
        background: linear-gradient(135deg, var(--wa-green), var(--nufotec-accent));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .nufotec-player-info { flex: 1; min-width: 0; }

    .nufotec-player-title {
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nufotec-player-artist {
        color: rgba(255,255,255,0.6);
        font-size: 0.8rem;
    }

    .nufotec-player-controls {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .nufotec-player-btn {
        width: 40px;
        height: 40px;
        border: none;
        background: transparent;
        color: white;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.2rem;
    }

    .nufotec-player-btn.play {
        width: 48px;
        height: 48px;
        background: var(--wa-green);
    }

    .nufotec-player-progress {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: rgba(255,255,255,0.1);
        cursor: pointer;
    }

    .nufotec-player-progress-bar {
        height: 100%;
        background: var(--wa-green);
        width: 30%;
    }

    /* FAB */
    .nufotec-fab {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 56px;
        height: 56px;
        background: var(--nufotec-primary);
        border: none;
        border-radius: 50%;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(11, 79, 46, 0.4);
        z-index: 999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Toast */
    .nufotec-toast-container {
        position: fixed;
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: 8px;
        pointer-events: none;
    }

    .nufotec-toast {
        background: rgba(0,0,0,0.9);
        color: white;
        padding: 12px 24px;
        border-radius: 24px;
        font-size: 0.9rem;
        font-weight: 500;
        animation: toastIn 0.3s ease;
    }

    @keyframes toastIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .nufotec-toast.success { background: var(--nufotec-primary); }
    .nufotec-toast.error { background: #dc3545; }

    /* Empty State */
    .nufotec-empty {
        text-align: center;
        padding: 80px 24px;
        color: var(--text-secondary);
    }

    .nufotec-empty-icon {
        width: 120px;
        height: 120px;
        background: var(--bg-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 3rem;
        color: var(--nufotec-primary);
        opacity: 0.5;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .nufotec-logo-text { display: none; }

        .nufotec-search-container {
            position: fixed;
            top: 56px;
            left: 0;
            right: 0;
            max-width: none;
            padding: 8px 16px;
            background: white;
            border-bottom: 1px solid var(--border-color);
            transform: translateY(-100%);
            opacity: 0;
            transition: all 0.3s;
            pointer-events: none;
        }

        .nufotec-search-container.active {
            transform: translateY(0);
            opacity: 1;
            pointer-events: all;
        }

        .nufotec-hero { padding: 32px 16px; }
        .nufotec-hero-title { font-size: 1.6rem; }
        .nufotec-content { padding: 0 12px 32px; }

        .nufotec-grid--video,
        .nufotec-grid--audio,
        .nufotec-grid--document { grid-template-columns: 1fr; }

        .nufotec-grid--image { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 480px) {
        .nufotec-grid--image { grid-template-columns: 1fr; }
    }
</style>

<?php
function getYoutubeId($url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches);
    return $matches[1] ?? null;
}

function formatDuration($seconds) {
    if (!$seconds) return '0:00';
    $hours = floor($seconds / 3600);
    $mins = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    if ($hours > 0) return sprintf('%d:%02d:%02d', $hours, $mins, $secs);
    return sprintf('%d:%02d', $mins, $secs);
}

function formatFileSize($bytes) {
    if (!$bytes) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $unitIndex = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $unitIndex), 2) . ' ' . $units[$unitIndex];
}

$videos = []; $audios = []; $images = []; $documents = []; $links = [];

foreach ($medias as $media) {
    $m = (array)$media;
    $m['youtube_id'] = ($m['type'] === 'link') ? getYoutubeId($m['lien'] ?? '') : null;

    switch ($m['type']) {
        case 'video': $videos[] = $m; break;
        case 'audio': $audios[] = $m; break;
        case 'image': $images[] = $m; break;
        case 'document': $documents[] = $m; break;
        case 'link': $links[] = $m; break;
    }
}

$allMedias = array_merge($videos, $audios, $images, $documents, $links);
?>

<header class="nufotec-header" id="header">
    <a href="<?= base_url() ?>" class="nufotec-logo">
        <div class="nufotec-logo-icon"><i class="fas fa-leaf"></i></div>
        <span class="nufotec-logo-text">NUFOTEC</span>
    </a>

    <div class="nufotec-search-container" id="searchContainer">
        <div class="nufotec-search-wrapper">
            <input type="text" class="nufotec-search-input" id="searchInput" placeholder="Rechercher..." autocomplete="off">
            <button class="nufotec-search-btn" id="searchBtn"><i class="fas fa-search"></i></button>
        </div>
        <div class="nufotec-search-dropdown" id="searchDropdown"></div>
    </div>

    <div class="nufotec-header-actions">
        <button class="nufotec-header-btn" id="searchToggleBtn"><i class="fas fa-search"></i></button>
        <button class="nufotec-header-btn"><i class="fas fa-user-circle"></i></button>
    </div>
</header>

<main class="nufotec-main">
    <section class="nufotec-hero">
        <div class="nufotec-hero-content">
            <h1 class="nufotec-hero-title">Médiathèque Nufotec</h1>
            <p class="nufotec-hero-subtitle">Découvrez nos ressources multimédias</p>
            <div class="nufotec-hero-stats">
                <div class="nufotec-hero-stat"><i class="fas fa-play-circle"></i> <?= count($videos) + count($links) ?> vidéos</div>
                <div class="nufotec-hero-stat"><i class="fas fa-music"></i> <?= count($audios) ?> audio</div>
                <div class="nufotec-hero-stat"><i class="fas fa-file-alt"></i> <?= count($documents) ?> documents</div>
            </div>
        </div>
    </section>

    <nav class="nufotec-categories">
        <div class="nufotec-categories-inner">
            <button class="nufotec-chip active" data-filter="all"><i class="fas fa-th-large"></i> Tout</button>
            <button class="nufotec-chip" data-filter="video"><i class="fas fa-video"></i> Vidéos</button>
            <button class="nufotec-chip" data-filter="audio"><i class="fas fa-music"></i> Audio</button>
            <button class="nufotec-chip" data-filter="image"><i class="fas fa-images"></i> Photos</button>
            <button class="nufotec-chip" data-filter="document"><i class="fas fa-book"></i> Documents</button>
        </div>
    </nav>

    <div class="nufotec-content">

        <?php if (!empty($videos) || !empty($links)): ?>
        <section class="nufotec-section" data-section="video">
            <h2 class="nufotec-section-title"><i class="fab fa-youtube"></i> Vidéos</h2>
            <div class="nufotec-grid nufotec-grid--video">
                <?php foreach (array_merge($videos, $links) as $media): 
                    $thumb = !empty($media['youtube_id']) ? "https://img.youtube.com/vi/{$media['youtube_id']}/hqdefault.jpg" : (!empty($media['miniature']) ? base_url($media['miniature']) : base_url('assets/images/video-thumb.jpg'));
                    $duration = $media['duree'] ? formatDuration($media['duree']) : '0:00';
                ?>
                <article class="nufotec-card--video" data-id="<?= $media['id_media'] ?>" data-type="video" data-title="<?= htmlspecialchars(strtolower($media['titre'])) ?>" onclick="openVideoPlayer(<?= htmlspecialchars(json_encode($media)) ?>)">
                    <div class="nufotec-video-thumb">
                        <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($media['titre']) ?>" loading="lazy">
                        <span class="nufotec-video-duration"><?= $duration ?></span>
                        <div class="nufotec-video-overlay">
                            <div class="nufotec-video-play"><i class="fas fa-play"></i></div>
                        </div>
                    </div>
                    <div class="nufotec-video-info">
                        <div class="nufotec-video-avatar"><i class="fas fa-play"></i></div>
                        <div class="nufotec-video-meta">
                            <h3 class="nufotec-video-title"><?= htmlspecialchars($media['titre']) ?></h3>
                            <div class="nufotec-video-channel"><?= htmlspecialchars($media['categorie'] ?? 'NUFOTEC') ?></div>
                            <div class="nufotec-video-stats">
                                <span><?= number_format($media['views_count'] ?? 0) ?> vues</span>
                                <span>•</span>
                                <span><?= date('d M Y', strtotime($media['created_at'] ?? 'now')) ?></span>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($audios)): ?>
        <section class="nufotec-section" data-section="audio">
            <h2 class="nufotec-section-title"><i class="fas fa-headphones-alt"></i> Audio</h2>
            <div class="nufotec-grid nufotec-grid--audio">
                <?php foreach ($audios as $media): 
                    $duration = $media['duree'] ? formatDuration($media['duree']) : '0:00';
                ?>
                <article class="nufotec-card--audio" data-id="<?= $media['id_media'] ?>" data-type="audio" data-title="<?= htmlspecialchars(strtolower($media['titre'])) ?>" onclick="playAudio(<?= htmlspecialchars(json_encode($media)) ?>)">
                    <div class="nufotec-audio-header">
                        <div class="nufotec-audio-cover"><i class="fas fa-music"></i></div>
                        <div class="nufotec-audio-info">
                            <h3 class="nufotec-audio-title"><?= htmlspecialchars($media['titre']) ?></h3>
                            <div class="nufotec-audio-artist"><?= htmlspecialchars($media['credits'] ?? 'Artiste inconnu') ?></div>
                        </div>
                    </div>
                    <div class="nufotec-audio-waveform">
                        <?php for($i=0; $i<20; $i++): ?><div class="nufotec-waveform-bar" style="height: <?= rand(30, 90) ?>%"></div><?php endfor; ?>
                    </div>
                    <div class="nufotec-audio-controls">
                        <button class="nufotec-audio-play"><i class="fas fa-play"></i></button>
                        <span class="nufotec-audio-time"><?= $duration ?></span>
                        <div class="nufotec-audio-actions">
                            <button class="nufotec-audio-btn" onclick="event.stopPropagation(); shareMedia(<?= $media['id_media'] ?>)"><i class="fas fa-share"></i></button>
                            <button class="nufotec-audio-btn" onclick="event.stopPropagation(); downloadMedia(<?= $media['id_media'] ?>)"><i class="fas fa-download"></i></button>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($images)): ?>
        <section class="nufotec-section" data-section="image">
            <h2 class="nufotec-section-title"><i class="fas fa-camera-retro"></i> Photos</h2>
            <div class="nufotec-grid nufotec-grid--image">
                <?php foreach ($images as $media): 
                    $thumb = !empty($media['fichier']) ? base_url($media['fichier']) : base_url('assets/images/photo-placeholder.jpg');
                ?>
                <article class="nufotec-card--image" data-id="<?= $media['id_media'] ?>" data-type="image" data-title="<?= htmlspecialchars(strtolower($media['titre'])) ?>" onclick="openLightbox('<?= $thumb ?>', '<?= htmlspecialchars($media['titre']) ?>')">
                    <div class="nufotec-image-container">
                        <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($media['titre']) ?>" loading="lazy">
                        <div class="nufotec-image-overlay">
                            <div class="nufotec-image-title"><?= htmlspecialchars($media['titre']) ?></div>
                            <div class="nufotec-image-stats">
                                <span class="nufotec-image-stat"><i class="fas fa-heart"></i> <?= $media['likes_count'] ?? 0 ?></span>
                                <span class="nufotec-image-stat"><i class="fas fa-comment"></i> <?= $media['comments_count'] ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($documents)): ?>
        <section class="nufotec-section" data-section="document">
            <h2 class="nufotec-section-title"><i class="fas fa-book-open"></i> Documents</h2>
            <div class="nufotec-grid nufotec-grid--document">
                <?php foreach ($documents as $media): 
                    $ext = pathinfo($media['fichier'] ?? '', PATHINFO_EXTENSION);
                    $icons = ['pdf' => 'fa-file-pdf', 'doc' => 'fa-file-word', 'docx' => 'fa-file-word', 'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel'];
                    $icon = $icons[strtolower($ext)] ?? 'fa-file-alt';
                ?>
                <article class="nufotec-card--document" data-id="<?= $media['id_media'] ?>" data-type="document" data-title="<?= htmlspecialchars(strtolower($media['titre'])) ?>" onclick="openDocument(<?= htmlspecialchars(json_encode($media)) ?>)">
                    <div class="nufotec-book-cover">
                        <div class="nufotec-book-spine"></div>
                        <div class="nufotec-book-content">
                            <i class="fas <?= $icon ?> nufotec-book-icon"></i>
                            <div class="nufotec-book-title"><?= htmlspecialchars($media['titre']) ?></div>
                            <div class="nufotec-book-author"><?= htmlspecialchars($media['credits'] ?? 'NUFOTEC') ?></div>
                        </div>
                    </div>
                    <div class="nufotec-book-meta">
                        <div class="nufotec-book-filename"><?= htmlspecialchars($media['titre']) ?></div>
                        <div class="nufotec-book-type"><?= strtoupper($ext) ?> • <?= formatFileSize($media['taille'] ?? 0) ?></div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (empty($allMedias)): ?>
        <div class="nufotec-empty">
            <div class="nufotec-empty-icon"><i class="fas fa-photo-video"></i></div>
            <h3>Aucun média disponible</h3>
            <p>La médiathèque est vide pour le moment.</p>
        </div>
        <?php endif; ?>

    </div>
</main>

<div class="nufotec-audio-player" id="audioPlayer">
    <div class="nufotec-player-progress" id="playerProgress"><div class="nufotec-player-progress-bar" id="progressBar"></div></div>
    <div class="nufotec-audio-player-inner">
        <div class="nufotec-player-cover"><i class="fas fa-music"></i></div>
        <div class="nufotec-player-info">
            <div class="nufotec-player-title" id="playerTitle">Titre</div>
            <div class="nufotec-player-artist" id="playerArtist">Artiste</div>
        </div>
        <div class="nufotec-player-controls">
            <button class="nufotec-player-btn play" id="playerPlay"><i class="fas fa-pause"></i></button>
            <button class="nufotec-player-btn" id="playerClose"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <audio id="audioElement" preload="metadata"></audio>
</div>

<div class="nufotec-lightbox" id="lightbox">
    <button class="nufotec-lightbox-close" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
    <div class="nufotec-lightbox-content">
        <img src="" alt="" class="nufotec-lightbox-media" id="lightboxImg">
        <div class="nufotec-lightbox-info">
            <h3 class="nufotec-lightbox-title" id="lightboxTitle"></h3>
        </div>
    </div>
</div>

<button class="nufotec-fab" id="fab" onclick="showToast('Fonctionnalité à venir', 'info')"><i class="fas fa-plus"></i></button>

<div class="nufotec-toast-container" id="toastContainer"></div>

<script>
const baseUrl = '<?= base_url() ?>';
let currentAudio = null;
let isPlaying = false;

// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().trim();
    filterContent(query);
});

document.getElementById('searchToggleBtn').addEventListener('click', function() {
    document.getElementById('searchContainer').classList.toggle('active');
});

// Category filters
document.querySelectorAll('.nufotec-chip').forEach(chip => {
    chip.addEventListener('click', function() {
        document.querySelectorAll('.nufotec-chip').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        filterByCategory(filter);
    });
});

function filterContent(query) {
    document.querySelectorAll('.nufotec-card--video, .nufotec-card--audio, .nufotec-card--image, .nufotec-card--document').forEach(card => {
        const title = card.dataset.title;
        if (title.includes(query)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function filterByCategory(filter) {
    document.querySelectorAll('.nufotec-section').forEach(section => {
        if (filter === 'all' || section.dataset.section === filter) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
}

function openVideoPlayer(media) {
    if (media.youtube_id) {
        window.open(`https://www.youtube.com/watch?v=${media.youtube_id}`, '_blank');
    } else if (media.fichier) {
        window.open(baseUrl + media.fichier, '_blank');
    }
    showToast('Lecture vidéo', 'success');
}

function playAudio(media) {
    const player = document.getElementById('audioPlayer');
    const audio = document.getElementById('audioElement');
    const title = document.getElementById('playerTitle');
    const artist = document.getElementById('playerArtist');
    const playBtn = document.getElementById('playerPlay');

    if (currentAudio === media.id_media && isPlaying) {
        audio.pause();
        isPlaying = false;
        playBtn.innerHTML = '<i class="fas fa-play"></i>';
        return;
    }

    currentAudio = media.id_media;
    title.textContent = media.titre;
    artist.textContent = media.credits || 'Artiste inconnu';

    if (media.fichier) {
        audio.src = baseUrl + media.fichier;
        audio.play();
        isPlaying = true;
        playBtn.innerHTML = '<i class="fas fa-pause"></i>';
        player.classList.add('active');
    }
}

document.getElementById('playerPlay').addEventListener('click', function() {
    const audio = document.getElementById('audioElement');
    if (isPlaying) {
        audio.pause();
        this.innerHTML = '<i class="fas fa-play"></i>';
    } else {
        audio.play();
        this.innerHTML = '<i class="fas fa-pause"></i>';
    }
    isPlaying = !isPlaying;
});

document.getElementById('playerClose').addEventListener('click', function() {
    const player = document.getElementById('audioPlayer');
    const audio = document.getElementById('audioElement');
    audio.pause();
    player.classList.remove('active');
    isPlaying = false;
});

function openLightbox(src, title) {
    const lightbox = document.getElementById('lightbox');
    const img = document.getElementById('lightboxImg');
    const titleEl = document.getElementById('lightboxTitle');

    img.src = src;
    titleEl.textContent = title;
    lightbox.classList.add('active');
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
}

function openDocument(media) {
    if (media.fichier) {
        window.open(baseUrl + media.fichier, '_blank');
    }
    showToast('Ouverture du document...', 'success');
}

function shareMedia(id) {
    if (navigator.share) {
        navigator.share({
            title: 'NUFOTEC Media',
            text: 'Découvrez ce média',
            url: window.location.href
        });
    } else {
        showToast('Lien copié !', 'success');
    }
}

function downloadMedia(id) {
    showToast('Téléchargement démarré', 'success');
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `nufotec-toast ${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Header scroll effect
window.addEventListener('scroll', function() {
    const header = document.getElementById('header');
    if (window.scrollY > 10) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>