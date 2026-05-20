<!DOCTYPE html>
<html lang="<?= $lang ?? 'fr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($media['titre'] ?? t('media_detail_title')) ?> - <?= $this->Model->get_setting('site_name', 'NUFOTEC') ?></title>
    <meta property="og:title" content="<?= htmlspecialchars($media['titre'] ?? '') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($media['description'] ?? $media['credits'] ?? '') ?>">
    <meta property="og:image" content="<?= $media['thumbnail_url'] ?? base_url('assets/images/default-share.jpg') ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:type" content="video.other">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#0a0a0a">
    <link rel="icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* ============================================
           VARIABLES PREMIUM
        ============================================ */
        :root {
            --bg-primary: #0a0a0a;
            --bg-secondary: #121212;
            --bg-tertiary: #1a1a1a;
            --bg-card: #1e1e1e;
            --bg-hover: #2a2a2a;
            --bg-glass: rgba(26, 26, 26, 0.95);
            --text-primary: #ffffff;
            --text-secondary: #aaaaaa;
            --text-tertiary: #717171;
            --accent-green: #00d084;
            --accent-blue: #3ea6ff;
            --accent-red: #ff0000;
            --border-color: #2a2a2a;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 16px 48px rgba(0, 0, 0, 0.5);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-fast: all 0.15s ease;
            --border-radius-sm: 8px;
            --border-radius-md: 12px;
            --border-radius-lg: 16px;
            --border-radius-xl: 24px;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: var(--bg-primary); 
            color: var(--text-primary);
            overflow-x: hidden;
            line-height: 1.5;
            top: 0 !important;
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* Scrollbar Premium */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-tertiary); }
        ::-webkit-scrollbar-thumb { background: var(--text-tertiary); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-secondary); }

        /* Cacher Google Translate */
        .goog-te-banner-frame, .goog-te-banner, .skiptranslate {
            display: none !important;
            height: 0 !important;
            visibility: hidden !important;
            opacity: 0 !important;
            position: absolute !important;
            top: -9999px !important;
        }
        body { top: 0 !important; position: relative !important; }

        /* ============================================
           NAVBAR PREMIUM
        ============================================ */
        .navbar {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1000;
            height: 64px;
            padding: 0 1.5rem;
        }
        
        .navbar .container-fluid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
            max-width: 1600px;
            margin: 0 auto;
            gap: 1rem;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }
        
        .navbar-brand img { height: 36px; width: auto; }
        .brand-name { font-weight: 700; font-size: 1.25rem; color: var(--text-primary); letter-spacing: -0.5px; }
        .brand-badge { 
            background: linear-gradient(135deg, var(--accent-green), #00a86b);
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        /* Language Selector */
        .lang-selector-custom { position: relative; margin-left: 8px; }
        .custom-language-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 30px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
            transition: var(--transition-fast);
        }
        .custom-language-btn:hover { border-color: var(--accent-blue); background: var(--bg-hover); transform: translateY(-1px); }
        .custom-language-btn img { width: 20px; height: 15px; border-radius: 3px; }
        .custom-language-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            background: var(--bg-tertiary);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            padding: 8px;
            min-width: 220px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            z-index: 1000;
            border: 1px solid var(--border-color);
            max-height: 400px;
            overflow-y: auto;
        }
        .custom-language-dropdown.active { opacity: 1; visibility: visible; transform: translateY(0); }
        .lang-option {
            display: flex !important;
            align-items: center !important;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            width: 100%;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
            transition: var(--transition-fast);
        }
        .lang-option:hover { background: var(--bg-hover); color: var(--accent-blue); transform: translateX(4px); }
        .lang-option img { width: 22px; height: 16px; border-radius: 3px; }

        /* ============================================
           MAIN CONTENT
        ============================================ */
        .main-content { max-width: 1600px; margin: 0 auto; padding: 1.5rem; }
        
        .watch-layout {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        @media (min-width: 1024px) {
            .watch-layout { flex-direction: row; }
            .video-column { flex: 2.5; min-width: 0; }
            .suggestions-column { flex: 1.2; min-width: 0; }
        }

        /* ============================================
           VIDEO PLAYER PREMIUM
        ============================================ */
        .video-wrapper {
            position: relative;
            background: #000;
            border-radius: var(--border-radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            aspect-ratio: 16 / 9;
        }
        
        .video-wrapper iframe, .video-wrapper video {
            width: 100%;
            height: 100%;
            border: none;
            object-fit: contain;
        }
        
        .video-controls-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            padding: 1rem;
            opacity: 0;
            transition: var(--transition);
        }
        
        .video-wrapper:hover .video-controls-overlay { opacity: 1; }
        
        .download-floating {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            z-index: 10;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(8px);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: var(--transition-fast);
            opacity: 0;
        }
        
        .video-wrapper:hover .download-floating { opacity: 1; }
        .download-floating:hover { background: var(--accent-blue); transform: scale(1.05); }

        /* ============================================
           VIDEO INFO PREMIUM
        ============================================ */
        .video-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 1rem 0 0.75rem;
            line-height: 1.3;
        }
        
        .video-meta-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1rem;
        }
        
        .video-stats {
            display: flex;
            gap: 1rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .video-stats i { margin-right: 0.25rem; }
        
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
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .action-btn:hover { background: var(--bg-hover); color: var(--text-primary); }
        .action-btn.active { color: var(--accent-blue); }
        .action-btn.disliked { color: var(--accent-blue); }

        /* Channel Info Premium */
        .channel-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1rem;
        }
        
        .channel-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .channel-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-green), #00a86b);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .channel-avatar i { font-size: 1.5rem; color: white; }
        
        .channel-details h4 { font-size: 1rem; font-weight: 600; margin-bottom: 0.25rem; }
        .channel-details p { font-size: 0.75rem; color: var(--text-tertiary); }
        
        .subscribe-btn {
            background: var(--accent-green);
            color: #0a0a0a;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 40px;
            font-weight: 700;
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .subscribe-btn:hover { transform: scale(1.02); background: #00e896; }

        /* Description Premium */
        .description-box {
            background: var(--bg-card);
            border-radius: var(--border-radius-md);
            padding: 1rem;
            margin: 1rem 0;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .description-box:hover { background: var(--bg-hover); }
        .description-text {
            color: var(--text-secondary);
            font-size: 0.875rem;
            line-height: 1.5;
        }
        .description-text:not(.expanded) {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .description-text.expanded { white-space: normal; }

        /* ============================================
           COMMENTS SECTION
        ============================================ */
        .comments-section { margin-top: 1.5rem; }
        .comments-title { font-size: 1rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        
        .comment-form { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bg-hover);
            flex-shrink: 0;
            overflow: hidden;
        }
        .comment-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .comment-input-wrapper { flex: 1; }
        .comment-input {
            width: 100%;
            background: transparent;
            border: none;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.5rem 0;
            font-size: 0.875rem;
        }
        .comment-input:focus { outline: none; border-bottom-color: var(--accent-blue); }
        .comment-submit {
            background: var(--accent-blue);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            transition: var(--transition-fast);
        }
        .comment-submit:hover { opacity: 0.9; }
        
        .comment-item { display: flex; gap: 1rem; margin-bottom: 1rem; padding: 0.5rem 0; }
        .comment-author { font-weight: 600; font-size: 0.8125rem; margin-bottom: 0.25rem; }
        .comment-text { font-size: 0.875rem; line-height: 1.4; word-break: break-word; }

        /* ============================================
           SUGGESTIONS PREMIUM
        ============================================ */
        .suggestions-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .related-item {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: var(--transition-fast);
            padding: 0.5rem;
            border-radius: var(--border-radius-sm);
        }
        
        .related-item:hover { background: var(--bg-hover); transform: translateX(4px); }
        
        .related-thumb {
            width: 168px;
            height: 94px;
            border-radius: var(--border-radius-sm);
            background-size: cover;
            background-position: center;
            flex-shrink: 0;
        }
        
        .related-info { flex: 1; min-width: 0; }
        .related-title-sm {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .related-meta { font-size: 0.75rem; color: var(--text-tertiary); }
        .related-meta i { font-size: 0.65rem; margin-right: 2px; }

        /* Audio Player Premium */
        .audio-player {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            text-align: center;
        }
        .audio-cover {
            width: 200px;
            height: 200px;
            border-radius: var(--border-radius-md);
            margin: 0 auto 1rem;
            background-size: cover;
            background-position: center;
            box-shadow: var(--shadow-md);
        }
        .audio-controls { display: flex; align-items: center; justify-content: center; gap: 1.5rem; margin: 1.5rem 0; }
        .audio-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        .audio-btn:hover { transform: scale(1.05); background: rgba(255,255,255,0.2); }
        .audio-btn.play-pause { width: 56px; height: 56px; background: var(--accent-blue); }
        .progress { cursor: pointer; height: 4px; background: rgba(255,255,255,0.3); border-radius: 2px; overflow: hidden; }
        .progress-bar { background: var(--accent-blue); height: 100%; width: 0%; transition: width 0.1s linear; }

        /* Toast */
        .toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 9999; }
        .toast-custom {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius-sm);
            margin-top: 0.5rem;
            animation: slideIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow-sm);
        }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar { padding: 0 1rem; height: 56px; }
            .main-content { padding: 1rem; }
            .video-title { font-size: 1.125rem; }
            .action-btn span:not(.count) { display: none; }
            .action-btn { padding: 0.5rem; }
            .related-item { flex-direction: column; }
            .related-thumb { width: 100%; aspect-ratio: 16/9; height: auto; }
            .channel-left { flex: 1; }
            .brand-badge { display: none; }
            .navbar-brand img { height: 28px; }
            .brand-name { font-size: 1rem; }
        }
        
        @media (max-width: 480px) {
            .video-stats { font-size: 0.75rem; gap: 0.75rem; }
            .channel-avatar { width: 36px; height: 36px; }
            .channel-avatar i { font-size: 1rem; }
            .subscribe-btn { padding: 0.35rem 1rem; font-size: 0.75rem; }
        }

        .alert-secondary { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--border-radius-md); padding: 1rem; }
        .alert-secondary a { color: var(--accent-blue); text-decoration: none; }
        .btn-outline-secondary { border: 1px solid var(--border-color); background: transparent; color: var(--text-secondary); border-radius: 30px; padding: 0.5rem 1rem; font-size: 0.875rem; transition: var(--transition-fast); }
        .btn-outline-secondary:hover { background: var(--bg-hover); border-color: var(--accent-blue); color: var(--text-primary); }
    </style>

    <script type="text/javascript">
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'fr',
        includedLanguages: 'fr,en,rn,sw,ar,de,es,pt,it,zh-CN,ru,nl,pl,tr,ja,ko,hi,vi,th,el,he,sv,da,no,fi,cs,hu,ro,uk',
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, 'google_translate_element');
    
    // Forcer l'application de la langue sauvegardée après initialisation
    setTimeout(function() {
        const savedLang = localStorage.getItem('preferred_language');
        if (savedLang && savedLang !== 'fr') {
            // Vérifier si le cookie est défini
            const hasCookie = document.cookie.indexOf(`googtrans=/fr/${savedLang}`) !== -1;
            if (!hasCookie) {
                document.cookie = `googtrans=/fr/${savedLang}; path=/; max-age=31536000`;
                window.location.reload();
                return;
            }
            
            // Forcer le sélecteur Google Translate
            const selectElement = document.querySelector('.goog-te-combo');
            if (selectElement && selectElement.value !== savedLang) {
                selectElement.value = savedLang;
                selectElement.dispatchEvent(new Event('change'));
            }
        }
    }, 500);
}
</script>
</head>
<body>

