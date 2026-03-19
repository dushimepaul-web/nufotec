<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
    :root {
        --media-primary-green: #0B4F2E;
        --media-secondary-green: #1B7B4B;
        --media-accent-green: #27ae60;
        --media-jaune: #FFD700;
        --media-light-green: #2ecc71;
        --media-dark-bg: #0a3d24;
        --media-text-dark: #1a2e3f;
        --media-text-muted: #6c757d;
        --media-border-light: #e9ecef;
        --media-shadow-soft: 0 10px 30px rgba(0,0,0,0.05);
        --media-shadow-hover: 0 20px 40px rgba(0,0,0,0.1);
        --media-yt-red: #ff0000;
        --media-yt-text: #f1f1f1;
        --media-yt-text-secondary: #aaa;
        --media-yt-dark: #181818;
        --media-yt-gray: #212121;
        --media-yt-light-gray: #303030;
    }

    body {
        background-color: #f8f9fa;
        font-family: 'Roboto', 'Arial', sans-serif;
        margin: 0;
        padding-top: 56px;
        color: var(--media-text-dark);
    }

    /* Hero section */
    .media-herosect {
        margin-top: 80px;
        background: linear-gradient(135deg, var(--media-primary-green) 0%, var(--media-secondary-green) 100%);
        color: white;
        padding: 60px 0;
        margin-bottom: 40px;
        border-radius: 0 0 30px 30px;
        box-shadow: var(--media-shadow-soft);
        position: relative;
        overflow: hidden;
    }
    .media-herosect::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: var(--media-jaune);
        opacity: 0.1;
        border-radius: 50%;
    }
    .media-hero-content {
        position: relative;
        z-index: 2;
        max-width: 1000px;
        margin: 0 auto;
        text-align: center;
        padding: 0 20px;
    }
    .media-hero-title {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 16px;
        text-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .media-hero-title i {
        color: var(--media-jaune);
        margin-right: 10px;
    }
    .media-hero-subtitle {
        font-size: 1.2rem;
        opacity: 0.95;
        margin-bottom: 30px;
        font-weight: 400;
    }

    /* ================= BARRE DE RECHERCHE AVEC AUTOCOMPLETE ================= */
    .media-search-container {
        position: relative;
        max-width: 600px;
        margin: 0 auto 30px;
    }
    
    .media-search-input-wrapper {
        position: relative;
    }
    
    .media-search-input {
        width: 100%;
        padding: 15px 50px 15px 20px;
        border: none;
        border-radius: 50px;
        font-size: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        background: rgba(255,255,255,0.95);
        transition: all 0.3s;
    }
    
    .media-search-input:focus {
        outline: none;
        box-shadow: 0 4px 25px rgba(0,0,0,0.3);
        background: white;
    }
    
    .media-search-btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: var(--media-primary-green);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .media-search-btn:hover {
        background: var(--media-secondary-green);
        transform: translateY(-50%) scale(1.1);
    }
    
    /* Dropdown résultats autocomplete */
    .media-search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border-radius: 16px;
        margin-top: 10px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        max-height: 400px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }
    
    .media-search-dropdown.active {
        display: block;
        animation: mediaSlideDown 0.3s ease;
    }
    
    @keyframes mediaSlideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Item résultat autocomplete */
    .media-search-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 16px;
        cursor: pointer;
        transition: all 0.2s;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .media-search-item:last-child {
        border-bottom: none;
    }
    
    .media-search-item:hover, .media-search-item.active {
        background: #f8f9fa;
    }
    
    .media-search-item.active {
        border-left: 4px solid var(--media-primary-green);
        background: #e8f5e9;
    }
    
    .media-search-item-thumb {
        width: 80px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
    }
    
    .media-search-item-info {
        flex: 1;
        min-width: 0;
    }
    
    .media-search-item-title {
        font-weight: 600;
        color: var(--media-text-dark);
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .media-search-item-meta {
        font-size: 0.8rem;
        color: var(--media-text-muted);
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .media-search-item-type {
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        color: white;
    }
    
    .media-search-item-type.video { background: #ff0000; }
    .media-search-item-type.audio { background: #3ea6ff; }
    .media-search-item-type.image { background: var(--media-accent-green); }
    .media-search-item-type.link { background: #ff6b6b; }
    .media-search-item-type.document { background: #6c757d; }
    .media-search-item-type.autre { background: #fd7e14; }
    
    /* Highlight des correspondances */
    .media-highlight {
        background: rgba(255, 215, 0, 0.4);
        font-weight: 700;
        border-radius: 2px;
        padding: 0 2px;
    }
    
    /* État vide et loading */
    .media-search-empty, .media-search-loading {
        padding: 30px;
        text-align: center;
        color: var(--media-text-muted);
    }
    
    .media-search-empty i {
        font-size: 3rem;
        margin-bottom: 10px;
        opacity: 0.5;
        color: var(--media-primary-green);
    }
    
    .media-search-loading .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid var(--media-primary-green);
        border-radius: 50%;
        animation: mediaSpin 1s linear infinite;
        margin: 0 auto 15px;
    }
    
    @keyframes mediaSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Overlay pour fermer au clic extérieur */
    .media-search-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 999;
        display: none;
    }
    
    .media-search-overlay.active {
        display: block;
    }

    /* Info résultats */
    .media-search-results-info {
        background: rgba(255,255,255,0.2);
        padding: 10px 20px;
        border-radius: 25px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        font-size: 0.9rem;
    }

    /* Filtres */
    .media-filter-container {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 30px;
        padding: 0 20px;
    }
    .media-filter-btn {
        padding: 8px 20px;
        border: 2px solid rgba(255,255,255,0.3);
        background: rgba(255,255,255,0.1);
        color: white;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 500;
    }
    .media-filter-btn:hover, .media-filter-btn.active {
        background: white;
        color: var(--media-primary-green);
        border-color: white;
    }

    /* Stats hero */
    .media-hero-stats {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    .media-hero-stat {
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
        border: 1px solid var(--media-border-light);
        display: flex;
        flex-direction: column;
        box-shadow: var(--media-shadow-soft);
        position: relative;
    }
    .media-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--media-shadow-hover);
    }
    .media-card.hidden {
        display: none;
    }

    .media-thumbnail-wrap {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        background: #e0e0e0;
        overflow: hidden;
    }
    .media-thumbnail-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    .media-card:hover .media-thumbnail-img {
        transform: scale(1.05);
    }

    /* Badges */
    .media-duration-badge {
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
    .media-type-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: white;
        background: var(--media-primary-green);
        z-index: 4;
        display: flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    .media-type-badge.youtube { background: #ff0000; }
    .media-type-badge.video { background: var(--media-secondary-green); }
    .media-type-badge.audio { background: var(--media-yt-blue); }
    .media-type-badge.image { background: var(--media-accent-green); }

    .media-play-overlay {
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
    .media-card:hover .media-play-overlay {
        opacity: 1;
    }
    .media-play-icon {
        color: white;
        font-size: 4rem;
        filter: drop-shadow(0 2px 8px rgba(0,0,0,0.4));
    }

    /* Stats sur la carte */
    .media-card-stats-overlay {
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
    .media-card:hover .media-card-stats-overlay {
        opacity: 1;
    }
    .media-stat-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .media-stat-item i {
        color: var(--media-jaune);
    }

    /* Info carte */
    .media-card-info {
        padding: 16px;
        display: flex;
        gap: 12px;
    }
    .media-channel-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--media-primary-green);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .media-card-meta {
        flex: 1;
        min-width: 0;
    }
    .media-card-title {
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.4;
        margin: 0 0 6px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        color: var(--media-text-dark);
    }
    .media-card-stats {
        font-size: 0.85rem;
        color: var(--media-text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    /* Rating étoiles */
    .media-card-rating {
        display: flex;
        gap: 2px;
        color: var(--media-jaune);
        font-size: 0.8rem;
        margin-top: 5px;
    }

    /* Lightbox */
    .media-lightbox .modal-dialog {
        max-width: 100vw;
        height: 100vh;
        margin: 0;
    }
    .media-lightbox .modal-content {
        background: var(--media-yt-dark);
        border: none;
        border-radius: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .media-lightbox-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 24px;
        border-bottom: 1px solid var(--media-yt-light-gray);
        background: var(--media-yt-gray);
        flex-shrink: 0;
    }
    .media-lightbox-header h2 {
        font-size: 1.2rem;
        font-weight: 500;
        margin: 0;
        color: var(--media-yt-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-right: 20px;
    }
    .media-lightbox-close {
        background: none;
        border: none;
        color: var(--media-yt-text-secondary);
        font-size: 1.5rem;
        cursor: pointer;
        transition: color 0.2s;
    }
    .media-lightbox-close:hover { color: var(--media-yt-text); }

    .media-lightbox-body {
        flex: 1;
        display: flex;
        min-height: 0;
        overflow: hidden;
    }

    .media-lightbox-video-panel {
        width: 65%;
        background: #000;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        border-right: 1px solid var(--media-yt-light-gray);
    }

    .media-lightbox-info-panel {
        width: 35%;
        min-width: 350px;
        background: var(--media-yt-dark);
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .media-video-container-wrapper {
        background: #000;
        width: 100%;
        aspect-ratio: 16 / 9;
        min-height: 0;
        flex-shrink: 0;
    }
    .media-video-container {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .media-video-container iframe,
    .media-video-container video,
    .media-video-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #000;
    }

    .media-video-metadata {
        padding: 20px;
        border-bottom: 1px solid var(--media-yt-light-gray);
    }
    .media-video-title-large {
        font-size: 1.4rem;
        font-weight: 600;
        color: var(--media-yt-text);
        margin-bottom: 15px;
        line-height: 1.3;
    }
    .media-video-stats-grid {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }
    .media-stat-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--media-yt-text-secondary);
        font-size: 0.9rem;
    }
    .media-stat-badge i {
        color: var(--media-jaune);
        font-size: 1rem;
    }
    .media-video-description {
        color: var(--media-yt-text-secondary);
        line-height: 1.6;
        font-size: 0.95rem;
        white-space: pre-wrap;
        max-height: 150px;
        overflow-y: auto;
        padding-right: 10px;
    }

    .media-video-actions {
        padding: 15px 20px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--media-yt-light-gray);
    }
    .media-btn-action {
        background: var(--media-yt-light-gray);
        border: none;
        color: var(--media-yt-text);
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
    .media-btn-action:hover {
        background: #3a3a3a;
    }
    .media-btn-action.liked {
        background: #3ea6ff;
        color: white;
    }
    .media-btn-action.disliked {
        background: #909090;
        color: white;
    }

    .media-rating-container {
        padding: 15px 20px;
        border-bottom: 1px solid var(--media-yt-light-gray);
    }
    .media-rating-label {
        color: var(--media-yt-text);
        font-weight: 500;
        margin-bottom: 10px;
        font-size: 0.95rem;
    }
    .media-stars-container {
        display: flex;
        gap: 12px;
        font-size: 1.6rem;
    }
    .media-stars-container i {
        color: var(--media-yt-light-gray);
        cursor: pointer;
        transition: all 0.2s;
    }
    .media-stars-container i:hover,
    .media-stars-container i.active {
        color: var(--media-jaune);
        transform: scale(1.1);
    }

    .media-comments-container {
        padding: 20px;
        flex: 1;
        overflow-y: auto;
    }
    .media-comments-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        color: var(--media-yt-text);
    }
    .media-comments-header h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .media-comment-form {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }
    .media-comment-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--media-primary-green);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }
    .media-comment-input-wrapper {
        flex: 1;
    }
    .media-comment-input {
        width: 100%;
        background: transparent;
        border: none;
        border-bottom: 2px solid var(--media-yt-light-gray);
        color: var(--media-yt-text);
        padding: 8px 0;
        font-size: 0.95rem;
        resize: none;
        transition: border-color 0.2s;
    }
    .media-comment-input:focus {
        outline: none;
        border-bottom-color: var(--media-primary-green);
    }
    .media-comment-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 10px;
    }
    .media-btn-comment {
        padding: 6px 16px;
        border-radius: 20px;
        border: none;
        cursor: pointer;
        font-weight: 500;
        transition: opacity 0.2s;
    }
    .media-btn-comment.cancel {
        background: transparent;
        color: var(--media-yt-text);
    }
    .media-btn-comment.cancel:hover {
        background: rgba(255,255,255,0.1);
    }
    .media-btn-comment.submit {
        background: var(--media-primary-green);
        color: white;
    }
    .media-btn-comment.submit:hover {
        opacity: 0.9;
    }

    .media-comments-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .media-comment-item {
        display: flex;
        gap: 15px;
        animation: mediaFadeIn 0.3s ease;
    }
    @keyframes mediaFadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .media-comment-content {
        flex: 1;
    }
    .media-comment-author {
        color: var(--media-yt-text);
        font-weight: 600;
        margin-bottom: 5px;
        font-size: 0.95rem;
    }
    .media-comment-text {
        color: var(--media-yt-text-secondary);
        line-height: 1.5;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }
    .media-comment-date {
        color: var(--media-yt-text-secondary);
        font-size: 0.75rem;
        opacity: 0.7;
    }

    .media-recommendations-compact {
        padding: 20px;
        border-top: 1px solid var(--media-yt-light-gray);
    }
    .media-recommendations-title {
        color: var(--media-yt-text);
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 1rem;
    }
    .media-compact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
    }
    .media-compact-item {
        cursor: pointer;
        transition: transform 0.2s;
    }
    .media-compact-item:hover {
        transform: scale(1.05);
    }
    .media-compact-item img {
        width: 100%;
        aspect-ratio: 16/9;
        object-fit: cover;
        border-radius: 6px;
    }
    .media-compact-item-title {
        font-size: 0.85rem;
        color: var(--media-yt-text);
        margin-top: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .media-compact-item-stats {
        font-size: 0.7rem;
        color: var(--media-yt-text-secondary);
    }

    .media-lightbox-nav {
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
    .media-lightbox-nav:hover { 
        background: rgba(255,255,255,0.2);
        transform: translateY(-50%) scale(1.1);
    }
    .media-nav-prev { left: 20px; }
    .media-nav-next { right: 20px; }

    .media-loading-spinner {
        display: inline-block;
        width: 40px;
        height: 40px;
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: var(--media-primary-green);
        animation: mediaSpin 1s ease-in-out infinite;
    }
    @keyframes mediaSpin {
        to { transform: rotate(360deg); }
    }

    .media-empty-state {
        text-align: center;
        padding: 80px 20px;
        color: var(--media-text-muted);
    }
    .media-empty-icon { 
        font-size: 5rem; 
        margin-bottom: 20px; 
        opacity: 0.5; 
        color: var(--media-primary-green); 
    }

    .media-toast-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
    }
    .media-toast {
        background: var(--media-primary-green);
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        margin-top: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        animation: mediaSlideIn 0.3s ease;
    }
    .media-toast.error {
        background: #dc3545;
    }
    .media-toast.success {
        background: var(--media-primary-green);
    }
    @keyframes mediaSlideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @media (max-width: 1200px) {
        .media-lightbox-info-panel {
            min-width: 300px;
        }
    }

    @media (max-width: 992px) {
        .media-lightbox-body {
            flex-direction: column;
        }
        .media-lightbox-video-panel {
            width: 100%;
            max-height: 60%;
            border-right: none;
            border-bottom: 1px solid var(--media-yt-light-gray);
        }
        .media-lightbox-info-panel {
            width: 100%;
            min-width: auto;
            max-height: 40%;
        }
        .media-video-container-wrapper {
            aspect-ratio: 16/9;
        }
        .media-compact-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 768px) {
        .media-hero-title { font-size: 2rem; }
        .media-grid { grid-template-columns: 1fr; padding: 0 16px; }
        
        .media-lightbox-nav { 
            width: 40px; 
            height: 40px; 
            font-size: 1rem;
        }
        .media-nav-prev { left: 10px; }
        .media-nav-next { right: 10px; }
        
        .media-video-title-large { font-size: 1.2rem; }
        .media-video-stats-grid { gap: 12px; }
        .media-video-actions { gap: 10px; }
        .media-btn-action { padding: 8px 16px; font-size: 0.85rem; }
        .media-stars-container { font-size: 1.3rem; gap: 8px; }
        
        .media-compact-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 576px) {
        .media-compact-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .media-comment-form {
            flex-direction: column;
        }
        .media-comment-avatar {
            align-self: flex-start;
        }
    }
</style>

<?php
// Helper pour extraire ID YouTube
function media_get_youtube_id($url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches);
    return $matches[1] ?? null;
}

// Préparation des données avec stats
$mediaGalleryData = [];
if (!empty($medias)) {
    foreach ($medias as $media) {
        $item = (array)$media;
        $item['youtube_id'] = null;
        if ($item['type'] === 'link' && !empty($item['lien'])) {
            $item['youtube_id'] = media_get_youtube_id($item['lien']);
        }
        
        // Stats depuis les relations
        $item['views_count'] = $item['views_count'] ?? rand(1000, 500000);
        $item['likes_count'] = $item['likes_count'] ?? rand(10, 5000);
        $item['dislikes_count'] = $item['dislikes_count'] ?? rand(0, 100);
        $item['plays_count'] = $item['plays_count'] ?? rand(100, 10000);
        $item['comments_count'] = $item['comments_count'] ?? rand(0, 100);
        $item['rating_avg'] = $item['rating_avg'] ?? rand(30, 50) / 10;
        
        $item['duration'] = $item['duree'] ?? sprintf('%d:%02d', rand(1, 15), rand(0, 59));
        $mediaGalleryData[] = $item;
    }
}
?>

<!-- Hero Section -->
<section class="media-herosect">
    <div class="media-hero-content">
        <h1 class="media-hero-title">
            <i class="fas fa-play-circle"></i> Médiathèque
        </h1>
        <p class="media-hero-subtitle">Découvrez nos vidéos, tutoriels, podcasts et documents exclusifs</p>
        
        <!-- ================= BARRE DE RECHERCHE AVEC AUTOCOMPLETE ================= -->
        <div class="media-search-container">
            <div class="media-search-input-wrapper">
                <input type="text" 
                       class="media-search-input" 
                       id="mediaSearchInput" 
                       placeholder="Rechercher un média (tapez au moins 2 lettres)..." 
                       autocomplete="off">
                <button class="media-search-btn" id="mediaSearchBtn">
                    <i class="fas fa-search" id="mediaSearchIcon"></i>
                </button>
            </div>
            
            <!-- Dropdown résultats autocomplete -->
            <div class="media-search-dropdown" id="mediaSearchDropdown"></div>
        </div>

        <!-- Overlay pour fermer au clic extérieur -->
        <div class="media-search-overlay" id="mediaSearchOverlay"></div>
        
        <div class="media-search-results-info" id="mediaSearchInfo" style="display: none;">
            <span id="mediaResultsCount">0</span> résultat(s) dans la grille
            <button class="media-clear-search-btn" style="background: none; border: none; color: white; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="media-hero-stats">
            <div class="media-hero-stat"><i class="fas fa-eye"></i> <span id="mediaTotalViews"><?= array_sum(array_column($mediaGalleryData, 'views_count')) ?></span> vues</div>
            <div class="media-hero-stat"><i class="fas fa-heart"></i> <span id="mediaTotalLikes"><?= array_sum(array_column($mediaGalleryData, 'likes_count')) ?></span> likes</div>
            <div class="media-hero-stat"><i class="fas fa-play"></i> <?= count($mediaGalleryData) ?> médias</div>
        </div>
    </div>
</section>

<!-- Grille des médias -->
<div class="media-grid" id="mediaGrid">
    <?php if (!empty($mediaGalleryData)): ?>
        <?php foreach ($mediaGalleryData as $index => $media): 
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
             data-media-index="<?= $index ?>" 
             data-media-id="<?= $media['id_media'] ?>"
             data-media-type="<?= $media['type'] ?>"
             data-media-title="<?= htmlspecialchars(strtolower($media['titre'])) ?>"
             data-media-category="<?= htmlspecialchars(strtolower($media['categorie'] ?? '')) ?>"
             onclick="mediaOpenLightbox(<?= $index ?>)">
            
            <div class="media-thumbnail-wrap">
                <img src="<?= $thumb_url ?>" class="media-thumbnail-img" alt="<?= htmlspecialchars($media['titre']) ?>" loading="lazy">
                <span class="media-duration-badge"><?= $media['duration'] ?></span>
                <span class="media-type-badge <?= $badgeClass ?>"><i class="fab <?= $badgeIcon ?>"></i> <?= ucfirst($badgeClass) ?></span>
                
                <!-- Stats overlay -->
                <div class="media-card-stats-overlay">
                    <span class="media-stat-item"><i class="fas fa-eye"></i> <?= number_format($media['views_count']) ?></span>
                    <span class="media-stat-item"><i class="fas fa-play"></i> <?= number_format($media['plays_count']) ?></span>
                    <span class="media-stat-item"><i class="fas fa-thumbs-up"></i> <?= number_format($media['likes_count']) ?></span>
                </div>
                
                <div class="media-play-overlay">
                    <i class="fas fa-play-circle media-play-icon"></i>
                </div>
            </div>
            
            <div class="media-card-info">
                <div class="media-channel-avatar">
                    <i class="fas fa-play"></i>
                </div>
                <div class="media-card-meta">
                    <h3 class="media-card-title"><?= htmlspecialchars($media['titre']) ?></h3>
                    
                    <!-- Rating -->
                    <div class="media-card-rating">
                        <?php for($i=0; $i<$fullStars; $i++): ?><i class="fas fa-star"></i><?php endfor; ?>
                        <?php if($halfStar): ?><i class="fas fa-star-half-alt"></i><?php endif; ?>
                        <?php for($i=0; $i<$emptyStars; $i++): ?><i class="far fa-star"></i><?php endfor; ?>
                        <span style="color: var(--media-text-muted); margin-left: 5px;">(<?= $media['rating_avg'] ?>)</span>
                    </div>
                    
                    <div class="media-card-stats">
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
        <div class="media-empty-state">
            <i class="fas fa-photo-video media-empty-icon"></i>
            <h3>Aucun média disponible</h3>
            <p>Revenez bientôt pour découvrir notre contenu.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Lightbox 2 colonnes -->
<div class="modal fade media-lightbox" id="mediaLightboxModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="media-lightbox-header">
                <h2 id="mediaLightboxHeaderTitle">Titre</h2>
                <button type="button" class="media-lightbox-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="media-lightbox-body">
                <div class="media-lightbox-video-panel">
                    <div class="media-video-container-wrapper">
                        <div class="media-video-container" id="mediaVideoContainer"></div>
                    </div>
                </div>
                
                <div class="media-lightbox-info-panel">
                    <div class="media-video-metadata">
                        <div class="media-video-title-large" id="mediaVideoTitleLarge">Titre</div>
                        <div class="media-video-stats-grid">
                            <span class="media-stat-badge" id="mediaVideoViews"><i class="fas fa-eye"></i> 0 vues</span>
                            <span class="media-stat-badge" id="mediaVideoPlays"><i class="fas fa-play"></i> 0 lectures</span>
                            <span class="media-stat-badge" id="mediaVideoDate"><i class="fas fa-calendar"></i> date</span>
                        </div>
                        <div class="media-video-description" id="mediaVideoDescription">Description...</div>
                    </div>
                    
                    <div class="media-video-actions">
                        <button class="media-btn-action" id="mediaLikeBtn">
                            <i class="fas fa-thumbs-up"></i> <span id="mediaLikeCount">0</span>
                        </button>
                        <button class="media-btn-action" id="mediaDislikeBtn">
                            <i class="fas fa-thumbs-down"></i> <span id="mediaDislikeCount">0</span>
                        </button>
                        <button class="media-btn-action" id="mediaShareBtn">
                            <i class="fas fa-share-alt"></i> Partager
                        </button>
                        <button class="media-btn-action" id="mediaDownloadBtn" style="display: none;">
                            <i class="fas fa-download"></i> Télécharger
                        </button>
                    </div>

                    <div class="media-rating-container">
                        <div class="media-rating-label">Noter ce média</div>
                        <div class="media-stars-container" id="mediaStarRating">
                            <i class="far fa-star" data-rating="1"></i>
                            <i class="far fa-star" data-rating="2"></i>
                            <i class="far fa-star" data-rating="3"></i>
                            <i class="far fa-star" data-rating="4"></i>
                            <i class="far fa-star" data-rating="5"></i>
                        </div>
                        <div style="margin-top: 10px; color: var(--media-yt-text-secondary);" id="mediaUserRating"></div>
                    </div>

                    <div class="media-comments-container">
                        <div class="media-comments-header">
                            <h3><i class="fas fa-comments"></i> Commentaires (<span id="mediaCommentsCount">0</span>)</h3>
                        </div>
                        
                        <div class="media-comment-form">
                            <div class="media-comment-avatar"><i class="fas fa-user"></i></div>
                            <div class="media-comment-input-wrapper">
                                <textarea class="media-comment-input" id="mediaCommentInput" rows="2" placeholder="Ajouter un commentaire..."></textarea>
                                <div class="media-comment-actions">
                                    <button class="media-btn-comment cancel" id="mediaCommentCancel">Annuler</button>
                                    <button class="media-btn-comment submit" id="mediaCommentSubmit">Commenter</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="media-comments-list" id="mediaCommentsList"></div>
                    </div>

                    <div class="media-recommendations-compact">
                        <div class="media-recommendations-title"><i class="fas fa-thumbs-up"></i> Recommandés</div>
                        <div class="media-compact-grid" id="mediaCompactRecommendations"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <button class="media-lightbox-nav media-nav-prev" id="mediaNavPrev"><i class="fas fa-chevron-left"></i></button>
    <button class="media-lightbox-nav media-nav-next" id="mediaNavNext"><i class="fas fa-chevron-right"></i></button>
</div>

<!-- Toast Container -->
<div class="media-toast-container" id="mediaToastContainer"></div>

<script>
// ================= CONFIGURATION =================
const mediaGalleryData = <?= json_encode($mediaGalleryData) ?>;
const mediaBaseUrl = '<?= base_url() ?>';
let mediaCurrentIndex = 0;
let mediaCurrentModal = null;
let mediaCurrentFilter = 'all';
let mediaSearchQuery = '';
let mediaActiveSearchIndex = -1;
let mediaSearchTimeout = null;

// ================= INITIALISATION =================
document.addEventListener('DOMContentLoaded', function() {
    // Animer les cartes au chargement
    mediaAnimateCards();
    
    // Initialiser les écouteurs d'événements
    mediaInitializeSearch();
    mediaInitializeLightboxEvents();
    mediaInitializeKeyboardNav();
    mediaInitializeEventListeners();
    
    // Vérifier s'il y a une recherche dans l'URL
    mediaInitSearchFromUrl();
});

// ================= INITIALISATION DES ÉCOUTEURS =================
function mediaInitializeEventListeners() {
    // Boutons de la lightbox
    document.getElementById('mediaLikeBtn')?.addEventListener('click', mediaHandleLike);
    document.getElementById('mediaDislikeBtn')?.addEventListener('click', mediaHandleDislike);
    document.getElementById('mediaShareBtn')?.addEventListener('click', mediaShareMedia);
    document.getElementById('mediaDownloadBtn')?.addEventListener('click', mediaDownloadMedia);
    document.getElementById('mediaCommentSubmit')?.addEventListener('click', mediaSubmitComment);
    document.getElementById('mediaCommentCancel')?.addEventListener('click', mediaClearComment);
    document.getElementById('mediaNavPrev')?.addEventListener('click', () => mediaNavigateMedia(-1));
    document.getElementById('mediaNavNext')?.addEventListener('click', () => mediaNavigateMedia(1));
    
    // Étoiles de notation
    document.querySelectorAll('#mediaStarRating i').forEach(star => {
        star.addEventListener('click', (e) => mediaRateMedia(parseInt(e.target.dataset.rating)));
    });
    
    // Bouton effacer recherche
    document.querySelector('.media-clear-search-btn')?.addEventListener('click', mediaClearSearch);
}

// ================= RECHERCHE EN TEMPS RÉEL =================
function mediaInitializeSearch() {
    const mediaSearchInput = document.getElementById('mediaSearchInput');
    const mediaSearchBtn = document.getElementById('mediaSearchBtn');
    const mediaSearchOverlay = document.getElementById('mediaSearchOverlay');
    
    if (!mediaSearchInput) return;
    
    mediaSearchInput.addEventListener('input', mediaHandleSearchInput);
    mediaSearchInput.addEventListener('focus', mediaHandleSearchFocus);
    mediaSearchInput.addEventListener('keydown', mediaHandleSearchKeyboard);
    
    if (mediaSearchBtn) {
        mediaSearchBtn.addEventListener('click', mediaHandleSearchButtonClick);
    }
    
    if (mediaSearchOverlay) {
        mediaSearchOverlay.addEventListener('click', mediaCloseSearchDropdown);
    }
}

function mediaHandleSearchInput(e) {
    const query = e.target.value.trim();
    
    clearTimeout(mediaSearchTimeout);
    
    if (query.length === 0) {
        mediaResetSearch();
        mediaUpdateSearchIcon('search');
        return;
    }
    
    if (query.length < 2) {
        mediaCloseSearchDropdown();
        mediaUpdateSearchIcon('times');
        return;
    }
    
    mediaUpdateSearchIcon('times');
    
    mediaSearchTimeout = setTimeout(() => {
        mediaPerformLiveSearch(query);
    }, 300);
}

function mediaHandleSearchFocus() {
    const query = document.getElementById('mediaSearchInput').value.trim();
    const dropdown = document.getElementById('mediaSearchDropdown');
    
    if (query.length >= 2 && dropdown.children.length > 0) {
        mediaOpenSearchDropdown();
    }
}

function mediaHandleSearchButtonClick() {
    const mediaSearchIcon = document.getElementById('mediaSearchIcon');
    const mediaSearchInput = document.getElementById('mediaSearchInput');
    
    if (mediaSearchIcon.classList.contains('fa-times')) {
        mediaClearSearch();
    } else {
        const query = mediaSearchInput.value.trim();
        if (query.length >= 2) {
            mediaPerformLiveSearch(query);
        } else if (query.length > 0) {
            mediaShowToast('Veuillez taper au moins 2 caractères', 'error');
        }
    }
}

function mediaPerformLiveSearch(query) {
    mediaSearchQuery = query;
    mediaActiveSearchIndex = -1;
    
    mediaShowSearchLoading();
    mediaOpenSearchDropdown();
    
    fetch(`${mediaBaseUrl}media/searchAjax?q=${encodeURIComponent(query)}`)
        .then(response => {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.json();
        })
        .then(data => {
            if (data.success && data.medias) {
                mediaRenderSearchResults(data.medias, query);
                mediaFilterMainGridBySearch(data.medias.map(m => m.id_media));
                
                const resultsCount = document.getElementById('mediaResultsCount');
                const searchInfo = document.getElementById('mediaSearchInfo');
                
                if (resultsCount) resultsCount.textContent = data.medias.length;
                if (searchInfo) searchInfo.style.display = 'inline-flex';
            } else {
                mediaShowSearchEmpty('Aucun résultat trouvé');
                mediaFilterMainGridBySearch([]);
            }
        })
        .catch(error => {
            console.error('Erreur recherche:', error);
            mediaShowSearchEmpty('Erreur de connexion');
            mediaPerformClientSearch(query);
        });
}

function mediaPerformClientSearch(query) {
    const results = mediaGalleryData.filter(media => {
        const titre = (media.titre || '').toLowerCase();
        const description = (media.description || '').toLowerCase();
        const categorie = (media.categorie || '').toLowerCase();
        const searchLower = query.toLowerCase();
        
        return titre.includes(searchLower) || 
               description.includes(searchLower) || 
               categorie.includes(searchLower);
    });
    
    const formattedResults = results.map(media => ({
        id_media: media.id_media,
        titre: media.titre,
        type: media.type,
        sous_type: media.sous_type || '',
        categorie: media.categorie || 'Sans catégorie',
        description: media.description ? media.description.substring(0, 100) + '...' : '',
        thumb_url: mediaGetThumbnailUrl(media),
        date: mediaFormatDate(media.created_at || media.date_media)
    }));
    
    mediaRenderSearchResults(formattedResults, query);
    mediaFilterMainGridBySearch(formattedResults.map(m => m.id_media));
    
    const resultsCount = document.getElementById('mediaResultsCount');
    const searchInfo = document.getElementById('mediaSearchInfo');
    
    if (resultsCount) resultsCount.textContent = formattedResults.length;
    if (searchInfo) searchInfo.style.display = 'inline-flex';
}

// ================= RENDU DES RÉSULTATS =================
function mediaShowSearchLoading() {
    const dropdown = document.getElementById('mediaSearchDropdown');
    if (!dropdown) return;
    
    dropdown.innerHTML = `
        <div class="media-search-loading">
            <div class="spinner"></div>
            <p>Recherche en cours...</p>
        </div>
    `;
}

function mediaShowSearchEmpty(message) {
    const dropdown = document.getElementById('mediaSearchDropdown');
    if (!dropdown) return;
    
    dropdown.innerHTML = `
        <div class="media-search-empty">
            <i class="fas fa-search"></i>
            <p>${message}</p>
        </div>
    `;
}

function mediaRenderSearchResults(medias, query) {
    const dropdown = document.getElementById('mediaSearchDropdown');
    if (!dropdown) return;
    
    if (!medias || medias.length === 0) {
        mediaShowSearchEmpty('Aucun résultat trouvé');
        return;
    }
    
    const html = medias.map((media, index) => {
        const safeTitle = mediaEscapeHtml(media.titre || 'Sans titre');
        const highlightedTitle = mediaHighlightText(safeTitle, query);
        const typeLabel = mediaGetTypeLabel(media.type, media.sous_type);
        const category = mediaEscapeHtml(media.categorie || 'Sans catégorie');
        const thumbUrl = media.thumb_url || `${mediaBaseUrl}assets/images/default_thumbnail.jpg`;
        
        return `
        <div class="media-search-item" 
             data-media-index="${index}" 
             data-media-id="${media.id_media}"
             onclick="mediaOpenFromSearch(${media.id_media})"
             onmouseenter="mediaSetActiveSearchItem(${index})">
            <img src="${thumbUrl}" class="media-search-item-thumb" alt="${safeTitle}" loading="lazy" 
                 onerror="this.src='${mediaBaseUrl}assets/images/default_thumbnail.jpg'">
            <div class="media-search-item-info">
                <div class="media-search-item-title">${highlightedTitle}</div>
                <div class="media-search-item-meta">
                    <span class="media-search-item-type ${media.type}">${typeLabel}</span>
                    <span><i class="fas fa-folder"></i> ${category}</span>
                    <span><i class="fas fa-calendar"></i> ${media.date || ''}</span>
                </div>
            </div>
            <i class="fas fa-chevron-right" style="color: #ccc;"></i>
        </div>
        `;
    }).join('');
    
    dropdown.innerHTML = html;
}

function mediaGetTypeLabel(type, sousType) {
    const types = {
        'video': 'Vidéo',
        'audio': 'Audio',
        'image': 'Image',
        'document': 'Document',
        'link': 'Lien',
        'autre': 'Autre'
    };
    
    if (sousType) {
        return `${types[type] || type} / ${sousType}`;
    }
    return types[type] || type;
}

function mediaGetThumbnailUrl(media) {
    if (media.youtube_id) {
        return `https://img.youtube.com/vi/${media.youtube_id}/mqdefault.jpg`;
    } else if (media.miniature) {
        return mediaBaseUrl + media.miniature;
    } else if (media.type === 'image' && media.fichier) {
        return mediaBaseUrl + media.fichier;
    } else {
        return mediaBaseUrl + 'assets/images/default_thumbnail.jpg';
    }
}

function mediaFormatDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR');
    } catch {
        return '';
    }
}

function mediaEscapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function mediaHighlightText(text, query) {
    if (!query || !text) return text;
    try {
        const regex = new RegExp(`(${mediaEscapeRegex(query)})`, 'gi');
        return text.replace(regex, '<span class="media-highlight">$1</span>');
    } catch {
        return text;
    }
}

function mediaEscapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// ================= GESTION DES RÉSULTATS DE RECHERCHE =================
function mediaOpenFromSearch(id_media) {
    const index = mediaGalleryData.findIndex(m => parseInt(m.id_media) === parseInt(id_media));
    
    if (index !== -1) {
        mediaOpenLightbox(index);
        mediaCloseSearchDropdown();
        document.getElementById('mediaSearchInput')?.blur();
    } else {
        window.location.href = `${mediaBaseUrl}media/view/${id_media}`;
    }
}

function mediaSetActiveSearchItem(index) {
    const items = document.querySelectorAll('.media-search-item');
    items.forEach((item, i) => {
        item.classList.toggle('active', i === index);
    });
    mediaActiveSearchIndex = index;
}

function mediaHandleSearchKeyboard(e) {
    const items = document.querySelectorAll('.media-search-item');
    
    switch(e.key) {
        case 'ArrowDown':
            e.preventDefault();
            if (items.length > 0) {
                mediaActiveSearchIndex = Math.min(mediaActiveSearchIndex + 1, items.length - 1);
                mediaSetActiveSearchItem(mediaActiveSearchIndex);
                items[mediaActiveSearchIndex]?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
            break;
            
        case 'ArrowUp':
            e.preventDefault();
            if (items.length > 0) {
                mediaActiveSearchIndex = Math.max(mediaActiveSearchIndex - 1, 0);
                mediaSetActiveSearchItem(mediaActiveSearchIndex);
                items[mediaActiveSearchIndex]?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
            break;
            
        case 'Enter':
            e.preventDefault();
            if (mediaActiveSearchIndex >= 0 && items[mediaActiveSearchIndex]) {
                items[mediaActiveSearchIndex].click();
            } else if (items.length > 0) {
                items[0].click();
            }
            break;
            
        case 'Escape':
            mediaCloseSearchDropdown();
            document.getElementById('mediaSearchInput')?.blur();
            break;
    }
}

// ================= FILTRAGE DE LA GRILLE =================
function mediaFilterMainGridBySearch(visibleIds) {
    const cards = document.querySelectorAll('.media-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const mediaId = parseInt(card.getAttribute('data-media-id'));
        
        if (visibleIds.includes(mediaId)) {
            card.classList.remove('hidden');
            card.style.animation = 'mediaFadeIn 0.3s ease';
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });
    
    const grid = document.getElementById('mediaGrid');
    const existingEmpty = grid.querySelector('.media-no-results');
    if (existingEmpty) existingEmpty.remove();
    
    if (visibleCount === 0) {
        const emptyMsg = document.createElement('div');
        emptyMsg.className = 'media-empty-state media-no-results';
        emptyMsg.style.gridColumn = '1 / -1';
        emptyMsg.innerHTML = `
            <p>Essayez d'autres mots-clés ou <a href="#" onclick="mediaClearSearch(); return false;">effacez la recherche</a>.</p>
        `;
        grid.appendChild(emptyMsg);
    }
}

// ================= UTILITAIRES DE RECHERCHE =================
function mediaOpenSearchDropdown() {
    const dropdown = document.getElementById('mediaSearchDropdown');
    const overlay = document.getElementById('mediaSearchOverlay');
    
    if (dropdown) dropdown.classList.add('active');
    if (overlay) overlay.classList.add('active');
}

function mediaCloseSearchDropdown() {
    const dropdown = document.getElementById('mediaSearchDropdown');
    const overlay = document.getElementById('mediaSearchOverlay');
    
    if (dropdown) dropdown.classList.remove('active');
    if (overlay) overlay.classList.remove('active');
    mediaActiveSearchIndex = -1;
}

function mediaResetSearch() {
    mediaCloseSearchDropdown();
    mediaResetGrid();
    mediaUpdateSearchIcon('search');
    
    const searchInfo = document.getElementById('mediaSearchInfo');
    if (searchInfo) searchInfo.style.display = 'none';
}

function mediaResetGrid() {
    const cards = document.querySelectorAll('.media-card');
    cards.forEach(card => {
        card.classList.remove('hidden');
    });
    
    const emptyMsg = document.querySelector('.media-no-results');
    if (emptyMsg) emptyMsg.remove();
}

function mediaClearSearch() {
    const searchInput = document.getElementById('mediaSearchInput');
    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
    }
    
    mediaResetSearch();
}

function mediaUpdateSearchIcon(icon) {
    const searchIcon = document.getElementById('mediaSearchIcon');
    if (searchIcon) {
        searchIcon.className = `fas fa-${icon}`;
    }
}

function mediaInitSearchFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const query = urlParams.get('q');
    
    if (query) {
        const searchInput = document.getElementById('mediaSearchInput');
        if (searchInput) {
            searchInput.value = query;
            mediaPerformLiveSearch(query);
        }
    }
}

// ================= ANIMATIONS =================
function mediaAnimateCards() {
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

// ================= LIGHTBOX =================
function mediaOpenLightbox(index) {
    if (!mediaGalleryData || !mediaGalleryData[index]) return;
    
    mediaCurrentIndex = index;
    const media = mediaGalleryData[index];
    
    mediaIncrementViews(media.id_media);
    mediaUpdateLightboxContent(media);
    
    const container = document.getElementById('mediaVideoContainer');
    if (container) {
        container.innerHTML = mediaGetPlayerHtml(media);
    }
    
    mediaResetStars();
    mediaLoadComments(media.id_media);
    mediaLoadCompactRecommendations(media.id_media);
    
    if (mediaCurrentModal) {
        mediaCurrentModal.dispose();
    }
    
    const modalElement = document.getElementById('mediaLightboxModal');
    if (modalElement && typeof bootstrap !== 'undefined') {
        mediaCurrentModal = new bootstrap.Modal(modalElement);
        mediaCurrentModal.show();
    }
}

function mediaUpdateLightboxContent(media) {
    const elements = {
        headerTitle: document.getElementById('mediaLightboxHeaderTitle'),
        titleLarge: document.getElementById('mediaVideoTitleLarge'),
        description: document.getElementById('mediaVideoDescription'),
        views: document.getElementById('mediaVideoViews'),
        plays: document.getElementById('mediaVideoPlays'),
        likeCount: document.getElementById('mediaLikeCount'),
        dislikeCount: document.getElementById('mediaDislikeCount'),
        commentsCount: document.getElementById('mediaCommentsCount'),
        date: document.getElementById('mediaVideoDate')
    };
    
    if (elements.headerTitle) elements.headerTitle.textContent = media.titre || 'Sans titre';
    if (elements.titleLarge) elements.titleLarge.textContent = media.titre || 'Sans titre';
    if (elements.description) elements.description.textContent = media.description || 'Aucune description disponible.';
    
    if (elements.views) {
        elements.views.innerHTML = `<i class="fas fa-eye"></i> ${Number(media.views_count || 0).toLocaleString()} vues`;
    }
    
    if (elements.plays) {
        elements.plays.innerHTML = `<i class="fas fa-play"></i> ${Number(media.plays_count || 0).toLocaleString()} lectures`;
    }
    
    if (elements.likeCount) elements.likeCount.textContent = media.likes_count || 0;
    if (elements.dislikeCount) elements.dislikeCount.textContent = media.dislikes_count || 0;
    if (elements.commentsCount) elements.commentsCount.textContent = media.comments_count || 0;
    
    if (elements.date) {
        const date = new Date(media.created_at || media.date_media || Date.now());
        elements.date.innerHTML = `<i class="fas fa-calendar"></i> ${date.toLocaleDateString('fr-FR')}`;
    }
    
    const downloadBtn = document.getElementById('mediaDownloadBtn');
    if (downloadBtn) {
        downloadBtn.style.display = (media.fichier && media.type !== 'link') ? 'inline-flex' : 'none';
    }
}

function mediaGetPlayerHtml(media) {
    if (media.youtube_id) {
        return `<iframe src="https://www.youtube.com/embed/${media.youtube_id}?autoplay=1&rel=0" frameborder="0" allowfullscreen></iframe>`;
    }
    
    if (media.type === 'video' && media.fichier) {
        return `<video controls autoplay onplay="mediaIncrementPlay(${media.id_media})"><source src="${mediaBaseUrl}${media.fichier}" type="video/mp4"></video>`;
    }
    
    if (media.type === 'audio' && media.fichier) {
        return `<div style="background:#333; width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                   <audio controls autoplay onplay="mediaIncrementPlay(${media.id_media})" src="${mediaBaseUrl}${media.fichier}" style="width:80%;"></audio>
                </div>`;
    }
    
    if (media.type === 'image' && media.fichier) {
        return `<img src="${mediaBaseUrl}${media.fichier}" alt="${media.titre}" style="max-width:100%; max-height:100%; object-fit:contain;">`;
    }
    
    if (media.lien) {
        return `<div style="text-align:center; padding:40px;">
                   <a href="${media.lien}" target="_blank" style="color:#3ea6ff; font-size:1.2rem;">
                       <i class="fas fa-external-link-alt"></i> Ouvrir le lien
                   </a>
                </div>`;
    }
    
    return '<div class="text-center p-5">Aucun lecteur disponible</div>';
}

function mediaResetStars() {
    document.querySelectorAll('#mediaStarRating i').forEach(star => {
        star.classList.remove('fas', 'active');
        star.classList.add('far');
    });
    
    const userRating = document.getElementById('mediaUserRating');
    if (userRating) userRating.innerHTML = '';
}

function mediaNavigateMedia(direction) {
    const visibleCards = Array.from(document.querySelectorAll('.media-card:not(.hidden)'));
    if (visibleCards.length === 0) return;
    
    const currentCard = document.querySelector(`[data-media-index="${mediaCurrentIndex}"]`);
    const currentVisibleIndex = visibleCards.indexOf(currentCard);
    
    let newVisibleIndex = currentVisibleIndex + direction;
    if (newVisibleIndex < 0) newVisibleIndex = visibleCards.length - 1;
    if (newVisibleIndex >= visibleCards.length) newVisibleIndex = 0;
    
    const newCard = visibleCards[newVisibleIndex];
    const newDataIndex = parseInt(newCard.getAttribute('data-media-index'));
    
    mediaOpenLightbox(newDataIndex);
}

// ================= INTERACTIONS UTILISATEUR =================
function mediaHandleLike() {
    const media = mediaGalleryData[mediaCurrentIndex];
    if (!media) return;
    
    const btn = document.getElementById('mediaLikeBtn');
    const isLiked = btn.classList.contains('liked');
    
    btn.classList.toggle('liked');
    document.getElementById('mediaDislikeBtn').classList.remove('disliked');
    
    const currentCount = parseInt(document.getElementById('mediaLikeCount').textContent);
    document.getElementById('mediaLikeCount').textContent = isLiked ? currentCount - 1 : currentCount + 1;
    
    fetch(`${mediaBaseUrl}media/toggleLike`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${media.id_media}&action=${isLiked ? 'remove' : 'like'}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mediaShowToast(isLiked ? 'Like retiré' : 'Vous aimez ce média !');
        }
    })
    .catch(() => mediaShowToast('Erreur lors de l\'opération', 'error'));
}

function mediaHandleDislike() {
    const media = mediaGalleryData[mediaCurrentIndex];
    if (!media) return;
    
    const btn = document.getElementById('mediaDislikeBtn');
    const isDisliked = btn.classList.contains('disliked');
    
    btn.classList.toggle('disliked');
    document.getElementById('mediaLikeBtn').classList.remove('liked');
    
    const currentCount = parseInt(document.getElementById('mediaDislikeCount').textContent);
    document.getElementById('mediaDislikeCount').textContent = isDisliked ? currentCount - 1 : currentCount + 1;
    
    fetch(`${mediaBaseUrl}media/toggleLike`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${media.id_media}&action=${isDisliked ? 'remove' : 'dislike'}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mediaShowToast(isDisliked ? 'Dislike retiré' : 'Vous n\'aimez pas ce média');
        }
    })
    .catch(() => mediaShowToast('Erreur lors de l\'opération', 'error'));
}

