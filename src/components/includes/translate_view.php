<?php
// Fichier: translate_view.php
// À placer dans: application/views/includes/translate_view.php
?>

<!-- ============================================ -->
<!-- GOOGLE TRANSLATE - COMPOSANT RÉUTILISABLE -->
<!-- ============================================ -->

<style>
/* ============================================ */
/* CACHER COMPLÈTEMENT L'INTERFACE GOOGLE TRANSLATE */
/* ============================================ */

/* Cacher la barre en haut */
.goog-te-banner-frame.skiptranslate,
.goog-te-banner-frame,
.goog-te-banner,
.goog-te-banner-frame.skiptranslate iframe,
body > .skiptranslate {
    display: none !important;
    height: 0 !important;
    width: 0 !important;
    visibility: hidden !important;
    opacity: 0 !important;
    position: absolute !important;
    top: -9999px !important;
    left: -9999px !important;
    overflow: hidden !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Supprimer l'espace que Google Translate laisse en haut */
body {
    top: 0 !important;
    position: relative !important;
    margin-top: 0 !important;
    padding-top: 0 !important;
}

/* Cacher les notifications */
.goog-te-spinner-pos,
.goog-tooltip,
.goog-text-highlight,
.goog-te-balloon-frame,
.goog-te-balloon-frame div,
.yt-uix-overlay {
    display: none !important;
}

/* Forcer le body à ne pas avoir de marge */
body.skiptranslate {
    top: 0 !important;
    position: static !important;
    margin-top: 0 !important;
    padding-top: 0 !important;
}

/* Cacher les iframes flottantes */
iframe.goog-te-banner-frame,
div.goog-te-banner-frame,
div[class*="goog-te-banner"] {
    display: none !important;
}

/* Cacher le logo Google */
.goog-logo-link {
    display: none !important;
}

.goog-te-gadget {
    color: transparent !important;
    font-size: 0 !important;
}

/* Cacher la barre de chargement */
.goog-te-spinner-pos {
    display: none !important;
}

/* Réinitialiser les marges du body */
html body {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

/* ============================================ */
/* BOUTON DE LANGUE PERSONNALISÉ */
/* ============================================ */
.lang-selector-custom {
    position: relative;
    margin-left: 8px;
}

.custom-language-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #1a1a1a;
    transition: all 0.2s ease;
}

.custom-language-btn:hover {
    border-color: #d4af37;
    background: #e8f5f0;
}

.custom-language-btn img {
    width: 20px;
    height: 15px;
    border-radius: 2px;
    object-fit: cover;
}

.custom-language-btn i {
    font-size: 10px;
    color: #64748b;
    transition: transform 0.2s ease;
}

.custom-language-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    padding: 8px;
    min-width: 180px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    z-index: 1000;
    border: 1px solid #e2e8f0;
}

.custom-language-dropdown.active {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
}

.lang-option-custom {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    padding: 10px 12px !important;
    border-radius: 8px !important;
    text-decoration: none !important;
    color: #1a1a1a !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    width: 100% !important;
    border: none !important;
    background: transparent !important;
    text-align: left !important;
}

.lang-option-custom:hover {
    background: #e8f5f0 !important;
    color: #0f4c3a !important;
}

.lang-option-custom img {
    width: 20px !important;
    height: 15px !important;
    border-radius: 2px !important;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .custom-language-btn {
        padding: 6px 10px;
        font-size: 12px;
    }
    .custom-language-btn img {
        width: 18px;
        height: 12px;
    }
    .custom-language-dropdown {
        min-width: 160px;
    }
}
</style>

<!-- Google Translate caché (nécessaire pour la traduction) -->
<div class="google-translate-container" style="display: none;">
    <div id="google_translate_element"></div>
</div>