<!-- Google Translate Container -->
<div id="google_translate_element" style="display: none;"></div>

<!-- Navbar Premium -->
<nav class="navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= base_url('media') ?>">
            <?php 
            $site_logo = $this->Model->get_setting('site_logo');
            if (!empty($site_logo)): ?>
                <img src="<?= base_url('attachments/Configurations/' . $site_logo) ?>" alt="NUFOTEC">
            <?php endif; ?>
            <span class="brand-name">NUFOTEC</span>
            <span class="brand-badge">MEDIA</span>
        </a>
        
        <div class="d-flex gap-2 align-items-center">
            <a href="<?= base_url('media') ?>" class="btn-outline-secondary" style="text-decoration: none;">
                <i class="bi bi-house"></i> <span class="d-none d-sm-inline">Accueil</span>
            </a>
            
            <div class="lang-selector-custom">
                <button class="custom-language-btn" id="customLanguageBtn">
                    <img src="https://flagcdn.com/w20/fr.png" alt="Français" id="currentLangFlag">
                    <span id="currentLangLabel">Français</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="custom-language-dropdown" id="customLanguageDropdown">
                    <button class="lang-option" data-lang="fr" data-flag="fr" data-label="Français"><img src="https://flagcdn.com/w20/fr.png"> Français</button>
                    <button class="lang-option" data-lang="en" data-flag="us" data-label="English"><img src="https://flagcdn.com/w20/us.png"> English</button>
                    <button class="lang-option" data-lang="rn" data-flag="bi" data-label="Kirundi"><img src="https://flagcdn.com/w20/bi.png"> Kirundi</button>
                    <button class="lang-option" data-lang="sw" data-flag="tz" data-label="Kiswahili"><img src="https://flagcdn.com/w20/tz.png"> Kiswahili</button>
                    <button class="lang-option" data-lang="ar" data-flag="sa" data-label="العربية"><img src="https://flagcdn.com/w20/sa.png"> العربية</button>
                    <button class="lang-option" data-lang="de" data-flag="de" data-label="Deutsch"><img src="https://flagcdn.com/w20/de.png"> Deutsch</button>
                    <button class="lang-option" data-lang="es" data-flag="es" data-label="Español"><img src="https://flagcdn.com/w20/es.png"> Español</button>
                    <button class="lang-option" data-lang="pt" data-flag="pt" data-label="Português"><img src="https://flagcdn.com/w20/pt.png"> Português</button>
                    <button class="lang-option" data-lang="it" data-flag="it" data-label="Italiano"><img src="https://flagcdn.com/w20/it.png"> Italiano</button>
                    <button class="lang-option" data-lang="zh-CN" data-flag="cn" data-label="中文"><img src="https://flagcdn.com/w20/cn.png"> 中文</button>
                    <button class="lang-option" data-lang="ru" data-flag="ru" data-label="Русский"><img src="https://flagcdn.com/w20/ru.png"> Русский</button>
                </div>
            </div>
        </div>
    </div>