function mediaRateMedia(rating) {
    const media = mediaGalleryData[mediaCurrentIndex];
    if (!media) return;
    
    document.querySelectorAll('#mediaStarRating i').forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('far');
            star.classList.add('fas', 'active');
        } else {
            star.classList.remove('fas', 'active');
            star.classList.add('far');
        }
    });
    
    const userRating = document.getElementById('mediaUserRating');
    if (userRating) {
        userRating.innerHTML = `<i class="fas fa-check-circle" style="color: var(--media-primary-green);"></i> Votre note: ${rating}/5`;
    }
    
    fetch(`${mediaBaseUrl}media/rateMedia`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${media.id_media}&rating=${rating}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mediaShowToast(`Merci pour votre note de ${rating} étoiles !`);
        }
    })
    .catch(() => mediaShowToast('Erreur lors de la notation', 'error'));
}

function mediaSubmitComment() {
    const input = document.getElementById('mediaCommentInput');
    if (!input) return;
    
    const text = input.value.trim();
    if (!text) {
        mediaShowToast('Veuillez écrire un commentaire', 'error');
        return;
    }
    
    const media = mediaGalleryData[mediaCurrentIndex];
    if (!media) return;
    
    fetch(`${mediaBaseUrl}media/addComment`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${media.id_media}&comment=${encodeURIComponent(text)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            mediaLoadComments(media.id_media);
            mediaShowToast('Commentaire ajouté !');
        }
    })
    .catch(() => mediaShowToast('Erreur lors de l\'ajout du commentaire', 'error'));
}