<script type="text/javascript">
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'fr',
        includedLanguages: 'fr,en,rn,sw,ar,de,es,pt,it,zh-CN,ru',
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, 'google_translate_element');
}
</script>
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<!-- BOUTON DE LANGUE PERSONNALISÉ -->
<div class="lang-selector-custom">
    <button class="custom-language-btn" id="customLanguageBtn">
        <img src="https://flagcdn.com/w20/fr.png" alt="Français" id="currentLangFlag">
        <span id="currentLangLabel">Français</span>
        <i class="bi bi-chevron-down"></i>
    </button>
    <div class="custom-language-dropdown" id="customLanguageDropdown">
        <button class="lang-option-custom" data-lang="fr" data-flag="fr" data-label="Français">
            <img src="https://flagcdn.com/w20/fr.png" alt="Français"> Français
        </button>
        <button class="lang-option-custom" data-lang="en" data-flag="us" data-label="English">
            <img src="https://flagcdn.com/w20/us.png" alt="English"> English
        </button>
        <button class="lang-option-custom" data-lang="rn" data-flag="bi" data-label="Kirundi">
            <img src="https://flagcdn.com/w20/bi.png" alt="Kirundi"> Kirundi
        </button>
        <button class="lang-option-custom" data-lang="sw" data-flag="tz" data-label="Kiswahili">
            <img src="https://flagcdn.com/w20/tz.png" alt="Kiswahili"> Kiswahili
        </button>
        <button class="lang-option-custom" data-lang="ar" data-flag="sa" data-label="العربية">
            <img src="https://flagcdn.com/w20/sa.png" alt="العربية"> العربية
        </button>
        <button class="lang-option-custom" data-lang="de" data-flag="de" data-label="Deutsch">
            <img src="https://flagcdn.com/w20/de.png" alt="Deutsch"> Deutsch
        </button>
        <button class="lang-option-custom" data-lang="es" data-flag="es" data-label="Español">
            <img src="https://flagcdn.com/w20/es.png" alt="Español"> Español
        </button>
        <button class="lang-option-custom" data-lang="pt" data-flag="pt" data-label="Português">
            <img src="https://flagcdn.com/w20/pt.png" alt="Português"> Português
        </button>
        <button class="lang-option-custom" data-lang="it" data-flag="it" data-label="Italiano">
            <img src="https://flagcdn.com/w20/it.png" alt="Italiano"> Italiano
        </button>
        <button class="lang-option-custom" data-lang="zh-CN" data-flag="cn" data-label="中文">
            <img src="https://flagcdn.com/w20/cn.png" alt="中文"> 中文
        </button>
        <button class="lang-option-custom" data-lang="ru" data-flag="ru" data-label="Русский">
            <img src="https://flagcdn.com/w20/ru.png" alt="Русский"> Русский
        </button>
    </div>
</div>

<script>
// ============================================
// GESTION DU BOUTON DE LANGUE PERSONNALISÉ
// ============================================

// Éléments DOM
const langBtn = document.getElementById('customLanguageBtn');
const langDropdown = document.getElementById('customLanguageDropdown');
const currentLangFlag = document.getElementById('currentLangFlag');
const currentLangLabel = document.getElementById('currentLangLabel');

// Vérifier si une langue est sauvegardée et l'appliquer
const savedLang = localStorage.getItem('preferred_language');
const savedFlag = localStorage.getItem('preferred_flag');
const savedLabel = localStorage.getItem('preferred_label');

// Appliquer la langue sauvegardée au chargement
if (savedLang && savedFlag && savedLabel && savedLang !== 'fr') {
    document.cookie = `googtrans=/fr/${savedLang}; path=/; max-age=31536000`;
}

// Fonction pour ouvrir/fermer le dropdown
function toggleDropdown() {
    if (langDropdown) langDropdown.classList.toggle('active');
}

// Fermer le dropdown si on clique ailleurs
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

// Fonction pour changer la langue
function changeLanguage(langCode, flagCode, label) {
    if (currentLangFlag) currentLangFlag.src = `https://flagcdn.com/w20/${flagCode}.png`;
    if (currentLangLabel) currentLangLabel.textContent = label;
    if (langDropdown) langDropdown.classList.remove('active');
    
    localStorage.setItem('preferred_language', langCode);
    localStorage.setItem('preferred_flag', flagCode);
    localStorage.setItem('preferred_label', label);
    
    document.cookie = `googtrans=/fr/${langCode}; path=/; max-age=31536000`;
    window.location.reload();
}

// Ajouter les événements de clic sur chaque option de langue
document.querySelectorAll('.lang-option-custom').forEach(option => {
    option.addEventListener('click', function(event) {
        event.stopPropagation();
        const langCode = this.getAttribute('data-lang');
        const flagCode = this.getAttribute('data-flag');
        const label = this.getAttribute('data-label');
        changeLanguage(langCode, flagCode, label);
    });
});

// Mettre à jour l'affichage du bouton avec la langue sauvegardée
if (savedLang && savedFlag && savedLabel && currentLangFlag && currentLangLabel) {
    currentLangFlag.src = `https://flagcdn.com/w20/${savedFlag}.png`;
    currentLangLabel.textContent = savedLabel;
}

// Supprimer la barre Google Translate immédiatement
setInterval(function() {
    var banner = document.querySelector('.goog-te-banner-frame');
    if (banner) {
        banner.style.display = 'none';
        banner.style.visibility = 'hidden';
        banner.style.height = '0';
    }
    document.body.style.marginTop = '0';
    document.body.style.top = '0';
    document.body.style.position = 'relative';
}, 100);

window.addEventListener('load', function() {
    setTimeout(function() {
        document.body.style.marginTop = '0';
        document.body.style.top = '0';
        document.body.style.position = 'relative';
        var banner = document.querySelector('.goog-te-banner-frame');
        if (banner) {
            banner.remove();
        }
    }, 500);
});
</script>