</nav>

<main class="main-content">
    <?php 
    function formatFileSize($bytes) {
        if (!$bytes) return '';
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 1) . ' Go';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 1) . ' Mo';
        if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' Ko';
        return $bytes . ' octets';
    }
    
    if ($media): 
        $mediaSlug = !empty($media['slug']) ? $media['slug'] : $media['id_media'];
        $type = $media['type'] ?? 'autre';
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
        <div class="watch-layout">
            <div class="video-column">
                <!-- Lecteur Vidéo Premium -->
                <?php if ($is_youtube_link): ?>
                    <div class="video-wrapper">
                        <iframe src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($youtube_id) ?>?autoplay=1&rel=0&modestbranding=1&showinfo=0&controls=1&fs=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                    
                <?php elseif ($type === 'video' && !empty($fichier)): ?>
                    <div class="video-wrapper">
                        <video controls autoplay playsinline>
                            <source src="<?= htmlspecialchars($fichier) ?>" type="video/mp4">
                        </video>
                        <button class="download-floating" onclick="downloadMedia('<?= htmlspecialchars($mediaSlug) ?>')">
                            <i class="bi bi-download"></i>
                        </button>
                    </div>
                    
                <?php elseif ($type === 'audio' && !empty($fichier)): ?>
                    <div class="audio-player">
                        <div class="audio-cover" style="background-image: url('<?= htmlspecialchars($media['cover_url'] ?? base_url('assets/images/audio-default.png')) ?>')"></div>
                        <div class="audio-title"><?= htmlspecialchars($media['titre']) ?></div>
                        <div class="audio-artist"><?= htmlspecialchars($media['artist'] ?? $media['credits'] ?? 'Artiste') ?></div>
                        <button class="btn-outline-secondary" style="margin-bottom: 1rem;" onclick="downloadMedia('<?= htmlspecialchars($mediaSlug) ?>')">
                            <i class="bi bi-download"></i> Télécharger
                        </button>
                        <audio id="audioElement" src="<?= htmlspecialchars($fichier) ?>" preload="metadata"></audio>
                        <div class="audio-controls">
                            <button class="audio-btn" onclick="previousTrack()"><i class="bi bi-skip-backward-fill"></i></button>
                            <button class="audio-btn play-pause" id="playPauseBtn" onclick="togglePlay()"><i class="bi bi-play-fill"></i></button>
                            <button class="audio-btn" onclick="nextTrack()"><i class="bi bi-skip-forward-fill"></i></button>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="d-flex justify-content-between small mb-1">
                                <span id="currentTime">0:00</span>
                                <span id="totalTime">0:00</span>
                            </div>
                            <div class="progress" onclick="seekAudio(event)"><div class="progress-bar" id="progressFill"></div></div>
                        </div>
                    </div>
                    
                <?php elseif ($type === 'image' && !empty($fichier)): ?>
                    <div class="image-viewer" style="text-align: center; background: #000; border-radius: var(--border-radius-lg); padding: 2rem; position: relative;">
                        <img src="<?= htmlspecialchars($fichier) ?>" alt="<?= htmlspecialchars($media['titre']) ?>" style="max-width: 100%; max-height: 500px; border-radius: var(--border-radius-md);">
                        <button class="download-floating" style="position: absolute; top: 1rem; right: 1rem;" onclick="downloadMedia('<?= htmlspecialchars($mediaSlug) ?>')">
                            <i class="bi bi-download"></i>
                        </button>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center p-5" style="background: var(--bg-card); border-radius: var(--border-radius-lg);">
                        <i class="bi bi-file-earmark" style="font-size: 4rem; opacity: 0.5;"></i>
                        <h4 class="mt-2"><?= htmlspecialchars($media['titre']) ?></h4>
                        <?php if (!empty($lien) || !empty($fichier)): ?>
                            <a href="<?= htmlspecialchars($lien ?: $fichier) ?>" target="_blank" class="btn-outline-secondary" style="display: inline-block; margin-top: 1rem; text-decoration: none;">
                                <i class="bi bi-box-arrow-up-right"></i> Ouvrir
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Informations Vidéo -->
                <h1 class="video-title"><?= htmlspecialchars($media['titre']) ?></h1>
                
                <div class="video-meta-bar">
                    <div class="video-stats">
                        <span><i class="bi bi-eye"></i> <?= number_format($media['views_count'] ?? 0) ?> vues</span>
                        <span><i class="bi bi-hand-thumbs-up"></i> <?= number_format($media['likes_count'] ?? 0) ?></span>
                        <span><i class="bi bi-chat"></i> <?= number_format($media['comments_count'] ?? 0) ?> commentaires</span>
                    </div>
                    <div class="action-buttons">
                        <button class="action-btn <?= ($media['user_like_action'] ?? '') === 'like' ? 'active' : '' ?>" onclick="toggleLike(<?= (int)$media['id_media'] ?>)">
                            <i class="bi bi-hand-thumbs-up"></i> <span id="likeCount"><?= (int)($media['likes_count'] ?? 0) ?></span>
                        </button>
                        <button class="action-btn <?= ($media['user_like_action'] ?? '') === 'dislike' ? 'disliked' : '' ?>" onclick="toggleDislike(<?= (int)$media['id_media'] ?>)">
                            <i class="bi bi-hand-thumbs-down"></i> <span id="dislikeCount"><?= (int)($media['dislikes_count'] ?? 0) ?></span>
                        </button>
                        <button class="action-btn" onclick="shareMedia()">
                            <i class="bi bi-share"></i> <span class="d-none d-md-inline">Partager</span>
                        </button>
                        <?php if ($is_downloadable && $type !== 'audio'): ?>
                            <button class="action-btn" onclick="downloadMedia('<?= htmlspecialchars($mediaSlug) ?>')">
                                <i class="bi bi-download"></i> <span class="d-none d-md-inline">Télécharger</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                                
                <!-- Description -->
                <div class="description-box" onclick="toggleDescription()">
                    <div class="description-text" id="descriptionText">
                        <?= nl2br(htmlspecialchars($media['description'] ?? 'Aucune description')) ?>
                    </div>
                    <small class="text-secondary mt-1 d-block" id="descriptionToggle">Afficher plus</small>
                </div>
                
                <!-- Commentaires -->
                <div class="comments-section">
                    <div class="comments-title"><i class="bi bi-chat-dots"></i> <?= (int)($media['comments_count'] ?? 0) ?> commentaires</div>
                    
                    <?php if (isset($user) && $user): ?>
                    <div class="comment-form">
                        <div class="comment-avatar">
                            <?php if (!empty($user['photo'])): ?>
                                <img src="<?= base_url('attachments/Users/' . $user['photo']) ?>" alt="Avatar">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: var(--accent-green); display: flex; align-items: center; justify-content: center;">
                                    <?= strtoupper(substr($user['prenom'] ?? 'U', 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="comment-input-wrapper">
                            <textarea class="comment-input" id="commentText" rows="1" placeholder="Ajouter un commentaire..."></textarea>
                            <button class="comment-submit d-none" id="commentSubmit" onclick="addComment(<?= (int)$media['id_media'] ?>)">Commenter</button>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert-secondary">
                        <i class="bi bi-info-circle"></i> 
                        <a href="<?= base_url('Auth') ?>">Connectez-vous</a> pour commenter
                    </div>
                    <?php endif; ?>
                    
                    <div class="comment-list" id="commentList">
                        <?php if (!empty($comments)): ?>
                            <?php foreach($comments as $comment): ?>
                                <div class="comment-item">
                                    <div class="comment-avatar">
                                        <?php if (!empty($comment['photo'])): ?>
                                            <img src="<?= base_url('attachments/Users/' . $comment['photo']) ?>" alt="Avatar">
                                        <?php else: ?>
                                            <div style="width: 100%; height: 100%; background: var(--accent-green); display: flex; align-items: center; justify-content: center;">
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
                                <p class="mt-2">Aucun commentaire pour le moment</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Suggestions -->
            <div class="suggestions-column">
                <div class="suggestions-title"><i class="bi bi-collection-play"></i> À regarder ensuite</div>
                <?php if (!empty($recommended)): ?>
                    <?php foreach($recommended as $related): 
                        $relatedSlug = !empty($related['slug']) ? $related['slug'] : $related['id_media'];
                    ?>
                        <div class="related-item" onclick="window.location.href='<?= base_url('media/detail/'.$relatedSlug) ?>'">
                            <div class="related-thumb" style="background-image: url('<?= htmlspecialchars($related['thumbnail_url'] ?? base_url('assets/images/default-thumbnail.jpg')) ?>')"></div>
                            <div class="related-info">
                                <p class="related-title-sm"><?= htmlspecialchars($related['titre']) ?></p>
                                <div class="related-meta">
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($related['credits'] ?? 'NUFOTEC') ?> • 
                                    <i class="bi bi-eye"></i> <?= number_format($related['views_count'] ?? 0) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-secondary py-4">
                        <i class="bi bi-collection-play display-6"></i>
                        <p class="mt-2">Aucune suggestion</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    <?php else: ?>
        <div class="text-center p-5" style="background: var(--bg-card); border-radius: var(--border-radius-lg);">
            <i class="bi bi-exclamation-triangle display-1" style="opacity: 0.5;"></i>
            <h3 class="mt-3">Média non trouvé</h3>
            <p class="text-secondary">Le média que vous recherchez n'existe pas ou a été supprimé.</p>
            <a href="<?= base_url('media') ?>" class="btn-outline-secondary" style="display: inline-block; margin-top: 1rem; text-decoration: none;">
                <i class="bi bi-house"></i> Retour à l'accueil
            </a>
        </div>
    <?php endif; ?>
</main>

<div class="toast-container" id="toastContainer"></div>

<script>
// Configuration
const mediaId = <?= (int)($media['id_media'] ?? 0) ?>;
const mediaSlug = '<?= htmlspecialchars($mediaSlug ?? '') ?>';
const baseUrl = '<?= rtrim(base_url(), '/') ?>';

// Audio Player
let audioElement = document.getElementById('audioElement');
let isPlaying = false;

if (audioElement) {
    audioElement.addEventListener('timeupdate', updateProgress);
    audioElement.addEventListener('ended', () => { isPlaying = false; updatePlayButton(); });
    audioElement.addEventListener('loadedmetadata', () => {
        const totalTimeSpan = document.getElementById('totalTime');
        if (totalTimeSpan) totalTimeSpan.textContent = formatTime(audioElement.duration);
    });
}

function togglePlay() {
    if (!audioElement) return;
    if (isPlaying) audioElement.pause();
    else audioElement.play();
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
    const currentTimeSpan = document.getElementById('currentTime');
    if (currentTimeSpan) currentTimeSpan.textContent = formatTime(audioElement.currentTime);
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

function previousTrack() { showToast('Fonctionnalité à venir', 'info'); }
function nextTrack() { showToast('Fonctionnalité à venir', 'info'); }

function downloadMedia(identifier) {
    const isNumeric = !isNaN(identifier) && !isNaN(parseFloat(identifier));
    const paramName = isNumeric ? 'id' : 'slug';
    const downloadUrl = baseUrl + '/media/downloader?' + paramName + '=' + encodeURIComponent(identifier);
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.setAttribute('download', '');
    link.click();
    showToast('Téléchargement démarré', 'success');
}

function toggleLike(mediaId) {
    const btn = document.querySelector('[onclick*="toggleLike"]');
    if (!btn) return;
    const isLiked = btn.classList.contains('active');
    
    fetch(baseUrl + '/media/apiToggleLike', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}&action=${isLiked ? 'remove' : 'like'}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('likeCount').textContent = data.likes;
            document.getElementById('dislikeCount').textContent = data.dislikes;
            btn.classList.toggle('active', !isLiked);
            const dislikeBtn = document.querySelector('[onclick*="toggleDislike"]');
            if (dislikeBtn) dislikeBtn.classList.remove('disliked');
            showToast(isLiked ? 'Like retiré' : 'Like ajouté', 'success');
        } else if (data.need_login) {
            showToast('Veuillez vous connecter', 'warning');
            setTimeout(() => window.location.href = baseUrl + '/Auth', 1500);
        }
    })
    .catch(() => showToast('Erreur', 'error'));
}