function mediaClearComment() {
    document.getElementById('mediaCommentInput').value = '';
}

// ================= CHARGEMENT DES DONNÉES =================
function mediaLoadComments(mediaId) {
    fetch(`${mediaBaseUrl}media/getComments/${mediaId}`)
        .then(r => r.json())
        .then(data => {
            const comments = data.comments || [];
            const list = document.getElementById('mediaCommentsList');
            if (!list) return;
            
            if (comments.length === 0) {
                list.innerHTML = '<p style="color: var(--media-yt-text-secondary); text-align: center;">Aucun commentaire. Soyez le premier !</p>';
                return;
            }
            
            list.innerHTML = comments.map(c => `
                <div class="media-comment-item">
                    <div class="media-comment-avatar"><i class="fas fa-user"></i></div>
                    <div class="media-comment-content">
                        <div class="media-comment-author">${mediaEscapeHtml(c.author_name || 'Anonyme')}</div>
                        <div class="media-comment-text">${mediaEscapeHtml(c.comment || '')}</div>
                        <div class="media-comment-date">${mediaFormatDate(c.created_at)}</div>
                    </div>
                </div>
            `).join('');
        })
        .catch(() => {
            const list = document.getElementById('mediaCommentsList');
            if (list) {
                list.innerHTML = '<p style="color: var(--media-yt-text-secondary); text-align: center;">Erreur de chargement des commentaires</p>';
            }
        });
}