function toggleDislike(mediaId) {
    const btn = document.querySelector('[onclick*="toggleDislike"]');
    if (!btn) return;
    const isDisliked = btn.classList.contains('disliked');
    
    fetch(baseUrl + '/media/apiToggleLike', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}&action=${isDisliked ? 'remove' : 'dislike'}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('likeCount').textContent = data.likes;
            document.getElementById('dislikeCount').textContent = data.dislikes;
            btn.classList.toggle('disliked', !isDisliked);
            const likeBtn = document.querySelector('[onclick*="toggleLike"]');
            if (likeBtn) likeBtn.classList.remove('active');
            showToast(isDisliked ? 'Dislike retiré' : 'Dislike ajouté', 'success');
        } else if (data.need_login) {
            showToast('Veuillez vous connecter', 'warning');
            setTimeout(() => window.location.href = baseUrl + '/Auth', 1500);
        }
    })
    .catch(() => showToast('Erreur', 'error'));
}

function addComment(mediaId) {
    const commentText = document.getElementById('commentText');
    const comment = commentText?.value.trim();
    
    if (!comment) {
        showToast('Veuillez écrire un commentaire', 'warning');
        return;
    }
    
    fetch(baseUrl + '/media/apiAddComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_media=${mediaId}&comment=${encodeURIComponent(comment)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Commentaire ajouté', 'success');
            commentText.value = '';
            setTimeout(() => location.reload(), 1000);
        } else if (data.need_login) {
            showToast('Veuillez vous connecter', 'warning');
            setTimeout(() => window.location.href = baseUrl + '/Auth', 1500);
        } else {
            showToast(data.message || 'Erreur', 'error');
        }
    })
    .catch(() => showToast('Erreur', 'error'));
}

function shareMedia() {
    if (navigator.share) {
        navigator.share({ title: '<?= htmlspecialchars($media['titre'] ?? '') ?>', url: window.location.href })
            .catch(() => copyToClipboard());
    } else {
        copyToClipboard();
    }
}

function copyToClipboard() {
    navigator.clipboard.writeText(window.location.href);
    showToast('Lien copié !', 'success');
}

let descriptionExpanded = false;
function toggleDescription() {
    const desc = document.getElementById('descriptionText');
    const toggle = document.getElementById('descriptionToggle');
    if (!desc) return;
    descriptionExpanded = !descriptionExpanded;
    desc.classList.toggle('expanded', descriptionExpanded);
    toggle.textContent = descriptionExpanded ? 'Afficher moins' : 'Afficher plus';
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = 'toast-custom';
    let icon = type === 'success' ? 'bi-check-circle-fill' : (type === 'error' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill');
    let bgColor = type === 'success' ? '#2e7d32' : (type === 'error' ? '#c62828' : '#1e1e1e');
    toast.style.background = bgColor;
    toast.innerHTML = `<i class="bi ${icon}"></i><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

const commentTextarea = document.getElementById('commentText');
if (commentTextarea) {
    commentTextarea.addEventListener('input', function() {
        const submit = document.getElementById('commentSubmit');
        if (submit) submit.classList.toggle('d-none', !this.value.trim());
    });
}

if (mediaId) {
    fetch(baseUrl + '/media/apiTrackView', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: `id_media=${mediaId}` }).catch(() => {});
}





// ============================================
// LANGUAGE MANAGEMENT - VERSION CORRIGÉE
// ============================================

// Éléments DOM
const langBtn = document.getElementById('customLanguageBtn');
const langDropdown = document.getElementById('customLanguageDropdown');
const currentLangFlag = document.getElementById('currentLangFlag');
const currentLangLabel = document.getElementById('currentLangLabel');

// Fonction pour supprimer tous les cookies googtrans
function clearGoogtransCookies() {
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        if (cookie.trim().startsWith('googtrans=')) {
            const cookieName = cookie.trim().split('=')[0];
            document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
            document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=${window.location.hostname};`;
        }
    }
}