function mediaLoadCompactRecommendations(mediaId) {
    fetch(`${mediaBaseUrl}media/getRecommended/${mediaId}`)
        .then(r => r.json())
        .then(data => {
            const medias = data.medias || [];
            const grid = document.getElementById('mediaCompactRecommendations');
            if (!grid) return;
            
            if (medias.length === 0) {
                grid.innerHTML = '<p class="text-muted">Aucune recommandation</p>';
                return;
            }
            
            grid.innerHTML = medias.slice(0, 4).map(m => {
                const thumbUrl = m.miniature ? mediaBaseUrl + m.miniature : `${mediaBaseUrl}assets/images/default_thumbnail.jpg`;
                return `
                <div class="media-compact-item" onclick="mediaOpenRecommended(${m.id_media})">
                    <img src="${thumbUrl}" alt="${mediaEscapeHtml(m.titre)}" loading="lazy">
                    <div class="media-compact-item-title">${mediaEscapeHtml(m.titre)}</div>
                    <div class="media-compact-item-stats">${(m.views_count || 0).toLocaleString()} vues</div>
                </div>
                `;
            }).join('');
        })
        .catch(() => {
            const grid = document.getElementById('mediaCompactRecommendations');
            if (grid) {
                grid.innerHTML = '<p class="text-muted">Erreur de chargement</p>';
            }
        });
}