// Fonction pour définir le cookie de langue
function setLanguageCookie(langCode) {
    clearGoogtransCookies();
    if (langCode !== 'fr') {
        document.cookie = `googtrans=/fr/${langCode}; path=/; max-age=31536000`;
        return true;
    }
    return false;
}

// Récupérer la langue sauvegardée
const savedLang = localStorage.getItem('preferred_language');
const savedFlag = localStorage.getItem('preferred_flag');
const savedLabel = localStorage.getItem('preferred_label');

// APPLIQUER LA LANGUE SAUVEGARDÉE AU CHARGEMENT (EXÉCUTION IMMÉDIATE)
(function applySavedLanguage() {
    if (savedLang && savedFlag && savedLabel && savedLang !== 'fr') {
        // Vérifier si le cookie existe déjà
        const cookieExists = document.cookie.indexOf(`googtrans=/fr/${savedLang}`) !== -1;
        
        if (!cookieExists) {
            // Définir le cookie
            setLanguageCookie(savedLang);
            // Recharger la page pour appliquer
            setTimeout(() => {
                window.location.reload();
            }, 50);
            return;
        }
        
        // Mettre à jour l'UI desktop
        if (currentLangFlag && currentLangLabel) {
            currentLangFlag.src = `https://flagcdn.com/w20/${savedFlag}.png`;
            currentLangLabel.textContent = savedLabel;
        }
    }
})();

// Fonction pour ouvrir/fermer le dropdown
function toggleDropdown() {
    if (langDropdown) {
        langDropdown.classList.toggle('active');
    }
}

// Fermer le dropdown en cliquant ailleurs
document.addEventListener('click', function(event) {
    if (langBtn && langDropdown && !langBtn.contains(event.target) && !langDropdown.contains(event.target)) {
        langDropdown.classList.remove('active');
    }
});

// Ouvrir/fermer au clic sur le bouton
if (langBtn) {
    langBtn.addEventListener('click', function(event) {
        event.stopPropagation();
        toggleDropdown();
    });
}

// FONCTION PRINCIPALE DE CHANGEMENT DE LANGUE
function changeLanguage(langCode, flagCode, label) {
    // Mettre à jour l'UI
    if (currentLangFlag && currentLangLabel) {
        currentLangFlag.src = `https://flagcdn.com/w20/${flagCode}.png`;
        currentLangLabel.textContent = label;
    }
    
    // Sauvegarder dans localStorage
    localStorage.setItem('preferred_language', langCode);
    localStorage.setItem('preferred_flag', flagCode);
    localStorage.setItem('preferred_label', label);
    
    // Définir le cookie
    setLanguageCookie(langCode);
    
    // Fermer le dropdown
    if (langDropdown) {
        langDropdown.classList.remove('active');
    }
    
    // Afficher un message de chargement
    showToast('Changement de langue en cours...', 'info');
    
    // Recharger la page pour appliquer la traduction
    setTimeout(() => {
        window.location.reload();
    }, 200);
}