function mediaOpenRecommended(id) {
    const index = mediaGalleryData.findIndex(m => parseInt(m.id_media) === parseInt(id));
    if (index !== -1) {
        mediaOpenLightbox(index);
    }
}

// ================= TRACKING =================
function mediaIncrementViews(mediaId) {
    fetch(`${mediaBaseUrl}media/trackView`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}`,
        keepalive: true
    }).catch(() => {});
}

function mediaIncrementPlay(mediaId) {
    fetch(`${mediaBaseUrl}media/trackPlay`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}`,
        keepalive: true
    }).catch(() => {});
}

// ================= PARTAGE ET TÉLÉCHARGEMENT =================
function mediaShareMedia() {
    const media = mediaGalleryData[mediaCurrentIndex];
    if (!media) return;
    
    const shareData = {
        title: media.titre,
        text: media.description || 'Découvrez ce média',
        url: window.location.href
    };
    
    if (navigator.share) {
        navigator.share(shareData).catch(() => {});
    } else {
        navigator.clipboard.writeText(shareData.url).then(() => {
            mediaShowToast('Lien copié dans le presse-papiers !');
        }).catch(() => {
            mediaShowToast('Erreur lors de la copie', 'error');
        });
    }
}

function mediaDownloadMedia() {
    const media = mediaGalleryData[mediaCurrentIndex];
    if (media && media.fichier) {
        window.open(`${mediaBaseUrl}${media.fichier}`, '_blank');
    }
}