// Événements pour les options de langue
document.querySelectorAll('.lang-option').forEach(option => {
    option.addEventListener('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        const langCode = this.getAttribute('data-lang');
        const flagCode = this.getAttribute('data-flag');
        const label = this.getAttribute('data-label');
        changeLanguage(langCode, flagCode, label);
    });
});

// ============================================
// FORCER L'APPLICATION DE LA LANGUE APRÈS CHARGEMENT
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const savedLanguage = localStorage.getItem('preferred_language');
    const savedFlagCode = localStorage.getItem('preferred_flag');
    const savedLabelText = localStorage.getItem('preferred_label');
    
    if (savedLanguage && savedLanguage !== 'fr') {
        // Vérifier si le cookie googtrans est correctement défini
        const hasCookie = document.cookie.indexOf(`googtrans=/fr/${savedLanguage}`) !== -1;
        
        if (!hasCookie) {
            // Recréer le cookie
            setLanguageCookie(savedLanguage);
            // Recharger
            setTimeout(() => {
                window.location.reload();
            }, 100);
            return;
        }
        
        // Vérifier si la page est déjà traduite en regardant l'attribut lang du html
        const htmlLang = document.documentElement.getAttribute('lang');
        
        // Si l'attribut lang n'est pas à jour, on recharge
        if (htmlLang && htmlLang !== savedLanguage && htmlLang !== 'fr') {
            setTimeout(() => {
                window.location.reload();
            }, 100);
        }
    }
});

// ============================================
// SUPPRESSION DE LA BARRE GOOGLE TRANSLATE
// ============================================

function removeGoogleTranslateBar() {
    // Supprimer la bannière
    const banner = document.querySelector('.goog-te-banner-frame');
    if (banner) {
        if (banner.parentNode) {
            banner.parentNode.removeChild(banner);
        }
    }
    
    // Supprimer les iframes flottantes
    const iframes = document.querySelectorAll('iframe');
    iframes.forEach(iframe => {
        if (iframe.src && (iframe.src.includes('translate') || iframe.src.includes('goog'))) {
            iframe.remove();
        }
    });
    
    // Réinitialiser les marges du body
    document.body.style.marginTop = '0';
    document.body.style.top = '0';
    document.body.style.position = 'relative';
    document.body.style.paddingTop = '0';
    
    // Cacher l'élément Google Translate
    const translateElement = document.getElementById('google_translate_element');
    if (translateElement) {
        translateElement.style.display = 'none';
    }
}

// Exécuter immédiatement
removeGoogleTranslateBar();

// Exécuter plusieurs fois pour être sûr
setInterval(removeGoogleTranslateBar, 100);
setTimeout(removeGoogleTranslateBar, 500);
setTimeout(removeGoogleTranslateBar, 1000);
setTimeout(removeGoogleTranslateBar, 3000);


</script>
</body>
</html>