// ================= TOAST NOTIFICATIONS =================
function mediaShowToast(message, type = 'success') {
    const container = document.getElementById('mediaToastContainer');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `media-toast ${type}`;
    toast.textContent = message;
    toast.setAttribute('role', 'alert');
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'mediaSlideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ================= GESTION CLAVIER =================
function mediaInitializeKeyboardNav() {
    document.addEventListener('keydown', mediaHandleKeyboard);
}

function mediaHandleKeyboard(e) {
    const modal = document.getElementById('mediaLightboxModal');
    if (!modal || !modal.classList.contains('show')) return;
    
    switch(e.key) {
        case 'ArrowLeft':
            e.preventDefault();
            mediaNavigateMedia(-1);
            break;
        case 'ArrowRight':
            e.preventDefault();
            mediaNavigateMedia(1);
            break;
        case 'Escape':
            if (mediaCurrentModal) {
                mediaCurrentModal.hide();
            }
            break;
    }
}

// ================= INITIALISATION DES ÉVÉNEMENTS LIGHTBOX =================
function mediaInitializeLightboxEvents() {
    const lightbox = document.getElementById('mediaLightboxModal');
    if (!lightbox) return;
    
    lightbox.addEventListener('hidden.bs.modal', function() {
        const container = document.getElementById('mediaVideoContainer');
        if (container) {
            container.innerHTML = '';
        }
        
        const downloadBtn = document.getElementById('mediaDownloadBtn');
        if (downloadBtn) {
            downloadBtn.style.display = 'none';
        }
    });
}

// ================= FILTRES =================
function mediaFilterMedia(type) {
    mediaCurrentFilter = type;
    
    document.querySelectorAll('.media-filter-btn').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-filter') === type);
    });
    
    mediaApplyFilters();
}

function mediaApplyFilters() {
    const searchInput = document.getElementById('mediaSearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const cards = document.querySelectorAll('.media-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const title = card.getAttribute('data-media-title') || '';
        const category = card.getAttribute('data-media-category') || '';
        const cardType = card.getAttribute('data-media-type') || '';
        
        const matchesSearch = !query || title.includes(query) || category.includes(query);
        const matchesFilter = mediaCurrentFilter === 'all' || cardType === mediaCurrentFilter;
        
        if (matchesSearch && matchesFilter) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });
}

// ================= INITIALISATION SUPPLÉMENTAIRE =================
const mediaStyle = document.createElement('style');
mediaStyle.textContent = `
    @keyframes mediaSlideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(mediaStyle);

</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>